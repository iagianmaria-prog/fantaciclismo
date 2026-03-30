<?php

namespace App\Filament\Resources\RaceResource\Pages;

use App\Filament\Resources\RaceResource;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Rider;
use App\Models\RaceCreditRule;
use App\Models\RaceLineup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class ManageRaceResults extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use WithFileUploads;

    protected static string $resource = RaceResource::class;

    protected static string $view = 'filament.resources.race-resource.pages.manage-race-results';

    public $csvFile;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return "Risultati: {$this->record->name}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(RaceResult::query()->where('race_id', $this->record->id))
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label('Pos.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rider.name')
                    ->label('Corridore')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rider.category.name')
                    ->label('Categoria'),
                Tables\Columns\TextColumn::make('credits_earned')
                    ->label('Crediti')
                    ->suffix('M'),
            ])
            ->defaultSort('position')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('addResult')
                    ->label('Aggiungi Risultato')
                    ->form([
                        Forms\Components\Select::make('rider_id')
                            ->label('Corridore')
                            ->options(Rider::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('position')
                            ->label('Posizione')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('credits_earned')
                            ->label('Crediti')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->action(function (array $data): void {
                        RaceResult::updateOrCreate(
                            [
                                'race_id' => $this->record->id,
                                'rider_id' => $data['rider_id'],
                            ],
                            [
                                'position' => $data['position'],
                                'credits_earned' => $data['credits_earned'],
                            ]
                        );

                        Notification::make()
                            ->title('Risultato aggiunto')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => 'required|file',
        ]);

        try {
            $path = $this->csvFile->getRealPath();

            if (!$path || !file_exists($path)) {
                Notification::make()
                    ->title('Errore')
                    ->body('Impossibile leggere il file caricato.')
                    ->danger()
                    ->send();
                return;
            }

            $content = file_get_contents($path);

            // Rileva il delimitatore (virgola o punto e virgola)
            $delimiter = (substr_count($content, ';') > substr_count($content, ',')) ? ';' : ',';

            $handle = fopen($path, 'r');
            if (!$handle) {
                Notification::make()
                    ->title('Errore')
                    ->body('Impossibile aprire il file.')
                    ->danger()
                    ->send();
                return;
            }

            // Salta l'header
            fgetcsv($handle, 0, $delimiter);

            $imported = 0;
            $errors = [];

            DB::beginTransaction();

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Formato atteso: nome_corridore, posizione, crediti
                if (count($row) < 3) continue;

                // Salta righe vuote
                if (empty(trim($row[0]))) continue;

                $riderName = trim($row[0]);
                $position = (int) trim($row[1]);
                $credits = (int) trim($row[2]);

                // Cerca corridore per nome esatto o parziale
                $rider = Rider::where('name', $riderName)->first();
                if (!$rider) {
                    $rider = Rider::where('name', 'like', "%{$riderName}%")->first();
                }

                if (!$rider) {
                    $errors[] = "Corridore non trovato: {$riderName}";
                    continue;
                }

                RaceResult::updateOrCreate(
                    [
                        'race_id' => $this->record->id,
                        'rider_id' => $rider->id,
                    ],
                    [
                        'position' => $position,
                        'credits_earned' => $credits,
                    ]
                );

                $imported++;
            }

            fclose($handle);

            DB::commit();

            $message = "Importati {$imported} risultati.";
            if (!empty($errors)) {
                $message .= " Errori: " . implode(', ', array_slice($errors, 0, 5));
            }

            Notification::make()
                ->title('Import completato')
                ->body($message)
                ->success()
                ->send();

            $this->csvFile = null;

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Errore import')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function distributeCredits(): void
    {
        if (!$this->record->results()->exists()) {
            Notification::make()
                ->title('Nessun risultato')
                ->body('Inserisci prima i risultati della gara.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            $lineups = $this->record->lineups()->with(['playerTeam', 'riders'])->get();
            $creditsDistributed = 0;

            foreach ($lineups as $lineup) {
                $teamCredits = $lineup->calculateCreditsEarned();

                if ($teamCredits > 0) {
                    $lineup->playerTeam->addCredits($teamCredits);
                    $creditsDistributed += $teamCredits;
                }
            }

            // Imposta la gara come completata
            $this->record->status = 'completed';
            $this->record->save();

            DB::commit();

            Notification::make()
                ->title('Crediti distribuiti')
                ->body("Distribuiti {$creditsDistributed}M a " . count($lineups) . " squadre.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Errore distribuzione')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('distributeCredits')
                ->label('Distribuisci Crediti')
                ->icon('heroicon-o-currency-euro')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Distribuisci Crediti')
                ->modalDescription('Questa azione calcolerà i crediti guadagnati da ogni squadra in base alla formazione schierata e li aggiungerà al loro budget. La gara verrà segnata come completata.')
                ->action(fn () => $this->distributeCredits()),
        ];
    }
}
