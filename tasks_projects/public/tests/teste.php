<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Painel Flutuante</title>

<style>
body {
  font-family: Arial, sans-serif;
  background: #f3f4f6;
  margin: 0;
  height: 200vh; /* só pra simular página grande */
}

/* Painel flutuante */
.painel {
  position: fixed;
  top: 40px;
  right: 40px;
  width: 320px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Caixa interna */
.box {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
  border: 1px solid #e5e7eb;
}

.box h3 {
  margin: 0 0 10px 0;
  font-size: 14px;
  color: #374151;
}

/* Lista de usuários */
.usuario {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  gap: 6px;
}

.usuario span {
  font-size: 13px;
  color: #111827;
}

select {
  font-size: 12px;
  padding: 4px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  background: white;
}

/* Botão */
.botao {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: none;
  background: #2563eb;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}

.botao:hover {
  background: #1e40af;
}
</style>
</head>

<body>

<div class="painel">

  <div class="box">
    <h3>Usuários no projeto</h3>

    <div class="usuario">
      <span>Ana</span>
      <select>
        <option>Responsável</option>
        <option>Revisor</option>
        <option>Acompanhando</option>
      </select>
    </div>

    <div class="usuario">
      <span>Carlos</span>
      <select>
        <option>Responsável</option>
        <option>Revisor</option>
        <option>Acompanhando</option>
      </select>
    </div>

    <div class="usuario">
      <span>Julia</span>
      <select>
        <option>Responsável</option>
        <option>Revisor</option>
        <option>Acompanhando</option>
      </select>
    </div>

  </div>

  <div class="box">
    <button class="botao">+ Adicionar tarefa</button>
  </div>

</div>

</body>
</html>
