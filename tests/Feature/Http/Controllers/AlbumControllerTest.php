<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\AlbumController
 */
class AlbumControllerTest extends TestCase
{
    /** @test */
    public function create_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $response = $this->get(route('albums.create'));

        $response->assertOk();
        $response->assertViewIs('album.create');

        // TODO: perform additional assertions
    }

    /** @test */
    public function destroy_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $album = \App\Models\Album::factory()->create();

        $response = $this->delete(route('albums.destroy', ['id' => $album->id]));

        $response->assertOk();
        $this->assertDeleted($album);

        // TODO: perform additional assertions
    }

    /** @test */
    public function index_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $albums = \App\Models\Album::factory()->times(3)->create();

        $response = $this->get(route('albums.index'));

        $response->assertOk();
        $response->assertViewIs('album.index');
        $response->assertViewHas('albums', $albums);

        // TODO: perform additional assertions
    }

    /** @test */
    public function show_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $album = \App\Models\Album::factory()->create();
        $albums = \App\Models\Album::factory()->times(3)->create();

        $response = $this->get(route('albums.show', ['id' => $album->id]));

        $response->assertOk();
        $response->assertViewIs('album.show');
        $response->assertViewHas('album', $album);
        $response->assertViewHas('albums', $albums);

        // TODO: perform additional assertions
    }

    /** @test */
    public function store_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $movie = \App\Models\Movie::factory()->create();

        $response = $this->post(route('albums.store'), [
            // TODO: send request data
        ]);

        $response->assertOk();

        // TODO: perform additional assertions
    }

    // test cases...
}
