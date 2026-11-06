<?php

namespace Tests\Feature;

use Tests\TestCase;

class MidtransConfigTest extends TestCase
{
    public function test_public_midtrans_config_does_not_expose_server_key(): void
    {
        $response = $this->getJson('/api/midtrans/config');

        $response->assertOk()
            ->assertJsonMissingPath('server_key')
            ->assertJsonStructure([
                'enabled',
                'is_production',
                'merchant_id',
                'client_key',
                'has_server_key',
                'snap_js',
            ]);
    }
}
