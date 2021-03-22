<?php


namespace TairoLima\MongodbODM;


use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Query;

class CollectionAdapter
{
    private string $mCollectionName;
    private Connection $mConnection;

    public function __construct(string $collectionName, Connection $connection)
    {
        $this->mCollectionName = $collectionName;
        $this->mConnection     = $connection;
    }

    private function getDatabaseCollection(): string
    {
        // DATABASE.COLLECTION
        return "{$this->mConnection->getDatabase()}.{$this->mCollectionName}";
    }


    /// Metodos de acesso ao banco de dados MongoDB

    public function find(array $params = [], bool $formatId = true): array
    {
        $filter  = array();
        $options = array();

        //filter - conditions
        $this->filterConditions($filter, $params);

        //options - limit
        $this->optionsLimit($options, $params);

        //options - sort
        $this->optionsSort($options, $params);

        //options - fields
        $this->optionsFields($options, $params);

        try {
            $cursor = $this->executeQuery($filter, $options);

            if ($formatId == true)
            {
                $data = [];
                foreach ($cursor as $document)
                {
                    if (isset($document->_id))
                    {
                        $document->_id = (string)$document->_id;
                    }

                    array_push($data, $document);
                }

                return $data;
            }

            return $cursor->toArray();

        } catch (Exception $e) {
            return [];
        }
    }

    public function findById(string $id): ?\stdClass
    {
        try {
            $filter  = array("_id" => new ObjectId($id));
            $options = array();

            $cursor   = $this->executeQuery($filter, $options);
            $document = current($cursor->toArray());

            if ($document != false)
            {
                $document->_id = (string)$document->_id;

                return $document;
            }

            return null;

        } catch (Exception $e) {
            print_r("ERROR: {$e->getMessage()}\n");
            return null;
        }
    }

    public function findFirst(array $params = []): ?\stdClass
    {
        try {
            $filter  = array();
            $options = array();

            //filter - conditions
            $this->filterConditions($filter, $params);

            //options - limit
            $options['limit'] = 1;

            //options - sort
            $this->optionsSort($options, $params);

            //options - fields
            $this->optionsFields($options, $params);

            $cursor   = $this->executeQuery($filter, $options);
            $document = current($cursor->toArray());

            if ($document != false)
            {
                $document->_id = (string)$document->_id;

                return $document;
            }

            return null;

        }catch (Exception $e){
            return null;
        }
    }

    public function create(array $data): bool
    {
        try {
            //Insert
            $bulk = new BulkWrite();
            $bulk->insert($data);

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getInsertedCount() >= 1)
            {
                return true;
            }

            return false;

        }catch (Exception $e){
            return false;
        }
    }

    public function update(array $data, array $updateOptions): bool
    {
        try {
            $id = $data["_id"] ?? null;

            if ($id == null)
            {
                return false;
            }

            //Transforma ['_id'] em ObjectId
            $data["_id"] = new ObjectId( (string) $id);

            //Update
            $bulk = new BulkWrite();
            $bulk->update(
                ["_id" => $data["_id"] ],
                ['$set' => $data],
                $updateOptions
            );

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getModifiedCount() >= 1)
            {
                return true;
            }

            return false;

        }catch (\Exception $e){
            return false;
        }
    }

    public function updateAll(array $data, array $conditions, array $updateOptions): int
    {
        try {
            if (isset($data["_id"]))
            {
                unset($data["_id"]);
            }

            //Update
            $bulk = new BulkWrite();
            $bulk->update(
                $conditions,
                ['$set' => $data],
                $updateOptions
            );

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getModifiedCount() >= 1)
            {
                return $result->getModifiedCount();
            }

            return 0;

        }catch (Exception $e){
            return false;
        }
    }

    public function delete(string $id): bool
    {
        try {
            $filter = [
                "_id" => new ObjectId($id)
            ];

            $bulk = new BulkWrite();
            $bulk->delete($filter);

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getDeletedCount() >= 1)
            {
                return true;
            }

            return false;

        }catch (Exception $e){
            return false;
        }
    }

    public function deleteAll(array $conditions, bool $deleteWithoutParams = false): int
    {
        try {
            if ($conditions == [] && $deleteWithoutParams == false)
            {
                return false;
            }
            
            $bulk = new BulkWrite();
            $bulk->delete($conditions);

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getDeletedCount() >= 1)
            {
                return $result->getDeletedCount();
            }

            return 0;

        }catch (Exception $e){
            return false;
        }
    }

    public function count(array $params = []): int
    {
        try {
            $filter  = array();
            $options = array();

            //filter - conditions
            $this->filterConditions($filter, $params);

            //options - fields
            $options["projection"]["_id"] = 1;

            $cursor   = $this->executeQuery($filter, $options);
            $document = $cursor->toArray();

            return \count($document);

        }catch (Exception $e){
            return 0;
        }
    }

    public function aggregate(array $pipeline, bool $current = false): array
    {
        try {
            $command = [
                "aggregate" => $this->mCollectionName,
                "pipeline" => $pipeline,
                "cursor" => new \stdClass(),
                "allowDiskUse" => true
            ];

            $cursor = $this->executeCommand($command);

            if ($current == true)
            {
                $document = $cursor->toArray();
                if (empty($document))
                {
                    return [];
                }

                return current($document);
            }

            return $cursor->toArray();

        }catch (Exception $e){
            return [];
        }
    }

    public function distinct(string $field, array $params = []): array
    {
        $command = [
            "distinct" => $this->mCollectionName,
            "key" => $field,
            "query" => (object) $params
        ];

        $cursor   = $this->executeCommand($command);
        $document = current($cursor->toArray());

        return $document["values"];
    }


    private function executeCommand(array $document): Cursor
    {
        $command = new Command($document);
        $cursor  = $this->mConnection->getManager()->executeCommand($this->mConnection->getDatabase(), $command);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor;
    }

    private function executeQuery(array $filter, array $options): Cursor
    {
        $query  = new Query($filter, $options);
        $cursor = $this->mConnection->getManager()->executeQuery($this->getDatabaseCollection(), $query);

        $cursor->setTypeMap(['root' => 'object', 'document' => 'array', 'array' => 'array']);

        return $cursor;
    }


    private function filterConditions(array &$filter, array $params)
    {
        if (isset($params['conditions']))
        {
            $filter = $params['conditions'];
        }
    }

    private function optionsSort(array &$options, array $params): void
    {
        if (isset($params['sort']))
        {
            $options['sort'] = $params["sort"];
        }
    }

    private function optionsLimit(array &$options, array $params): void
    {
        if (isset($params['limit']))
        {
            $options['limit'] = (int) $params['limit'];
        }
    }

    private function optionsFields(array &$options, array $params): void
    {
        $options['projection'] = [];
        if (isset($params['fields']) && is_array($params['fields']) && !empty($params['fields']))
        {
            foreach ($params['fields'] as $key => $value)
            {
                $options['projection'][$key] = $value;
            }
        }
    }

}