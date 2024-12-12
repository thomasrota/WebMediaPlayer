<?php
session_start();

if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Gestione dell'aggiornamento del profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profileImage = $_FILES['profileImage'];

    // Verifica se le password corrispondono
    if ($password !== $confirm_password) {
        $error = 'Le password non corrispondono.';
    } else {
        // Verifica la vecchia password
        $query = "SELECT password FROM WBM_utente WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($old_password, $user['password'])) {
            $error = 'La vecchia password non è corretta.';
        } else {
            // Verifica se il nome utente è già presente nel database
            $query = "SELECT id FROM WBM_utente WHERE username = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $username, PDO::PARAM_STR);
            $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = 'Il nome utente è già in uso. Scegli un altro nome utente.';
            } else {
                // Aggiorna il nome utente e la password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE WBM_utente SET username = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(1, $username, PDO::PARAM_STR);
                $stmt->bindValue(2, $hashed_password, PDO::PARAM_STR);
                $stmt->bindValue(3, $user_id, PDO::PARAM_INT);
                $stmt->execute();

                // Gestisci l'upload dell'immagine del profilo
                if ($profileImage['size'] > 0) {
                    $target_dir = "../usrimg/";
                    $target_file = $target_dir . basename($profileImage["name"]);
                    move_uploaded_file($profileImage["tmp_name"], $target_file);

                    // Aggiorna il percorso dell'immagine nel database
                    $query = "UPDATE WBM_utente SET immagine = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bindValue(1, basename($profileImage["name"]), PDO::PARAM_STR);
                    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                }

                $success = 'Profilo aggiornato con successo.';
                header("Location: ./application.php");
                exit();
            }
        }
    }
}

$query = "SELECT username, immagine FROM WBM_utente WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'];
$pfp = $user['immagine'];
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Gestione Profilo</title>
            <meta charset="utf-8" />
            <meta
                name="viewport"
                content="width=device-width, initial-scale=1, shrink-to-fit=no"
            />
            <link
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
                rel="stylesheet"
                integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
                crossorigin="anonymous"
            />
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="../css/style.css?v=4.15">
            <link rel="icon" href="../assets/logo.png" type="image/x-icon">
            <style>
            .profile-pic {
                position: relative;
                display: inline-block;
            }
            .profile-pic img {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
            }
            .profile-pic .edit-icon {
                position: absolute;
                bottom: 0;
                right: 0;
                background-color: rgba(0, 0, 0, 0.5);
                border-radius: 50%;
                padding: 5px;
                cursor: pointer;
                display: none;
            }
            .profile-pic:hover .edit-icon {
                display: block;
            }
        </style>
    </head>
    <body>
        <header>
            <!-- Your header content -->
        </header>
        <main>
            <div class="container-fluid">
                <div class="row flex-nowrap">
                    <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-secondary">
                        <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                            <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                                <img src="../assets/logo.png" alt="Logo" width="40" height="40" class="me-2">
                                <span class="fs-5 d-none d-sm-inline">WebMediaPlayer</span>
                            </a>
                            <hr class="w-100">
                            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
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
                                    <a href="#" class="nav-link text-white">
                                        <img src="../assets/uploadw.png" alt="upload" class="bi me-2" width="16" height="16">
                                        <span class="d-none d-sm-inline">Carica brano</span>
                                    </a>
                                </li>
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link text-white">
                                        <img src="../assets/libw.png" alt="upload" class="bi me-2" width="16" height="16">
                                        <span class="d-none d-sm-inline">La tua libreria</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="dropdown pb-4 mt-auto">
                                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="../usrimg/<?php echo htmlspecialchars($pfp); ?>" alt="" width="30" height="30" class="rounded-circle">
                                    <span class="d-none d-sm-inline mx-1"><?php echo htmlspecialchars($username); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                                    <li><a class="dropdown-item" href="./profile.php">Profilo</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="./logout.php">Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col py-3 d-flex justify-content-center">
                        <div class="w-100" style="max-width: 600px;">
                            <h1 class="text-center mb-4">Modifica Profilo</h1>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="profile-pic">
                                            <img src="../usrimg/<?php echo htmlspecialchars($pfp); ?>" alt="Profile Image" id="profileImageDisplay">
                                            <span class="edit-icon" onclick="document.getElementById('profileImage').click();">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                                    <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 3L13 4.793 14.793 3 13 1.207 11.207 3zM10.5 3.5L1 13v2h2l9.5-9.5-2-2z"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <form action="profile.php" method="post" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Nome utente</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="old_password" class="form-label">Vecchia password</label>
                                            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Inserisci vecchia password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nuova password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Inserisci nuova password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Conferma Nuova Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Conferma nuova password" required>
                                        </div>
                                        <div class="mb-3">
                                            <input type="file" class="form-control" id="profileImage" name="profileImage" style="display: none;" onchange="previewImage(event)">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Aggiorna Profilo</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
        <script>
            function previewImage(event) {
                var reader = new FileReader();
                reader.onload = function(){
                    var output = document.getElementById('profileImageDisplay');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        </script>
    </body>
</html>