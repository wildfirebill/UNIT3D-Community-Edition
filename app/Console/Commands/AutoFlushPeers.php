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

namespace App\Console\Commands;

use App\Models\History;
use App\Models\Peer;
use App\Models\Torrent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * @see \Tests\Unit\Console\Commands\AutoFlushPeersTest
 */
class AutoFlushPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:flush_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flushes Ghost Peers';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        $carbon = new Carbon();
        $peers = Peer::select(['id', 'torrent_id', 'user_id', 'seeder', 'updated_at'])
            ->where('updated_at', '<', $carbon->copy()->subHours(2)->toDateTimeString())
            ->where('active', '=', 1)
            ->get();

        foreach ($peers as $peer) {
            History::query()
                ->where('torrent_id', '=', $peer->torrent_id)
                ->where('user_id', '=', $peer->user_id)
                ->update([
                    'active'     => false,
                    'updated_at' => DB::raw('updated_at')
                ]);

            Torrent::where('id', '=', $peer->torrent_id)->update([
                'seeders'  => DB::raw('seeders - '.((int) $peer->seeder)),
                'leechers' => DB::raw('leechers - '.((int) !$peer->seeder)),
            ]);

            $peer->active = false;
            $peer->timestamps = false;
            $peer->save();
        }

        // Keep peers that stopped being announced without a `stopped` event
        // in case a user has internet issues and comes back online within the
        // next 2 days
        $peers = Peer::select(['id', 'user_id'])
            ->where('updated_at', '<', $carbon->copy()->subDays(2))
            ->where('active', '=', 0)
            ->get();

        foreach ($peers as $peer) {
            cache()->decrement('user-leeching-count:'.$peer->user_id);

            $peer->delete();
        }

        $this->comment('Automated Flush Ghost Peers Command Complete');
    }
}
