<?php
require_once '../config/connection.php';

# API de gerenciamento do Administrador
class Admin {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    # Adicionar Classe de Usuário
    public function addClassUs($nome) {
        try {
            // Verifica se já existe
            $check = $this->pdo->prepare("SELECT COUNT(*) FROM classe_usuario WHERE nome = :nome");
            $check->execute([':nome' => $nome]);
            if ($check->fetchColumn() > 0) {
                return "Essa classe de usuário já está registrada.";
            }

            $sql = "INSERT INTO classe_usuario (nome) VALUES (:nome)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nome' => $nome]);

            return "Classe de usuário {$nome} adicionada com sucesso!";
        } catch (PDOException $e) {
            return "Erro ao adicionar classe de usuário: " . $e->getMessage();
        }
    }

    # Adicionar Área de Atuação
    public function addAreaAtuacaoUs($nome) {
        try {
            // Verifica se já existe
            $check = $this->pdo->prepare("SELECT COUNT(*) FROM area_atuacao WHERE nome = :nome");
            $check->execute([':nome' => $nome]);
            if ($check->fetchColumn() > 0) {
                return "Essa área de atuação já está registrada.";
            }

            $sql = "INSERT INTO area_atuacao (nome) VALUES (:nome)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nome' => $nome]);

            return "Área de atuação {$nome} adicionada com sucesso!";
        } catch (PDOException $e) {
            return "Erro ao adicionar área de atuação: " . $e->getMessage();
        }
    }

    # Deletar Classe de Usuário
    public function deleteClassUs($nome) {
        try {
            $sql = "DELETE FROM classe_usuario WHERE nome = :nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nome' => $nome]);

            if ($stmt->rowCount() > 0) {
                return "Classe de usuário {$nome} deletada com sucesso!";
            } else {
                return "Nenhuma classe de usuário encontrada com esse nome.";
            }
        } catch (PDOException $e) {
            return "Erro ao tentar deletar classe de usuário: " . $e->getMessage();
        }
    }

    # Deletar Área de Atuação
    public function deleteAreaAtuacaoUs($nome) {
        try {
            $sql = "DELETE FROM area_atuacao WHERE nome = :nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nome' => $nome]);

            if ($stmt->rowCount() > 0) {
                return "Área de atuação {$nome} deletada com sucesso!";
            } else {
                return "Nenhuma área de atuação encontrada com esse nome.";
            }
        } catch (PDOException $e) {
            return "Erro ao tentar deletar área de atuação: " . $e->getMessage();
        }
    }

    # Pega as Todas as Classes de Usuário exisentes
    public function viewClassUs(){

        try{
            $sql = "SELECT * FROM classe_usuario ORDER BY nome ASC";
            $stmt = $this->pdo->query($sql);
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $classes;

        }catch(PDOException $e){
            return "Não foi possível carregar as classes Existentes, por favor contate o desenvolvedor.". $e->getMessage();
        }
    }

    # Pega Todas as Áreas de atuação existentes
    public function viewAreaAtuacaoUs(){

        try{
            $sql = "SELECT * FROM area_atuacao ORDER BY nome ASC";
            $stmt = $this->pdo->query($sql);
            $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $areas;

        }catch(PDOException $e){
            return "Não foi possível carregar as Áreas de Atuação Existentes, por favor contate o desenvolvedor.". $e->getMessage();
        }

    }
}
?>