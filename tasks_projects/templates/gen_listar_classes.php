<?php $lista_classes = basename($_SERVER['PHP_SELF']); ?>

<style>
    /* Estilização da tabela de classes */
    .content table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-radius: 6px;
        overflow: hidden; /* arredondar bordas da tabela */
    }

    .content thead {
        background-color: #2c3e50; /* azul petróleo escuro */
        color: #ecf0f1; /* texto claro */
    }

    .content thead th {
        text-align: left;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .content tbody tr {
        border-bottom: 1px solid #ecf0f1;
        transition: background-color 0.2s ease;
    }

    .content tbody tr:hover {
        background-color: #f4f6f8; /* leve destaque ao passar o mouse */
    }

    .content tbody td {
        padding: 12px 16px;
        font-size: 15px;
        color: #2c3e50;
    }

    /* Mensagem de vazio dentro do main */
    .content p {
        margin-top: 20px;
        color: #7f8c8d;
        font-style: italic;
        text-align: center;
    }

</style>

<main class="content">
    <h1>Lista de Classes de Usuário Existentes no Sistema</h1>
    <table>
        <thead>
            <tr>
                <th>Grau de Acesso</th>
                <th>Nome da Classe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($classes) > 0):?>
                <?php foreach ($classes as $classe): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($classe['grau_acesso']); ?></td>
                        <td><?php echo htmlspecialchars($classe['nome']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td><i>Nenhuma Classe Cadastrada</i></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>