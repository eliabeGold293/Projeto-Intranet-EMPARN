<?php
require_once "../config/connection.php"; // já tem $pdo

try {
    // Captura dos dados
    $titulo       = $_POST['titulo'] ?? null;
    $subtitulo    = $_POST['subtitulo'] ?? null;
    $texto        = $_POST['texto'] ?? null;
    $autoria      = $_POST['autoria'] ?? null;
    $link         = $_POST['link'] ?? null;
    $fonte_imagem = $_POST['fonte_imagem'] ?? null;

    if (!$titulo || !$texto || !$autoria || !$link || !$fonte_imagem) {
        throw new Exception("Dados obrigatórios não informados.");
    }

    // Upload da imagem
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== 0) {
        throw new Exception("Imagem não enviada ou inválida.");
    }

    $pasta = __DIR__ . "/../uploads_noticias/";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nome_imagem = time() . "_" . basename($_FILES['imagem']['name']);

    // Caminho salvo no banco (relativo ao site, acessível pelo navegador)
    $caminho = "uploads_noticias/" . $nome_imagem;

    // Caminho físico para mover o arquivo
    $destino_fisico = $pasta . $nome_imagem;

    if (!file_exists($_FILES['imagem']['tmp_name'])) {
        throw new Exception("Arquivo temporário não existe mais.");
    }
    if (!is_writable($pasta)) {
        throw new Exception("A pasta destino não é gravável.");
    }

    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino_fisico)) {
        throw new Exception("Erro ao fazer upload da imagem. Caminho destino: " . $destino_fisico);
    }

    // Inserção no banco (sem data_publicacao, o banco gera automaticamente)
    $sql = "INSERT INTO noticias (titulo, subtitulo, texto, imagem, autoria, link, fonte_imagem) 
            VALUES (:titulo, :subtitulo, :texto, :imagem, :autoria, :link, :fonte_imagem)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titulo'       => $titulo,
        ':subtitulo'    => $subtitulo,
        ':texto'        => $texto,
        ':imagem'       => $caminho,   // caminho limpo para o navegador
        ':autoria'      => $autoria,
        ':link'         => $link,
        ':fonte_imagem' => $fonte_imagem
    ]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        "status"       => "success",
        "id"           => $id,
        "titulo"       => $titulo,
        "imagem"       => $caminho,
        "link"         => $link,
        "fonte_imagem" => $fonte_imagem
    ]);

} catch (Exception $e) {
    // Tratamento de erro responsável
    http_response_code(500);
    echo "<div style='color:red; font-family:Arial; padding:20px;'>
            <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
}
?>
