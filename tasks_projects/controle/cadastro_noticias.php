<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Notícias - EMPARN</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--Chave API tinymce-->
    <script src="https://cdn.tiny.cloud/1/9d4u5hfzh4o32wm7its2j0pp4f0y03n88ysh73vfxn63ogiq/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script>
      tinymce.init({
        selector: 'textarea#default'
      });
    </script>

    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* espaço para o menu lateral */
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            footer {
                margin-left: 0;
            }
        }

        .card {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .status-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
    
</head>
<body>
    <!-- Menu reutilizável -->
    <?php include '../templates/gen_menu.php'; ?>
    <?php
    require_once "../config/connection.php";
    include '../templates/gen_menu.php';
    ?>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <h2 class="mb-4 text-primary">📰 Cadastro de Notícias</h2>

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
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        🔗 Layout da Notícia
                    </div>
                    <div class="card-body">
                        <div id="statusAnuncio" class="status-label"></div>
                        <form id="formAnuncio" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Título:</label>
                                <input type="text" name="titulo" class="form-control" required>
                                <div class="invalid-feedback">Informe o título.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subtítulo:</label>
                                <input type="text" name="subtitulo" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Autoria:</label>
                                <input type="text" name="autoria" class="form-control" required>
                                <div class="invalid-feedback">Informe a autoria.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link da Notícia:</label>
                                <input type="url" id="linkInput" name="link" class="form-control" placeholder="https://www.exemplo.com" required>
                                <div class="invalid-feedback">Informe um link válido.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Imagem:</label>
                                <input type="file" name="imagem" class="form-control" accept="image/*" required>
                                <div class="invalid-feedback">Selecione uma imagem.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fonte da Imagem:</label>
                                <textarea name="fonte_imagem" class="form-control" required></textarea>
                                <div class="invalid-feedback">Informe a fonte da imagem.</div>
                            </div>
                            <button type="button" class="btn btn-success px-4" onclick="salvarTemporario('anuncio')">Salvar Anúncio</button>
                            <div id="msgAnuncio" class="alert mt-2 d-none"></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulário de Notícia Completa Interna -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        📝 Notícia Completa
                    </div>
                    <div class="card-body">
                        <div id="statusNoticia" class="status-label"></div>
                        <form id="formNoticia" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Título:</label>
                                <input type="text" name="titulo" class="form-control" required>
                                <div class="invalid-feedback">Informe o título.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subtítulo:</label>
                                <input type="text" name="subtitulo" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Autoria:</label>
                                <input type="text" name="autoria" class="form-control" required>
                                <div class="invalid-feedback">Informe a autoria.</div>
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
                                        <textarea name="topicos[0][texto]" id="default" class="form-control editor" rows="4" required></textarea>
                                        <div class="invalid-feedback">Informe o texto do tópico.</div>
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
                            <button type="button" class="btn btn-danger px-4" onclick="salvarTemporario('noticia')">Salvar Notícia Completa</button>
                            <div id="msgNoticia" class="alert mt-2 d-none"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-light text-center py-3 mt-4">
        <small>© <?= date('Y') ?> EMPARN - Painel de Controle</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/noticias.js"></script>
</body>
</html>
