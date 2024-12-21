<?php
require 'conf.php';

header('Content-Type: application/json');

if (isset($_GET['album_id'])) {
    $albumId = $_GET['album_id'];

    $query = "SELECT b.titolo, b.mp3 
              FROM WBM_brano b
              WHERE b.id_album = ?
              ORDER BY b.id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $albumId, PDO::PARAM_INT);
    $stmt->execute();
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['tracks' => $tracks]);
} else {
    echo json_encode(['error' => 'Album ID not provided']);
}
?>