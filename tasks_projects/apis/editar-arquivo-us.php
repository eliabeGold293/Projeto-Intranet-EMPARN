<?php
session_start();
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../utils/log-action.php'; // IMPORTANTE

try {

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não autenticado.");
    }

    if (!isset($_POST['id'])) {
        throw new Exception("Arquivo inválido.");
    }

    $usuario_id = $_SESSION['usuario_id'];
    $id = (int) $_POST['id'];
    $descricao = $_POST['descricao'] ?? null;

    // ================================
    // BUSCAR DADOS ATUAIS
    // ================================
    $stmt = $pdo->prepare("SELECT * FROM arquivo_usuario WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$arquivo || $arquivo['usuario_id'] != $usuario_id) {
        throw new Exception("Você não tem permissão para editar este arquivo.");
    }

    // Guardar dados antigos para log
    $nomeAntigo = $arquivo['nome_original'];
    $descricaoAntiga = $arquivo['descricao'];

    $caminhoBanco = $arquivo['caminho_armazenado'];
    $tipo = $arquivo['tipo'];
    $tamanho = $arquivo['tamanho'];
    $nomeOriginal = $arquivo['nome_original'];

    $arquivoSubstituido = false;

    // ================================
    // SE ENVIOU NOVO ARQUIVO
    // ================================
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {

        $arquivoNovo = $_FILES['arquivo'];

        $pastaDestino = __DIR__ . '/../uploads/';

        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $nomeOriginal = $arquivoNovo['name'];
        $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);

        $nomeUnico = uniqid() . '_' . basename($nomeOriginal);
        $caminhoFisico = $pastaDestino . $nomeUnico;

        if (!move_uploaded_file($arquivoNovo['tmp_name'], $caminhoFisico)) {
            throw new Exception("Erro ao mover novo arquivo.");
        }

        // Remove o antigo
        $arquivoAntigoFisico = __DIR__ . '/../' . $arquivo['caminho_armazenado'];

        if (file_exists($arquivoAntigoFisico)) {
            unlink($arquivoAntigoFisico);
        }

        $caminhoBanco = 'uploads/' . $nomeUnico;
        $tipo = $arquivoNovo['type'];
        $tamanho = $arquivoNovo['size'];

        $arquivoSubstituido = true;
    }

    // ================================
    // UPDATE
    // ================================
    $sql = "UPDATE arquivo_usuario
            SET descricao = :descricao,
                caminho_armazenado = :caminho,
                nome_original = :nome_original,
                tipo = :tipo,
                tamanho = :tamanho
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':descricao' => $descricao,
        ':caminho' => $caminhoBanco,
        ':nome_original' => $nomeOriginal,
        ':tipo' => $tipo,
        ':tamanho' => $tamanho,
        ':id' => $id
    ]);

    // ================================
    // LOG
    // ================================
    try {

        $mensagem = "Arquivo '{$nomeAntigo}' (ID {$id}) atualizado";

        if ($descricaoAntiga !== $descricao) {
            $mensagem .= " | Descrição alterada";
        }

        if ($arquivoSubstituido) {
            $mensagem .= " | Arquivo substituído por '{$nomeOriginal}'";
        }

        registrarLog(
            $pdo,
            $usuario_id,
            'arquivo_usuario',
            'UPDATE',
            $mensagem
        );

    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }

    echo "<script>alert('Arquivo atualizado com sucesso!'); window.location.href=document.referrer;</script>";

} catch (Exception $e) {

    echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";

}