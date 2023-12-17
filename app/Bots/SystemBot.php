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

namespace App\Bots;

use App\Events\Chatter;
use App\Http\Resources\UserAudibleResource;
use App\Http\Resources\UserEchoResource;
use App\Models\BonTransactions;
use App\Models\Bot;
use App\Models\User;
use App\Models\UserAudible;
use App\Models\UserEcho;
use App\Notifications\NewBon;
use App\Repositories\ChatRepository;
use Illuminate\Support\Carbon;

class SystemBot
{
    private Bot $bot;

    private User $target;

    private string $type;

    private string $message;

    private string $log;

    public function __construct(private readonly ChatRepository $chatRepository)
    {
        $this->bot = Bot::where('is_systembot', '=', '1')->sole();
    }

    public function replaceVars(string $output): string
    {
        $output = str_replace(['{me}', '{command}'], [$this->bot->name, $this->bot->command], $output);

        if (str_contains($output, '{bots}')) {
            $botHelp = '';
            $bots = Bot::where('active', '=', 1)->where('id', '!=', $this->bot->id)->oldest('position')->get();

            foreach ($bots as $bot) {
                $botHelp .= '( ! | / | @)'.$bot->command.' help triggers help file for '.$bot->name."\n";
            }

            $output = str_replace('{bots}', $botHelp, $output);
        }

        return $output;
    }

    /**
     * Get Help.
     */
    public function getHelp(): string
    {
        return $this->replaceVars($this->bot->help);
    }

    /**
     * Send Gift.
     *
     * @param array<string> $note
     */
    public function putGift(string $receiver = '', float $amount = 0, array $note = ['']): string
    {
        $output = implode(' ', $note);
        $v = validator(['receiver' => $receiver, 'amount' => $amount, 'note' => $output], [
            'receiver' => 'required|string|exists:users,username',
            'amount'   => sprintf('required|numeric|min:1|max:%s', $this->target->seedbonus),
            'note'     => 'required|string',
        ]);

        if ($v->passes()) {
            $recipient = User::where('username', 'LIKE', $receiver)->first();

            if (!$recipient || $recipient->id === $this->target->id) {
                return 'Your BON gift could not be sent.';
            }

            $value = $amount;
            $recipient->seedbonus += $value;
            $recipient->save();

            $this->target->seedbonus -= $value;
            $this->target->save();

            $bonTransactions = new BonTransactions();
            $bonTransactions->bon_exchange_id = 0;
            $bonTransactions->name = 'gift';
            $bonTransactions->cost = $value;
            $bonTransactions->sender_id = $this->target->id;
            $bonTransactions->receiver_id = $recipient->id;
            $bonTransactions->comment = $output;
            $bonTransactions->torrent_id = null;
            $bonTransactions->save();

            if ($this->target->id !== $recipient->id && $recipient->acceptsNotification($this->target, $recipient, 'bon', 'show_bon_gift')) {
                $recipient->notify(new NewBon('gift', $this->target->username, $bonTransactions));
            }

            $profileUrl = href_profile($this->target);
            $recipientUrl = href_profile($recipient);

            $this->chatRepository->systemMessage(
                sprintf('[url=%s]%s[/url] has gifted %s BON to [url=%s]%s[/url]', $profileUrl, $this->target->username, $value, $recipientUrl, $recipient->username)
            );

            return 'Your gift to '.$recipient->username.' for '.$amount.' BON has been sent!';
        }

        return 'Your BON gift could not be sent.';
    }

    /**
     * Process Message.
     */
    public function process(string $type, User $user, string $message = ''): \Illuminate\Http\Response|bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $this->target = $user;

        if ($type === 'message') {
            $x = 0;
            $y = 1;
            $z = 2;
        } else {
            $x = 1;
            $y = 2;
            $z = 3;
        }

        if ($message === '') {
            $log = '';
        } else {
            $log = 'All '.$this->bot->name.' commands must be a private message or begin with /'.$this->bot->command.' or !'.$this->bot->command.'. Need help? Type /'.$this->bot->command.' help and you shall be helped.';
        }

