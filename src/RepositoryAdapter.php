<?php


namespace TairoLima\MongodbODM;


class RepositoryAdapter
{
    private CollectionAdapter $mMongo;

    public function __construct(string $collectionName, Connection $connection)
    {
        $this->mMongo = new CollectionAdapter($collectionName, $connection);
    }

    public function find(array $params = [], bool $formatId = true): array
    {
        return $this->mMongo->find($params, $formatId);
    }

    public function findById(string $id): ?\stdClass
    {
        return $this->mMongo->findById($id);
    }

    public function findFirst(array $params = []): ?\stdClass
    {
        return $this->mMongo->findFirst($params);
    }

    public function create(array $data): bool
    {
        return $this->mMongo->create($data);
    }

    public function update(array $data): bool
    {
        return $this->mMongo->update($data);
    }

    public function updateAll(array $data, array $conditions): int
    {
        return $this->mMongo->updateAll($data, $conditions);
    }

    public function delete(string $id): bool
    {
        return $this->mMongo->delete($id);
    }

    public function deleteAll(array $conditions, bool $deleteWithoutParams = false): int
    {
        return $this->mMongo->deleteAll($conditions, $deleteWithoutParams);
    }

    public function count(array $params = []): int
    {
        return $this->mMongo->count($params);
    }

    public function aggregate(array $pipeline, bool $current = false): array
    {
        return $this->mMongo->aggregate($pipeline, $current);
    }

    public function distinct(string $field, array $params = []): array
    {
        return $this->mMongo->distinct($field, $params);
    }
}