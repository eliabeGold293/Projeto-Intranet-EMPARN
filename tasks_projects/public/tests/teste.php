<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <title>Ala de Testes</title>

    <style>
        body {
            background: #f4f6f9;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .form-box {
            max-width: 520px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 28px;
            position: relative;
        }

        .form-close {
            position: absolute;
            top: 12px;
            right: 12px;
            border: none;
            background: transparent;
            font-size: 20px;
            color: #6c757d;
            cursor: pointer;
            transition: 0.2s;
        }

        .form-close:hover {
            color: #dc3545;
            transform: scale(1.1);
        }

        .form-title {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 20px;
            color: #343a40;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #495057;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #dee2e6;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.25);
        }

        .datas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .btn-projeto {
            width: 100%;
            margin-top: 10px;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            background: #0d6efd;
            border: none;
            color: #fff;
            transition: 0.2s;
        }

        .btn-projeto:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(13,110,253,0.25);
        }
    </style>

</head>

<body>

    <div class="form-box">

        <button class="form-close">
            <i class="bi bi-x-lg"></i>
        </button>

        <form action="">

            <div class="form-title">
                Cadastro de Projeto
            </div>

            <div class="form-group">
                <label class="form-label" for="titulo">Título do Projeto</label>
                <input type="text" id="titulo" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="descricao">Descrição do Projeto</label>
                <input type="text" id="descricao" class="form-control">
            </div>

            <div class="datas">
                <div class="form-group">
                    <label class="form-label" for="data_inicio">Data de Início</label>
                    <input type="date" id="data_inicio" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_fim">Data Final</label>
                    <input type="date" id="data_fim" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-projeto">
                <i class="bi bi-plus-circle me-2"></i>
                Adicionar Projeto
            </button>

        </form>
    </div>

</body>
</html>
