<?php


namespace TairoLima\MongodbODM;


use MongoDB\Driver\Manager;

class Connection
{
    private string $database;
    private Manager $manager;
    private static self $mInstance;

    private function __construct(string $database, string $uri)
    {
        /***
            // Local - sem autenticação
            $uri = "mongodb://localhost:27017";
            //Autenticação
            $uri = "mongodb://{user}:{senha}@{host}:{port}/{database}";
         */
        print_r("\tCONEXAO\n");
        $this->database = $database;
        $this->manager  = new Manager($uri);
    }

    public static function getInstance(string $database, string $uri = "mongodb://localhost:27017")
    {
        if (!isset(self::$mInstance))
        {
            self::$mInstance = new self($database, $uri);
        }

        return self::$mInstance;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }
}