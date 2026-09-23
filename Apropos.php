<?php
$pageTitle = "À propos de nous | Jardin des Agriculteurs";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Découvrez Jardin des Agriculteurs, une marketplace moderne dédiée aux produits et solutions agricoles.">

    <meta name="theme-color" content="#14532d">

    <title><?= htmlspecialchars($pageTitle) ?></title>

    <style>
        :root {
            --forest: #14532d;
            --forest-dark: #082b18;
            --green: #1f7a3f;
            --green-soft: #eaf6ee;
            --gold: #d8aa45;
            --white: #ffffff;
            --text: #223229;
            --muted: #6b786f;
            --bg: #f7faf8;
            --border: #e1eae3;
            --shadow: 0 20px 60px rgba(8, 43, 24, .10);
            --radius: 22px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: var(--white);
            line-height: 1.7;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
            display: block;
        }

        .container {
            width: min(1180px, 92%);
            margin: auto;
        }

        /* =========================
           NAVBAR PREMIUM
        ========================== */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(225,234,227,.85);
        }

        .nav-content {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 11px;
            color: var(--forest-dark);
            font-weight: 850;
            font-size: 1.05rem;
            white-space: nowrap;
        }

        .logo-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--forest), var(--green));
            color: white;
            font-weight: 900;
            box-shadow: 0 10px 25px rgba(20,83,45,.25);
        }

        .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 23px;
        }

        .nav-links a {
            position: relative;
            font-weight: 650;
            color: #34443a;
            transition: .25s ease;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -7px;
            width: 0;
            height: 2px;
            background: var(--green);
            transition: .25s ease;
        }

        .nav-links a:hover,
        .nav-links .active {
            color: var(--green);
        }

        .nav-links a:hover::after,
        .nav-links .active::after {
            width: 100%;
        }

        .nav-cta {
            padding: 10px 18px;
            border-radius: 999px;
            background: var(--forest);
            color: white !important;
            box-shadow: 0 8px 20px rgba(20,83,45,.20);
        }

        .nav-cta::after {
            display: none;
        }

        .nav-cta:hover {
            background: var(--forest-dark);
            transform: translateY(-2px);
        }

        /* =========================
           HERO PREMIUM
        ========================== */

        .hero {
            position: relative;
            min-height: 590px;
            display: flex;
            align-items: center;
            overflow: hidden;
            color: white;

            background:
                linear-gradient(90deg,
                    rgba(4,35,17,.94) 0%,
                    rgba(10,70,34,.82) 52%,
                    rgba(20,83,45,.48) 100%),
                url("images/about-agriculture.jpg") center/cover;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            right: -150px;
            bottom: -190px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            max-width: 790px;
            padding: 95px 0;
            animation: heroReveal 1s ease both;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 14px;
            color: #f3d58b;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: .82rem;
            font-weight: 850;
        }

        .hero h1 {
            font-size: clamp(2.4rem, 5.5vw, 5rem);
            line-height: 1.03;
            letter-spacing: -1.5px;
            margin-bottom: 24px;
        }

        .hero p {
            max-width: 700px;
            font-size: 1.13rem;
            color: rgba(255,255,255,.92);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 12px 23px;
            border-radius: 999px;
            font-weight: 750;
            transition: .28s ease;
        }

        .btn-primary {
            background: white;
            color: var(--forest-dark);
        }

        .btn-outline {
            color: white;
            border: 1px solid rgba(255,255,255,.6);
            background: rgba(255,255,255,.05);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,.16);
        }

        /* =========================
           SECTIONS
        ========================== */

        section {
            padding: 92px 0;
        }

        .section-heading {
            max-width: 780px;
            margin: 0 auto 50px;
            text-align: center;
        }

        .section-heading span {
            color: var(--green);
            font-size: .82rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 1.7px;
        }

        .section-heading h2 {
            margin: 9px 0 15px;
            color: var(--forest-dark);
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.12;
            letter-spacing: -.8px;
        }

        .section-heading p {
            color: var(--muted);
        }

        /* =========================
           STORY
        ========================== */

        .story {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 65px;
            align-items: center;
        }

        .story-visual {
            position: relative;
            min-height: 470px;
            border-radius: 30px;
            overflow: hidden;
            background:
                linear-gradient(0deg, rgba(0,0,0,.12), rgba(0,0,0,.02)),
                url("images/agriculteur.jpg") center/cover;
            box-shadow: var(--shadow);
        }

        .story-badge {
            position: absolute;
            left: 25px;
            bottom: 25px;
            padding: 15px 18px;
            border-radius: 16px;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(8px);
            box-shadow: 0 12px 30px rgba(0,0,0,.14);
        }

        .story-badge strong {
            display: block;
            color: var(--forest);
            font-size: 1.15rem;
        }

        .story-badge span {
            color: var(--muted);
            font-size: .9rem;
        }

        .story-copy h2 {
            color: var(--forest-dark);
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.12;
            margin-bottom: 20px;
        }

        .story-copy p {
            color: var(--muted);
            margin-bottom: 16px;
        }

        .check-list {
            list-style: none;
            margin-top: 24px;
        }

        .check-list li {
            margin: 12px 0;
            font-weight: 650;
        }

        .check-list li::before {
            content: "✓";
            display: inline-grid;
            place-items: center;
            width: 27px;
            height: 27px;
            margin-right: 10px;
            border-radius: 50%;
            background: var(--green-soft);
            color: var(--green);
            font-weight: 900;
        }

        /* =========================
           VALUES
        ========================== */

        .soft-section {
            background: var(--bg);
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .card {
            padding: 34px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: 0 8px 28px rgba(8,43,24,.045);
            transition: .3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow);
            border-color: #cfe0d3;
        }

        .card-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            margin-bottom: 20px;
            border-radius: 17px;
            background: var(--green-soft);
            font-size: 1.55rem;
        }

        .card h3 {
            margin-bottom: 10px;
            color: var(--forest-dark);
            font-size: 1.25rem;
        }

        .card p {
            color: var(--muted);
        }

        /* =========================
           CATEGORIES
        ========================== */

        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .category {
            padding: 24px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: white;
            color: var(--forest-dark);
            font-weight: 800;
            transition: .28s ease;
        }

        .category:hover {
            color: white;
            background: linear-gradient(135deg, var(--forest), var(--green));
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(20,83,45,.16);
        }

        /* =========================
           PROMISE
        ========================== */

        .promise {
            position: relative;
            overflow: hidden;
            padding: 65px 35px;
            border-radius: 32px;
            text-align: center;
            color: white;
            background:
                radial-gradient(circle at 10% 20%,
                    rgba(255,255,255,.10),
                    transparent 30%),
                linear-gradient(135deg,
                    var(--forest-dark),
                    var(--green));
            box-shadow: var(--shadow);
        }

        .promise h2 {
            font-size: clamp(2rem, 4vw, 3.1rem);
            line-height: 1.12;
            margin-bottom: 16px;
        }

        .promise p {
            max-width: 750px;
            margin: auto;
            color: rgba(255,255,255,.9);
        }

        .promise .hero-actions {
            justify-content: center;
        }

        /* =========================
           FOOTER
        ========================== */

        footer {
            background: #061f12;
            color: white;
            padding: 55px 0 25px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 40px;
        }

        footer h3 {
            margin-bottom: 15px;
        }

        footer p,
        footer a {
            color: #bfd0c5;
        }

        footer a:hover {
            color: white;
        }

        .footer-bottom {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,.12);
            text-align: center;
            color: #9db0a4;
            font-size: .9rem;
        }

        /* =========================
           ANIMATIONS
        ========================== */

        @keyframes heroReveal {
            from {
                opacity: 0;
                transform: translateY(35px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 900px) {

            .nav-content {
                flex-direction: column;
                padding: 14px 0;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 13px;
            }

            .story,
            .cards,
            .category-grid {
                grid-template-columns: 1fr;
            }

            .story-visual {
                min-height: 350px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {

            section {
                padding: 65px 0;
            }

            .hero {
                min-height: 650px;
            }

            .hero-content {
                padding: 70px 0;
            }

            .hero h1 {
                letter-spacing: -1px;
            }

            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

            .story-visual {
                min-height: 280px;
            }

            .promise {
                padding: 45px 20px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="container nav-content">

        <a href="index.php" class="logo">
            <span class="logo-mark">JA</span>
            <span>Jardin des Agriculteurs</span>
        </a>

        <nav>
            <ul class="nav-links">

                <li>
                    <a href="index.php">Accueil</a>
                </li>

                <li>
                    <a href="products.php">Produits</a>
                </li>

                <li>
                    <a href="about.php" class="active">À propos</a>
                </li>

                <li>
                    <a href="contact.php">Contact</a>
                </li>

                <li>
                    <a href="panier.php">Panier</a>
                </li>

                <li>
                    <a href="connexion.php" class="nav-cta">Connexion</a>
                </li>

            </ul>
        </nav>

    </div>
</header>


<main>

    <!-- HERO -->

    <section class="hero">

        <div class="container">

            <div class="hero-content">

                <span class="eyebrow">
                    Notre histoire • Notre vision
                </span>

                <h1>
                    Une agriculture plus simple,
                    plus accessible et plus connectée.
                </h1>

                <p>
                    Bienvenue au <strong>Jardin des Agriculteurs</strong>,
                    une marketplace pensée pour rapprocher les agriculteurs,
                    jardiniers et clients des produits et solutions agricoles
                    dont ils ont besoin.
                </p>

                <div class="hero-actions">

                    <a href="products.php"
                       class="btn btn-primary">
                        Découvrir nos produits
                    </a>

                    <a href="contact.php"
                       class="btn btn-outline">
                        Nous contacter
                    </a>

                </div>

            </div>

        </div>

    </section>


    <!-- PRESENTATION -->

    <section>

        <div class="container story">

            <div class="story-visual">

                <div class="story-badge">
                    <strong>Jardin des Agriculteurs</strong>
                    <span>Une agriculture connectée</span>
                </div>

            </div>

            <div class="story-copy">

                <span class="eyebrow"
                      style="color:#1f7a3f;">
                    Qui sommes-nous ?
                </span>

                <h2>
                    Le partenaire digital
                    de vos activités agricoles.
                </h2>

                <p>
                    <strong>Jardin des Agriculteurs</strong> est une
                    marketplace dédiée aux produits et solutions agricoles.
                    Notre ambition est de rendre l'accès aux produits
                    agricoles plus simple, plus rapide et plus transparent.
                </p>

                <p>
                    Grâce à notre plateforme, les utilisateurs peuvent
                    découvrir les produits disponibles, consulter leurs
                    informations et leurs prix, constituer leur panier,
                    passer une commande et suivre leurs achats.
                </p>

                <ul class="check-list">

                    <li>
                        Catalogue organisé par catégories
                    </li>

                    <li>
                        Informations et prix accessibles
                    </li>

                    <li>
                        Commande en ligne simplifiée
                    </li>

                    <li>
                        Gestion et suivi des commandes
                    </li>

                    <li>
                        Expérience optimisée pour mobile
                    </li>

                </ul>

            </div>

        </div>

    </section>


    <!-- VALEURS -->

    <section class="soft-section">

        <div class="container">

            <div class="section-heading">

                <span>Nos valeurs</span>

                <h2>
                    Ce qui guide Jardin des Agriculteurs
                </h2>

                <p>
                    Nous voulons construire une expérience digitale
                    agricole basée sur la qualité, la confiance,
                    l'innovation et la simplicité.
                </p>

            </div>

            <div class="cards">

                <article class="card">

                    <div class="card-icon">
                        🌱
                    </div>

                    <h3>Qualité</h3>

                    <p>
                        Mettre en avant des produits et solutions
                        agricoles présentés de façon claire et
                        professionnelle.
                    </p>

                </article>


                <article class="card">

                    <div class="card-icon">
                        🤝
                    </div>

                    <h3>Confiance</h3>

                    <p>
                        Construire une relation fiable entre les
                        clients et les différents acteurs du secteur
                        agricole.
                    </p>

                </article>


                <article class="card">

                    <div class="card-icon">
                        ⚡
                    </div>

                    <h3>Simplicité</h3>

                    <p>
                        Permettre à l'utilisateur de rechercher,
                        comparer et commander facilement les produits
                        dont il a besoin.
                    </p>

                </article>

            </div>

        </div>

    </section>


    <!-- CATEGORIES -->

    <section>

        <div class="container">

            <div class="section-heading">

                <span>Notre univers</span>

                <h2>
                    Des solutions pour différents besoins agricoles
                </h2>

                <p>
                    Le catalogue est organisé en catégories afin de
                    permettre aux utilisateurs de trouver rapidement
                    les produits recherchés.
                </p>

            </div>


            <div class="category-grid">

                <a href="products.php?categorie=insecticides"
                   class="category">
                    🐛 Insecticides
                </a>

                <a href="products.php?categorie=herbicides"
                   class="category">
                    🌿 Herbicides
                </a>

                <a href="products.php?categorie=fongicides"
                   class="category">
                    🍃 Fongicides
                </a>

                <a href="products.php?categorie=outils"
                   class="category">
                    🛠️ Outils agricoles
                </a>

                <a href="products.php?categorie=semences"
                   class="category">
                    🌾 Semences améliorées
                </a>

                <a href="products.php?categorie=engrais"
                   class="category">
                    🌱 Engrais
                </a>

            </div>

        </div>

    </section>


    <!-- SLOGAN / CTA -->

    <section>

        <div class="container">

            <div class="promise">

                <h2>
                    « Le prix, oui ! La qualité surtout ! »
                </h2>

                <p>
                    Notre objectif est de faire du Jardin des Agriculteurs
                    une marketplace agricole moderne, fiable et accessible,
                    capable de simplifier l'expérience d'achat tout en
                    valorisant la qualité des produits agricoles.
                </p>

                <div class="hero-actions">

                    <a href="products.php"
                       class="btn btn-primary">
                        Voir le catalogue
                    </a>

                    <a href="contact.php"
                       class="btn btn-outline">
                        Nous contacter
                    </a>

                </div>

            </div>

        </div>

    </section>

</main>


<footer>

    <div class="container">

        <div class="footer-grid">

            <div>

                <h3>
                    Jardin des Agriculteurs
                </h3>

                <p>
                    Votre marketplace dédiée aux produits,
                    équipements et solutions agricoles.
                </p>

            </div>


            <div>

                <h3>Navigation</h3>

                <p>
                    <a href="index.php">Accueil</a>
                </p>

                <p>
                    <a href="products.php">Produits</a>
                </p>

                <p>
                    <a href="About.php">À propos de nous</a>
                </p>

                <p>
                    <a href="contact.php">Contact</a>
                </p>

            </div>


            <div>

                <h3>Services</h3>

                <p>
                    <a href="panier.php">Mon panier</a>
                </p>

                <p>
                    <a href="connexion.php">Mon compte</a>
                </p>

                <p>
                    <a href="commande.php">Mes commandes</a>
                </p>

            </div>

        </div>


        <div class="footer-bottom">

            © <?= date("Y") ?>
            Jardin des Agriculteurs —
            Tous droits réservés.

        </div>

    </div>

</footer>

</body>
</html>