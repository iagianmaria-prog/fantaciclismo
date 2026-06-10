<?php

namespace Tests\Feature;

use App\Models\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_auction_opens_automatically_when_start_time_passes(): void
    {
        $auction = Auction::create([
            'name' => 'Asta Test',
            'type' => 'initial',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => 'scheduled',
        ]);

        $active = Auction::active();

        $this->assertNotNull($active);
        $this->assertEquals($auction->id, $active->id);
        $this->assertEquals('open', $auction->fresh()->status);
    }

    public function test_open_auction_closes_automatically_when_end_time_passes(): void
    {
        $auction = Auction::create([
            'name' => 'Asta Scaduta',
            'type' => 'initial',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subMinute(),
            'status' => 'open',
        ]);

        $active = Auction::active();

        $this->assertNull($active);
        $this->assertEquals('closed', $auction->fresh()->status);
    }

    public function test_future_auction_stays_scheduled(): void
    {
        $auction = Auction::create([
            'name' => 'Asta Futura',
            'type' => 'repair',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(3),
            'status' => 'scheduled',
        ]);

        $active = Auction::active();

        $this->assertNull($active);
        $this->assertEquals('scheduled', $auction->fresh()->status);
    }
}
