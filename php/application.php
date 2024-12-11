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
    </head>

    <body>
        <header>
            <!-- place navbar here -->
             
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
                                    <a href="./application.php" class="nav-link active text-white" aria-current="page">
                                        <img src="../assets/home.png" alt="home" class="bi me-2" width="16" height="16">
                                        <span class="d-none d-sm-inline">Home</span>
                                    </a>
                                </li>
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link text-white">
                                        <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"></use></svg>
                                        <span class="d-none d-sm-inline">Dashboard</span>
                                    </a>
                                </li>
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link text-white">
                                        <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"></use></svg>
                                        <span class="d-none d-sm-inline">Orders</span>
                                    </a>
                                </li>
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link text-white">
                                        <svg class="bi me-2" width="16" height="16"><use xlink:href="#grid"></use></svg>
                                        <span class="d-none d-sm-inline">Products</span>
                                    </a>
                                </li>
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link text-white">
                                        <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"></use></svg>
                                        <span class="d-none d-sm-inline">Customers</span>
                                    </a>
                                </li>
                            </ul>
                            <hr class="w-100">
                            <div class="dropdown pb-4 mt-auto">
                                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="../usrimg/<?php echo htmlspecialchars($pfp); ?>" alt="" width="30" height="30" class="rounded-circle">
                                    <span class="d-none d-sm-inline mx-1"><?php echo htmlspecialchars($username); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                                    <li><a class="dropdown-item" href="#">Settings</a></li>
                                    <li><a class="dropdown-item" href="./profile.php">Profilo</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="./logout.php">Sign out</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col py-3">
                        <!-- Contenuto aggiuntivo -->
                        <h1>Benvenuto in WebMediaPlayer</h1>
                        <p>Questo è il contenuto principale della pagina.</p>
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
