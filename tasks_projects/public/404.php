<?php
$urlRecebida = $_GET['url'] ?? '(nenhuma rota)';
$urlCompleta = $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Erro 404</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 40px;
        }

        .debug {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }

        code {
            background: #eee;
            padding: 3px 6px;
        }
    </style>

</head>

<body>

    <h1>🚫 Erro 404</h1>
    <p>Página não encontrada.</p>

    <div class="debug">

        <h3>Informações de Debug</h3>

        <p><strong>Rota recebida:</strong></p>
        <code><?php echo htmlspecialchars($urlRecebida); ?></code>

        <p><strong>URL completa:</strong></p>
        <code><?php echo htmlspecialchars($urlCompleta); ?></code>

        <p><strong>Parâmetros GET:</strong></p>

        <pre>
<?php print_r($_GET); ?>
</pre>

    </div>

    <br>

    <?php
    echo "<a href='" . URL . "login'>Ir para login</a><br>";
    echo "<a href='" . URL . "home'>Ir para home</a>";
    ?>

</body>

</html>