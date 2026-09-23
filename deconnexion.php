<?php
/*
|--------------------------------------------------------------------------
| JARDIN DES AGRICULTEURS
| Fichier : deconnexion.php
|--------------------------------------------------------------------------
| Fonctionnalités :
| - Démarrage sécurisé de la session si nécessaire
| - Destruction complète de la session
| - Suppression du cookie de session
| - Protection contre l'affichage des pages privées avec "Retour"
| - Page de confirmation moderne et responsive
| - Compte à rebours avant redirection vers connexion.php
|--------------------------------------------------------------------------
*/

// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vider toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session s'il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruire complètement la session
session_destroy();

// Empêcher la mise en cache de cette page et des pages privées précédentes
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Message utilisé par JavaScript
$redirectPage = "connexion.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Déconnexion | Jardin des Agriculteurs</title>

    <meta name="robots" content="noindex, nofollow">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background:
                radial-gradient(circle at 15% 20%, rgba(42, 157, 143, 0.28), transparent 28%),
                radial-gradient(circle at 85% 80%, rgba(46, 125, 50, 0.30), transparent 30%),
                linear-gradient(135deg, #063b2a, #0b5d3b 48%, #0a7a4b);
            position: relative;
        }

        /* Décoration d'arrière-plan */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
        }

        .circle.one {
            width: 300px;
            height: 300px;
            top: -120px;
            left: -90px;
        }

        .circle.two {
            width: 420px;
            height: 420px;
            right: -180px;
            bottom: -190px;
        }

        .circle.three {
            width: 150px;
            height: 150px;
            right: 15%;
            top: 10%;
        }

        .container {
            width: min(92%, 560px);
            position: relative;
            z-index: 2;
        }

        .card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 28px;
            padding: 42px 35px;
            text-align: center;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.28);
            animation: apparition 0.7s ease;
        }

        @keyframes apparition {
            from {
                opacity: 0;
                transform: translateY(25px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo {
            width: 82px;
            height: 82px;
            margin: 0 auto 22px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 31px;
            font-weight: 900;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #087443, #18a558);
            box-shadow: 0 12px 28px rgba(8, 116, 67, 0.30);
        }

        .check {
            width: 74px;
            height: 74px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #22a06b;
            color: #158754;
            font-size: 38px;
            font-weight: bold;
            animation: check 0.8s ease 0.2s both;
        }

        @keyframes check {
            from {
                opacity: 0;
                transform: scale(0.5) rotate(-20deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotate(0);
            }
        }

        h1 {
            color: #123c2b;
            font-size: clamp(25px, 5vw, 34px);
            margin-bottom: 13px;
        }

        .message {
            color: #5d6d66;
            line-height: 1.65;
            font-size: 15px;
            margin-bottom: 25px;
        }

        .message strong {
            color: #13814f;
        }

        .security {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 15px;
            background: #eefaf4;
            border: 1px solid #ccebdc;
            color: #356454;
            font-size: 13px;
            line-height: 1.45;
        }

        .security-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #d7f4e5;
            font-size: 18px;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            text-decoration: none;
            cursor: pointer;
            min-width: 190px;
            padding: 14px 22px;
            border-radius: 13px;
            font-weight: 700;
            font-size: 14px;
            transition: 0.25s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, #087443, #16a05a);
            box-shadow: 0 9px 20px rgba(8, 116, 67, 0.22);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 13px 25px rgba(8, 116, 67, 0.30);
        }

        .btn-secondary {
            color: #176345;
            background: #edf7f2;
            border: 1px solid #cfe9db;
        }

        .btn-secondary:hover {
            background: #e0f2e9;
            transform: translateY(-2px);
        }

        .countdown {
            margin-top: 21px;
            color: #7a8983;
            font-size: 12px;
        }

        #seconds {
            color: #13814f;
            font-weight: 800;
        }

        .footer {
            margin-top: 25px;
            padding-top: 17px;
            border-top: 1px solid #e7eee9;
            color: #8a9791;
            font-size: 11px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 21px;
                border-radius: 22px;
            }

            .logo {
                width: 70px;
                height: 70px;
                font-size: 26px;
            }

            .check {
                width: 65px;
                height: 65px;
                font-size: 33px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="circle one"></div>
    <div class="circle two"></div>
    <div class="circle three"></div>

    <main class="container">
        <section class="card">

            <div class="logo">JA</div>

            <div class="check">✓</div>

            <h1>Déconnexion réussie</h1>

            <p class="message">
                Vous avez été déconnecté(e) de votre espace
                <strong>Jardin des Agriculteurs</strong>.
                Votre session a été fermée correctement.
            </p>

            <div class="security">
                <div class="security-icon">🔒</div>
                <div>
                    <strong>Session sécurisée</strong><br>
                    Vos informations de session ont été supprimées.
                    Pour accéder à nouveau à votre compte, veuillez vous reconnecter.
                </div>
            </div>

            <div class="actions">
                <a href="<?php echo htmlspecialchars($redirectPage, ENT_QUOTES, 'UTF-8'); ?>"
                   class="btn btn-primary">
                    Se reconnecter
                </a>

                <a href="index.php" class="btn btn-secondary">
                    Retour à l'accueil
                </a>
            </div>

            <div class="countdown">
                Redirection automatique dans
                <span id="seconds">5</span> seconde(s)...
            </div>

            <div class="footer">
                © <?php echo date("Y"); ?> Jardin des Agriculteurs
                — « le prix, oui ! La qualité surtout ! »
            </div>

        </section>
    </main>

    <script>
        // Empêcher l'utilisateur de revenir facilement à une page privée
        // déjà chargée après la déconnexion.
        window.history.replaceState(null, "", window.location.href);

        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Redirection automatique vers connexion.php
        let seconds = 5;
        const secondsElement = document.getElementById("seconds");

        const timer = setInterval(function () {
            seconds--;
            secondsElement.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.replace("<?php echo
                    htmlspecialchars($redirectPage, ENT_QUOTES, 'UTF-8');
                ?>");
            }
        }, 1000);
    </script>

</body>
</html>
