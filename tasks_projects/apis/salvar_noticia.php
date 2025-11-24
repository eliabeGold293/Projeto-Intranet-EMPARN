<?php
require_once "../config/connection.php"; // já tem $pdo

try {
    // Captura dos dados principais
    $titulo       = $_POST['titulo'] ?? null;
    $subtitulo    = $_POST['subtitulo'] ?? null;
    $autoria      = $_POST['autoria'] ?? null;
    $link         = $_POST['link'] ?? null;
    $fonte_imagem = $_POST['fonte_imagem'] ?? null;
    $texto        = $_POST['texto'] ?? null;

    // Tipo de notícia (enviado pelo JS junto com o FormData)
    $tipoNoticia  = $_POST['tipo_noticia'] ?? null;

    if (!$titulo || !$autoria) {
        throw new Exception("Título e autoria são obrigatórios.");
    }

    // Decodifica link se vier em Base64
    if ($link) {
        $decoded = base64_decode($link, true);
        if ($decoded !== false) {
            $link = $decoded;
        }
    }

    // Valida obrigatórios conforme tipo
    if ($tipoNoticia === "existente" && !$link) {
        throw new Exception("Link é obrigatório para notícia existente.");
    }

    if ($tipoNoticia === "propria" && !$texto) {
        $temTextoTopico = false;

        if (isset($_POST['topicos']) && is_array($_POST['topicos'])) {
            foreach ($_POST['topicos'] as $topico) {
                if (!empty($topico['texto'])) {
                    $temTextoTopico = true;
                    break;
                }
            }
        }

        if (!$texto) {
            $texto = "Notícia composta apenas por tópicos.";
        }
    }

    // Preenche texto padrão para anúncio externo se estiver vazio
    if ($tipoNoticia === "existente" && !$texto) {
        $texto = "Anúncio externo sem conteúdo textual.";
    }

    // Pasta de uploads
    $pasta = __DIR__ . "/../uploads_noticias/";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    // Upload da imagem principal (se existir)
    $caminhoImagemPrincipal = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $nome_imagem = time() . "_" . basename($_FILES['imagem']['name']);
        $caminhoImagemPrincipal = "uploads_noticias/" . $nome_imagem;
        $destino_fisico = $pasta . $nome_imagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino_fisico)) {
            throw new Exception("Erro ao fazer upload da imagem principal.");
        }
    }

    // Inserção da notícia
    $sql = "INSERT INTO noticias (titulo, subtitulo, texto, imagem, autoria, link, fonte_imagem) 
            VALUES (:titulo, :subtitulo, :texto, :imagem, :autoria, :link, :fonte_imagem)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titulo'       => $titulo,
        ':subtitulo'    => $subtitulo,
        ':texto'        => $texto,
        ':imagem'       => $caminhoImagemPrincipal,
        ':autoria'      => $autoria,
        ':link'         => $link,
        ':fonte_imagem' => $fonte_imagem
    ]);

    $idNoticia = $pdo->lastInsertId();

    // Atualiza o link correto para notícia própria
    if ($tipoNoticia === "propria") {
        $link = "../public/noticia_gen.php?id=" . $idNoticia;
        $sqlUpdate = "UPDATE noticias SET link = :link WHERE id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':link' => $link,
            ':id'   => $idNoticia
        ]);
    }

    // Se houver tópicos, salvar na tabela noticia_topicos
    if (isset($_POST['topicos']) && is_array($_POST['topicos'])) {
        foreach ($_POST['topicos'] as $index => $topico) {
            $tituloTopico = $topico['titulo'] ?? null;
            $textoTopico  = $topico['texto'] ?? null;
            $fonteTopico  = $topico['fonte_imagem'] ?? null;

            if (!$textoTopico) continue;

            $caminhoImagemTopico = null;
            if (isset($_FILES['topicos']['name'][$index]['imagem']) && $_FILES['topicos']['error'][$index]['imagem'] === 0) {
                $nome_img_topico = time() . "_" . basename($_FILES['topicos']['name'][$index]['imagem']);
                $caminhoImagemTopico = "uploads_noticias/" . $nome_img_topico;
                $destino_topico = $pasta . $nome_img_topico;

                if (!move_uploaded_file($_FILES['topicos']['tmp_name'][$index]['imagem'], $destino_topico)) {
                    throw new Exception("Erro ao fazer upload da imagem do tópico $index.");
                }
            }

            $sqlTopico = "INSERT INTO noticia_topicos (noticia_id, titulo_topico, texto_topico, imagem_topico, fonte_imagem, ordem)
                          VALUES (:noticia_id, :titulo_topico, :texto_topico, :imagem_topico, :fonte_imagem, :ordem)";
            $stmtTopico = $pdo->prepare($sqlTopico);
            $stmtTopico->execute([
                ':noticia_id'   => $idNoticia,
                ':titulo_topico'=> $tituloTopico,
                ':texto_topico' => $textoTopico,
                ':imagem_topico'=> $caminhoImagemTopico,
                ':fonte_imagem' => $fonteTopico,
                ':ordem'        => $index+1
            ]);
        }
    }

    // Registrar ação no log
    $descricao = "Notícia '{$titulo}' criada";
    $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                              VALUES (:usuario_id, 'noticias', 'INSERIR', :descricao)");
    // Aqui você pode usar o ID do usuário logado na sessão, se houver.
    // Como exemplo, deixamos NULL.
    $stmtLog->execute([
        ':usuario_id' => null,
        ':descricao'  => $descricao
    ]);

    header('Content-Type: application/json');
    echo json_encode([
        "status" => "success",
        "id"     => $idNoticia,
        "titulo" => $titulo,
        "link"   => $link // retorna o link correto também
    ]);

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
