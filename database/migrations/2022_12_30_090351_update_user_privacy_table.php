<?php

use App\Enums\UserGroups;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allowedGroups = DB::table('groups')
            ->where('is_modo', '=', '0')
            ->where('is_admin', '=', '0')
            ->where('id', '!=', UserGroups::VALIDATING->value)
            ->where('id', '!=', UserGroups::PRUNED->value)
            ->where('id', '!=', UserGroups::BANNED->value)
            ->where('id', '!=', UserGroups::DISABLED->value)
            ->pluck('id')
            ->toArray();

        //
        // Input format looks like ("1" means accepts notifications, "0" means blocks notifications):
        // {
        //   "default_groups": {
        //     "1": 0,
        //     "2": 1,
        //     "3": 0,
        //     "4": 1
        //   }
        // }
        //
        // Output format looks like (Presence means blocks notifications)
        // [
        //   1,
        //   3
        // ]
        //

        $migrate = function ($jsonGroups) use ($allowedGroups) {
            $new = [];
            $old = json_decode($jsonGroups);

            if (\is_object($old) && \is_object($old->default_groups)) {
                foreach ($old->default_groups as $groupId => $isAllowed) {
                    if (!$isAllowed && \in_array($groupId, $allowedGroups)) {
                        $new[] = (int) $groupId;
                    }
                }
            }

            return json_encode(array_values(array_unique($new)));
        } ;

        foreach (DB::table('user_privacy')->get() as $user_privacy) {
            DB::table('user_privacy')
                ->where('id', '=', $user_privacy->id)
                ->update([
                    'json_profile_groups'     => $migrate($user_privacy->json_profile_groups),
                    'json_torrent_groups'     => $migrate($user_privacy->json_torrent_groups),
                    'json_forum_groups'       => $migrate($user_privacy->json_forum_groups),
                    'json_bon_groups'         => $migrate($user_privacy->json_bon_groups),
                    'json_comment_groups'     => $migrate($user_privacy->json_comment_groups),
                    'json_wishlist_groups'    => $migrate($user_privacy->json_wishlist_groups),
                    'json_follower_groups'    => $migrate($user_privacy->json_follower_groups),
                    'json_achievement_groups' => $migrate($user_privacy->json_achievement_groups),
                    'json_rank_groups'        => $migrate($user_privacy->json_rank_groups),
                    'json_request_groups'     => $migrate($user_privacy->json_request_groups),
                    'json_other_groups'       => $migrate($user_privacy->json_other_groups),
                ]);
        }
    }
};
