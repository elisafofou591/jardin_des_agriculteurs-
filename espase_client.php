<?php

session_start();

/*
 * ESPACE CLIENT - JARDIN DES AGRICULTEURS
 * ---------------------------------------------------------
 * Conditions :
 * - connexion_bd.php doit créer une connexion mysqli dans $connexion
 * - la table utilisateurs doit contenir : id, nom, email, mot_de_passe, role
 * - le rôle client est exactement : "client"
 */
 
// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit();
}
 
// L'espace client est réservé aux clients
if (($_SESSION["role"] ?? "") === "administrateur") {
    header("Location: dashboard.php");
    exit();
}
 
if (($_SESSION["role"] ?? "") !== "client") {
    header("Location: index.php");
    exit();
}
 
$id_client = (int) $_SESSION["id"];
 
// Récupérer les informations du client connecté
$sql = "SELECT id, nom, email, role FROM utilisateurs WHERE id = ?";
$stmt = mysqli_prepare($connexion, $sql);
 
if (!$stmt) {
    die("Erreur de préparation de la requête.");
}
 
mysqli_stmt_bind_param($stmt, "i", $id_client);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($resultat);
mysqli_stmt_close($stmt);
 
if (!$client) {
    session_unset();
    session_destroy();
    header("Location: connexion.php");
    exit();
}
 
$nom_client = htmlspecialchars($client["nom"], ENT_QUOTES, "UTF-8");
$email_client = htmlspecialchars($client["email"], ENT_QUOTES, "UTF-8");
 
// Initiales du client
$initiales = "";
$parties = preg_split('/\s+/', trim($client["nom"]));
 
foreach ($parties as $partie) {
    if ($partie !== "") {
        $initiales .= strtoupper(substr($partie, 0, 1));
    }
}
 
$initiales = substr($initiales, 0, 2);
 
