document.addEventListener("DOMContentLoaded", () => {

    // ============================================
    // 1) ADICIONAR NOVOS ARQUIVOS ILIMITADOS
    // ============================================

    const btnAddFile = document.getElementById("btnAddFile");
    const newFilesArea = document.getElementById("newFilesArea");

    if (btnAddFile) {
        btnAddFile.addEventListener("click", () => {
            const box = document.createElement("div");
            box.className = "d-flex gap-2 mt-2";

            box.innerHTML = `
                <input type="file" name="novos_arquivos[]" class="form-control">
                <button type="button" class="btn btn-outline-danger btn-sm remove-field">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;

            box.querySelector(".remove-field").onclick = () => box.remove();

            newFilesArea.appendChild(box);
        });
    }



    // ============================================
    // 2) REMOVER ARQUIVO EXISTENTE (AJAX)
    // ============================================

    // todos os arquivos existentes estão dentro do container .existing-file-wrapper
    document.body.addEventListener("click", async (ev) => {
        const btn = ev.target.closest(".btn-delete-file");
        if (!btn) return;

        const fileId = btn.dataset.fileId;
        if (!fileId) return;

        if (!confirm("Deseja remover este arquivo definitivamente?")) return;

        // loading
        btn.disabled = true;
        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        `;

        try {
            const res = await fetch("../apis/remover_arquivo.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(fileId)
            });

            const json = await res.json();

            if (json.status === "success") {
                // remove o bloco visual
                const row = document.querySelector(`[data-file-id="${fileId}"]`);
                if (row) row.remove();

                showMessage("Arquivo removido com sucesso.", "success");

            } else {
                showMessage(json.message || "Erro ao remover arquivo.", "danger");
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-trash"></i>`;
            }

        } catch (e) {
            console.error(e);
            showMessage("Erro inesperado ao remover arquivo.", "danger");
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-trash"></i>`;
        }
    });



    // ============================================
    // 3) VALIDAÇÃO DO FORMULÁRIO
    // ============================================

    const form = document.getElementById("formEditarTopico");
    if (form) {
        form.addEventListener("submit", (ev) => {
            const nome = form.querySelector('input[name="nome"]').value.trim();

            if (!nome) {
                ev.preventDefault();
                showMessage("O nome do tópico é obrigatório.", "warning");
                form.querySelector('input[name="nome"]').focus();
                return false;
            }
        });
    }

    // ============================================
    // 4) MODAL DE FEEDBACK
    // ============================================

    function showMessage(text, type = "info") {
        const body = document.getElementById("feedbackModalBody");
        body.innerHTML = `<div class="alert alert-${type} mb-0">${text}</div>`;

        const modal = new bootstrap.Modal(document.getElementById("feedbackModal"));
        modal.show();

        setTimeout(() => modal.hide(), 1600);
    }

    // ============================================
    // 5) SALVAR ALTERAÇÕES VIA AJAX (FETCH POST)
    // ============================================

    if (form) {
        form.addEventListener("submit", async (ev) => {
            ev.preventDefault(); // evita reload da página

            const nome = form.querySelector('input[name="nome"]').value.trim();

            if (!nome) {
                showMessage("O nome do tópico é obrigatório.", "warning");
                form.querySelector('input[name="nome"]').focus();
                return;
            }

            const submitBtn = form.querySelector("button[type=submit]");
            const originalBtnHTML = submitBtn.innerHTML;

            // loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Salvando...
            `;

            // prepara os dados (inclui arquivos)
            const formData = new FormData(form);

            try {
                const response = await fetch("../apis/salvar_edicao_topicos.php", {
                    method: "POST",
                    body: formData
                });

                const json = await response.json();

                if (json.status === "success") {
                    showMessage("Alterações salvas com sucesso!", "success");

                    // atualizar a página após 1.2s
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);

                } else {
                    showMessage(json.message || "Erro ao salvar alterações", "danger");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHTML;
                }

            } catch (error) {
                console.error(error);
                showMessage("Erro inesperado ao salvar.", "danger");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
            }
        });
    }


});
