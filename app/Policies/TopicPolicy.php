<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Topic;

class TopicPolicy extends Policy
{
    public function update(User $user, Topic $topic)
    {
        // return $topic->user_id == $user->id;
        return $user->isAuthorOf($topic);
    }

    public function destroy(User $user, Topic $topic)
    {
       // return $topic->user_id == $user->id;
        return $user->isAuthorOf($topic);
    }


    public function deleted(Topic $topic)
    {
        // 删除帖子连带删除回复
        \DB::table('replies')->where('topic_id', $topic->id)->delete();
    }
}
