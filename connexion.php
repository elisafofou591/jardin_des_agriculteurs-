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
 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <title>Connexion | Jardin des Agriculteurs</title>
 
    <meta name="description"
          content="Connectez-vous à votre espace client Jardin des Agriculteurs.">
 
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        :root {
            --vert-fonce: #0b3d2e;
            --vert: #176b4d;
            --vert-clair: #2f8f68;
            --vert-pale: #eaf6f0;
            --blanc: #ffffff;
            --gris: #667085;
            --gris-clair: #f5f7f6;
            --noir: #17221d;
            --rouge: #c0392b;
            --ombre: 0 20px 60px rgba(11, 61, 46, 0.14);
        }
 
        body {
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(47,143,104,.15), transparent 30%),
                radial-gradient(circle at 90% 90%, rgba(11,61,46,.12), transparent 30%),
                linear-gradient(135deg, #f7fbf9, #edf7f2);
            color: var(--noir);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            overflow-x: hidden;
        }
 
        .background-shape {
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(47,143,104,.08);
            filter: blur(3px);
            animation: float 8s ease-in-out infinite;
            z-index: -1;
        }
 
        .shape-one {
            top: -120px;
            left: -100px;
        }
 
        .shape-two {
            bottom: -150px;
            right: -100px;
            animation-delay: -3s;
        }
 
        @keyframes float {
            0%,100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
 
        .login-container {
            width: 100%;
            max-width: 1050px;
            min-height: 650px;
            background: rgba(255,255,255,.94);
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: var(--ombre);
            border: 1px solid rgba(255,255,255,.8);
            animation: appear .7s ease;
        }
 
        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
 
        .brand-panel {
            position: relative;
            background:
                linear-gradient(rgba(11,61,46,.9), rgba(11,61,46,.96)),
                linear-gradient(135deg, #176b4d, #0b3d2e);
            color: white;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }
 
        .brand-panel::before,
        .brand-panel::after {
            content: "";
            position: absolute;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 50%;
        }
 
        .brand-panel::before {
            width: 400px;
            height: 400px;
            right: -200px;
            top: -180px;
        }
 
        .brand-panel::after {
            width: 300px;
            height: 300px;
            left: -180px;
            bottom: -150px;
        }
 
        .logo {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: rgba(255,255,255,.13);
            border: 1px solid rgba(255,255,255,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 28px;
            backdrop-filter: blur(8px);
        }
 
        .brand-panel h1 {
            font-size: clamp(32px, 4vw, 48px);
            line-height: 1.08;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }
 
        .brand-panel h1 span {
            color: #9be2c2;
        }
 
        .brand-panel p {
            color: rgba(255,255,255,.78);
            font-size: 16px;
            line-height: 1.8;
            max-width: 430px;
            position: relative;
            z-index: 1;
        }
 
        .benefits {
            margin-top: 32px;
            display: grid;
            gap: 14px;
            position: relative;
            z-index: 1;
        }
 
        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.88);
            font-size: 14px;
        }
 
        .benefit-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(155,226,194,.14);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9be2c2;
        }
 
        .form-panel {
            padding: 55px clamp(30px, 5vw, 70px);
            display: flex;
            align-items: center;
        }
 
        .form-content {
            width: 100%;
            max-width: 430px;
            margin: auto;
        }
 
        .form-header {
            margin-bottom: 32px;
        }
 
        .form-header small {
            color: var(--vert);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
 
        .form-header h2 {
            margin-top: 9px;
            font-size: 32px;
            color: var(--vert-fonce);
        }
 
        .form-header p {
            margin-top: 8px;
            color: var(--gris);
            line-height: 1.6;
        }
 
        .alert {
            padding: 13px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: shake .35s ease;
        }
 
        .alert.error {
            color: var(--rouge);
            background: #fff0ee;
            border: 1px solid #ffd5cf;
        }
 
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
 
        .field {
            margin-bottom: 20px;
        }
 
        .field label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #344054;
        }
 
        .input-wrapper {
            position: relative;
        }
 
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #98a2b3;
            pointer-events: none;
        }
 
        .field input {
            width: 100%;
            height: 54px;
            border: 1px solid #d0d5dd;
            border-radius: 13px;
            padding: 0 48px;
            outline: none;
            font-size: 15px;
            color: var(--noir);
            background: white;
            transition: .25s ease;
        }
 
        .field input:focus {
            border-color: var(--vert);
            box-shadow: 0 0 0 4px rgba(23,107,77,.10);
        }
 
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            cursor: pointer;
            color: #667085;
            font-size: 17px;
        }
 
        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 2px 0 25px;
            font-size: 13px;
        }
 
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gris);
        }
 
        .remember input {
            accent-color: var(--vert);
        }
 
        .forgot {
            color: var(--vert);
            text-decoration: none;
            font-weight: 700;
        }
 
        .forgot:hover {
            text-decoration: underline;
        }
 
        .login-btn {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--vert), var(--vert-fonce));
            color: white;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 25px rgba(23,107,77,.20);
            transition: .3s ease;
            position: relative;
            overflow: hidden;
        }
 
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(23,107,77,.28);
        }
 
        .login-btn:active {
            transform: translateY(0);
        }
 
        .login-btn.loading {
            pointer-events: none;
            opacity: .8;
        }
 
        .register {
            text-align: center;
            margin-top: 25px;
            color: var(--gris);
            font-size: 14px;
        }
 
        .register a {
            color: var(--vert);
            font-weight: 800;
            text-decoration: none;
        }
 
        .register a:hover {
            text-decoration: underline;
        }
 
        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #98a2b3;
            font-size: 13px;
            text-decoration: none;
            transition: .2s;
        }
 
        .back-home:hover {
            color: var(--vert);
        }
 
        @media (max-width: 850px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
 
            .brand-panel {
                padding: 40px;
                min-height: 330px;
            }
 
            .brand-panel h1 {
                font-size: 35px;
            }
 
            .benefits {
                grid-template-columns: 1fr 1fr;
            }
 
            .form-panel {
                padding: 45px 35px;
            }
        }
 
        @media (max-width: 520px) {
            body {
                padding: 12px;
            }
 
            .login-container {
                border-radius: 20px;
            }
 
            .brand-panel {
                padding: 32px 25px;
            }
 
            .brand-panel p {
                font-size: 14px;
            }
 
            .benefits {
                grid-template-columns: 1fr;
            }
 
            .form-panel {
                padding: 35px 22px;
            }
 
            .form-header h2 {
                font-size: 28px;
            }
 
            .options {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
 
<body>
 
<div class="background-shape shape-one"></div>
<div class="background-shape shape-two"></div>
 
<main class="login-container">
 
    <!-- =========================
         PARTIE IMAGE / MARQUE
    ========================== -->
    <section class="brand-panel">
 
        <div class="logo">JA</div>
 
        <h1>
            Bienvenue au<br>
            <span>Jardin des Agriculteurs</span>
        </h1>
 
        <p>
            Retrouvez vos commandes, vos informations personnelles
            et profitez d'une expérience simple et professionnelle
            pour vos achats agricoles.
        </p>
 
        <div class="benefits">
 
            <div class="benefit">
                <span class="benefit-icon">✓</span>
                <span>Suivez facilement vos commandes</span>
            </div>
 
            <div class="benefit">
                <span class="benefit-icon">✓</span>
                <span>Accédez rapidement à votre panier</span>
            </div>
 
            <div class="benefit">
                <span class="benefit-icon">✓</span>
                <span>Une expérience pensée pour les agriculteurs</span>
            </div>
 
        </div>
    </section>
 
    <!-- =========================
         FORMULAIRE
    ========================== -->
    <section class="form-panel">
 
        <div class="form-content">
 
            <div class="form-header">
                <small>Espace client</small>
                <h2>Connexion</h2>
                <p>
                    Connectez-vous pour accéder à votre compte.
                </p>
            </div>
 
            <?php if ($message !== ""): ?>
                <div class="alert <?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
 
            <form method="POST" id="loginForm" autocomplete="on">
 
                <div class="field">
                    <label for="email">Adresse e-mail</label>
 
                    <div class="input-wrapper">
 
                        <span class="input-icon">✉</span>
 
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="exemple@email.com"
                            value="<?= htmlspecialchars($_POST["email"] ?? ($_COOKIE["user_email"] ?? "")) ?>"
                            autocomplete="email"
                            required
                        >
 
                    </div>
                </div>
 
                <div class="field">
 
                    <label for="mot_de_passe">Mot de passe</label>
 
                    <div class="input-wrapper">
 
                        <span class="input-icon">🔒</span>
 
                        <input
                            type="password"
                            id="mot_de_passe"
                            name="mot_de_passe"
                            placeholder="Votre mot de passe"
                            autocomplete="current-password"
                            required
                        >
 
                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Afficher le mot de passe"
                        >👁</button>
 
                    </div>
                </div>
 
                <div class="options">
 
                    <label class="remember">
                        <input
                            type="checkbox"
                            name="remember"
                            <?= isset($_COOKIE["user_email"]) ? "checked" : "" ?>
                        >
                        Se souvenir de moi
                    </label>
 
                    <a href="mot_de_passe_oublie.php" class="forgot">
                        Mot de passe oublié ?
                    </a>
 
                </div>
 
                <button type="submit" class="login-btn" id="loginButton">
                    Se connecter
                </button>
 
            </form>
 
            <div class="register">
                Vous n'avez pas encore de compte ?
                <a href="inscription.php">Créer un compte</a>
            </div>
 
            <a href="index.php" class="back-home">
                ← Retour à l'accueil
            </a>
 
        </div>
    </section>
 
</main>
 
<script>
    // Afficher / masquer le mot de passe
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("mot_de_passe");
 
    togglePassword.addEventListener("click", function () {
        const isPassword = passwordInput.type === "password";
 
        passwordInput.type = isPassword ? "text" : "password";
        togglePassword.textContent = isPassword ? "🙈" : "👁";
        togglePassword.setAttribute(
            "aria-label",
            isPassword ? "Masquer le mot de passe" : "Afficher le mot de passe"
        );
    });
 
    // Animation du bouton lors de l'envoi
    const loginForm = document.getElementById("loginForm");
    const loginButton = document.getElementById("loginButton");
 
    loginForm.addEventListener("submit", function () {
        loginButton.classList.add("loading");
        loginButton.textContent = "Connexion en cours...";
    });
 
    // Petite animation lors du focus des champs
    document.querySelectorAll(".field input").forEach(input => {
 
        input.addEventListener("focus", function () {
            this.parentElement.parentElement.style.transform = "translateY(-1px)";
        });
 
        input.addEventListener("blur", function () {
            this.parentElement.parentElement.style.transform = "translateY(0)";
        });
    });
</script>
 
</body>
</html>