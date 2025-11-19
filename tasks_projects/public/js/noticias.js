let contadorTopicos = 1;
let dadosTemporarios = { anuncio: null, noticia: null };

document.addEventListener("DOMContentLoaded", () => {
    const radioExistente = document.getElementById("noticiaExistente");
    const radioPropria = document.getElementById("noticiaPropria");
    const formAnuncio = document.getElementById("formAnuncio");
    const formNoticia = document.getElementById("formNoticia");
    const linkInput = document.getElementById("linkInput");
    const statusAnuncio = document.getElementById("statusAnuncio");
    const statusNoticia = document.getElementById("statusNoticia");
    const btnSalvarTudo = document.getElementById("btnSalvarTudo");

    function atualizarInterface() {
        if (radioExistente.checked) {
            formAnuncio.querySelectorAll("input, textarea, button").forEach(el => el.disabled = false);
            formNoticia.querySelectorAll("input, textarea, button").forEach(el => el.disabled = true);

            linkInput.removeAttribute("readonly");
            linkInput.value = "";

            statusAnuncio.textContent = "Habilitada";
            statusAnuncio.className = "status-label habilitada";

            statusNoticia.textContent = "Desabilitada";
            statusNoticia.className = "status-label desabilitada";
        } else {
            formAnuncio.querySelectorAll("input, textarea, button").forEach(el => el.disabled = false);
            formNoticia.querySelectorAll("input, textarea, button").forEach(el => el.disabled = false);

            const noticiaPath = "../public/noticia_gen.php?id=";
            const encoded = btoa(noticiaPath);
            linkInput.value = encoded;
            linkInput.setAttribute("readonly", true);

            statusAnuncio.textContent = "Habilitada";
            statusAnuncio.className = "status-label habilitada";

            statusNoticia.textContent = "Habilitada";
            statusNoticia.className = "status-label habilitada";
        }
    }

    radioExistente.addEventListener("change", atualizarInterface);
    radioPropria.addEventListener("change", atualizarInterface);

    atualizarInterface();

    btnSalvarTudo.addEventListener("click", () => {
        const tipo = radioExistente.checked ? "existente" : "propria";

        if (tipo === "existente") {
            if (!dadosTemporarios.anuncio) {
                alert("Você precisa clicar em 'Salvar Anúncio' antes de salvar todas as alterações.");
                return;
            }
            enviarDados(dadosTemporarios.anuncio, "Anúncio");
        } else {
            if (!dadosTemporarios.anuncio || !dadosTemporarios.noticia) {
                alert("Você precisa clicar em 'Salvar Anúncio' e 'Salvar Notícia Completa' antes de salvar todas as alterações.");
                return;
            }

            // 🔄 Combina os dois FormData
            const combinado = new FormData();
            for (let [key, value] of dadosTemporarios.anuncio.entries()) {
                combinado.append(key, value);
            }
            for (let [key, value] of dadosTemporarios.noticia.entries()) {
                if (!combinado.has(key)) {
                    combinado.append(key, value);
                }
            }

            enviarDados(combinado, "Notícia completa");
        }
    });

    // 🔄 Sincronização de campos só quando "Cadastrar minha própria notícia" estiver ativo
    const anuncioTitulo = document.querySelector("#formAnuncio input[name='titulo']");
    const anuncioSubtitulo = document.querySelector("#formAnuncio input[name='subtitulo']");
    const anuncioAutoria = document.querySelector("#formAnuncio input[name='autoria']");

    const noticiaTitulo = document.querySelector("#formNoticia input[name='titulo']");
    const noticiaSubtitulo = document.querySelector("#formNoticia input[name='subtitulo']");
    const noticiaAutoria = document.querySelector("#formNoticia input[name='autoria']");

    function sincronizarCampos(campoA, campoB) {
        campoA.addEventListener("input", () => {
            if (radioPropria.checked) {
                if (campoA.value.trim() !== "") {
                    campoB.value = campoA.value;
                    campoB.setAttribute("readonly", true);
                    campoA.removeAttribute("readonly");
                } else {
                    campoB.value = "";
                    campoB.removeAttribute("readonly");
                }
            }
        });

        campoB.addEventListener("input", () => {
            if (radioPropria.checked) {
                if (campoB.value.trim() !== "") {
                    campoA.value = campoB.value;
                    campoA.setAttribute("readonly", true);
                    campoB.removeAttribute("readonly");
                } else {
                    campoA.value = "";
                    campoA.removeAttribute("readonly");
                }
            }
        });
    }

    sincronizarCampos(anuncioTitulo, noticiaTitulo);
    sincronizarCampos(anuncioSubtitulo, noticiaSubtitulo);
    sincronizarCampos(anuncioAutoria, noticiaAutoria);
});

async function enviarDados(formData, tipo) {
    try {
        const res = await fetch("../apis/salvar_noticia.php", { method: "POST", body: formData });
        const text = await res.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch {
            throw new Error(`Resposta não é JSON (${tipo}): ${text}`);
        }

        if (!res.ok || data.status === "error") {
            throw new Error(data.message || `Erro ao salvar ${tipo}.`);
        }

        alert(`${tipo} salvo com sucesso!`);
    } catch (err) {
        alert(`Erro ao salvar ${tipo}: ${err.message}`);
        console.error("Detalhes do erro:", err);
    }
}

function salvarTemporario(tipo) {
    const form = document.getElementById(tipo === 'anuncio' ? 'formAnuncio' : 'formNoticia');
    const formData = new FormData(form);

    const radioExistente = document.getElementById("noticiaExistente");
    formData.append("tipo_noticia", radioExistente.checked ? "existente" : "propria");

    dadosTemporarios[tipo] = formData;

    const msgBox = document.getElementById(tipo === 'anuncio' ? 'msgAnuncio' : 'msgNoticia');
    msgBox.classList.remove("d-none");
    msgBox.textContent = "Alterações Salvas";
}

function adicionarTopico() {
    const container = document.getElementById('topicos-container');
    const novoTopico = document.createElement('div');
    novoTopico.classList.add('topico','border','rounded','p-3','mb-3');
    novoTopico.innerHTML = `
        <h5>Tópico ${contadorTopicos+1}</h5>
        <div class="mb-3">
            <label class="form-label">Título do Tópico:</label>
            <input type="text" name="topicos[${contadorTopicos}][titulo]" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Texto:</label>
            <textarea name="topicos[${contadorTopicos}][texto]" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagem:</label>
            <input type="file" name="topicos[${contadorTopicos}][imagem]" class="form-control" accept="image/*">
        </div>
        <div class="mb-3">
            <label class="form-label">Fonte da Imagem:</label>
            <textarea name="topicos[${contadorTopicos}][fonte_imagem]" class="form-control"></textarea>
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removerTopico(this)">Remover</button>
    `;
    container.appendChild(novoTopico);
    contadorTopicos++;
}

function removerTopico(botao) {
    const topico = botao.closest('.topico');
    topico.remove();
}
