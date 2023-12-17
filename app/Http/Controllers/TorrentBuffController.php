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

namespace App\Http\Controllers;

use App\Bots\IRCAnnounceBot;
use App\Models\FeaturedTorrent;
use App\Models\FreeleechToken;
use App\Models\Scopes\ApprovedScope;
use App\Models\Torrent;
use App\Repositories\ChatRepository;
use App\Services\Unit3dAnnounce;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\TorrentControllerTest
 */
class TorrentBuffController extends Controller
{
    /**
     * TorrentController Constructor.
     */
    public function __construct(private readonly ChatRepository $chatRepository)
    {
    }

    /**
     * Bump A Torrent.
     */
    public function bumpTorrent(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo || $user->group->is_internal, 403);
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrent->bumped_at = Carbon::now();
        $torrent->save();

        // Announce To Chat
        $torrentUrl = href_torrent($torrent);
        $profileUrl = href_profile($user);

        $this->chatRepository->systemMessage(
            sprintf('Attention, [url=%s]%s[/url] has been bumped to the top by [url=%s]%s[/url]! It could use more seeds!', $torrentUrl, $torrent->name, $profileUrl, $user->username)
        );

        // Announce To IRC
        if (config('irc-bot.enabled')) {
            $appname = config('app.name');
            $ircAnnounceBot = new IRCAnnounceBot();
            $ircAnnounceBot->message(config('irc-bot.channel'), '['.$appname.'] User '.$user->username.' has bumped '.$torrent->name.' , it could use more seeds!');
            $ircAnnounceBot->message(config('irc-bot.channel'), '[Category: '.$torrent->category->name.'] [Type: '.$torrent->type->name.'] [Size:'.$torrent->getSize().']');
            $ircAnnounceBot->message(config('irc-bot.channel'), sprintf('[Link: %s]', $torrentUrl));
        }

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Torrent Has Been Bumped To The Top Successfully!');
    }

    /**
     * Sticky A Torrent.
     */
    public function sticky(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo || $user->group->is_internal, 403);
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrent->sticky = !$torrent->sticky;
        $torrent->save();

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Torrent Sticky Status Has Been Adjusted!');
    }

    /**
     * Freeleech A Torrent (1% to 100% Free).
     */
    public function grantFL(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo || $user->group->is_internal, 403);
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrentUrl = href_torrent($torrent);

        $request->validate([
            'freeleech' => 'numeric|min:0|max:100',
            'fl_until'  => 'nullable|numeric'
        ]);

        if ($request->freeleech != 0) {
            if ($request->fl_until !== null) {
                $torrent->fl_until = Carbon::now()->addDays($request->fl_until);
                $this->chatRepository->systemMessage(
                    sprintf('Ladies and Gents, [url=%s]%s[/url] has been granted %s%% FreeLeech for '.$request->fl_until.' days. :stopwatch:', $torrentUrl, $torrent->name, $request->freeleech)
                );
            } else {
                $this->chatRepository->systemMessage(
                    sprintf('Ladies and Gents, [url=%s]%s[/url] has been granted %s%% FreeLeech! Grab It While You Can! :fire:', $torrentUrl, $torrent->name, $request->freeleech)
                );
            }
        } elseif ($torrent->free != 0) {
            $this->chatRepository->systemMessage(
                sprintf('Ladies and Gents, [url=%s]%s[/url] has been revoked of its %s%% FreeLeech! :poop:', $torrentUrl, $torrent->name, $torrent->free)
            );
        }

        $torrent->free = $request->freeleech;
        $torrent->save();

        cache()->forget('announce-torrents:by-infohash:'.$torrent->info_hash);

        Unit3dAnnounce::addTorrent($torrent);

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Torrent FL Has Been Adjusted!');
    }

    /**
     * Feature A Torrent.
     */
    public function grantFeatured(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo || $user->group->is_internal, 403);
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);

        if ($torrent->featured == 0) {
            $torrent->free = 100;
            $torrent->doubleup = true;
            $torrent->featured = true;
            $torrent->save();

            cache()->forget('announce-torrents:by-infohash:'.$torrent->info_hash);

            Unit3dAnnounce::addTorrent($torrent);

            $featured = new FeaturedTorrent();
            $featured->user_id = $user->id;
            $featured->torrent_id = $torrent->id;
            $featured->save();

            $torrentUrl = href_torrent($torrent);
            $profileUrl = href_profile($user);
            $this->chatRepository->systemMessage(
                sprintf('Ladies and Gents, [url=%s]%s[/url] has been added to the Featured Torrents Slider by [url=%s]%s[/url]! Grab It While You Can! :fire:', $torrentUrl, $torrent->name, $profileUrl, $user->username)
            );

            return to_route('torrents.show', ['id' => $torrent->id])
                ->withSuccess('Torrent Is Now Featured!');
        }

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withErrors('Torrent Is Already Featured!');
    }

    /**
     * UnFeature A Torrent.
     */
    public function revokeFeatured(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo, 403);

        $featured_torrent = FeaturedTorrent::where('torrent_id', '=', $id)->sole();

        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrent->free = 0;
        $torrent->doubleup = false;
        $torrent->featured = false;
        $torrent->save();

        cache()->forget('announce-torrents:by-infohash:'.$torrent->info_hash);

        Unit3dAnnounce::addTorrent($torrent);

        $appurl = config('app.url');

        $this->chatRepository->systemMessage(
            sprintf('Ladies and Gents, [url=%s/torrents/%s]%s[/url] is no longer featured. :poop:', $appurl, $torrent->id, $torrent->name)
        );

        $featured_torrent->delete();

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Revoked featured from Torrent!');
    }

    /**
     * Double Upload A Torrent.
     */
    public function grantDoubleUp(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->group->is_modo || $user->group->is_internal, 403);
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrentUrl = href_torrent($torrent);

        if (!$torrent->doubleup) {
            $torrent->doubleup = true;
            $du_until = $request->input('du_until');

            if ($du_until !== null) {
                $torrent->du_until = Carbon::now()->addDays($request->input('du_until'));
                $this->chatRepository->systemMessage(
                    sprintf('Ladies and Gents, [url=%s]%s[/url] has been granted Double Upload for '.$request->input('du_until').' days. :stopwatch:', $torrentUrl, $torrent->name)
                );
            } else {
                $this->chatRepository->systemMessage(
                    sprintf('Ladies and Gents, [url=%s]%s[/url] has been granted Double Upload! Grab It While You Can! :fire:', $torrentUrl, $torrent->name)
                );
            }
        } else {
            $torrent->doubleup = false;
            $this->chatRepository->systemMessage(
                sprintf('Ladies and Gents, [url=%s]%s[/url] has been revoked of its Double Upload! :poop:', $torrentUrl, $torrent->name)
            );
        }

        $torrent->save();

        cache()->forget('announce-torrents:by-infohash:'.$torrent->info_hash);

        Unit3dAnnounce::addTorrent($torrent);

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Torrent DoubleUpload Has Been Adjusted!');
    }

    /**
     * Use Freeleech Token On A Torrent.
     */
    public function freeleechToken(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);

        $activeToken = cache()->get('freeleech_token:'.$user->id.':'.$torrent->id);

        if ($user->fl_tokens >= 1 && !$activeToken) {
            $freeleechToken = new FreeleechToken();
            $freeleechToken->user_id = $user->id;
            $freeleechToken->torrent_id = $torrent->id;
            $freeleechToken->save();

            Unit3dAnnounce::addFreeleechToken($user->id, $torrent->id);

            $user->fl_tokens -= '1';
            $user->save();

            cache()->put('freeleech_token:'.$user->id.':'.$torrent->id, true);

            return to_route('torrents.show', ['id' => $torrent->id])
                ->withSuccess('You Have Successfully Activated A Freeleech Token For This Torrent!');
        }

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withErrors('You Dont Have Enough Freeleech Tokens Or Already Have One Activated On This Torrent.');
    }

    /**
     * Set Torrents Refudable Status.
     */
    public function setRefundable(Request $request, $id)
    {
        $user = $request->user();
        abort_unless($user->group->is_modo || $user->group->is_internal, 403);

        $torrent = Torrent::withoutGlobalScope(ApprovedScope::class)->findOrFail($id);
        $torrent_url = href_torrent($torrent);

        if (!$torrent->refundable) {
            $torrent->refundable = true;

            $this->chatRepository->systemMessage(
                sprintf('Ladies and Gents, [url=%s]%s[/url] is now refundable! Grab It While You Can! :fire:', $torrent_url, $torrent->name)
            );
        } else {
            $torrent->refundable = 0;

            $this->chatRepository->systemMessage(
                sprintf('Ladies and Gents, [url=%s]%s[/url] is no longer refundable! :poop:', $torrent_url, $torrent->name)
            );
        }

        $torrent->save();

        return to_route('torrents.show', ['id' => $torrent->id])
            ->withSuccess('Torrent\'s Refundable Status Has Been Adjusted!');
    }
}
