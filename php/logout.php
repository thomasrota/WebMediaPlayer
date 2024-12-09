<?php
session_start(); // Avvia la sessione
session_unset(); // Cancella tutte le variabili di sessione
session_destroy(); // Distrugge la sessione
header("Location: login.php"); // Reindirizza alla pagina di login
exit();
?>