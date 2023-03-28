<?php

namespace App\Contracts\RepositoryInterfaces;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

Interface BaseRepositoryContract
{
    /**
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data = []): bool;

    /**
     * @param array $attributes
     * @param int|string $entityId
     *
     * @return Model|bool
     */
    public function update(int|string $entityId, array $attributes = []): Model|bool;

    /**
     * @param $entityId
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int|string $entityId): bool;

    /**
     * @param int|string $entityId
     * @param array $columns
     *
     *  @return Model
     */
    public function find(int|string $entityId, array $columns = ['*']): Model;

    /**
     * @param int|string $entityId
     * @param array $columns
     *
     *@return Model
     *@throws ModelNotFoundException
     *
     */
    public function findOrFail(int|string $entityId = 0, array $columns = ['*']): Model;


    /**
     * @return Builder|Model
     */
    public function builder(): Builder|Model;

    /**
     * @param array $criteria
     * @param array $columns
     *
     *  @return Model
     */
    public function findBy(array $criteria = [], array $columns = ['*']): Model;

    /**
     * @param int $limit
     * @param array $criteria
     *
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = 10, array $criteria = []): LengthAwarePaginator;

    /**
     * @param int $limit
     * @param array $criteria
     *
     * @return Paginator
     */
    public function simplePaginate(int $limit = 10, array $criteria = []): Paginator;

    /**
     * @param array $criteria
     * @param array $columns
     *
//     * @return Builder[]|Collection
     */
    public function get(array $criteria = [], array $columns = []): Collection|array;

    /**
     * @param string $name
     * @param string $entityId
     * @param array $criteria
     *
     * @return array
     */
    public function pluck(string $name = 'name', string $entityId = 'id', array $criteria = []): array;

    /**
     * @param array $filter
     * @param array $columns
     *
     *  @return Model
     */
    public function first(array $filter = [], array $columns = ['*']): Model;

    /**
     * @param array $data
     *
     * @return Model
     */
    public function create(array $data = []): Model;

    /**
     * @param array $data
     *
     * @return Model
     */
    public function createOrFirst(array $data = []): Model;

    /**
     * @param array $data
     *
     * @return Model
     */
    public function createOrUpdate(array $data = []): Model;

    /**
     * Get entity name.
     *
     * @return string
     */
    public function entityName(): string;

    public function trash();

    public function withTrash();

    /**
     * @param int|string $entityId
     *
     * @return bool
     */
    public function restore(int|string $entityId = 0): bool;

    /**
     * @param int|string $id
     *
     * @return bool
     */
    public function forceDelete(int|string $id = 0): bool;
}
