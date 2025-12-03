<?php
session_start();
require_once "../config/connection.php"; // já contém $pdo

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);

        // Busca usuário pelo email e já traz o grau_acesso
        $sql = "SELECT u.id, u.nome, u.email, u.senha, u.classe_id, 
                       c.nome AS classe_nome, c.grau_acesso
                FROM usuario u
                JOIN classe_usuario c ON u.classe_id = c.id
                WHERE u.email = :email
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Cria sessão
            $_SESSION['usuario_id']    = $usuario['id'];
            $_SESSION['usuario_nome']  = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['classe_nome']   = $usuario['classe_nome'];   // opcional
            $_SESSION['grau_acesso']   = $usuario['grau_acesso'];   // 1 a 4

            // Retorna JSON de sucesso
            echo json_encode([
                "success" => true,
                "grau_acesso" => $usuario['grau_acesso'],
                "usuario_nome" => $usuario['nome']
            ]);
        } else {
            // Login inválido
            echo json_encode([
                "success" => false,
                "error" => "credenciais"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "error" => "metodo_invalido"
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro de banco: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "servidor"
    ]);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "servidor"
    ]);
}
