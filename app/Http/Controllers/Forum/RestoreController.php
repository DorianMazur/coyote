<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Services\Stream\Activities\Restore as Stream_Restore;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Events\PostWasSaved;
use Coyote\Events\TopicWasSaved;

class RestoreController extends BaseController
{
    /**
     * Restore post or whole thread
     *
     * @param int $id post id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($id)
    {
        // Step 1. Does post really exist?
        /** @var \Coyote\Post $post */
        $post = $this->post->withTrashed()->findOrFail($id);

        $this->authorize('delete', [$post, $post->forum]);
        $post->forum->userCanAccess($this->userId) || abort(401, 'Unauthorized');

        return $this->transaction(function () use ($post) {
            // build url to post
            $url = route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug], false);

            if ($post->id === $post->topic->first_post_id) {
                $post->topic->restore();

                event(new TopicWasSaved($post->topic));

                $object = (new Stream_Topic())->map($post->topic, $post->forum);
                $target = (new Stream_Forum())->map($post->forum);
            } else {
                $url .= '?p=' . $post->id . '#id' . $post->id;
                $post->restore();

                // fire the event. add post to search engine
                event(new PostWasSaved($post));

                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($post->topic, $post->forum);
            }

            stream(Stream_Restore::class, $object, $target);

            return redirect()->to($url)->with('success', 'Post został przywrócony.');
        });
    }
}