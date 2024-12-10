<?php
session_start();
if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($login) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, password FROM WBM_utente WHERE username = :login OR email = :login");
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    header("Location: application.php");
                    exit();
                } else {
                    $error = "Password errata.";
                }
            } else {
                $error = "Utente non trovato.";
            }
        } catch (PDOException $e) {
            $error = "Errore di sistema: " . $e->getMessage();
        }
    } else {
        $error = "Inserisci username/email e password.";
    }
}
?>
<!doctype html>
<html lang="it">
    <head>
        <title>Login</title>
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
        <main>
            <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
                <img src="../assets/logo.png" alt="Logo" style="width: 100px; margin-bottom: 2.5vw;">                <div class="card shadow-lg" style="width: 350px;">
                <div class="card-body">
                    <h3 class="card-title text-center">Login</h3>
                    <hr class="hr-green" style="margin-bottom: 2vw">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-center" style="background-color:  #cc0c0c; border-color: #890c0c;" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="login" class="form-label">Username o Email</label>
                            <input type="text" class="form-control" id="login" name="login" placeholder="Inserisci username o email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Inserisci password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Accedi</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="register.php">Non hai un account? Registrati</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
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
