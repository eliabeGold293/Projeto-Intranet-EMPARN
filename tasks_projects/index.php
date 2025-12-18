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

    # Rotas de arquivos da pasta public
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

    case 'perfil-us':
        include 'public/perfil_us.php';
        break;

    # Rotas de páginas de controle
    case 'view-noticias-existentes':
        include 'controle/get_noticias.php';
        break;

    case 'control':
        include 'controle/index_controle.php';
        break;

    case 'cadastrar-usuario':
        include 'controle/cadastrar_us.php';
        break;
    
    case 'listar-usuarios':
        include 'controle/get_us.php';
        break;

    case 'criar-classe':
        include 'controle/criar_classe.php';
        break;

    case 'listar-classes':
        include 'controle/listar_classes.php';
        break;
    
    case 'criar-nova-area':
        include 'controle/criar_area.php';
        break;
    
    case 'listar-areas-existentes':
        include 'controle/listar_areas.php';
        break;
    
    case 'cadastrar-noticias':
        include 'controle/cadastro_noticias.php';
        break;
    
    case 'gerenciador-de-dashboards':
        include 'controle/gerenciar_dashboard.php';
        break;
    
    case 'gerenciar-documentos-institucionais':
        include 'controle/documentos.php';
        break;
    
    case 'editar-noticia':

        if (!isset($_GET['id'])) {
            die('ID da notícia não informado');
        }

        include 'controle/editar_noticia.php';
        break;

    # Rotas de apis

    case 'auth':
        include 'apis/auth.php';
        break;

    case 'deletar-noticia':

        if (!isset($_GET['id'])) {
            die('ID da notícia não informado');
        }

        include 'apis/deletar_noticias.php';
        break;
    
    case 'salvar-noticia':
        include 'apis/salvar_noticias.php';
        break;
    
    case 'criar-area-de-atuacao':
        include 'apis/criar_area.php';
        break;
    
    case 'cadastrar-us':
        include 'apis/criar_us.php';
        break;
    
    case 'salvar-primeiro-acesso':
        include 'apis/primeiro_acesso_salvar.php';
        break;
    
    case 'deletar-usuario':
        include 'apis/deletar_us.php';
        break;
    
    case 'atualizar-info-usuario':
        include 'apis/set_us.php';
        break;
    
    case 'criar-classe-usuario-api':
        include 'apis/criar_classe_us.php';
        break;
    
    case 'atualizar-info-classe':
        include 'apis/set_classes.php';
        break;
    
    case 'deletar-classe-usuario':
        include 'apis/deletar_classes.php';
        break;
    
    case 'criar-area-usuario':
        include 'apis/criar_area.php';
        break;
    
    case 'atualizar-area-usuario':
        include 'apis/set_area.php';
        break;
    
    case 'deletar-area-usuario':
        include 'apis/deletar_area.php';
        break;
    
    case 'criar-nova-noticia':
        include 'apis/salvar_noticia.php';
        break;

    default:
        http_response_code(404);
        echo 'Página não encontrada';
        break;
}

?>