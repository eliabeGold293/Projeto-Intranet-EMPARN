<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php';

session_start();

header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Origin: *");

try {
    $idNoticia    = $_POST['id'] ?? null;
    $titulo       = $_POST['titulo'] ?? null;
    $subtitulo    = $_POST['subtitulo'] ?? null;
    $autoria      = $_POST['autoria'] ?? null;
    $link         = $_POST['link'] ?? null;
    $texto        = $_POST['texto'] ?? null;
    $fonte_imagem = $_POST['fonte_imagem'] ?? null;

    if (!$titulo || !$subtitulo || !$autoria || !$texto || !$fonte_imagem) {
        throw new Exception("Preencha todos os campos da notícia por favor.");
    }

    // Usuário logado
    $usuarioLogadoId = $_SESSION['usuario_id'] ?? null;

    // ===== Pasta upload =====
    $pasta = __DIR__ . "/../uploads/uploads_noticias/";
    if (!is_dir($pasta)) mkdir($pasta, 0777, true);

    // ===== Upload imagem principal =====
    $caminhoImagemPrincipal = null;

    if (!empty($_FILES['imagem']['name'])) {
        $nome_imagem = time() . "_" . basename($_FILES['imagem']['name']);
        $caminhoImagemPrincipal = "uploads_noticias/" . $nome_imagem;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $nome_imagem);
    }

    // ===========================================
    // INSERT OU UPDATE
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

        $acao = "UPDATE";
        $descricaoLog = "Notícia '{$titulo}' (ID {$idNoticia}) atualizada.";

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

        $acao = "CREATE";
        $descricaoLog = "Notícia criada: '{$titulo}' (ID {$idNoticia}).";
    }

    // ===========================================
    // TÓPICOS (mantido igual)
    // ===========================================
    $topicos = $_POST['topicos'] ?? [];

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

        $caminhoImg = null;

        if (
            isset($_FILES['topicos']['name'][$index]['imagem']) &&
            $_FILES['topicos']['error'][$index]['imagem'] === 0
        ) {
            $nomeImg = time() . "_" . basename($_FILES['topicos']['name'][$index]['imagem']);
            $caminhoImg = "uploads_noticias/" . $nomeImg;

            move_uploaded_file(
                $_FILES['topicos']['tmp_name'][$index]['imagem'],
                $pasta . $nomeImg
            );
        }

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

    $idsParaRemover = array_diff($idsExistentes, $idsMantidos);

    if (!empty($idsParaRemover)) {
        $in = implode(",", array_fill(0, count($idsParaRemover), "?"));
        $sql = "DELETE FROM noticia_topicos WHERE id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($idsParaRemover));
    }

    // ===========================================
    // LOG PADRONIZADO
    // ===========================================
    registrarLog(
        $pdo,
        $usuarioLogadoId,
        "noticia",
        $acao,
        $descricaoLog
    );

    // ===========================================
    // RETORNO
    // ===========================================
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