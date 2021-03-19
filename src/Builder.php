<?php

namespace  Cristal\ApiWrapper;

use Cristal\ApiWrapper\Exceptions\ApiEntityNotFoundException;

class Builder
{
    const MAX_RESULTS = 9999;

    const FILTER_MAPPING_LIMIT = 'limit';
    const PAGINATION_MAPPING_PAGE = 'page';
    const PAGINATION_MAPPING_TOTAL = 'total';
    const PAGINATION_MAPPING_PER_PAGE = 'per_page';
    const PAGINATION_MAPPING_CURRENT_PAGE = 'current_page';

    static protected $operatorMap = [
        '<>' => 'ne',
        '<' => 'lt',
        '<=' => 'le',
        '=' => 'eq',
        '>=' => 'ge',
        '>' => 'gt',
        'like' => 'like'
    ];

    /**
     * @var array
     */
    protected $query = [];

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * Get the underlying query builder instance.
     *
     * @return array
     */
    public function getQuery()
    {
        return array_merge(
            array_merge(...array_values($this->scopes)),
            $this->query
        );
    }

    /**
     * Applied global scopes.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Removed global scopes.
     *
     * @var array
     */
    protected $removedScopes = [];

    /**
     * Set a model instance for the model being queried.
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the model instance being queried.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    public function first()
    {
        return $this->get()[0] ?? null;
    }

    public function find($field, $value = null)
    {
        $res = null;

        try {
            $res = $this->findOrFail($field, $value);
        } catch (ApiEntityNotFoundException $e) {
            return null;
        }

        return $res;
    }

    public function findOrFail($field, $value = null)
    {
        if (is_array($field)) {
            $this->query = array_merge($this->query, ['id' => $field]);

            return $this->where($this->query)->get();
        } elseif (!is_int($field) && $value !== null && count($this->query)) {
            $this->query = array_merge($this->query, [$field => $value]);

            return $this->where($this->query)->get()[0] ?? null;
        }

        $data = $this->model->getApi()->{'get'.ucfirst($this->model->getEntity())}($field, $this->getQuery());

        return $this->model->newInstance($data, true);
    }

    /**
     * Add a basic where clause to the query.
     * When operator is given, a colon separated operator key (see operatorMap) will
     * added before the query value
     *
     * @param string|array $field
     * @param string|null $operator
     * @param null $value
     *
     * @return self
     */
    public function where($field, $operator = null, $value = null)
    {
        if(!is_array($field) && in_array($field, [static::FILTER_MAPPING_LIMIT, static::PAGINATION_MAPPING_PAGE])){
            return $this;
        }

        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $value = static::$operatorMap[$operator] . ':' . $value;
        if (!is_array($field)) {
            $field = [$field => $value];
        }
        $this->query = array_merge($this->query, $field);

        return $this;
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string|null  $boolean
     * @return self
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $second = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$first => 'column:' . static::$operatorMap[$operator] . ':' . $second]);
        return $this;
    }

    /**
     * Add a where between statement to the query.
     *
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return self
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $this->query = array_merge($this->query, [$column => ($not ? 'not' : '') . 'between:' . implode(',', $values)]);
        return $this;
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @return self
     */
    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return self
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$column => 'date:' . static::$operatorMap[$operator] . ':' . $value]);
        return $this;
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return self
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$column => 'day:' . static::$operatorMap[$operator] . ':' . $value]);
        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return self
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->query = array_merge($this->query, [$column => ($not ? 'not' : '') . 'in:' . implode(',', $values)]);
        return $this;
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return self
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return self
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$column => 'month:' . static::$operatorMap[$operator] . ':' . $value]);
        return $this;
    }

    /**
     * Add a "where time" statement to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return self
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$column => 'time:' . static::$operatorMap[$operator] . ':' . $value]);
        return $this;
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        if(func_num_args() === 2){
            $value = $operator;
            $operator = '=';
        }
        $this->query = array_merge($this->query, [$column => 'year:' . static::$operatorMap[$operator] . ':' . $value]);
        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string|array  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $this->query = array_merge($this->query, [$column => ($not ? 'not' : '') . 'null:']);
        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string|array  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNotNull($column, $boolean = 'and', $not = false)
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add a withTrashed parameter
     *
     * @return $this
     */
    public function withTrashed()
    {
        $this->query = array_merge($this->query, ['trashed' => 'with']);
        return $this;
    }


    /**
     * Add a onlyTrashed parameter
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->query = array_merge($this->query, ['trashed' => 'only']);
        return $this;
    }

    /**
     * @return self[]
     */
    public function all()
    {
        return $this->take(static::MAX_RESULTS)->get();
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param int $value
     *
     * @return Builder|static
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     *
     * @return Builder|static
     */
    public function limit($value)
    {
        $field = [static::FILTER_MAPPING_LIMIT => $value];
        $this->query = array_merge($this->query, $field);
        return $this;
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return Builder|static
     */
    public function forPage($page, $perPage = 15)
    {
        $field = [static::PAGINATION_MAPPING_PAGE => $page];
        $this->query = array_merge($this->query, $field);
        return $this->take($perPage);
    }

    /**
     * Register a new global scope.
     *
     * @param string $identifier
     * @param array  $scope
     *
     * @return $this
     */
    public function withGlobalScope($identifier, array $scope)
    {
        $this->scopes[$identifier] = $scope;

        return $this;
    }

    /**
     * Remove a registered global scope.
     *
     * @param  string  $identifier
     * @return $this
     */
    public function withoutGlobalScope(string $identifier)
    {
        unset($this->scopes[$identifier]);
        $this->removedScopes[] = $identifier;

        return $this;
    }

    /**
     * Apply the given scope on the current builder instance.
     *
     * @param array $scope
     * @param array $parameters
     *
     * @return mixed
     */
    protected function callScope(array $scope, $parameters = [])
    {
        [$model, $method] = $scope;

        return $model->$method($this, ...$parameters) ?? $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->model, $scope = 'scope'.ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        try {
            $this->query->{$method}(...$parameters);
        } catch (\Throwable $e) {
            // Pour une raison qui m'échappe, PHP retourne une Fatal exception qui efface la stack d'exception
            // si une erreur arrive... on re throw qqc de plus expressif
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Execute the query.
     *
     * @return array|static[]
     */
    public function get()
    {
        $entities = $this->raw();

        return $this->instanciateModels($entities);
    }

    public function raw()
    {
        $instance = $this->getModel();
        try {
            return $instance->getApi()->{'get'.ucfirst($instance->getEntities())}($this->getQuery());
        } catch (ApiEntityNotFoundException $e) {
            return [];
        }
    }

    public function instanciateModels($data)
    {
        if (!$data) {
            return null;
        }

        return array_map(function ($entity) {
            return $this->model->newInstance($entity, true);
        }, $data);
    }

    public function paginate(?int $perPage = null, ?int $page = 1)
    {
        $this->limit($perPage);
        $field = [static::PAGINATION_MAPPING_PAGE => $page];
        $this->query = array_merge($this->query, $field);

        $entities = $this->raw();

        return [
            'data' => $this->instanciateModels($entities),
            'total' => $entities[static::PAGINATION_MAPPING_TOTAL] ?? null,
            'per_page' => $entities[static::PAGINATION_MAPPING_PER_PAGE] ?? $perPage,
            'current_page' => $entities[static::PAGINATION_MAPPING_CURRENT_PAGE] ?? $page
        ];
    }
}
