<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ReservationApiRouteTest extends TestCase
{
    public function test_reservation_api_persists_session_state(): void
    {
        $response = $this->postJson('/api/reservation', [
            'action' => 'get_reservation',
            'step' => 'vehicle',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('step', 'vehicle');

        $this->postJson('/api/reservation', [
            'action' => 'reset_reservation',
        ])->assertOk();

        $this->postJson('/api/reservation', [
            'action' => 'get_reservation',
        ])->assertOk()->assertExactJson([]);
    }
}
