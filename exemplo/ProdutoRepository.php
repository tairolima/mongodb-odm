<?php


namespace TairoLima\Test;

use TairoLima\MongodbODM\DataHora;
use TairoLima\MongodbODM\MongoObjectID;

class ProdutoRepository extends Repository
{
    private static ?self $mInstancia;

    private function __construct(string $collectionName)
    {
        //print_r("\t--- ProdutoRepository\n");
        parent::__construct($collectionName);
    }

    public static function getInstancia(): self
    {
        if (!isset(self::$mInstancia))
        {
            self::$mInstancia = new self("Produto");
        }

        return self::$mInstancia;
    }


    public function getAll(): array
    {
        //return $this->mRepository->find();

        return $this->mRepository->find([
            //"fields" => ["nome" => 1],
            //"conditions" => ["nome" => "Mouse USB - 900"],
            //"sort" => ["_id" => -1],
            "limit" => 5
        ]);
    }

    public function adicionar(array $produto)
    {
        $this->mRepository->create($produto);
    }

    public function visualizar(string $id): ?\stdClass
    {
        /*return $this->mRepository->findFirst([
            "conditions" => ["nome" => "Notebook Dell"]
        ]);*/
        return $this->mRepository->findById($id);
    }

    public function atualizar(\stdClass $produto): bool
    {
        $data = [
            "_id" => MongoObjectID::getId($produto->_id),
            "item" => $produto->item,
            "nome" => $produto->nome ,
            "preco" => $produto->preco,
            "ativo" => $produto->ativo,
            "tags" => ["Kotlin", "PHP8", "MongoDB", "Java", "C#"],
            "categoria" => ["id" => 1, "descricao" => "Livros"],
            "lojas" => [
                ["nome" => "Amazon", "preco" => 100.00],
                ["nome" => "AWS", "preco" => 103.50],
                ["nome" => "Azure", "preco" => 99.87],
            ],
            //"created" => null,
            "modified" => DataHora::getDataHoraAtualMongoDB()
        ];

        //salva no banco
        return $this->mRepository->update($data);
    }

    public function updateAll(): int
    {
        $conditions = [
            "preco" =>  ['$lte' => 1_000]
        ];

        $data = [
            //"nome" => "Arquitetura Lima",
            "quantidade" => 50,
            "tags" => ["PHP", "C++", "SQL Server", "Oracle"]
        ];

        //salva no banco
        return $this->mRepository->updateAll($data, $conditions);
    }

    public function excluir(string $id): bool
    {
        return $this->mRepository->delete($id);
    }

    public function excluirTodos(): int
    {
        $conditions = [
            "preco" =>  ['$gte' => 1_000]
        ];

        return $this->mRepository->deleteAll($conditions);
    }

    public function fakeAdicionarProdutos()
    {
        for ($i = 1; $i <= 10_000; $i++)
        {
            $produto = [
                "_id" => MongoObjectID::getId(),
                "item" => $i,
                "nome" => "Livro - " . random_int(1, 999) ,
                "preco" => (float) (random_int(1, 10_001).".".random_int(0,99)),
                "ativo" => (bool) random_int(0, 1),
                "created" => DataHora::getDataHoraAtualMongoDB(),
                "modified" => null
            ];

            print_r(".");
            $this->adicionar($produto);
            //$this->mRepository->create($produto);
        }
    }

    public function total(): int
    {
        return $this->mRepository->count([]);
    }

    public function aggregate(): array
    {
        /*
          db.Produto.aggregate([
            {$match: {"preco": {$lte: 100} }},
            {$project: {"nome": 1, "preco": 1, "totalTags": {$size: "$tags"} }},
            {$limit: 10}
          ])
        */
        $pipeline = [
            ['$match' => ["preco" => array('$lte' => 10)  ] ],
            ['$project' => ["nome" => 1, "preco" => 1, "totalTags" => array('$size' => '$tags')] ],
            ['$sort' => ["preco" => -1] ],
            ['$limit' => 10]
        ];

        return $this->mRepository->aggregate($pipeline);
    }

    public function distinct()
    {
        $retorno = $this->mRepository->distinct("nome", ["preco" => array('$lte' => 250) ]);

        print_r($retorno);
    }

    public function existeRegistro(): void
    {
        // db.users.find({"name": {"$regex": "string", "$options": "i"}})
        $conditions = [
            "nome" => array('$regex' => "Kotlin", '$options' => "i")
        ];

        $retorno = $this->mRepository->isExistData($conditions);
        var_dump($retorno);
    }

}