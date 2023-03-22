<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\CategoryRepositoryContract;
use App\Models\Category;

class CategoryRepository extends BaseRepository implements CategoryRepositoryContract
{

    protected function getModelClass(): string
    {
       return Category::class;
    }
}
