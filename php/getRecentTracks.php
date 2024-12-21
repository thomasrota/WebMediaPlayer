<?php
require 'conf.php';

header('Content-Type: application/json');

if (isset($_GET['artist_id'])) {
    $artistId = $_GET['artist_id'];

    $query = "SELECT b.titolo, al.titolo AS album, b.mp3 
              FROM WBM_brano b
              JOIN WBM_album al ON b.id_album = al.id
              JOIN WBM_artista_album aa ON al.id = aa.id_album
              WHERE aa.id_artista = ?
              ORDER BY b.id DESC
              LIMIT 3";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $artistId, PDO::PARAM_INT);
    $stmt->execute();
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['tracks' => $tracks]);
} else {
    echo json_encode(['error' => 'Artist ID not provided']);
}
?>