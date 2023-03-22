<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\FileRepositoryContract;
use App\Models\File;

class FileRepository extends BaseRepository implements FileRepositoryContract
{

    protected function getModelClass(): string
    {
        return File::class;
    }
}
