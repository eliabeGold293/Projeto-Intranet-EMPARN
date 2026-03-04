<?php

session_start();
require_once __DIR__ . '/../config/connection.php';

try {

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não autenticado.");
    }

    $usuario_id = $_SESSION['usuario_id'];

    /* ==============================
        VERIFICAÇÃO DE PERMISSÃO
       ============================== */

    $stmtPermissao = $pdo->prepare("
        SELECT cu.grau_acesso
        FROM usuario u
        INNER JOIN classe_usuario cu ON cu.id = u.classe_id
        WHERE u.id = ?
    ");

    $stmtPermissao->execute([$usuario_id]);
    $grauAcesso = (int) $stmtPermissao->fetchColumn();

    if ($grauAcesso < 2) {
        throw new Exception("Você não tem permissão para enviar arquivos.");
    }

    /* ==============================
        VALIDAÇÃO DO ARQUIVO
       ============================== */

    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no envio do arquivo.");
    }

    $arquivo = $_FILES['arquivo'];
    $descricao = $_POST['descricao'] ?? null;

    $nomeOriginal = $arquivo['name'];
    $tipo = $arquivo['type'];
    $tamanho = $arquivo['size'];

    $pastaDestino = __DIR__ . '/../uploads/';
    
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    $nomeUnico = uniqid() . '_' . basename($nomeOriginal);
    $caminhoFinal = $pastaDestino . $nomeUnico;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
        throw new Exception("Erro ao mover o arquivo.");
    }

    $caminhoBanco = 'uploads/' . $nomeUnico;

    $sql = "INSERT INTO arquivo_usuario 
            (usuario_id, nome_original, caminho_armazenado, descricao, tipo, tamanho)
            VALUES 
            (:usuario_id, :nome_original, :caminho, :descricao, :tipo, :tamanho)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':nome_original' => $nomeOriginal,
        ':caminho' => $caminhoBanco,
        ':descricao' => $descricao,
        ':tipo' => $tipo,
        ':tamanho' => $tamanho
    ]);

    echo "<script>alert('Upload realizado com sucesso!'); window.history.back();</script>";

} catch (Exception $e) {

    echo "<script>alert('Erro no upload: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";

}