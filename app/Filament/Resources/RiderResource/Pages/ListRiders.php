<?php

namespace App\Filament\Resources\RiderResource\Pages;

use App\Filament\Resources\RiderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use App\Models\RiderCategory;
use App\Models\Rider;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Exception;
use Livewire\WithFileUploads;

class ListRiders extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = RiderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importRiders')
                ->label('Importa Corridori da CSV')
                ->color('primary')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('File CSV dei Corridori')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'text/plain'])
                        // QUESTA È LA MODIFICA CHIAVE
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    // ORA $data['csv_file'] CONTERRÀ UN OGGETTO TEMPORANEO
                    // DI CUI POSSIAMO LEGGERE IL PERCORSO REALE.

                    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
                    $file = $data['csv_file'];

                    // Usiamo getRealPath() che ora dovrebbe funzionare.
                    $filePath = $file->getRealPath();

                    if (($handle = fopen($filePath, 'r')) === FALSE) {
                        Notification::make()->title('Errore')->body('Impossibile aprire il file CSV.')->danger()->send();
                        return;
                    }

                    DB::beginTransaction();
                    try {
                        $header = fgetcsv($handle, 1000, ';');
                        $importedCount = 0;
                        $rowNumber = 1;

                        while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                            $rowNumber++;
                            if (count($header) !== count($row)) {
                                throw new Exception("Il numero di colonne alla riga {$rowNumber} non corrisponde all'intestazione.");
                            }
                            $rowData = array_combine($header, $row);

                            $fullName = trim($rowData['Cognome'] . ' ' . $rowData['Nome']);
                            $category = RiderCategory::firstOrCreate(['name' => trim($rowData['Categoria'])]);

                            Rider::create([
                                'name' => $fullName,
                                'real_team' => trim($rowData['Team_Ufficiale']),
                                'rider_category_id' => $category->id,
                                'initial_value' => (int)trim($rowData['Prezzo']),
                            ]);

                            $importedCount++;
                        }

                        fclose($handle);
                        DB::commit();

                        Notification::make()
                            ->title('Importazione Completata')
                            ->body("{$importedCount} corridori sono stati importati con successo.")
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        DB::rollBack();
                        $errorMessage = "Errore durante l'importazione alla riga {$rowNumber}: " . $e->getMessage();
                        Notification::make()
                            ->title('Errore di Importazione')
                            ->body($errorMessage)
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
