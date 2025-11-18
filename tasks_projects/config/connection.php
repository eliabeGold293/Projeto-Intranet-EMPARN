
<?php
$host = 'localhost'; // ou o IP do servidor (deve ser este)
$port = '5432'; // porta padrão do PostgreSQL
$dbname = 'emparn'; //nome da base de dados
$user = 'postgres'; // nome padrão (deve ser ele)
$password = 'password'; // teste primeiro com 'password' se não der certo coloque sua senha do postgrsql "admin"
$modo_debug = true; // deve estar sempre true durante teste.

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
