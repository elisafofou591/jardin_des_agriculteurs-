<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function utilisateurConnecte(): bool
{
    return isset($_SESSION["id_utilisateur"]);
}

function exigerConnexion(): void
{
    if (!utilisateurConnecte()) {
        header("Location: connexion.php");
        exit;
    }
}

function exigerAdministrateur(): void
{
    exigerConnexion();

    if (($_SESSION["role"] ?? "") !== "administrateur") {
        header("Location: index.php");
        exit;
    }
}

