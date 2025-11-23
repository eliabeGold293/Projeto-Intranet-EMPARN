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
        // Buscar dados antigos do usuário antes da atualização
        $stmtAntigo = $pdo->prepare("SELECT nome, email FROM usuario WHERE id = :id");
        $stmtAntigo->execute([':id' => $id]);
        $usuarioAntigo = $stmtAntigo->fetch(PDO::FETCH_ASSOC);

        if (!$usuarioAntigo) {
            echo "❌ Usuário não encontrado.";
            exit;
        }

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

            // Registrar ação no log
            $descricao = "✏️ Usuário '{$usuarioAntigo['nome']}' atualizado";
            if ($nome) {
                $descricao .= " → novo nome: '{$nome}'";
            }
            if ($email) {
                $descricao .= " → novo email: '{$email}'";
            }
            if ($classe_id) {
                $descricao .= " → nova classe_id: {$classe_id}";
            }
            if ($area_id) {
                $descricao .= " → nova area_id: {$area_id}";
            }
            if ($senha) {
                $descricao .= " → senha alterada";
            }

            $stmtLog = $pdo->prepare("INSERT INTO log_acao (usuario_id, entidade, acao, descricao) 
                                      VALUES (:usuario_id, 'usuario', 'ATUALIZAR', :descricao)");
            // Aqui você pode usar o ID do usuário logado na sessão, se houver.
            // Como exemplo, deixamos NULL.
            $stmtLog->execute([
                ':usuario_id' => null,
                ':descricao'  => $descricao
            ]);

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
?>
