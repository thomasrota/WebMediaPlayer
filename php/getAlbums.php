<?php
if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

$artist_id = $_GET['artist_id'];
$query = "SELECT al.id, al.titolo, al.immagine, al.anno, GROUP_CONCAT(DISTINCT a.nome ORDER BY a.nome ASC SEPARATOR ', ') AS artisti
          FROM WBM_album al
          JOIN WBM_artista_album aa ON al.id = aa.id_album
          JOIN WBM_artista a ON aa.id_artista = a.id
          WHERE aa.id_artista = ?
          GROUP BY al.id, al.titolo, al.immagine, al.anno";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $artist_id, PDO::PARAM_INT);
$stmt->execute();
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($albums as &$album) {
    $query = "SELECT b.titolo, b.mp3
              FROM WBM_brano b
              WHERE b.id_album = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $album['id'], PDO::PARAM_INT);
    $stmt->execute();
    $album['tracks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode(['albums' => $albums]);
?>