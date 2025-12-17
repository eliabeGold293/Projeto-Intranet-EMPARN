<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'config/config.php';
//include_once 'apis/auth.php';

$url = $_GET['url'] ?? 'login';
//var_dump($url);

// criar o caminho da página com o nome que está na primeira posição do array, criado acima e atribuir a extensão .php.
$arquivo = $url;
// var_dump($arquivo);

switch($arquivo){

    case 'login':
        include 'public/login.php';
        break;

    case 'home':
        include 'public/home.php';
        break;
    
    case 'logout':
        include 'public/logout.php';
        break;
    
    case 'noticia-gen':
        if (!isset($_GET['id'])) {
            die('ID da notícia não informado');
        }
        include __DIR__ . '/public/noticia_gen.php';
        break;

    case 'primeiro-acesso':
        include 'public/primeiro_acesso.php';
        break;
    
    case 'todas-as-noticias':
        include 'public/todas_as_noticias.php';
        break;

    case 'auth':
        include 'apis/auth.php';
        break;

    case 'perfil-us':
        include 'public/perfil_us.php';
        break;

    case 'controle':
        include 'controle/index_controle.php';
        break;

    default:
        http_response_code(404);
        echo 'Página não encontrada';
        break;
}

?>