
<style>
    header {
        background: linear-gradient(90deg, #003e4d, #0046a0);
        color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    header img {
        max-height: 100px;
    }

    /* Botão de voltar */
    .btn-voltar {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    @media (max-width: 576px) {
        .btn-voltar {
            left: 10px;
            top: 50%;
        }
    }
</style>

<!-- templates/header.php -->
<header class="position-relative text-center py-3">
    <img src="/tasks_projects/public/img/logo-emparn.png" alt="Logo EMPARN" class="mx-auto">
</header>
