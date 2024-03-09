<?php


namespace TairoLima\Test;


use TairoLima\MongodbODM\Connection;
use TairoLima\MongodbODM\RepositoryAdapter;

abstract class Repository
{
    protected RepositoryAdapter $mRepository;

    public function __construct(string $collectionName)
    {
        //Arquivo de configuração
        $config = require_once __DIR__ . "/config.php";

        //print_r("\t--- Repository.php\n");
        $conexao = Connection::getInstance($config["database"], $config["uri"]);

        //cria instância do Objeto e define nome da Collection
        $this->mRepository = new RepositoryAdapter($collectionName, $conexao);
    }
}