<?php

ini_set('default_charset', 'utf-8');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

use TairoLima\Test\ProdutoRepository;

try{
    print_r("\n[Atualiza todos os registros]\n");

    $produtoRepository = ProdutoRepository::getInstancia();

    $total = $produtoRepository->updateAll();

    print_r("Total de itens: {$total} \n\n");

}catch (\Exception $e){
    print_r("{$e->getCode()} - {$e->getMessage()}");
}
