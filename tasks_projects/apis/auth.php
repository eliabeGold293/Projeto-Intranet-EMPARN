<?php
session_start();
require_once "../config/connection.php";

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);

        // Busca usuário
        $sql = "SELECT 
                    u.id, u.nome, u.email, u.senha, u.classe_id, 
                    u.primeiro_acesso,
                    c.nome AS classe_nome, c.grau_acesso
                FROM usuario u
                JOIN classe_usuario c ON u.classe_id = c.id
                WHERE u.email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if (!$usuario) {
            $pdo->prepare("
                INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
                VALUES (NULL, 'auth', 'FALHA_LOGIN', :descricao)
            ")->execute([
                ":descricao" => "Tentativa de login com email inexistente '{$email}'"
            ]);

            echo json_encode([
                "success" => false,
                "error" => "credenciais"
            ]);
            exit;
        }

    
        if ($usuario['primeiro_acesso'] == true) {

            // Verifica se ele está tentando usar a senha temporária
            if (password_verify($senha, $usuario['senha'])) {

                // Bloqueia login normal
                $pdo->prepare("
                    INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
                    VALUES (:usuario_id, 'auth', 'PRIMEIRO_ACESSO_NEGADO', :descricao)
                ")->execute([
                    ":usuario_id" => $usuario['id'],
                    ":descricao" => "Usuário tentou usar senha de primeiro acesso na tela de login normal"
                ]);

                echo json_encode([
                    "success" => false,
                    "error" => "primeiro_acesso"
                ]);
                exit;
            }
        }


        if (!password_verify($senha, $usuario['senha'])) {

            $pdo->prepare("
                INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
                VALUES (:usuario_id, 'auth', 'FALHA_LOGIN', :descricao)
            ")->execute([
                ":usuario_id" => $usuario['id'],
                ":descricao" => "Senha incorreta para '{$email}'"
            ]);

            echo json_encode([
                "success" => false,
                "error" => "credenciais"
            ]);
            exit;
        }

        
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['usuario_nome']  = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['classe_nome']   = $usuario['classe_nome'];
        $_SESSION['grau_acesso']   = $usuario['grau_acesso'];

        $pdo->prepare("
            INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
            VALUES (:usuario_id, 'auth', 'LOGIN', :descricao)
        ")->execute([
            ":usuario_id" => $usuario['id'],
            ":descricao" => "Login bem-sucedido"
        ]);

        echo json_encode([
            "success" => true,
            "grau_acesso" => $usuario['grau_acesso'],
            "usuario_nome" => $usuario['nome']
        ]);

    } else {

        $pdo->prepare("
            INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
            VALUES (NULL, 'auth', 'MÉTODO_INVÁLIDO', 'Método HTTP incorreto')
        ")->execute();

        echo json_encode([
            "success" => false,
            "error" => "metodo_invalido"
        ]);
    }

} catch (Exception $e) {

    $pdo->prepare("
        INSERT INTO log_acao (usuario_id, entidade, acao, descricao)
        VALUES (NULL, 'auth', 'ERRO', :descricao)
    ")->execute([
        ":descricao" => $e->getMessage()
    ]);

    echo json_encode([
        "success" => false,
        "error" => "servidor"
    ]);
}
