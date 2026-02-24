<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Projetos</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>

body {
    background-color: #f4f6f9;
}

.page-container {
    padding: 50px 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 50px;
}

.card-projeto {
    border: none;
    border-radius: 18px;
    transition: all 0.25s ease;
    cursor: pointer;
}

.card-projeto:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
}

.projeto-descricao {
    min-height: 50px;
}

.empty-state {
    margin-top: 80px;
}

</style>
</head>
<body>

<div class="container page-container">

    <div class="page-header">
        <h2 class="fw-bold">
            <i class="bi bi-kanban text-primary"></i> Projetos Disponíveis
        </h2>
        <p class="text-muted mt-2">
            Selecione um projeto para visualizar seus membros e tarefas.
        </p>
    </div>

    <div class="row" id="gridProjetos">
        <!-- Projetos via JS -->
    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", carregarProjetos);

function carregarProjetos(){

    fetch("listar-projetos.php")
    .then(r => r.json())
    .then(lista => {

        const grid = document.getElementById("gridProjetos");
        grid.innerHTML = "";

        if(lista.length === 0){
            grid.innerHTML = `
                <div class="col-12 text-center text-muted empty-state">
                    <i class="bi bi-folder2-open" style="font-size:50px;"></i>
                    <p class="mt-3 fs-5">Nenhum projeto disponível no momento.</p>
                </div>
            `;
            return;
        }

        lista.forEach(projeto => {

            const col = document.createElement("div");
            col.className = "col-lg-4 col-md-6 mb-4";

            col.innerHTML = `
                <div class="card card-projeto shadow-sm p-4 h-100" onclick="abrirProjeto(${projeto.id})">
                    
                    <h5 class="fw-bold mb-2">
                        <i class="bi bi-folder-fill text-primary"></i>
                        ${projeto.nome}
                    </h5>

                    <p class="text-muted projeto-descricao">
                        ${projeto.descricao ?? "Sem descrição cadastrada."}
                    </p>

                    <hr>

                    <div class="d-flex justify-content-between small text-muted">
                        <div>
                            <i class="bi bi-people"></i>
                            ${projeto.total_membros} membros
                        </div>

                        <div>
                            <i class="bi bi-list-task"></i>
                            ${projeto.total_tarefas} tarefas
                        </div>
                    </div>

                </div>
            `;

            grid.appendChild(col);

        });

    });

}

function abrirProjeto(id){
    window.location.href = `projeto-detalhe.php?id=${id}`;
}

</script>

</body>
</html>