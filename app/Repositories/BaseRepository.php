<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\BaseRepositoryContract;
use Illuminate\Container\Container as App;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository implements BaseRepositoryContract
{
    protected array $with = [];

    /**
     * @var App
     */
    protected App $app;

    /** @var string|null */
    protected string|null $order = null;

    /** @var string */
    protected string $direction = 'desc';

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var bool
     */
    private bool $trash = false;

    /**
     * @var bool
     */
    private bool $withTrash = false;

    /**
     * @var bool
     */
    private bool $allowCaching = true;

    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @param App $app
     * @throws BindingResolutionException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function makeModel(): void
    {
        $this->model = $this->app->make($this->getModelClass());
    }

    /**
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * @param int $limit
     * @param array $criteria
     *
     * @return Paginator
     */
    public function simplePaginate(int $limit = 10, array $criteria = []): Paginator
    {
        return $this->filter($criteria)->simplePaginate($limit);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function builder(): \Illuminate\Database\Eloquent\Builder|Model
    {
        return $this->model->query();
    }

    /**
     * @param array $criteria
     *
     * @return Builder
     */
    public function filter(array $criteria = []): Builder
    {
        $criteria = $this->order($criteria);

        /** @var Model $latest */
        $latest = $this->model->with($this->with);
        if ($this->order !== '') {
            $latest->orderBy($this->order, $this->direction);
        }

        if (isset($criteria['search'])) {
            if (isset($this->model->searchable)) {
                foreach ($this->model->searchable as $method => $columns) {
                    if (method_exists($this->model, $method)) {
                        $latest->orWhereHas($method, function ($query) use ($criteria, $columns) {
                            $query->where(function ($query2) use ($criteria, $columns) {
                                foreach ((array)$columns as $column) {
                                    $query2->orWhere($column, 'like', '%' . $criteria['search'] . '%');
                                }
                            });
                        });
                    } else {
                        $latest->orWhere($columns, 'like', '%' . $criteria['search'] . '%');
                    }
                }
            }
        }
        unset($criteria['search']);

        if ($this->trash && method_exists($latest, 'onlyTrashed' )) {
            $latest->onlyTrashed();
        }

        if ($this->withTrash && method_exists($latest, 'withTrashed' )) {
            $latest->withTrashed();
        }

        return $latest->where($criteria);
    }

    /**
     * prepare order for query.
     *
     * @param array $criteria
     *
     * @return array
     */
    private function order(array $criteria = []): array
    {
        if (isset($criteria['order'])) {
            $this->order = $criteria['order'];
            unset($criteria['order']);
        }

        if (isset($criteria['direction'])) {
            $this->direction = $criteria['direction'];
            unset($criteria['direction']);
        }
        unset($criteria['page']);

        return $criteria;
    }

    /**
     * @param int $limit
     * @param array $criteria
     *
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = 10, array $criteria = []): LengthAwarePaginator
    {
        return $this->filter($criteria)->paginate($limit);
    }

    /**
     * @param array $criteria
     *
     * @param array $columns
     * @return array|Collection
     */
    public function get(array $criteria = [], array $columns = ['*']): Collection|array
    {
        return $this->filter($criteria)->get($columns);
    }

    /**
     * @param int|string $entityId
     * @param array $attributes
     *
     * @return  Model|bool
     */
    public function update(int|string $entityId = 0, array $attributes = []): Model|bool
    {
        $item = $this->model->findOrFail($entityId);

        if ($item->update($attributes)) {
            return $item;
        }

        return false;
    }

    /**
     * @param int|string $entityId
     *
     * @return bool
     *
     */
    public function delete(int|string $entityId = 0): bool
    {
        $item = $this->model->findOrFail($entityId);

        return $item->delete();
    }

    /**
     * @param array $attributes
     *
     * @return bool
     */
    public function insert(array $attributes = []): bool
    {
        return $this->model->insert($attributes);
    }

    /**
     * @param string $name
     * @param int|string $entityId
     * @param array $criteria
     *
     * @return array
     */
    public function pluck(string $name = 'name', int|string $entityId = 'id', array $criteria = []): array
    {
        return $this->filter($criteria)->pluck($name, $entityId)->toArray();
    }

    /**
     * @param int|string $entityId
     * @param array $columns
     *
     * @return Model
     */
    public function find(int|string $entityId = 0, array $columns = ['*']): Model
    {
        if ($this->allowCaching) {
            if (isset($this->cache[$entityId])) {
                return $this->cache[$entityId];
            }
        }

        $entity = $this->model->with($this->with)->find($entityId, $columns);

        if ($this->allowCaching) {
            $this->cache[$entityId] = $entity;
        }

        return $entity;
    }

    /**
     * @param int|string $entityId
     * @param array $columns
     *
     * @return Model
     * @throws ModelNotFoundException
     *
     */
    public function findOrFail(int|string $entityId = 0, array $columns = ['*']): Model
    {
        if ($this->allowCaching) {
            if (isset($this->cache[$entityId])) {
                return $this->cache[$entityId];
            }
        }

        $entity = $this->model->with($this->with)->findOrFail($entityId, $columns);

        if ($this->allowCaching) {
            $this->cache[$entityId] = $entity;
        }

        return $entity;
    }

    /**
     * @param array $filter
     * @param array $columns
     *
     * @return Model
     */
    public function first(array $filter = [], array $columns = ['*']): Model
    {
        if ($this->allowCaching) {
            if (isset($this->cache['first'])) {
                return $this->cache['first'];
            }
        }

        $entity = $this->filter($filter)->with($this->with)->select($columns)->first();

        if ($this->allowCaching) {
            $this->cache['first'] = $entity;
        }

        return $entity;
    }

    /**
     * @param array $filter
     * @param array $columns
     *
     * @return Model
     */
    public function last(array $filter = [], array $columns = ['*']): Model
    {
        if ($this->allowCaching) {
            if (isset($this->cache['last'])) {
                return $this->cache['last'];
            }

        }

        $entity = $this->filter($filter)
            ->with($this->with)
            ->select($columns)
            ->orderBy('id', 'desc')
            ->first();

        if ($this->allowCaching) {
            $this->cache['last'] = $entity;
        }

        return $entity;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return array<Model>|Collection
     */
    public function search($haystack, $needle): array|Collection
    {
        return $this->model->where($haystack, 'like', $needle)->get();
    }

    /**
     * @param array $criteria
     * @param array $columns
     *
     * @return Model
     */
    public function findBy(array $criteria = [], array $columns = ['*']): Model
    {
        return $this->model->with($this->with)
            ->select($columns)
            ->where($criteria)
            ->first();
    }

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes = []): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function createOrUpdate(array $attributes = []): Model
    {
        return $this->model->updateOrCreate($attributes);
    }

    /**
     * @param array $data
     *
     * @return Model
     */
    public function createOrFirst(array $data = []): Model
    {
        return $this->model->firstOrCreate($data);
    }

    /**
     * Get entity name.
     *
     * @return string
     */
    public function entityName(): string
    {
        return $this->getModelClass();
    }

    /**
     * @param int|string $entityId
     *
     * @return bool
     */
    public function restore(int|string $entityId = 0): bool
    {
        /** @var Model|null $entity */
        $entity = $this->model->whereId($entityId)
            ->first();
        if ($entity && method_exists($entity, 'restore')) {
            return $entity->restore() ?? false;
        }

        return false;
    }

    /**
     * @param int|string $entityId
     *
     * @return bool
     */
    public function forceDelete(int|string $entityId = 0): bool
    {
        if (method_exists($this->model, 'withTrashed')) {
            /** @var Model|null $entity */
            $entity = $this->model->withTrashed()
                ->whereId($entityId)
                ->first();
        } else {
            /** @var Model|null $entity */
            $entity = $this->model->whereId($entityId)
                ->first();
        }

        if ($entity) {
            return $entity->forceDelete() ?? false;
        }

        return false;
    }

    public function trash(): void
    {
        $this->trash = true;
        $this->withTrash = false;
    }

    public function withTrash(): void
    {
        $this->trash = false;
        $this->withTrash = true;
    }

    public function disableCaching(): static
    {
        $this->allowCaching = false;
        return $this;
    }

    public function allowCaching(): static
    {
        $this->allowCaching = true;
        return $this;
    }
}
