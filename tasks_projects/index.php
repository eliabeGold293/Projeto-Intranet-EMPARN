<?php
include_once 'config/config.php';

$url = (!empty(filter_input(INPUT_GET, 'url', FILTER_DEFAULT)) ? filter_input(INPUT_GET, 'url', FILTER_DEFAULT): 'login');

// Convertendo a URL de uma string para um array.
$url = array_filter(explode('/', $url));

//var_dump($url);

// criando o caminho da paǵina com o nome que está na primeira posição do array criado acima e atribuir a extensão .php

$arquivo = 'public/' . $url[0] . '.php';
//var_dump($arquivo);

# verfifica se o arquivo está realmente dentro da pasta public
if (is_file($arquivo)){
    include $arquivo;
}else{
    include 'public/404.php';
}

?>