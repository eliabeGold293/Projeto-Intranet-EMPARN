<?php 
require_once "../config/connection.php";

$id          = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$nome        = $_POST['nome'] ?? null;
$email       = $_POST['email'] ?? null;
$senha       = $_POST['senha'] ?? null;
$classe_id   = isset($_POST['classe_id']) ? (int) $_POST['classe_id'] : null;
$area_id     = isset($_POST['area_id']) ? (int) $_POST['area_id'] : null;

if ($id > 0) {
    try {
        $campos = [];
        $params = [':id' => $id];

        if ($nome) {
            $campos[] = "nome = :nome";
            $params[':nome'] = $nome;
        }
        if ($email) {
            $campos[] = "email = :email";
            $params[':email'] = $email;
        }
        if ($senha) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $campos[] = "senha = :senha";
            $params[':senha'] = $senha_hash;
        }
        if ($classe_id) {
            $campos[] = "classe_id = :classe_id";
            $params[':classe_id'] = $classe_id;
        }
        if ($area_id) {
            $campos[] = "area_id = :area_id";
            $params[':area_id'] = $area_id;
        }

        if (!empty($campos)) {
            $sql = "UPDATE usuario SET " . implode(", ", $campos) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo "✅ Alterações realizadas com sucesso!";
        } else {
            echo "⚠️ Nenhum campo foi informado para atualização.";
        }

    } catch (PDOException $e) {
        echo "❌ Erro ao salvar alterações: " . $e->getMessage();
    }
} else {
    echo "⚠️ Informe um ID válido.";
}

