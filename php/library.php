<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

$user_id = $_SESSION['user_id'];

$query = "SELECT DISTINCT a.id, a.nome, a.immagine 
          FROM WBM_artista a
          JOIN WBM_artista_album aa ON a.id = aa.id_artista
          JOIN WBM_album al ON aa.id_album = al.id
          JOIN WBM_brano b ON al.id = b.id_album
          JOIN WBM_utente_brani ub ON b.id = ub.id_brano
          WHERE ub.id_utente = ?";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$artists = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT username, immagine FROM WBM_utente WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'];
$pfp = $user['immagine'];
?>
<!doctype html>
<html lang="it">

<head>
    <title>WebMediaPlayer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=4.15">
    <link rel="icon" href="../assets/logo.png" type="image/x-icon">
    <style>
        .artist-list {
            max-height: calc(100vh - 56px);
            overflow-y: auto;
            width: 80%;
            background-color: #000;
            color: #fff;
        }

        .artist-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: #1a1a1a;
            border-radius: 0.25rem;
        }

        .artist-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            position: relative;
        }

        .artist-item:hover .edit-icon {
            display: block;
        }

        .edit-icon {
            display: none;
            position: absolute;
            top: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 2px;
            cursor: pointer;
        }

        .artist-info {
            flex-grow: 1;
        }

        .artist-stats {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.875rem;
        }

        .modal-content {
            background-color: #1a1a1a;
            color: #fff;
            border: 1px solid #444;
        }

        .btn-close {
            filter: invert(1);
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .album-section {
            margin-bottom: 1.5rem;
        }

        .album-section h5 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .album-section p {
            margin-bottom: 0.5rem;
        }

        .album-section hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .album-section img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            position: relative;
        }

        .album-section:hover .edit-icon {
            display: block;
        }

        .album-section .edit-icon {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
    </header>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-secondary">
                    <div
                        class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                        <a href="#"
                            class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                            <img src="../assets/logo.png" alt="Logo" width="40" height="40" class="me-2">
                            <span class="fs-5 d-none d-sm-inline">WebMediaPlayer</span>
                        </a>
                        <hr class="w-100">
                        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
                            id="menu">
                            <li class="nav-item w-100">
                                <a href="./application.php" class="nav-link text-white" aria-current="page">
                                    <img src="../assets/homew.png" alt="home" class="bi me-2" width="16" height="16">
                                    <span class="d-none d-sm-inline">Home</span>
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a href="#" class="nav-link text-white">
                                    <img src="../assets/srcw.png" alt="search" class="bi me-2" width="16" height="16">
                                    <span class="d-none d-sm-inline">Cerca</span>
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a href="./uploadTrack.php" class="nav-link text-white">
                                    <img src="../assets/uploadw.png" alt="upload" class="bi me-2" width="16"
                                        height="16">
                                    <span class="d-none d-sm-inline">Carica brano</span>
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a href="./library.php" class="nav-link active text-white">
                                    <img src="../assets/lib.png" alt="upload" class="bi me-2" width="16" height="16">
                                    <span class="d-none d-sm-inline">La tua libreria</span>
                                </a>
                            </li>
                        </ul>
                        <div class="dropdown pb-4 mt-auto">
                            <a href="#"
                                class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../usrimg/<?php echo htmlspecialchars($pfp); ?>" alt="" width="30" height="30"
                                    class="rounded-circle">
                                <span class="d-none d-sm-inline mx-1"><?php echo htmlspecialchars($username); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow"
                                aria-labelledby="dropdownUser1">
                                <li><a class="dropdown-item" href="./profile.php">Profilo</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="./logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col py-3">
                    <div class="container mx-4 my-4">
                        <h2>La tua libreria</h2>
                        <div class="artist-list">
                            <?php foreach ($artists as $artist): ?>
                                <?php
                                $query = "SELECT COUNT(DISTINCT al.id) AS album_count, COUNT(b.id) AS track_count
                                          FROM WBM_album al
                                          JOIN WBM_artista_album aa ON al.id = aa.id_album
                                          JOIN WBM_brano b ON al.id = b.id_album
                                          WHERE aa.id_artista = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bindValue(1, $artist['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                $counts = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <div class="artist-item">
                                    <div style="position: relative;">
                                        <img src="../artistimg/<?php echo htmlspecialchars($artist['immagine']); ?>"
                                            alt="<?php echo htmlspecialchars($artist['nome']); ?>">
                                        <span class="edit-icon"
                                            onclick="editArtist('<?php echo $artist['id']; ?>', '<?php echo htmlspecialchars($artist['nome']); ?>')">
                                            <img src="../assets/edit.png" alt="Edit" width="16" height="16">
                                        </span>
                                    </div>
                                    <div class="artist-info">
                                        <h5><?php echo htmlspecialchars($artist['nome']); ?></h5>
                                        <p class="artist-stats"><?php echo $counts['album_count']; ?> album,
                                            <?php echo $counts['track_count']; ?> tracce
                                        </p>
                                    </div>
                                    <button class="btn btn-primary btn-lg"
                                        onclick="showAlbums('<?php echo $artist['id']; ?>')">Mostra Album</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="albumsModal" tabindex="-1" aria-labelledby="albumsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="albumsModalLabel">Album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="albumsModalBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editArtistModal" tabindex="-1" aria-labelledby="editArtistModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editArtistModalLabel">Modifica Artista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editArtistForm" enctype="multipart/form-data">
                        <input type="hidden" id="editArtistId" name="editArtistId">
                        <div class="mb-3">
                            <label for="editArtistName" class="form-label">Nome Artista</label>
                            <input class="form-control" type="text" id="editArtistName" name="editArtistName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editArtistImage" class="form-label">Seleziona Immagine</label>
                            <input class="form-control" type="file" id="editArtistImage" name="editArtistImage"
                                accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAlbumModal" tabindex="-1" aria-labelledby="editAlbumModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAlbumModalLabel">Modifica Album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAlbumForm" enctype="multipart/form-data">
                        <input type="hidden" id="editAlbumId" name="editAlbumId">
                        <div class="mb-3">
                            <label for="editAlbumTitle" class="form-label">Titolo Album</label>
                            <input class="form-control" type="text" id="editAlbumTitle" name="editAlbumTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAlbumYear" class="form-label">Anno di Uscita</label>
                            <input class="form-control" type="number" id="editAlbumYear" name="editAlbumYear" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAlbumImage" class="form-label">Seleziona Immagine</label>
                            <input class="form-control" type="file" id="editAlbumImage" name="editAlbumImage"
                                accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAlbums(artistId) {
            fetch('getAlbums.php?artist_id=' + artistId)
                .then(response => response.json())
                .then(data => {
                    if (data.albums && data.albums.length > 0) {
                        let albumsHtml = '';
                        data.albums.forEach(album => {
                            albumsHtml += `
                        <div class="album-section">
                            <h5>${album.titolo} &bull; ${album.artisti} &bull; ${album.anno}</h5>
                            <div style="position: relative;">
                                <img src="../albumimg/${album.immagine}" class="img-fluid" alt="${album.titolo}">
                            </div>
                            <button class="btn btn-secondary mt-2" onclick="editAlbum('${album.id}', '${album.titolo}', '${album.anno}')">Modifica Album</button>
                            <hr>
                            <h6>Brani</h6>
                            <ul>`;
                            album.tracks.forEach(track => {
                                albumsHtml += `
                            <li>
                                <p><strong>${track.titolo}</strong></p>
                                <audio controls>
                                    <source src="../mp3/${track.mp3}" type="audio/mpeg">
                                    Il tuo browser non supporta l'elemento audio.
                                </audio>
                            </li>`;
                            });
                            albumsHtml += `
                            </ul>
                        </div>
                        <hr>`;
                        });
                        document.getElementById('albumsModalBody').innerHTML = albumsHtml;
                        var myModal = new bootstrap.Modal(document.getElementById('albumsModal'));
                        myModal.show();
                    } else {
                        document.getElementById('albumsModalBody').innerHTML = '<p>Nessun album trovato.</p>';
                        var myModal = new bootstrap.Modal(document.getElementById('albumsModal'));
                        myModal.show();
                    }
                })
                .catch(error => console.error('Error fetching albums:', error));
        }

        function editArtist(artistId, artistName) {
            document.getElementById('editArtistId').value = artistId;
            document.getElementById('editArtistName').value = artistName;
            var myModal = new bootstrap.Modal(document.getElementById('editArtistModal'));
            myModal.show();
        }

        function editAlbum(albumId, albumTitle, albumYear) {
            document.getElementById('editAlbumId').value = albumId;
            document.getElementById('editAlbumTitle').value = albumTitle;
            document.getElementById('editAlbumYear').value = albumYear;
            var myModal = new bootstrap.Modal(document.getElementById('editAlbumModal'));
            myModal.show();
        }

        document.getElementById('editArtistForm').addEventListener('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(this);
            fetch('editArtist.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore durante la modifica dell\'artista.');
                    }
                })
                .catch(error => console.error('Error editing artist:', error));
        });

        document.getElementById('editAlbumForm').addEventListener('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(this);
            fetch('editAlbum.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore durante la modifica dell\'album.');
                    }
                })
                .catch(error => console.error('Error editing album:', error));
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>