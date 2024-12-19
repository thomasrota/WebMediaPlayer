<?php
session_start();
if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

require 'vendor/autoload.php';

$uploadError = '';
$uploadSuccess = '';
$trackTitle = '';
$artist = '';
$album = '';
$year = '';
$albumImage = 'default.jpg';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['trackFile'])) {
        $trackFile = $_FILES['trackFile'];
        $targetDir = "../mp3/";
        $targetFile = $targetDir . basename($trackFile["name"]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check file type
        if ($fileType != "mp3" && $fileType != "wav") {
            $uploadError = "Solo file MP3 e WAV sono ammessi.";
        } else {
            // Move uploaded file to target directory
            if (move_uploaded_file($trackFile["tmp_name"], $targetFile)) {
                try {
                    // Extract metadata using duncan3dc/metaaudio
                    $tagger = new \duncan3dc\MetaAudio\Tagger;
                    $tagger->addDefaultModules();
                    $mp3 = $tagger->open($targetFile);

                    $trackTitle = $mp3->getTitle() ?: '';
                    $artist = $mp3->getArtist() ?: '';
                    $album = $mp3->getAlbum() ?: '';
                    $year = $mp3->getYear() ?: '';

                    // Handle album image upload
                    if (isset($_FILES['albumImage']) && $_FILES['albumImage']['error'] == UPLOAD_ERR_OK) {
                        $albumImageFile = $_FILES['albumImage'];
                        $albumImageTargetDir = "../albumimg/";
                        $albumImageTargetFile = $albumImageTargetDir . basename($albumImageFile["name"]);
                        $albumImageFileType = strtolower(pathinfo($albumImageTargetFile, PATHINFO_EXTENSION));

                        // Check file type for album image
                        if (in_array($albumImageFileType, ['jpg', 'jpeg', 'png'])) {
                            if (move_uploaded_file($albumImageFile["tmp_name"], $albumImageTargetFile)) {
                                $albumImage = basename($albumImageFile["name"]);
                            }
                        }
                    }

                    $uploadSuccess = "File caricato con successo! Compila i campi rimanenti e conferma.";
                } catch (Exception $e) {
                    $uploadError = "Errore nell'estrazione dei metadati: " . $e->getMessage();
                }
            } else {
                $uploadError = "Errore nel caricamento del file.";
            }
        }
    } else {
        // Handle form submission for track details
        $trackTitle = $_POST['trackTitle'] ?? '';
        $artist = $_POST['artist'] ?? '';
        $album = $_POST['album'] ?? '';
        $year = $_POST['year'] ?? '';
        $uploadedBy = $_SESSION['user_id'] ?? null;
        $targetFile = $_POST['filePath'] ?? '';
        $albumImage = $_POST['albumImage'] ?? 'default.jpg';

        if (!empty($trackTitle) && !empty($artist) && $uploadedBy) {
            try {
                // Insert album if not exists
                $stmt = $conn->prepare("SELECT id FROM WBM_album WHERE titolo = :album");
                $stmt->bindParam(':album', $album, PDO::PARAM_STR);
                $stmt->execute();
                $albumId = $stmt->fetchColumn();
                if (!$albumId) {
                    $stmt = $conn->prepare("INSERT INTO WBM_album (titolo, anno, immagine) VALUES (:album, :year, :image)");
                    $stmt->bindParam(':album', $album, PDO::PARAM_STR);
                    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
                    $stmt->bindParam(':image', $albumImage, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt = $conn->prepare("SELECT id FROM WBM_album WHERE titolo = :album");
                    $stmt->bindParam(':album', $album, PDO::PARAM_STR);
                    $stmt->execute();
                    $albumId = $stmt->fetchColumn();
                } else {
                    // Update album image if a new image was uploaded
                    if ($albumImage !== 'default.jpg') {
                        $stmt = $conn->prepare("UPDATE WBM_album SET immagine = :image WHERE id = :albumId");
                        $stmt->bindParam(':image', $albumImage, PDO::PARAM_STR);
                        $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }

                // Insert track
                $stmt = $conn->prepare("INSERT INTO WBM_brano (titolo, id_album, mp3) VALUES (:title, :albumId, :filePath)");
                $stmt->bindParam(':title', $trackTitle, PDO::PARAM_STR);
                $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
                $stmt->bindParam(':filePath', basename($targetFile), PDO::PARAM_STR);
                $stmt->execute();
                $stmt = $conn->prepare("SELECT id FROM WBM_brano WHERE titolo = :title AND id_album = :albumId");
                $stmt->bindParam(':title', $trackTitle, PDO::PARAM_STR);
                $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
                $stmt->execute();
                $trackId = $stmt->fetchColumn();

                // Split artists by comma or semicolon
                $artists = preg_split('/[,;]+/', $artist);

                foreach ($artists as $artistName) {
                    $artistName = trim($artistName);
                    if (empty($artistName)) {
                        continue;
                    }

                    // Insert artist if not exists
                    $stmt = $conn->prepare("SELECT id FROM WBM_artista WHERE nome = :artist");
                    $stmt->bindParam(':artist', $artistName, PDO::PARAM_STR);
                    $stmt->execute();
                    $artistId = $stmt->fetchColumn();
                    if (!$artistId) {
                        $stmt = $conn->prepare("INSERT INTO WBM_artista (nome) VALUES (:artist)");
                        $stmt->bindParam(':artist', $artistName, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = $conn->prepare("SELECT id FROM WBM_artista WHERE nome = :artist");
                        $stmt->bindParam(':artist', $artistName, PDO::PARAM_STR);
                        $stmt->execute();
                        $artistId = $stmt->fetchColumn();
                    }

                    // Link artist to album if not exists
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM WBM_artista_album WHERE id_artista = :artistId AND id_album = :albumId");
                    $stmt->bindParam(':artistId', $artistId, PDO::PARAM_INT);
                    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
                    $stmt->execute();
                    $exists = $stmt->fetchColumn();
                    if (!$exists) {
                        $stmt = $conn->prepare("INSERT INTO WBM_artista_album (id_artista, id_album) VALUES (:artistId, :albumId)");
                        $stmt->bindParam(':artistId', $artistId, PDO::PARAM_INT);
                        $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }

                // Link track to user
                $stmt = $conn->prepare("INSERT INTO WBM_utente_brani (id_utente, id_brano) VALUES (:userId, :trackId)");
                $stmt->bindParam(':userId', $uploadedBy, PDO::PARAM_INT);
                $stmt->bindParam(':trackId', $trackId, PDO::PARAM_INT);
                $stmt->execute();

                header("Location: library.php");
                exit();
            } catch (PDOException $e) {
                $uploadError = "Errore di sistema: " . $e->getMessage();
            } catch (Exception $e) {
                $uploadError = $e->getMessage();
            }
        } else {
            $uploadError = "Compila tutti i campi obbligatori.";
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    <title>Carica Brano</title>
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
</head>

<body>
    <header>
        <!-- place navbar here -->
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
                                <a href="#" class="nav-link active text-white">
                                    <img src="../assets/upload.png" alt="upload" class="bi me-2" width="16" height="16">
                                    <span class="d-none d-sm-inline">Carica brano</span>
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a href="./library.php" class="nav-link text-white">
                                    <img src="../assets/libw.png" alt="upload" class="bi me-2" width="16" height="16">
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
                        <h2>Carica un nuovo brano</h2>
                        <?php if (isset($uploadError) && $uploadError): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $uploadError; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $uploadSuccess; ?>
                            </div>
                        <?php endif; ?>
                        <form action="uploadTrack.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="trackFile" class="form-label">File del brano</label>
                                <input type="file" class="form-control" id="trackFile" name="trackFile"
                                    accept=".mp3,.wav" required>
                            </div>
                            <div class="mb-3">
                                <label for="albumImage" class="form-label">Immagine dell'album (opzionale)</label>
                                <input type="file" class="form-control" id="albumImage" name="albumImage"
                                    accept=".jpg,.jpeg,.png">
                            </div>
                            <button type="submit" class="btn btn-primary">Carica</button>
                        </form>

                        <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
                            <form action="uploadTrack.php" method="post">
                                <div class="mb-3">
                                    <label for="trackTitle" class="form-label">Titolo del brano</label>
                                    <input type="text" class="form-control" id="trackTitle" name="trackTitle"
                                        value="<?php echo htmlspecialchars($trackTitle); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="artist" class="form-label">Artista</label>
                                    <input type="text" class="form-control" id="artist" name="artist"
                                        value="<?php echo htmlspecialchars($artist); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="album" class="form-label">Album</label>
                                    <input type="text" class="form-control" id="album" name="album"
                                        value="<?php echo htmlspecialchars($album); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="year" class="form-label">Anno</label>
                                    <input type="text" class="form-control" id="year" name="year"
                                        value="<?php echo htmlspecialchars($year); ?>">
                                </div>
                                <input type="hidden" name="filePath"
                                    value="<?php echo htmlspecialchars(basename($targetFile)); ?>">
                                <input type="hidden" name="albumImage" value="<?php echo htmlspecialchars($albumImage); ?>">
                                <button type="submit" class="btn btn-primary">Conferma</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const trackTitle = document.getElementById("trackTitle");
            const artist = document.getElementById("artist");
            const album = document.getElementById("album");
            const year = document.getElementById("year");

            if (trackTitle.value) {
                trackTitle.readOnly = true;
            }
            if (artist.value) {
                artist.readOnly = true;
            }
            if (album.value) {
                album.readOnly = true;
            }
            if (year.value) {
                year.readOnly = true;
            }
        });
    </script>
</body>

</html>