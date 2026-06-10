<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlayerTeam;
use App\Models\Rider;
use App\Models\RiderCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyRiderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private PlayerTeam $team;
    private RiderCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = PlayerTeam::create([
            'user_id' => $this->user->id,
            'name' => 'Test Team',
            'balance' => 100,
        ]);
        $this->category = RiderCategory::create(['name' => 'GC']);
    }

    private function makeFreeRider(int $value = 50): Rider
    {
        return Rider::create([
            'name' => 'Test Rider ' . uniqid(),
            'rider_category_id' => $this->category->id,
            'initial_value' => $value,
        ]);
    }

    public function test_player_can_buy_a_free_rider(): void
    {
        $rider = $this->makeFreeRider(50);

        $response = $this->actingAs($this->user)
            ->post(route('auction.buy', $rider));

        $response->assertSessionHas('status');
        $this->assertEquals($this->team->id, $rider->fresh()->player_team_id);
        $this->assertEquals(50, $this->team->fresh()->balance);
        $this->assertEquals(50, $rider->fresh()->purchase_price);
        $this->assertNotNull($rider->fresh()->contract_years);
    }

    public function test_purchase_uses_current_value_when_set(): void
    {
        $rider = $this->makeFreeRider(50);
        $rider->update(['current_value' => 80]);

        $this->actingAs($this->user)->post(route('auction.buy', $rider));

        $this->assertEquals(80, $rider->fresh()->purchase_price);
        $this->assertEquals(20, $this->team->fresh()->balance);
    }

    public function test_cannot_buy_rider_already_owned(): void
    {
        $otherTeam = PlayerTeam::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Other Team',
            'balance' => 100,
        ]);
        $rider = $this->makeFreeRider(50);
        $rider->update(['player_team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->post(route('auction.buy', $rider));

        $response->assertSessionHas('error');
        $this->assertEquals($otherTeam->id, $rider->fresh()->player_team_id);
        $this->assertEquals(100, $this->team->fresh()->balance);
    }

    public function test_cannot_buy_rider_with_insufficient_budget(): void
    {
        $rider = $this->makeFreeRider(150);

        $response = $this->actingAs($this->user)
            ->post(route('auction.buy', $rider));

        $response->assertSessionHas('error');
        $this->assertNull($rider->fresh()->player_team_id);
        $this->assertEquals(100, $this->team->fresh()->balance);
    }

    public function test_cannot_exceed_category_limit(): void
    {
        // Il default di max_gc è 8: riempie la rosa fino al limite
        for ($i = 0; $i < 8; $i++) {
            Rider::create([
                'name' => "Owned Rider {$i}",
                'rider_category_id' => $this->category->id,
                'initial_value' => 1,
                'player_team_id' => $this->team->id,
            ]);
        }

        $rider = $this->makeFreeRider(10);

        $response = $this->actingAs($this->user)
            ->post(route('auction.buy', $rider));

        $response->assertSessionHas('error');
        $this->assertNull($rider->fresh()->player_team_id);
    }

    public function test_repair_auction_assigns_repair_contract_duration(): void
    {
        Auction::create([
            'name' => 'Asta Riparazione',
            'type' => 'repair',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'status' => 'open',
        ]);

        $rider = $this->makeFreeRider(50);

        $this->actingAs($this->user)->post(route('auction.buy', $rider));

        // Default contract_duration_repair = 1.5 → cast (int) = 1
        $this->assertEquals(1, $rider->fresh()->contract_years);
    }

    public function test_user_without_team_is_redirected(): void
    {
        $userWithoutTeam = User::factory()->create();
        $rider = $this->makeFreeRider(50);

        $response = $this->actingAs($userWithoutTeam)
            ->post(route('auction.buy', $rider));

        $response->assertRedirect(route('player-team.create'));
        $this->assertNull($rider->fresh()->player_team_id);
    }
}
