<?php
session_start();
require 'conf.php'; // Importa il file di connessione

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id, password FROM WBM_utente WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id']; // Imposta la sessione
                    header("Location: dashboard.php"); // Reindirizza alla dashboard
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
        $error = "Inserisci username e password.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg" style="width: 350px;">
            <div class="card-body">
                <h2 class="card-title text-center">Login</h2>
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
    </div>
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
