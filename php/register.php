<?php
if (!isset($conn) || $conn == null) {
    require 'conf.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $error = '';

    // Validazione dati
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tutti i campi sono obbligatori.";
    } elseif ($password !== $confirm_password) {
        $error = "Le password non corrispondono.";
    } else {
        try {
            // Controlla se l'username o l'email esistono già
            $stmt = $conn->prepare("SELECT id FROM WBM_utente WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Username o email già in uso.";
            } else {
                // Inserisce l'utente nel database con la password criptata
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO WBM_utente (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt->execute();

                // Reindirizza alla pagina di login
                header("Location: login.php?registered=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Errore di sistema: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="it">
    <head>
        <title>Registrati</title>
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
    </head>

    <body>
        <main>
            <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
                <img src="../assets/logo.png" alt="Logo" style="width: 100px; margin-bottom: 2.5vw;">                <div class="card shadow-lg" style="width: 350px;">
                <div class="card-body">
                    <h3 class="card-title text-center">Registrati</h3>
                    <hr class="hr-green" style="margin-bottom: 2vw">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Inserisci username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Inserisci email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Inserisci password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Conferma Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Conferma password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrati</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php">Hai già un account? Accedi</a>
                    </div>
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
