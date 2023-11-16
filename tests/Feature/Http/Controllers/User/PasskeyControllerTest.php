<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

use App\Models\User;

test('edit returns an ok response', function (): void {
    $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

    $user = User::factory()->create();
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser)->get(route('users.passkeys.index', [$user]));

    $response->assertOk();
    $response->assertViewIs('user.passkey.index');
    $response->assertViewHas('user', $user);

    // TODO: perform additional assertions
});

test('edit aborts with a 403', function (): void {
    $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

    $user = User::factory()->create();
    $authUser = User::factory()->create();

    // TODO: perform additional setup to trigger `abort_unless(403)`...

    $response = $this->actingAs($authUser)->get(route('users.passkeys.index', [$user]));

    $response->assertForbidden();
});

// test cases...
