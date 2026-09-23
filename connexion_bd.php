<?php
session_start();

/* ================================
   CONNEXION À LA BASE DE DONNÉES
   Adapte seulement ces 4 valeurs
   si nécessaire.
================================ */
$host = "localhost";
$dbname = "jardin_des_agriculteurs";
$username = "root";
$password = "";

$message = "";
$messageType = "";

/* ================================
   PROTECTION CSRF
================================ */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    $message = "Impossible de se connecter à la base de données.";
    $messageType = "error";
}

/* ================================
   TRAITEMENT DE LA CONNEXION
================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($pdo)) {

    $email = trim($_POST["email"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";
    $remember = isset($_POST["remember"]);
   
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
     
     
        $message = "Veuillez saisir une adresse e-mail valide.";
        $messageType = "error";
    }else{

        

        try {
            $stmt = $pdo->prepare(
                "SELECT id, nom, email, mot_de_passe, role
                 FROM utilisateurs
                 WHERE email = :email
                 LIMIT 1"
            );
            $stmt->execute(["email" => $email]);
            $utilisateur = $stmt->fetch();
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, nom, email, mot_de_passe, role
                     FROM utilisateur
                     WHERE email = :email
                     LIMIT 1"
                );
                $stmt->execute(["email" => $email]);
                $utilisateur = $stmt->fetch();
            } catch (PDOException $e2) {
                $utilisateur = false;
            }
        }

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur["mot_de_passe"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = (int)$utilisateur["id"];
            $_SESSION["user_nom"] = $utilisateur["nom"];
            $_SESSION["user_email"] = $utilisateur["email"];
            $_SESSION["user_role"] = strtolower(trim($utilisateur["role"] ?? "client"));

            /* Compatibilité avec les autres pages du projet */
            $_SESSION["id"] = (int)$utilisateur["id"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["email"] = $utilisateur["email"];
            $_SESSION["role"] = $_SESSION["user_role"];
            $_SESSION["connecte"] = true;

            /* ================================
               SE SOUVENIR DE MOI
            ================================= */
            if ($remember) {
                setcookie(
                    "user_email",
                    $email,
                    [
                        "expires" => time() + (86400 * 30),
                        "path" => "/",
                        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]
                );
            } else {
                setcookie(
                    "user_email",
                    "",
                    [
                        "expires" => time() - 3600,
                        "path" => "/",
                        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]
                );
            }

            /* ================================
               REDIRECTION SELON LE RÔLE
            ================================= */
            if ($_SESSION["user_role"] === "administrateur" ||
                $_SESSION["user_role"] === "administrateur") {

                header("Location: dashboard.php");
                exit();

            } elseif ($_SESSION["user_role"] === "client") {

                header("Location: espace_client.php");
                exit();

            } else {

                /* Tout rôle non reconnu est traité comme client */
                header("Location: espace_client.php");
                exit();
            }

        } else {
            $message = "E-mail ou mot de passe incorrect.";
            $messageType = "error";
           }
           }
    }

?>
 