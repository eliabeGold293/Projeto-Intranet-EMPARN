// Função de busca na tabela
function searchUser() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#userTable tr:not(:first-child)");

    rows.forEach(row => {
        const nome = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        row.style.display = (nome.includes(input) || email.includes(input)) ? "" : "none";
    });
}

// Exibir formulário de edição ao lado da tabela
function showEditForm(id, nome, email, classe, area) {
    const formContainer = document.getElementById("editFormContainer");
    formContainer.style.display = "block";

    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nome").value = nome;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_senha").value = "";
    document.getElementById("edit_classe").value = classe;
    document.getElementById("edit_area").value = area;
}

// Submissão do formulário de edição via fetch
document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.getElementById("editForm");
    if (editForm) {
        editForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("atualizar-info-usuario", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const msgDiv = document.getElementById("editMessage");
                if (data.toLowerCase().includes("sucesso")) {
                    msgDiv.textContent = "Usuário atualizado com sucesso!";
                    msgDiv.className = "message success";
                
                    // Aguarda 1 segundo para o usuário ver a mensagem e recarrega a página
                setTimeout(() => {
                    location.reload();
                }, 1000);

                } else {
                    msgDiv.textContent = data;
                    msgDiv.className = "message error";
                }
            })
            .catch(() => {
                const msgDiv = document.getElementById("editMessage");
                msgDiv.textContent = "Erro ao atualizar usuário.";
                msgDiv.className = "message error";
            });
        });
    }
});

// Função para excluir usuário
function deleteUser(id) {
    if (confirm("Deseja realmente excluir o usuário?")) {
        fetch("deletar-usuario", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(data => {
            const msgDiv = document.getElementById("message");
            if (data.includes("sucesso")) {
                msgDiv.textContent = "Usuário excluído com sucesso";
                msgDiv.className = "message success";

                // Remove a linha da tabela correspondente
                const rows = document.querySelectorAll("#userTable tr");
                rows.forEach(row => {
                    if (row.cells[0] && row.cells[0].textContent == id) {
                        row.remove();
                    }
                });
            } else {
                msgDiv.textContent = data;
                msgDiv.className = "message error";
            }
        })
        .catch(() => {
            const msgDiv = document.getElementById("message");
            msgDiv.textContent = "Erro ao excluir usuário";
            msgDiv.className = "message error";
        });
    }
}