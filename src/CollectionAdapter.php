<?php


namespace TairoLima\MongodbODM;


use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Query;

class CollectionAdapter
{
    private string $mCollectionName;
    private Connection $mConnection;

    public function __construct(string $collectionName, Connection $connection)
    {
        print_r("\t[CollectionAdapter]\n");
        $this->mCollectionName = $collectionName;
        $this->mConnection     = $connection;
    }

    private function getDatabaseCollection(): string
    {
        // DATABASE.COLLECTION
        return "{$this->mConnection->getDatabase()}.{$this->mCollectionName}";
    }


    /// Metodos de acesso ao banco de dados MongoDB

    public function find(array $params = []): array
    {
        $filter  = array();
        $options = array();

        //filter - conditions
        $this->filterConditions($filter, $params);
        /*if (isset($params['conditions']))
        {
            $filter = $params['conditions'];
        }*/

        //options - limit
        $this->optionsLimit($options, $params);
        /*if (isset($params['limit']))
        {
            $options['limit'] = (int) $params['limit'];
        }*/

        //options - sort
        $this->optionsSort($options, $params);
        /*if (isset($params['sort']))
        {
            $options['sort'] = $params["sort"];
        }*/

        //options - fields
        $this->optionsFields($options, $params);
        /*$options['projection'] = [];
        if (isset($params['fields']) && is_array($params['fields']) && !empty($params['fields']))
        {
            foreach ($params['fields'] as $key => $value)
            {
                $options['projection'][$key] = $value;
            }
        }*/

        try {
            /*$query  = new Query($filter, $options);
            $cursor = $this->mConnection->getManager()->executeQuery($this->getDatabaseCollection(), $query);

            $cursor->setTypeMap(['root' => 'object', 'document' => 'array', 'array' => 'array']);*/

            $cursor = $this->executeQuery($filter, $options);

            $data = [];
            foreach ($cursor as $document)
            {
                $document->_id = (string)$document->_id;

                array_push($data, $document);
            }

            return $data;

        } catch (Exception $e) {
            return [];
        }
    }

    public function findById(string $id): ?\stdClass
    {
        try {
            $filter  = array("_id" => new ObjectId($id));
            $options = array();

            $cursor = $this->executeQuery($filter, $options);
            /*$query  = new Query($filter, $options);
            $cursor = $this->mConnection->getManager()->executeQuery($this->getDatabaseCollection(), $query);

            $cursor->setTypeMap(['root' => 'object', 'document' => 'array', 'array' => 'array']);*/

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

    public function update(array $data): bool
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
                ['multi' => false, 'upsert' => false]
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

    public function updateAll(array $data, array $conditions): bool
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
                ['multi' => true, 'upsert' => false]
            );

            $result = $this->mConnection->getManager()->executeBulkWrite($this->getDatabaseCollection(), $bulk);

            if ($result->getModifiedCount() >= 1)
            {
                return true;
            }

            return false;

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

    public function deleteAll(array $conditions, bool $deleteWithoutParams = false): bool
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
                return true;
            }

            return false;

        }catch (Exception $e){
            return false;
        }
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