<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\AnnounceController
 */
class AnnounceControllerTest extends TestCase
{
    /** @test */
    public function index_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $response = $this->get(route('announce', ['passkey' => $passkey]));

        $response->assertOk();

        // TODO: perform additional assertions
    }

    // test cases...
}
