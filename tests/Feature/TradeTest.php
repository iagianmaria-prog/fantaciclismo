<?php

namespace Tests\Feature;

use App\Livewire\CreateTradeForm;
use App\Models\PlayerTeam;
use App\Models\Rider;
use App\Models\RiderCategory;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TradeTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private PlayerTeam $teamA;
    private PlayerTeam $teamB;
    private RiderCategory $category;
    private Rider $riderA;
    private Rider $riderB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();

        $this->teamA = PlayerTeam::create([
            'user_id' => $this->userA->id,
            'name' => 'Team A',
            'balance' => 100,
        ]);
        $this->teamB = PlayerTeam::create([
            'user_id' => $this->userB->id,
            'name' => 'Team B',
            'balance' => 100,
        ]);

        $this->category = RiderCategory::create(['name' => 'GC']);

        $this->riderA = Rider::create([
            'name' => 'Rider of A',
            'rider_category_id' => $this->category->id,
            'initial_value' => 30,
            'player_team_id' => $this->teamA->id,
        ]);
        $this->riderB = Rider::create([
            'name' => 'Rider of B',
            'rider_category_id' => $this->category->id,
            'initial_value' => 40,
            'player_team_id' => $this->teamB->id,
        ]);
    }

    /**
     * Crea uno scambio pendente: A offre riderA, chiede riderB.
     */
    private function makePendingTrade(int $moneyAdjustment = 0): Trade
    {
        $trade = Trade::create([
            'offering_team_id' => $this->teamA->id,
            'receiving_team_id' => $this->teamB->id,
            'money_adjustment' => $moneyAdjustment,
            'status' => 'pending',
        ]);
        $trade->riders()->attach($this->riderA->id, ['direction' => 'offering']);
        $trade->riders()->attach($this->riderB->id, ['direction' => 'receiving']);

        return $trade;
    }

    public function test_accepting_trade_swaps_riders(): void
    {
        $trade = $this->makePendingTrade();

        $response = $this->actingAs($this->userB)
            ->post(route('market.accept', $trade));

        $response->assertSessionHas('status');
        $this->assertEquals('accepted', $trade->fresh()->status);
        $this->assertEquals($this->teamB->id, $this->riderA->fresh()->player_team_id);
        $this->assertEquals($this->teamA->id, $this->riderB->fresh()->player_team_id);
    }

    public function test_accepting_trade_transfers_credits(): void
    {
        // money_adjustment > 0: chi accetta (B) paga, chi propone (A) riceve
        $trade = $this->makePendingTrade(20);

        $this->actingAs($this->userB)->post(route('market.accept', $trade));

        $this->assertEquals(120, $this->teamA->fresh()->balance);
        $this->assertEquals(80, $this->teamB->fresh()->balance);
    }

    public function test_cannot_accept_trade_addressed_to_another_team(): void
    {
        $trade = $this->makePendingTrade();

        $response = $this->actingAs($this->userA)
            ->post(route('market.accept', $trade));

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $trade->fresh()->status);
    }

    public function test_cannot_accept_trade_twice(): void
    {
        $trade = $this->makePendingTrade(20);

        $this->actingAs($this->userB)->post(route('market.accept', $trade));
        $response = $this->actingAs($this->userB)->post(route('market.accept', $trade));

        $response->assertSessionHas('error');
        // I saldi devono riflettere UN solo trasferimento
        $this->assertEquals(120, $this->teamA->fresh()->balance);
        $this->assertEquals(80, $this->teamB->fresh()->balance);
    }

    public function test_cannot_accept_trade_when_offered_rider_was_sold(): void
    {
        $trade = $this->makePendingTrade();

        // Il corridore offerto non appartiene più alla squadra proponente
        $this->riderA->update(['player_team_id' => null]);

        $response = $this->actingAs($this->userB)
            ->post(route('market.accept', $trade));

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $trade->fresh()->status);
        $this->assertEquals($this->teamB->id, $this->riderB->fresh()->player_team_id);
    }

    public function test_trade_form_rejects_negative_credits(): void
    {
        Livewire::actingAs($this->userA)
            ->test(CreateTradeForm::class)
            ->set('selectedTeamId', $this->teamB->id)
            ->set('requestedRiderIds', [$this->riderB->id])
            ->set('moneyRequest', -50)
            ->call('submitTrade');

        $this->assertEquals(0, Trade::count());
    }

    public function test_trade_form_rejects_riders_not_owned_by_proposer(): void
    {
        Livewire::actingAs($this->userA)
            ->test(CreateTradeForm::class)
            ->set('selectedTeamId', $this->teamB->id)
            // Prova a offrire il corridore di B come se fosse suo
            ->set('offeredRiderIds', [$this->riderB->id])
            ->call('submitTrade');

        $this->assertEquals(0, Trade::count());
    }

    public function test_trade_form_creates_pending_trade(): void
    {
        Livewire::actingAs($this->userA)
            ->test(CreateTradeForm::class)
            ->set('selectedTeamId', $this->teamB->id)
            ->set('offeredRiderIds', [$this->riderA->id])
            ->set('requestedRiderIds', [$this->riderB->id])
            ->call('submitTrade');

        $this->assertEquals(1, Trade::count());
        $trade = Trade::first();
        $this->assertEquals('pending', $trade->status);
        $this->assertEquals($this->teamA->id, $trade->offering_team_id);
        $this->assertEquals($this->teamB->id, $trade->receiving_team_id);
    }
}
