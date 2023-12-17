<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     Roardom <roardom@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Console\Commands;

use App\Models\Peer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Exception;

/**
 * @see \Tests\Unit\Console\Commands\AutoFlushPeersTest
 */
class AutoUpsertPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:upsert_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upserts peers in batches';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        /**
         * MySql can handle a max of 65k placeholders per query,
         * and there are 15 fields on each peer that are updated.
         * (`active`, `agent`, `connectable`, `created_at`, `downloaded`, `id`, `ip`, `left`, `peer_id`, `port`, `seeder`, `torrent_id`, `updated_at`, `uploaded`, `user_id`).
         */
        $peerPerCycle = intdiv(65_000, 15);

        $key = config('cache.prefix').':peers:batch';
        $peerCount = Redis::connection('announce')->command('LLEN', [$key]);

        for ($peersLeft = $peerCount; $peersLeft > 0; $peersLeft -= $peerPerCycle) {
            $peers = Redis::connection('announce')->command('LPOP', [$key, $peerPerCycle]);

            if ($peers === false) {
                break;
            }

            $peers = array_map('unserialize', $peers);

            DB::transaction(function () use ($peers): void {
                Peer::upsert(
                    $peers,
                    ['user_id', 'torrent_id', 'peer_id'],
                    [
                        'peer_id',
                        'ip',
                        'port',
                        'agent',
                        'uploaded',
                        'downloaded',
                        'left',
                        'seeder',
                        'torrent_id',
                        'user_id',
                        'connectable',
                        'active'
                    ],
                );
            }, 5);
        }

        $this->comment('Automated insert peers command complete');
    }
}
