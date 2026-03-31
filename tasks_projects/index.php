<?php
session_start();

include_once 'config/config.php';

$url = $_GET['url'] ?? 'login';

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS (não exigem login)
|--------------------------------------------------------------------------
*/
$rotas_publicas = [
    'login2',
    'login',
    'auth',
    'primeiro-acesso',
    'salvar-primeiro-acesso'
];

/*
|--------------------------------------------------------------------------
| PROTEÇÃO GLOBAL DE LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['grau_acesso'])) {

    if (!in_array($url, $rotas_publicas)) {
        header("Location: ?url=login");
        exit;
    }

}

/*
|--------------------------------------------------------------------------
| PROTEÇÃO DE CACHE (somente para usuários logados)
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['usuario_id'])) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

}

$arquivo = $url;

switch($arquivo){

    # Rotas de arquivos da pasta public

    case 'login2':
        include 'public/login2.php';
        break;
    
    case 'uploads-usuarios':
        include 'public/uploads-usuarios.php';
        break;

    case 'teste':
        include 'public/tests/teste.php';
        break;

    case 'view-projetos':
        include 'public/view-projetos.php';
        break;

    case 'login':
        include 'public/login.php';
        break;

    case 'home':
        include 'public/home.php';
        break;
    
    case 'logout':
        include 'public/logout.php';
        break;

    case 'documentos-institucionais':
        include 'public/documents_inst.php';
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

    case 'controle-projetos':
        include 'controle/controle-projetos.php';
        break;

    case 'relatorio-acesso':
        include 'controle/relatorio-acesso.php';
        break;

    case 'ger-projetos-tarefas':
        include 'controle/ger-projetos-tarefas.php';
        break;

    case 'editar-topico-documento':

        if (!isset($_GET['id'])) {
            die('ID do topico não informado');
        }
        include 'controle/editar_topico_doc.php';
        break;

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
    
    # Utils

    case 'ajax-filtro':
        include 'utils/ajax_filtro.php';
        break;

    # Rotas de apis
    case 'adicionar-arquivo-topico':
        include 'apis/adicionar-arquivo-topico.php';
        break;

    case 'editar-arquivo-inline':
        include 'apis/editar-arquivo-inline.php';
        break;

    case 'editar-topico-inline':
        include 'apis/editar-topico-inline.php';
        break;

    case 'editar-dashboard':
        include 'apis/editar-dashboard.php';
        break;

    case 'confirmar-controle':
        include 'apis/confirmar-controle.php';
        break;

    case 'prorrogar-tarefa':
        include 'apis/prorrogar-tarefa.php';
        break;

    case 'excluir-arquivo-us':
        include 'apis/excluir-arquivo-us.php';
        break;

    case 'editar-arquivo-us':
        include 'apis/editar-arquivo-us.php';
        break;

    case 'enviar-arquivo-us':
        include 'apis/enviar-arquivo-us.php';
        break;

    case 'buscar-tarefa':
        include 'apis/buscar-tarefa.php';
        break;

    case 'editar-tarefa':
        include 'apis/editar-tarefa.php';
        break;

    case 'excluir-tarefa':
        include 'apis/excluir-tarefa.php';
        break;

    case 'listar-tarefas':
        include 'apis/listar-tarefas.php';
        break;

    case 'salvar-tarefa':
        include 'apis/salvar-tarefa.php';
        break;

    case 'alterar-papel-usuario':
        include 'apis/alter-papel-us.php';
        break;

    case 'deletar-projeto':
        include 'apis/deletar-projeto.php';
        break;

    case 'remover-usuario-projeto':
        include 'apis/remover-usuario-projeto.php';
        break;

    case 'usuarios-projeto':
        include 'apis/listar-usuarios.php';
        break;

    case 'add-usuario-projeto':
        include 'apis/add-usuario-projeto.php';
        break;

    case 'editar-projeto':
        include 'apis/editar_projeto.php';
        break;

    case 'criar-projeto':
        include 'apis/adicionar_projeto.php';
        break;

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
        include 'apis/salvar_noticia.php';
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

    case 'adicionar-cards':
        include 'apis/salvar_cards.php';
        break;

    case 'deletar-cards':

        if (!isset($_GET['id'])) {
            die('ID do card não informado');
        }

        include 'apis/deletar_card.php';
        break;
    
    case 'excluir-topico-documento':
        include 'apis/excluir_topico.php';
        break;
    
    case 'salvar-documento':
        include 'apis/salvar_topicos.php';
        break;

    case 'salvar-edicao-documento':
        include 'apis/salvar_edicao_topicos.php';
        break;
    
    case 'remover-arquivo-doc':
        include 'apis/remover_arquivo.php';
        break;
    
    default:
        http_response_code(404);
        include 'public/404.php';
        break;
}

