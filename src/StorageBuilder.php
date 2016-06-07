<?php
namespace Kaleidoscope;

use Illuminate\Support\Collection;

class StorageBuilder
{

    /**
     * The storage instance
     *
     * @var Citytalk\StorageBundle\Storages\{ ... }
     */
    protected $storage;

    /**
     * The active connection
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    public function __construct($storage, $connection)
    {
        $this->storage = $storage;
        $this->connection = $connection;
    }

    /**
     * Find a entity by its primary key.
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function raw($id, $columns = ['*'])
    {
        $table = $this->connection->table('kaleidoscope');

        $results = $table->find($id, $columns);

        return new Collection($results);
    }

    /**
     * Handle type's saving events.
     *
     * @param string $attribute
     * @param array $options
     */
    public function save($attribute, array $values)
    {
        return $this->connection->transaction(function () use ($attribute, $values) {
            $now = time();
            $table = $this->connection->table('kaleidoscope');

            $values['created_at'] = $now;
            $values['updated_at'] = $now;

            // First to fetch sequence number
            $params['id'] = $table->insertGetId($values);

            // Matching storage path by special key.
            $params['path'] = $this->storage->getRelativePath("kaleidoscope.$attribute", $params);

            // Always update path by current object.
            $table->where('id', $params['id'])
                ->update([
                    'path' => $params['path']
                ]);

            // Return the id, path and current time fields.
            return $params;
        });
    }

    /**
     * Handle dynamic method.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        throw new \BadMethodCallException("Call to undefined method $method");
    }
}
