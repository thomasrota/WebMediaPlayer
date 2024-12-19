<?php
require 'conf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artistId = $_POST['editArtistId'];
    $artistName = $_POST['editArtistName'];
    $artistImage = $_FILES['editArtistImage'];

    $query = "UPDATE WBM_artista SET nome = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $artistName, PDO::PARAM_STR);
    $stmt->bindValue(2, $artistId, PDO::PARAM_INT);
    $stmt->execute();

    if ($artistImage['size'] > 0) {
        $imagePath = '../artistimg/' . basename($artistImage['name']);
        move_uploaded_file($artistImage['tmp_name'], $imagePath);

        $query = "UPDATE WBM_artista SET immagine = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, basename($artistImage['name']), PDO::PARAM_STR);
        $stmt->bindValue(2, $artistId, PDO::PARAM_INT);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
}
?>