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

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\InviteUser;
use App\Models\Invite;
use App\Models\User;
use App\Rules\EmailBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Exception;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\InviteControllerTest
 */
class InviteController extends Controller
{
    /**
     * Invite Tree.
     */
    public function index(Request $request, User $user): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        abort_unless($request->user()->group->is_modo || $request->user()->is($user), 403);

        return view('user.invite.index', [
            'user'    => $user,
            'invites' => $user->sentInvites()->withTrashed()->with(['sender.group', 'receiver.group'])->latest()->paginate(25),
        ]);
    }

    /**
     * Invite Form.
     */
    public function create(Request $request, User $user): \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->is($user), 403);

        if (!config('other.invite-only')) {
            return to_route('home.index')
                ->withErrors(trans('user.invites-disabled'));
        }

        if ($user->can_invite == 0) {
            return to_route('home.index')
                ->withErrors(trans('user.invites-banned'));
        }

        if (config('other.invites_restriced') && !\in_array($user->group->name, config('other.invite_groups'), true)) {
            return to_route('home.index')
                ->withErrors(trans('user.invites-disabled-group'));
        }

        return view('user.invite.create', ['user' => $user]);
    }

    /**
     * Send Invite.
     *
     * @throws Exception
     */
    public function store(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->is($user) && $user->can_invite, 403);

        if (config('other.invites_restriced') && !\in_array($user->group->name, config('other.invite_groups'), true)) {
            return to_route('home.index')
                ->withErrors(trans('user.invites-disabled-group'));
        }

        if ($user->invites <= 0) {
            return to_route('users.invites.create', ['user' => $user])
                ->withErrors(trans('user.not-enough-invites'));
        }

        $request->validate([
            'bail',
            'message' => 'required',
            'email'   => [
                'required',
                'string',
                'email',
                'max:70',
                'unique:invites',
                'unique:users',
                'unique:applications',
                Rule::when(config('email-blacklist.enabled'), fn () => new EmailBlacklist())
            ],
        ]);

        $user->decrement('invites');

        $invite = Invite::create([
            'user_id'    => $user->id,
            'email'      => $request->input('email'),
            'code'       => Uuid::uuid4()->toString(),
            'expires_on' => now()->addDays(config('other.invite_expire')),
            'custom'     => $request->input('message'),
        ]);

        Mail::to($request->input('email'))->send(new InviteUser($invite));

        return to_route('users.invites.create', ['user' => $user])
            ->withSuccess(trans('user.invite-sent-success'));
    }

    /**
     * Retract a sent invite.
     */
    public function destroy(Request $request, User $user, Invite $sentInvite): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->group->is_modo || ($request->user()->is($user) && $user->can_invite), 403);

        if ($sentInvite->accepted_by !== null) {
            return to_route('users.invites.index', ['user' => $user])
                ->withErrors(trans('user.invite-already-used'));
        }

        if ($sentInvite->expires_on < now()) {
            return to_route('users.invites.index', ['user' => $user])
                ->withErrors(trans('user.invite-expired'));
        }

        $sentInvite->delete();

        return to_route('users.invites.index', ['user' => $user])
            ->withSuccess('Invite deleted successfully.');
    }

    /**
     * Resend Invite.
     */
    public function send(Request $request, User $user, Invite $sentInvite): \Illuminate\Http\RedirectResponse
    {
        abort_unless($request->user()->group->is_modo || ($request->user()->is($user) && $user->can_invite), 403);

        if ($sentInvite->accepted_by !== null) {
            return to_route('users.invites.index', ['user' => $user])
                ->withErrors(trans('user.invite-already-used'));
        }

        if ($sentInvite->expires_on < now()) {
            return to_route('users.invites.index', ['user' => $user])
                ->withErrors(trans('user.invite-expired'));
        }

        Mail::to($sentInvite->email)->send(new InviteUser($sentInvite));

        return to_route('users.invites.index', ['user' => $user])
            ->withSuccess(trans('user.invite-resent-success'));
    }
}
