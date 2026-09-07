<?php
require_once 'config.php';

header('Content-Type: application/json');

$cabang_id = isset($_GET['cabang_id']) ? (int)$_GET['cabang_id'] : 1;
$lantai = isset($_GET['lantai']) ? (int)$_GET['lantai'] : 1;

$query = "SELECT id, nama_meja, status FROM meja WHERE cabang_id = $cabang_id AND lantai = $lantai ORDER BY id";
$result = $conn->query($query);

$tables = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row;
    }
}

echo json_encode($tables);
?>