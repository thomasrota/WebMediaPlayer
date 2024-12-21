<?php
require 'conf.php';

header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Ricerca artisti
    $stmt = $conn->prepare("SELECT id, nome, immagine FROM WBM_artista WHERE nome LIKE ?");
    $stmt->execute(['%' . $query . '%']);
    $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ricerca album
    $stmt = $conn->prepare("SELECT id, titolo, immagine FROM WBM_album WHERE titolo LIKE ?");
    $stmt->execute(['%' . $query . '%']);
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ricerca brani
    $stmt = $conn->prepare("SELECT b.id, b.titolo, b.mp3, al.titolo as album FROM WBM_brano b JOIN WBM_album al ON b.id_album = al.id WHERE b.titolo LIKE ?");
    $stmt->execute(['%' . $query . '%']);
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['artists' => $artists, 'albums' => $albums, 'tracks' => $tracks]);
} else {
    echo json_encode(['error' => 'Query not provided']);
}
?>