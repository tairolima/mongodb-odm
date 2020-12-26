<?php
ini_set('default_charset', 'utf-8');
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

use TairoLima\Test\ProdutoRepository;

try{
    $produtoRepository = ProdutoRepository::getInstancia();

    $produtoRepository->fakeAdicionarProdutos();

    print_r("\nAdicionado com sucesso.\n");

}catch (\Exception $e){
    print_r("{$e->getCode()} - {$e->getMessage()}");
}
