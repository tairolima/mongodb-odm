<?php


namespace TairoLima\MongodbODM;


class RepositoryAdapter
{
    private CollectionAdapter $mMongo;

    public function __construct(string $collectionName, Connection $connection)
    {
        print_r("\tRepositoryAdapter\n");
        $this->mMongo = new CollectionAdapter($collectionName, $connection);
    }

    public function find(array $params = []): array
    {
        return $this->mMongo->find($params);
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

    public function updateAll(array $data, array $conditions): bool
    {
        return $this->mMongo->updateAll($data, $conditions);
    }

    public function delete(string $id): bool
    {
        return $this->mMongo->delete($id);
    }


}