<?php
require 'conf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $albumId = $_POST['editAlbumId'];
    $albumTitle = $_POST['editAlbumTitle'];
    $albumYear = $_POST['editAlbumYear'];
    $albumImage = $_FILES['editAlbumImage'];

    $query = "UPDATE WBM_album SET titolo = ?, anno = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $albumTitle, PDO::PARAM_STR);
    $stmt->bindValue(2, $albumYear, PDO::PARAM_INT);
    $stmt->bindValue(3, $albumId, PDO::PARAM_INT);
    $stmt->execute();

    if ($albumImage['size'] > 0) {
        $imagePath = '../albumimg/' . basename($albumImage['name']);
        move_uploaded_file($albumImage['tmp_name'], $imagePath);

        $query = "UPDATE WBM_album SET immagine = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, basename($albumImage['name']), PDO::PARAM_STR);
        $stmt->bindValue(2, $albumId, PDO::PARAM_INT);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
}
?>