        $command = @explode(' ', $message);

        if (\array_key_exists($x, $command)) {
            if ($command[$x] === 'gift' && \array_key_exists($y, $command) && \array_key_exists($z, $command) && \array_key_exists($z + 1, $command)) {
                $clone = $command;
                array_shift($clone);
                array_shift($clone);
                array_shift($clone);
                array_shift($clone);
                $log = $this->putGift($command[$y], (float) $command[$z], $clone);
            }

            if ($command[$x] === 'help') {
                $log = $this->getHelp();
            }
        }

        $this->type = $type;
        $this->message = $message;
        $this->log = $log;

        return $this->pm();
    }

    /**
     * Output Message.
     */
    public function pm(): \Illuminate\Http\Response|bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $type = $this->type;
        $target = $this->target;
        $txt = $this->log;
        $message = $this->message;

        if ($type === 'message' || $type === 'private') {
            $receiverDirty = false;
            $receiverEchoes = cache()->get('user-echoes'.$target->id);

            if (!$receiverEchoes || !\is_array($receiverEchoes)) {
                $receiverEchoes = UserEcho::with(['room', 'target', 'bot'])->where('user_id', '=', $target->id)->get();
            }

            $receiverListening = false;

            foreach ($receiverEchoes as $receiverEcho) {
                if ($receiverEcho->bot_id === $this->bot->id) {
                    $receiverListening = true;

                    break;
                }
            }

            if (!$receiverListening) {
                $receiverPort = new UserEcho();
                $receiverPort->user_id = $target->id;
                $receiverPort->bot_id = $this->bot->id;
                $receiverPort->save();
                $receiverEchoes = UserEcho::with(['room', 'target', 'bot'])->where('user_id', '=', $target->id)->get();
                $receiverDirty = true;
            }

            if ($receiverDirty) {
                $expiresAt = Carbon::now()->addMinutes(60);
                cache()->put('user-echoes'.$target->id, $receiverEchoes, $expiresAt);
                event(new Chatter('echo', $target->id, UserEchoResource::collection($receiverEchoes)));
            }

            $receiverDirty = false;
            $receiverAudibles = cache()->get('user-audibles'.$target->id);

            if (!$receiverAudibles || !\is_array($receiverAudibles)) {
                $receiverAudibles = UserAudible::with(['room', 'target', 'bot'])->where('user_id', '=', $target->id)->get();
            }

            $receiverListening = false;

            foreach ($receiverAudibles as $se => $receiverEcho) {
                if ($receiverEcho->bot_id === $this->bot->id) {
                    $receiverListening = true;

                    break;
                }
            }

            if (!$receiverListening) {
                $receiverPort = new UserAudible();
                $receiverPort->user_id = $target->id;
                $receiverPort->bot_id = $this->bot->id;
                $receiverPort->save();
                $receiverAudibles = UserAudible::with(['room', 'target', 'bot'])->where('user_id', '=', $target->id)->get();
                $receiverDirty = true;
            }

            if ($receiverDirty) {
                $expiresAt = Carbon::now()->addMinutes(60);
                cache()->put('user-audibles'.$target->id, $receiverAudibles, $expiresAt);
                event(new Chatter('audible', $target->id, UserAudibleResource::collection($receiverAudibles)));
            }

            if ($txt !== '') {
                $roomId = 0;
                $this->chatRepository->privateMessage($target->id, $roomId, $message, 1, $this->bot->id);
                $this->chatRepository->privateMessage(1, $roomId, $txt, $target->id, $this->bot->id);
            }

            return response('success');
        }

        if ($type === 'echo') {
            if ($txt !== '') {
                $roomId = 0;
                $this->chatRepository->botMessage($this->bot->id, $roomId, $txt, $target->id);
            }

            return response('success');
        }

        if ($type === 'public') {
            if ($txt !== '') {
                $this->chatRepository->message($target->id, $target->chatroom->id, $message, null, null);
                $this->chatRepository->message(1, $target->chatroom->id, $txt, null, $this->bot->id);
            }

            return response('success');
        }

        return true;
    }
}
