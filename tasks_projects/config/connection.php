
<?php

$host = 'localhost'; // ou o IP do servidor
$port = '5432'; // porta padrão do PostgreSQL
$dbname = 'emparn';
$user = 'postgres';
$password = 'password';
$modo_debug = false;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Define o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($modo_debug){
        echo "Conexão bem-sucedida!";
    }

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}

?>
