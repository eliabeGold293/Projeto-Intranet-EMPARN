
<?php
$host = 'localhost';
$port = '5432';
$dbname = 'emparn';
$user = 'postgres';
$password = 'password';
$modo_debug = true;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Durante testes, use log interno ou console, mas nunca echo
    if ($modo_debug){
        // file_put_contents('log.txt', "Conexão bem-sucedida!\n", FILE_APPEND);
        // ou apenas comente:
        // echo "Conexão bem-sucedida!";
    }
} catch (PDOException $e) {
    // Em produção, envie erro como JSON ou log
    // echo "Erro na conexão: " . $e->getMessage();
    throw new Exception("Erro na conexão: " . $e->getMessage());
}
?>

