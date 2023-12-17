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

use App\Achievements\UserMade100Posts;
use App\Achievements\UserMade200Posts;
use App\Achievements\UserMade25Posts;
use App\Achievements\UserMade300Posts;
use App\Achievements\UserMade400Posts;
use App\Achievements\UserMade500Posts;
use App\Achievements\UserMade50Posts;
use App\Achievements\UserMade600Posts;
use App\Achievements\UserMade700Posts;
use App\Achievements\UserMade800Posts;
use App\Achievements\UserMade900Posts;
use App\Achievements\UserMadeFirstPost;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\NewPostTag;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Exception;

/**
 * @see \Tests\Todo\Feature\Http\Controllers\PostControllerTest
 */
class PostController extends Controller
{
    /**
     * PostController Constructor.
     */
    public function __construct(private readonly ChatRepository $chatRepository)
    {
    }

    /**
     * Posts Index.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return view('forum.post.index');
    }

    /**
     * Store A New Post To A Topic.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'content'  => 'required|min:1',
            'topic_id' => 'required|integer',
        ]);

        $user = $request->user();

        $topic = Topic::whereRelation('forumPermissions', [
            ['reply_topic', '=', 1],
            ['group_id', '=', $user->group_id]
        ])
            ->when(!$user->group->is_modo, fn ($query) => $query->where('state', '=', 'open'))
            ->findOrFail($request->topic_id);

        $forum = $topic->forum;

        $post = Post::create([
            'content'  => $request->input('content'),
            'user_id'  => $user->id,
            'topic_id' => $topic->id,
        ]);

        $topic->update([
            'last_post_user_id'       => $user->id,
            'last_post_user_username' => $user->username,
            'num_post'                => $topic->posts()->count(),
            'last_reply_at'           => $post->created_at,
        ]);

        $forum->update([
            'num_post'                => $forum->posts()->count(),
            'num_topic'               => $forum->topics()->count(),
            'last_post_user_id'       => $user->id,
            'last_post_user_username' => $user->username,
            'last_topic_id'           => $topic->id,
            'last_topic_name'         => $topic->name,
            'updated_at'              => $post->created_at,
        ]);

        // Post To Chatbox and Notify Subscribers
        $appUrl = config('app.url');
        $postUrl = sprintf('%s/forums/topics/%s/posts/%s', $appUrl, $topic->id, $post->id);
        $realUrl = sprintf('/forums/topics/%s/posts/%s', $topic->id, $post->id);
        $profileUrl = sprintf('%s/users/%s', $appUrl, $user->username);

        if (config('other.staff-forum-notify') && ($forum->id == config('other.staff-forum-id') || $forum->parent_id == config('other.staff-forum-id'))) {
            $topic->notifyStaffers($user, $topic, $post);
        } else {
            $this->chatRepository->systemMessage(sprintf('[url=%s]%s[/url] has left a reply on topic [url=%s]%s[/url]', $profileUrl, $user->username, $postUrl, $topic->name));

            // Notify All Subscribers Of New Reply
            if ($topic->first_post_user_id != $user->id) {
                $topic->notifyStarter($user, $topic, $post);
            }

            $topic->notifySubscribers($user, $topic, $post);

            // Achievements
            $user->unlock(new UserMadeFirstPost());
            $user->addProgress(new UserMade25Posts(), 1);
            $user->addProgress(new UserMade50Posts(), 1);
            $user->addProgress(new UserMade100Posts(), 1);
            $user->addProgress(new UserMade200Posts(), 1);
            $user->addProgress(new UserMade300Posts(), 1);
            $user->addProgress(new UserMade400Posts(), 1);
            $user->addProgress(new UserMade500Posts(), 1);
            $user->addProgress(new UserMade600Posts(), 1);
            $user->addProgress(new UserMade700Posts(), 1);
            $user->addProgress(new UserMade800Posts(), 1);
            $user->addProgress(new UserMade900Posts(), 1);
        }

        // User Tagged Notification
        if ($user->id !== $post->user_id) {
            preg_match_all('/@([\w\-]+)/', (string) $post->content, $matches);
            $users = User::whereIn('username', $matches[1])->get();
            Notification::send($users, new NewPostTag($post));
        }

        return redirect()->to($realUrl)
            ->withSuccess(trans('forum.reply-topic-success'));
    }

    /**
     * Edit A Post.
     */
    public function edit(Request $request, int $id): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $user = $request->user();

        $post = Post::find($id);
        $topic = $post->topic()
            ->whereRelation('forumPermissions', [
                ['show_forum', '=', 1],
                ['read_topic', '=', 1],
                ['reply_topic', '=', 1],
                ['group_id', '=', $user->group_id],
            ])
            ->when(!$user->group->is_modo, fn ($query) => $query->where('state', '=', 'open'))
            ->sole();

        abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);

        $forum = $topic->forum;
        $category = $forum->category;

        return view('forum.post.edit', [
            'topic'    => $topic,
            'forum'    => $forum,
            'post'     => $post,
            'category' => $category,
        ]);
    }

    /**
     * Update A Post.
     */
    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'content' => 'required|min:1',
        ]);

        $user = $request->user();

        $post = Post::findOrFail($id);
        $postUrl = sprintf('forums/topics/%s/posts/%s', $post->topic->id, $id);

        abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);

        abort_unless(
            $post->topic()
                ->whereRelation('forumPermissions', [
                    ['reply_topic', '=', 1],
                    ['group_id', '=', $user->group_id],
                ])
                ->when(!$user->group->is_modo, fn ($query) => $query->where('state', '=', 'open'))
                ->exists(),
            403
        );

        $post->update([
            'content' => $request->input('content'),
        ]);

        return redirect()->to($postUrl)
            ->withSuccess(trans('forum.edit-post-success'));
    }

    /**
     * Delete A Post.
     *
     * @throws Exception
     */
    public function destroy(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $post = Post::with('topic')->findOrFail($id);

        abort_unless($user->group->is_modo || $user->id === $post->user_id, 403);

        $topic = $post->topic()->whereRelation('forumPermissions', [
            ['reply_topic', '=', 1],
            ['group_id', '=', $user->group_id],
        ])
            ->sole();

        $post->delete();

        $latestPost = $topic->latestPost;
        $isTopicDeleted = false;

        if ($latestPost === null) {
            $topic->delete();
            $isTopicDeleted = true;
        } else {
            $latestPoster = $latestPost->user;
            $topic->update([
                'last_post_user_id'       => $latestPoster->id,
                'last_post_user_username' => $latestPoster->username,
                'num_post'                => $topic->posts()->count(),
                'last_reply_at'           => $latestPost->created_at,
            ]);
        }

        $forum = $topic->forum;
        $lastRepliedTopic = $forum->lastRepliedTopic;
        $latestPost = $lastRepliedTopic->latestPost;
        $latestPoster = $latestPost->user;

        $forum->update([
            'num_post'                => $forum->posts()->count(),
            'num_topic'               => $forum->topics()->count(),
            'last_post_user_id'       => $latestPoster->id,
            'last_post_user_username' => $latestPoster->username,
            'last_topic_id'           => $lastRepliedTopic->id,
            'last_topic_name'         => $lastRepliedTopic->name,
            'updated_at'              => $latestPost->created_at,
        ]);

        if ($isTopicDeleted === true) {
            return to_route('forums.show', ['id' => $forum->id])
                ->withSuccess(trans('forum.delete-post-success'));
        }

        return to_route('topics.show', ['id' => $post->topic->id])
            ->withSuccess(trans('forum.delete-post-success'));
    }
}
