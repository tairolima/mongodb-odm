<?php


namespace TairoLima\MongodbODM;


use MongoDB\BSON\ObjectId;

class MongoObjectID
{
    public static function getId(?string $id = null): ObjectId
    {
        if ($id == null)
        {
            //cria um novo ID
            return new ObjectId();
        }

        //retorna ObjectId
        return new ObjectId($id);
    }
}