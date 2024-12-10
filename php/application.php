<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicativo</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-primary" role="alert">
            Benvenuto nell'applicativo! Utente ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?>.
        </div>
        <a href="./logout.php" class="btn btn-danger">Logout</a>
    </div>
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
