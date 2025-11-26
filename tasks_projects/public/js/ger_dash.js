document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const tbody = document.querySelector("table tbody");

    // Intercepta envio do formulário
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch("../apis/salvar_cards.php", {
                method: "POST",
                body: formData
            });

            const text = await response.text();
            console.log("Resposta bruta:", text); // 👈 Aqui você vê o que o PHP está retornando

            const result = JSON.parse(text);

            if (result.status === "success") {
                const newRow = document.createElement("tr");
                newRow.innerHTML = `
                    <td>${result.titulo}</td>
                    <td><div style="width:30px; height:30px; background:${result.cor}; border-radius:5px;"></div></td>
                    <td><a href="${result.link}" target="_blank">${result.link}</a></td>
                    <td><button class="btn btn-danger btn-sm excluir" data-id="${result.id}">Excluir</button></td>
                `;
                tbody.prepend(newRow);
                form.reset();
            } else {
                alert("Erro ao adicionar: " + result.message);
            }
        } catch (error) {
            alert("Erro ao interpretar JSON: " + error.message);
        }
    });

    // Delegação para excluir
    tbody.addEventListener("click", async (e) => {
        if (e.target.classList.contains("excluir")) {
            const row = e.target.closest("tr");
            const id = e.target.dataset.id;

            if (!id) return;

            try {
                const response = await fetch("../apis/deletar_card.php?id=" + id);
                const text = await response.text();
                console.log("Resposta bruta (exclusão):", text);

                const result = JSON.parse(text);

                if (result.status === "success") {
                    row.remove();
                } else {
                    alert("Erro ao excluir: " + result.message);
                }
            } catch (error) {
                alert("Erro ao interpretar JSON: " + error.message);
            }
        }
    });
});