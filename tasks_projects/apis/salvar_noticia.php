<?php
require_once "../config/connection.php";

try {
    $idNoticia    = $_POST['id'] ?? null;
    $titulo       = $_POST['titulo'] ?? null;
    $subtitulo    = $_POST['subtitulo'] ?? null;
    $autoria      = $_POST['autoria'] ?? null;
    $link         = $_POST['link'] ?? null;
    $texto        = $_POST['texto'] ?? null;
    $fonte_imagem = $_POST['fonte_imagem'] ?? null;

    if (!$titulo || !$autoria || !$texto) {
        throw new Exception("Título, autoria e conteúdo são obrigatórios.");
    }

    // ===== Criar pasta de uploads =====
    $pasta = __DIR__ . "/../uploads_noticias/";
    if (!is_dir($pasta)) mkdir($pasta, 0777, true);


    // ===========================================
    //  UPLOAD IMAGEM PRINCIPAL
    // ===========================================
    $caminhoImagemPrincipal = null;

    if (!empty($_FILES['imagem']['name'])) {
        $nome_imagem = time() . "_" . basename($_FILES['imagem']['name']);
        $caminhoImagemPrincipal = "uploads_noticias/" . $nome_imagem;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $nome_imagem);
    }


    // ===========================================
    //  INSERIR OU ATUALIZAR NOTÍCIA
    // ===========================================
    if ($idNoticia) {
        // UPDATE
        $sql = "UPDATE noticias SET 
                    titulo = :titulo, 
                    subtitulo = :subtitulo,
                    texto = :texto,
                    imagem = COALESCE(:imagem, imagem),
                    autoria = :autoria,
                    link = :link,
                    fonte_imagem = :fonte_imagem,
                    data_edicao = NOW()
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':subtitulo' => $subtitulo,
            ':texto' => $texto,
            ':imagem' => $caminhoImagemPrincipal,
            ':autoria' => $autoria,
            ':link' => $link,
            ':fonte_imagem' => $fonte_imagem,
            ':id' => $idNoticia
        ]);
    } else {
        // INSERT
        $sql = "INSERT INTO noticias (titulo, subtitulo, texto, imagem, autoria, link, fonte_imagem)
                VALUES (:titulo, :subtitulo, :texto, :imagem, :autoria, :link, :fonte_imagem)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':subtitulo' => $subtitulo,
            ':texto' => $texto,
            ':imagem' => $caminhoImagemPrincipal,
            ':autoria' => $autoria,
            ':link' => $link,
            ':fonte_imagem' => $fonte_imagem
        ]);
        $idNoticia = $pdo->lastInsertId();
    }


    // =====================================================
    //  TRATAMENTO DE TÓPICOS — (INSERIR / ATUALIZAR / REMOVER)
    // =====================================================

    $topicos = $_POST['topicos'] ?? [];

    // ----- 1. Obter IDs atuais dos tópicos no banco -----
    $stmt = $pdo->prepare("SELECT id FROM noticia_topicos WHERE noticia_id = ?");
    $stmt->execute([$idNoticia]);
    $idsExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $idsMantidos = [];

    $i = 0;
    foreach ($topicos as $index => $t) {

        $idTopico = $t['id'] ?? null;
        $tituloT = $t['titulo'] ?? null;
        $textoT  = $t['texto'] ?? null;
        $fonteT  = $t['fonte_imagem'] ?? null;

        if (!$textoT) continue;

        // ----- 2. Upload da imagem do tópico -----
        $campoArquivo = "topicos_{$index}_imagem";
        $caminhoImg = null;

        if (isset($_FILES[$campoArquivo]) && $_FILES[$campoArquivo]['error'] === 0) {
            $nomeImg = time() . "_" . basename($_FILES[$campoArquivo]['name']);
            $caminhoImg = "uploads_noticias/" . $nomeImg;
            move_uploaded_file($_FILES[$campoArquivo]['tmp_name'], $pasta . $nomeImg);
        }

        // ----- 3. Atualizar se tiver ID -----
        if ($idTopico) {
            $sql = "UPDATE noticia_topicos SET
                        titulo = :titulo,
                        texto = :texto,
                        imagem = COALESCE(:imagem, imagem),
                        fonte_imagem = :fonte_imagem,
                        ordem = :ordem
                    WHERE id = :id AND noticia_id = :noticia_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $tituloT,
                ':texto' => $textoT,
                ':imagem' => $caminhoImg,
                ':fonte_imagem' => $fonteT,
                ':ordem' => $i,
                ':id' => $idTopico,
                ':noticia_id' => $idNoticia
            ]);

            $idsMantidos[] = $idTopico;
        } else {
            // ----- 4. Inserir tópico novo -----
            $sql = "INSERT INTO noticia_topicos (noticia_id, titulo, texto, imagem, fonte_imagem, ordem)
                    VALUES (:noticia_id, :titulo, :texto, :imagem, :fonte_imagem, :ordem)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':noticia_id' => $idNoticia,
                ':titulo' => $tituloT,
                ':texto' => $textoT,
                ':imagem' => $caminhoImg,
                ':fonte_imagem' => $fonteT,
                ':ordem' => $i
            ]);

            $idsMantidos[] = $pdo->lastInsertId();
        }

        $i++;
    }

    // ----- 5. Remover tópicos excluídos no frontend -----
    $idsParaRemover = array_diff($idsExistentes, $idsMantidos);

    if (!empty($idsParaRemover)) {
        $in = implode(",", array_fill(0, count($idsParaRemover), "?"));
        $sql = "DELETE FROM noticia_topicos WHERE id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($idsParaRemover));
    }


    // ===================================
    //  RETORNO JSON
    // ===================================
    echo json_encode([
        "status" => "success",
        "id" => $idNoticia,
        "titulo" => $titulo
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
