<?php

require_once '../../model/connection.php';
require_once '../../model/adminModel/productsModel.php';

$data = [];

if (isset($_GET['q'])) {
    $conn = conn_open();
    $data = getSuggestions($_GET['q'], $conn);
    conn_close($conn);
}

echo json_encode($data);

?>