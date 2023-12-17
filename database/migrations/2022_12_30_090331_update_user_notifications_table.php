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
                foreach ($old->default_groups as $groupId => $acceptsNotifications) {
                    if (!$acceptsNotifications && \in_array($groupId, $allowedGroups)) {
                        $new[] = (int) $groupId;
                    }
                }
            }

            return json_encode(array_values(array_unique($new)));
        } ;

        foreach (DB::table('user_notifications')->get() as $user_notification) {
            DB::table('user_notifications')
                ->where('id', '=', $user_notification->id)
                ->update([
                    'json_account_groups'      => $migrate($user_notification->json_account_groups),
                    'json_bon_groups'          => $migrate($user_notification->json_bon_groups),
                    'json_mention_groups'      => $migrate($user_notification->json_mention_groups),
                    'json_request_groups'      => $migrate($user_notification->json_request_groups),
                    'json_torrent_groups'      => $migrate($user_notification->json_torrent_groups),
                    'json_forum_groups'        => $migrate($user_notification->json_forum_groups),
                    'json_following_groups'    => $migrate($user_notification->json_following_groups),
                    'json_subscription_groups' => $migrate($user_notification->json_subscription_groups),
                ]);
        }
    }
};
