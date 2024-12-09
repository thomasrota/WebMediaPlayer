<?php
session_start(); // Avvia la sessione

// Controlla se la sessione utente è attiva
if (!isset($_SESSION['user_id'])) {
    // Se l'utente non è autenticato, reindirizza alla pagina di login
    header("Location: php/login.php");
    exit();
}

// Se l'utente è autenticato, reindirizza all'applicativo principale
header("Location: php/application.php");
exit();
?>
