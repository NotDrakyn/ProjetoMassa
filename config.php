<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eventos";

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => "Conexão falhou: " . $conn->connect_error
    ]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitização e validação dos dados
    $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
    $data = filter_var($_POST['data'], FILTER_SANITIZE_STRING);
    $local = filter_var($_POST['local'], FILTER_SANITIZE_STRING);
    $descricao = filter_var($_POST['descricao'], FILTER_SANITIZE_STRING);
    $participantes = filter_var($_POST['participantes'], FILTER_SANITIZE_STRING);

    // Verificar se todos os campos foram preenchidos
    if (empty($nome) || empty($data) || empty($local) || empty($descricao) || empty($participantes)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Todos os campos são obrigatórios!'
        ]);
        exit;
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Atualizar evento
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE eventos SET nome=?, data=?, local=?, descricao=?, participantes=? WHERE id=?");
        $stmt->bind_param("sssssi", $nome, $data, $local, $descricao, $participantes, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Evento atualizado com sucesso'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao atualizar evento: ' . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        // Inserir novo evento
        $stmt = $conn->prepare("INSERT INTO eventos (nome, data, local, descricao, participantes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $data, $local, $descricao, $participantes);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Novo evento cadastrado com sucesso'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao inserir evento: ' . $stmt->error
            ]);
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    // Deletar evento
    $id = $_GET['id'];

    // Verificar se o evento existe antes de tentar deletá-lo
    $stmt = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $stmt = $conn->prepare("DELETE FROM eventos WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Evento deletado com sucesso'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao deletar evento: ' . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Evento não encontrado'
        ]);
    }
}

// Consultar todos os eventos
$sql = "SELECT * FROM eventos";
$result = $conn->query($sql);

$eventos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $eventos[] = $row;
    }
}

echo json_encode($eventos);

$conn->close();
?>