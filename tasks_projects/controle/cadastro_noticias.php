<?php
require_once "../config/connection.php";
include '../templates/gen_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Notícias - EMPARN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/noticias.css"> <!-- CSS separado -->
</head>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #f4f6f8;
        margin: 0;
    }

    .main-content {
        margin-left: 250px; /* espaço para o menu lateral */
        padding: 30px;
    }

    h2 {
        font-size: 1.8rem;
        font-weight: bold;
        color: #198754; /* verde bootstrap */
        margin-bottom: 20px;
    }

    form {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    form:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Diferenciação visual dos formulários */
    .form-anuncio h4 {
        color: #0d6efd; /* azul bootstrap */
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }

    .form-noticia h4 {
        color: #dc3545; /* vermelho bootstrap */
        border-bottom: 2px solid #dc3545;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }

    .btn-success {
        background-color: #0d6efd;
        border: none;
    }

    .btn-success:hover {
        background-color: #0b5ed7;
    }

    .btn-primary {
        background-color: #dc3545;
        border: none;
    }

    .btn-primary:hover {
        background-color: #bb2d3b;
    }

    footer {
        margin-left: 250px;
        background: #e9ecef;
        padding: 15px;
        text-align: center;
        border-top: 1px solid #ccc;
        margin-top: 40px;
    }

    /* Estilo para tópicos */
    .topico h5 {
        color: #198754;
        font-weight: bold;
    }

    .status-label {
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .status-label.habilitada {
        color: #198754; /* verde bootstrap */
    }

    .status-label.desabilitada {
        color: #dc3545; /* vermelho bootstrap */
    }

    .alert {
        font-size: 0.9rem;
        padding: 8px 12px;
    }


</style>
<body>

<div class="main-content">
    <h2>📰 Cadastro de Notícias</h2>

   <!-- Seletor de tipo de notícia + botão global -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <label class="form-label fw-bold">Tipo de notícia:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo_noticia" id="noticiaExistente" value="existente" checked>
                <label class="form-check-label" for="noticiaExistente">Notícia existente (link externo)</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo_noticia" id="noticiaPropria" value="propria">
                <label class="form-check-label" for="noticiaPropria">Cadastrar minha própria notícia</label>
            </div>
        </div>
        <button id="btnSalvarTudo" class="btn btn-success">💾 Salvar Todas Alterações</button>
    </div>

    <div class="row">
        <!-- Formulário de Anúncio Externo -->
        <div class="col-md-6">
            <div id="statusAnuncio" class="status-label"></div>
            <form id="formAnuncio" class="form-anuncio" method="POST" enctype="multipart/form-data">
                <h4>🔗 Anúncio Externo</h4>

                <div class="mb-3">
                    <label class="form-label">Título:</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subtítulo:</label>
                    <input type="text" name="subtitulo" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Autoria:</label>
                    <input type="text" name="autoria" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Link da Notícia:</label>
                    <input type="url" id="linkInput" name="link" class="form-control" placeholder="https://www.exemplo.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagem:</label>
                    <input type="file" name="imagem" class="form-control" accept="image/*" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fonte da Imagem:</label>
                    <textarea name="fonte_imagem" class="form-control" required></textarea>
                </div>

                <button type="button" class="btn btn-success px-4" onclick="salvarTemporario('anuncio')">Salvar Anúncio</button>
                <div id="msgAnuncio" class="alert alert-success mt-2 d-none">Alterações Salvas</div>
            </form>

        </div>
        <!-- Formulário de Notícia Completa Interna -->
        <div class="col-md-6">
            <div id="statusNoticia" class="status-label"></div>
            <form id="formNoticia" class="form-noticia" method="POST" enctype="multipart/form-data">
                <h4>📝 Notícia Completa</h4>
                <div class="mb-3">
                    <label class="form-label">Título:</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtítulo:</label>
                    <input type="text" name="subtitulo" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Autoria:</label>
                    <input type="text" name="autoria" class="form-control" required>
                </div>

                <!-- Container de tópicos -->
                <div id="topicos-container">
                    <div class="topico border rounded p-3 mb-3">
                        <h5>Tópico 1</h5>
                        <div class="mb-3">
                            <label class="form-label">Título do Tópico:</label>
                            <input type="text" name="topicos[0][titulo]" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Texto:</label>
                            <textarea name="topicos[0][texto]" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imagem:</label>
                            <input type="file" name="topicos[0][imagem]" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fonte da Imagem:</label>
                            <textarea name="topicos[0][fonte_imagem]" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary mb-3" onclick="adicionarTopico()">+ Adicionar Tópico</button>

                <button type="button" class="btn btn-primary px-4" onclick="salvarTemporario('noticia')">Salvar Notícia Completa</button>
                <div id="msgNoticia" class="alert alert-success mt-2 d-none">Alterações Salvas</div>

            </form>
        </div>
    </div>
</div>

<footer>
    <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/noticias.js"></script> <!-- JS separado -->
</body>
</html>
