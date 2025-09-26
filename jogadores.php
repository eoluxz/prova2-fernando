<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$host = "localhost";
$user = "root";
$pass = "";
$db = "api";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['pesquisa'])) {
            $pesquisa = "%" . $_GET['pesquisa'] . "%";
            $stmt = $conn->prepare("SELECT * FROM vct WHERE LOGIN LIKE ? OR NOME LIKE ?");
            $stmt->bind_param("ss", $pesquisa, $pesquisa);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM vct order by ID desc");
        }
    
        $retorno = [];
    
        while ($linha = $result->fetch_assoc()) {
            $retorno[] = $linha;
        }

        echo json_encode($retorno);
        break;

    case 'POST':

        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO vct (NOME, TITULOS, IDADE, TIME, ATIVO) VALUES (?, ?, ?, ?, ?)");  
        $stmt->bind_param("ssssi", $data['NOME'], $data['TITULOS'], $data['IDADE'], $data['TIME'], $data['ATIVO']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE vct SET NOME=?, TITULOS=?, IDADE=?, TIME=?, ATIVO=? WHERE ID=?");
        $stmt->bind_param("ssssii", $data['NOME'], $data['TITULOS'], $data['IDADE'], $data['TIME'], $data['ATIVO'], $data['ID']);
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;


    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM vct WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;

}

$conn->close();