<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\PostRepositoryContract;
use App\Models\Post;

class PostRepository extends BaseRepository implements PostRepositoryContract
{

    protected function getModelClass(): string
    {
        return Post::class;
    }
}
