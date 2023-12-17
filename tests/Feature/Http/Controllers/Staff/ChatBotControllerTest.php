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

use App\Enums\UserGroups;
use App\Http\Controllers\Staff\ChatBotController;
use App\Http\Requests\Staff\UpdateChatBotRequest;
use App\Models\Bot;
use App\Models\Group;
use App\Models\User;
use Database\Seeders\GroupsTableSeeder;

beforeEach(function (): void {
    $this->staffUser = User::factory()->create([
        'group_id' => fn () => Group::factory()->create([
            'is_owner' => true,
            'is_admin' => true,
            'is_modo'  => true,
        ])->id,
    ]);
});

test('destroy returns an ok response', function (): void {
    $bot = Bot::factory()->create([
        'is_protected' => false,
    ]);

    $response = $this->actingAs($this->staffUser)->delete(route('staff.bots.destroy', ['bot' => $bot]));
    $response->assertRedirect(route('staff.bots.index'))->assertSessionHas('success', 'The Humans Vs Machines War Has Begun! Humans: 1 and Bots: 0');

    $this->assertModelMissing($bot);
});

test('destroy aborts with a 403', function (): void {
    $this->seed(GroupsTableSeeder::class);

    $user = User::factory()->create([
        'group_id' => UserGroups::USER->value,
    ]);

    $bot = Bot::factory()->create();

    $response = $this->actingAs($user)->delete(route('staff.bots.destroy', [$bot]));
    $response->assertForbidden();
});

test('disable returns an ok response', function (): void {
    $bot = Bot::factory()->create([
        'active' => true,
    ]);

    $response = $this->actingAs($this->staffUser)->post(route('staff.bots.disable', [$bot]), [
        'active' => false,
    ]);
    $response->assertRedirect(route('staff.bots.index'))->assertSessionHas('success', 'The Bot Has Been Disabled');
});

test('edit returns an ok response', function (): void {
    $bot = Bot::factory()->create();

    $response = $this->actingAs($this->staffUser)->get(route('staff.bots.edit', [$bot]));
    $response->assertOk();
    $response->assertViewIs('Staff.chat.bot.edit');
    $response->assertViewHas('bot', $bot);
});

test('enable returns an ok response', function (): void {
    $bot = Bot::factory()->create([
        'active' => false,
    ]);

    $response = $this->actingAs($this->staffUser)->post(route('staff.bots.enable', [$bot]), [
        'active' => true,
    ]);
    $response->assertRedirect(route('staff.bots.index'))->assertSessionHas('success', 'The Bot Has Been Enabled');
});

test('index returns an ok response', function (): void {
    $response = $this->actingAs($this->staffUser)->get(route('staff.bots.index'));
    $response->assertOk();
    $response->assertViewIs('Staff.chat.bot.index');
});

test('update validates with a form request', function (): void {
    $this->assertActionUsesFormRequest(
        ChatBotController::class,
        'update',
        UpdateChatBotRequest::class
    );
});

test('update returns an ok response', function (): void {
    $bot = Bot::factory()->create([
        'is_protected' => false,
    ]);

    $response = $this->actingAs($this->staffUser)->patch(route('staff.bots.update', [$bot]), [
        'position'     => $bot->position,
        'name'         => $bot->name,
        'command'      => $bot->command,
        'color'        => $bot->color,
        'icon'         => $bot->icon,
        'emoji'        => $bot->emoji,
        'info'         => $bot->info,
        'about'        => $bot->about,
        'help'         => $bot->help,
        'active'       => true,
        'is_protected' => $bot->is_protected,
        'is_triviabot' => $bot->is_triviabot,
        'is_nerdbot'   => $bot->is_nerdbot,
        'is_systembot' => $bot->is_systembot,
        'is_casinobot' => $bot->is_casinobot,
        'is_betbot'    => $bot->is_betbot,
        'uploaded'     => $bot->uploaded,
        'downloaded'   => $bot->downloaded,
        'fl_tokens'    => $bot->fl_tokens,
        'seedbonus'    => $bot->seedbonus,
        'invites'      => $bot->invites,
    ]);
    $response->assertRedirect(route('staff.bots.index'))->assertSessionHas('success', 'The Bot Has Been Updated');
});