// Message après une éventuelle action
$message = $_GET["message"] ?? "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace client - Jardin des Agriculteurs">
    <title>Espace Client | Jardin des Agriculteurs</title>
 
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        :root {
            --vert-fonce: #123c2a;
            --vert: #1f6b45;
            --vert-clair: #eaf6ef;
            --or: #d5a83d;
            --blanc: #ffffff;
            --fond: #f5f8f6;
            --texte: #183126;
            --gris: #6c7a73;
            --ombre: 0 15px 40px rgba(18, 60, 42, 0.10);
        }
 
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--fond);
            color: var(--texte);
            min-height: 100vh;
        }
 
        a {
            text-decoration: none;
            color: inherit;
        }
 
        .navbar {
            height: 76px;
            background: var(--blanc);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            box-shadow: 0 3px 18px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }
 
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            color: var(--vert-fonce);
            font-size: 20px;
        }
 
        .logo-mark {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--vert-fonce);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            letter-spacing: -1px;
            box-shadow: 0 8px 20px rgba(18,60,42,.18);
        }
 
        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
            font-size: 14px;
            font-weight: 600;
        }
 
        .nav-links a {
            transition: .25s ease;
        }
 
        .nav-links a:hover {
            color: var(--vert);
            transform: translateY(-1px);
        }
 
        .logout {
            background: var(--vert-fonce);
            color: white !important;
            padding: 11px 18px;
            border-radius: 12px;
        }
 
        .logout:hover {
            background: var(--vert) !important;
        }
 
        .container {
            width: min(1180px, 90%);
            margin: 0 auto;
        }
 
        .hero {
            margin-top: 34px;
            background: linear-gradient(135deg, #123c2a, #1f6b45);
            border-radius: 28px;
            padding: 42px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            box-shadow: var(--ombre);
            overflow: hidden;
            position: relative;
        }
 
        .hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 50%;
            right: -70px;
            top: -90px;
        }
 
        .hero-text {
            position: relative;
            z-index: 2;
        }
 
        .hero small {
            color: #d9f0e2;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
 
        .hero h1 {
            margin: 10px 0;
            font-size: clamp(28px, 4vw, 44px);
        }
 
        .hero p {
            color: #e9f6ee;
            max-width: 620px;
            line-height: 1.7;
        }
 
        .avatar {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            background: white;
            color: var(--vert-fonce);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 31px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 15px 35px rgba(0,0,0,.18);
            position: relative;
            z-index: 2;
        }
 
        .message {
            margin-top: 20px;
            padding: 14px 18px;
            background: #eaf6ef;
            color: var(--vert-fonce);
            border-left: 4px solid var(--vert);
            border-radius: 10px;
        }
 
        .section-title {
            margin: 42px 0 20px;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
        }
 
        .section-title h2 {
            font-size: 25px;
            color: var(--vert-fonce);
        }
 
        .section-title p {
            color: var(--gris);
            font-size: 14px;
        }
 
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
 
        .card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--ombre);
            transition: transform .25s ease, box-shadow .25s ease;
        }
 
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(18,60,42,.14);
        }
 
        .icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            background: var(--vert-clair);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            margin-bottom: 16px;
        }
 
        .card h3 {
            font-size: 17px;
            margin-bottom: 8px;
            color: var(--vert-fonce);
        }
 
        .card p {
            color: var(--gris);
            font-size: 13px;
            line-height: 1.55;
            min-height: 40px;
        }
 
        .card-btn {
            display: inline-block;
            margin-top: 17px;
            color: var(--vert);
            font-weight: 700;
            font-size: 13px;
        }
 
        .profile {
            background: white;
            border-radius: 22px;
            box-shadow: var(--ombre);
            padding: 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
 
        .info {
            background: #f8faf9;
            border-radius: 14px;
            padding: 18px;
        }
 
        .info span {
            display: block;
            color: var(--gris);
            font-size: 12px;
            margin-bottom: 6px;
        }
 
        .info strong {
            font-size: 15px;
            word-break: break-word;
        }
 
        .cta {
            margin: 42px 0;
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--ombre);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
 
        .cta h2 {
            color: var(--vert-fonce);
            margin-bottom: 8px;
        }
 
        .cta p {
            color: var(--gris);
            line-height: 1.6;
        }
 
        .btn {
            display: inline-block;
            background: var(--vert-fonce);
            color: white;
            padding: 13px 21px;
            border-radius: 12px;
            font-weight: 700;
            white-space: nowrap;
            transition: .25s ease;
        }
 
        .btn:hover {
            background: var(--vert);
            transform: translateY(-2px);
        }
 
        footer {
            margin-top: 50px;
            padding: 25px 5%;
            text-align: center;
            background: var(--vert-fonce);
            color: #dcebe3;
            font-size: 13px;
        }
 
        @media (max-width: 900px) {
            .cards {
                grid-template-columns: repeat(2, 1fr);
            }
 
            .nav-links {
                gap: 12px;
            }
        }
 
        @media (max-width: 650px) {
            .navbar {
                height: auto;
                padding: 14px 5%;
                flex-wrap: wrap;
                gap: 12px;
            }
 
            .nav-links {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
 
            .hero {
                padding: 28px 22px;
                flex-direction: column;
                align-items: flex-start;
            }
 
            .avatar {
                width: 75px;
                height: 75px;
                font-size: 23px;
            }
 
            .cards,
            .profile {
                grid-template-columns: 1fr;
            }
 
            .cta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
 
<body>
 
<header class="navbar">
    <a href="index.php" class="logo">
        <span class="logo-mark">JA</span>
        <span>Jardin des Agriculteurs</span>
    </a>
 
    <nav class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="products.php">Produits</a>
        <a href="panier.php">Panier</a>
        <a href="commande.php">Mes commandes</a>
        <a href="deconnexion.php" class="logout">Déconnexion</a>
    </nav>
</header>
 
<main class="container">
 
    <section class="hero">
        <div class="hero-text">
            <small>Espace personnel</small>
            <h1>Bienvenue, <?= $nom_client ?> 👋</h1>
            <p>
                Retrouvez ici vos informations, vos produits, votre panier
                et vos commandes au sein du Jardin des Agriculteurs.
            </p>
        </div>
 
        <div class="avatar"><?= htmlspecialchars($initiales) ?></div>
    </section>
 
    <?php if ($message !== ""): ?>
        <div class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>
 
    <div class="section-title">
        <div>
            <h2>Mon espace</h2>
            <p>Accédez rapidement aux principales fonctionnalités.</p>
        </div>
    </div>
 
    <section class="cards">
 
        <article class="card">
            <div class="icon">🌱</div>
            <h3>Nos produits</h3>
            <p>Découvrez les produits agricoles disponibles dans notre catalogue.</p>
            <a href="products.php" class="card-btn">Voir les produits →</a>
        </article>
 
        <article class="card">
            <div class="icon">🛒</div>
            <h3>Mon panier</h3>
            <p>Consultez les articles ajoutés à votre panier avant de commander.</p>
            <a href="panier.php" class="card-btn">Ouvrir mon panier →</a>
        </article>
 
        <article class="card">
            <div class="icon">📦</div>
            <h3>Mes commandes</h3>
            <p>Retrouvez l'accès à vos commandes et à leur suivi.</p>
            <a href="commande.php" class="card-btn">Voir mes commandes →</a>
        </article>
 
        <article class="card">
            <div class="icon">👤</div>
            <h3>Mon profil</h3>
            <p>Consultez vos informations personnelles enregistrées sur le site.</p>
            <a href="#profil" class="card-btn">Voir mon profil →</a>
        </article>
 
    </section>
 
    <div class="section-title" id="profil">
        <div>
            <h2>Mes informations</h2>
            <p>Informations du compte actuellement connecté.</p>
        </div>
    </div>
 
    <section class="profile">
        <div class="info">
            <span>Nom complet</span>
            <strong><?= $nom_client ?></strong>
        </div>
 
        <div class="info">
            <span>Adresse e-mail</span>
            <strong><?= $email_client ?></strong>
        </div>
 
        <div class="info">
            <span>Type de compte</span>
            <strong>Client</strong>
        </div>
 
        <div class="info">
            <span>Identifiant du compte</span>
            <strong>#<?= (int) $client["id"] ?></strong>
        </div>
    </section>
 
    <section class="cta">
        <div>
            <h2>Prêt à faire vos achats ? 🌿</h2>
            <p>
                Explorez notre catalogue et choisissez les produits
                adaptés à vos besoins agricoles.
            </p>
        </div>
 
        <a href="products.php" class="btn">Explorer les produits</a>
    </section>
 
</main>
 
<footer>
    © <?= date("Y") ?> Jardin des Agriculteurs — Le prix, oui ! La qualité surtout !
</footer>
 
</body>