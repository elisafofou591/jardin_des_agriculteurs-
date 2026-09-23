<?php
session_start();

/* =========================================================
   JARDIN DES AGRICULTEURS
   commande.php - VERSION COMPLETE ET ORGANISEE
   Tout est dans ce fichier : PHP + HTML + CSS + JavaScript.
   ========================================================= */

/* -------------------- PANIER -------------------- */
$panier = isset($_SESSION['panier']) && is_array($_SESSION['panier'])
    ? $_SESSION['panier']
    : [];

/* Rend la page compatible avec plusieurs noms de champs du panier. */
function get_article_value($article, $keys, $default = null)
{
    if (!is_array($article)) {
        return $default;
    }

    foreach ($keys as $key) {
        if (isset($article[$key])) {
            return $article[$key];
        }
    }

    return $default;
}

$articles = [];

foreach ($panier as $article) {
    if (!is_array($article)) {
        continue;
    }

    $nom = get_article_value(
        $article,
        ['nom', 'name', 'nom_produit', 'produit'],
        'Produit'
    );

    $prix = get_article_value(
        $article,
        ['prix', 'price', 'prix_unitaire'],
        0
    );

    $quantite = get_article_value(
        $article,
        ['quantite', 'quantity', 'qte'],
        1
    );

    $nom = is_scalar($nom) ? (string)$nom : 'Produit';
    $prix = is_numeric($prix) ? (float)$prix : 0;
    $quantite = is_numeric($quantite) ? (int)$quantite : 1;

    if ($quantite < 1) {
        $quantite = 1;
    }

    $articles[] = [
        'nom' => $nom,
        'prix' => $prix,
        'quantite' => $quantite
    ];
}

$total = 0;

foreach ($articles as $article) {
    $total += $article['prix'] * $article['quantite'];
}

/* -------------------- TRAITEMENT DU FORMULAIRE -------------------- */
$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim((string)($_POST['nom'] ?? ''));
    $telephone = trim((string)($_POST['telephone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $adresse = trim((string)($_POST['adresse'] ?? ''));
    $mode_livraison = trim(
        (string)($_POST['mode_livraison'] ?? 'Livraison à domicile')
    );

    if (empty($articles)) {
        $erreur = 'Votre panier est vide. Ajoutez un produit avant de commander.';
    } elseif ($nom === '' || $telephone === '' || $email === '' || $adresse === '') {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez saisir une adresse e-mail valide.';
    } elseif (!preg_match('/^[0-9 +().-]{8,20}$/', $telephone)) {
        $erreur = 'Veuillez saisir un numéro de téléphone valide.';
    } else {

        /*
         * La commande est conservée en session afin que cette page
         * fonctionne immédiatement sans dépendre de noms de colonnes
         * inconnus dans votre base de données.
         *
         * L'enregistrement MySQL définitif pourra être raccordé
         * ensuite à vos tables commande et detail_commande.
         */
        $_SESSION['derniere_commande'] = [
            'nom' => $nom,
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse,
            'mode_livraison' => $mode_livraison,
            'articles' => $articles,
            'total' => $total,
            'date' => date('Y-m-d H:i:s')
        ];

        $succes = 'Votre commande a été validée avec succès. Merci pour votre confiance !';

        /* Le panier est vidé après validation. */
        $_SESSION['panier'] = [];
        $articles = [];
        $total = 0;
    }
}

/* -------------------- AFFICHAGE FCFA -------------------- */
function afficher_fcfa($montant)
{
    return number_format((float)$montant, 0, ',', ' ') . ' FCFA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Commande | Jardin des Agriculteurs</title>

    <style>
        /* =================================================
           1. RESET GENERAL
           ================================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f4;
            color: #26372c;
            line-height: 1.5;
        }

        a {
            text-decoration: none;
        }

        /* =================================================
           2. HEADER / LOGO
           ================================================= */
        .header {
            width: 100%;
            background: #155d32;
            color: #ffffff;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.12);
        }

        .header-inner {
            width: min(1180px, 92%);
            min-height: 82px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Logo J-A : J = Jardin, A = Agriculteurs */
        .logo-ja {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border: 2px solid #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.10);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-name {
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
        }

        .slogan {
            margin-top: 2px;
            color: #e4f0e7;
            font-size: 11px;
        }

        .navigation {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .navigation a {
            color: #ffffff;
            font-size: 14px;
            padding: 9px 12px;
            border-radius: 7px;
            transition: 0.2s ease;
        }

        .navigation a:hover,
        .navigation a.active {
            background: rgba(255, 255, 255, 0.17);
        }

        /* =================================================
           3. CONTENU PRINCIPAL
           ================================================= */
        .page {
            width: min(1180px, 92%);
            margin: 0 auto;
            padding: 42px 0 60px;
        }

        .page-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-heading h1 {
            color: #155d32;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #69766e;
            font-size: 15px;
        }

        /* =================================================
           4. MESSAGES
           ================================================= */
        .message {
            width: 100%;
            margin-bottom: 22px;
            padding: 14px 17px;
            border-radius: 9px;
            font-size: 14px;
        }

        .message.success {
            background: #e6f5ea;
            border: 1px solid #b7ddc1;
            color: #176437;
        }

        .message.error {
            background: #fff0f0;
            border: 1px solid #edc1c1;
            color: #9d2b2b;
        }

        /* =================================================
           5. BLOCS DE COMMANDE
           ================================================= */
        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(300px, 0.9fr) minmax(420px, 1.4fr);
            gap: 26px;
            align-items: start;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e2e9e3;
            border-radius: 14px;
            padding: 26px;
            box-shadow: 0 6px 24px rgba(28, 67, 39, 0.08);
        }

        .card-title {
            color: #155d32;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e4ebe5;
        }

        /* =================================================
           6. RESUME DU PANIER
           ================================================= */
        .empty-cart {
            text-align: center;
            padding: 25px 8px;
            color: #6b776f;
        }

        .empty-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 13px;
            border-radius: 50%;
            background: #edf5ef;
            color: #155d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
        }

        .product {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #edf1ed;
        }

        .product-name {
            color: #293a2f;
            font-weight: 700;
            font-size: 15px;
        }

        .product-info {
            color: #758178;
            font-size: 13px;
            margin-top: 4px;
        }

        .product-total {
            color: #155d32;
            font-weight: 700;
            white-space: nowrap;
            text-align: right;
        }

        .total-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-top: 20px;
            padding-top: 17px;
            border-top: 2px solid #155d32;
        }

        .total-label {
            font-size: 16px;
            font-weight: 700;
        }

        .total-amount {
            color: #155d32;
            font-size: 22px;
            font-weight: 800;
            white-space: nowrap;
        }

        .products-button {
            display: inline-block;
            margin-top: 18px;
            padding: 11px 17px;
            border-radius: 7px;
            background: #155d32;
            color: #ffffff;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .products-button:hover {
            background: #0f4726;
        }

        /* =================================================
           7. FORMULAIRE
           ================================================= */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 17px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            color: #344239;
            font-size: 14px;
            font-weight: 700;
        }

        .required {
            color: #b42b2b;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid #cdd7cf;
            border-radius: 8px;
            padding: 12px 13px;
            background: #ffffff;
            color: #26372c;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            outline: none;
            transition: 0.2s ease;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #155d32;
            box-shadow: 0 0 0 3px rgba(21, 93, 50, 0.10);
        }

        .submit-button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            background: #155d32;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .submit-button:hover {
            background: #0f4726;
            transform: translateY(-1px);
        }

        .submit-button:disabled {
            background: #9ca9a0;
            cursor: not-allowed;
            transform: none;
        }

        .form-note {
            margin-top: 12px;
            color: #78837c;
            text-align: center;
            font-size: 12px;
        }

        .invalid {
            border-color: #c0392b !important;
        }

        /* =================================================
           8. FOOTER
           ================================================= */
        .footer {
            width: 100%;
            background: #123f26;
            color: #ffffff;
            text-align: center;
            padding: 23px 15px;
            font-size: 13px;
        }

        /* =================================================
           9. RESPONSIVE
           ================================================= */
        @media (max-width: 900px) {

            .header-inner {
                flex-direction: column;
                justify-content: center;
                padding: 17px 0;
            }

            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {

            .page {
                width: 94%;
                padding-top: 28px;
            }

            .page-heading h1 {
                font-size: 26px;
            }

            .card {
                padding: 19px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .brand-name {
                font-size: 19px;
            }

            .logo-ja {
                width: 46px;
                height: 46px;
                flex-basis: 46px;
                font-size: 18px;
            }

            .navigation a {
                font-size: 13px;
                padding: 8px 9px;
            }
        }
    </style>
</head>

<body>

<!-- =====================================================
     HEADER
     ===================================================== -->
<header class="header">
    <div class="header-inner">

        <div class="brand-area">

            <!-- J-A : J = Jardin, A = Agriculteurs -->
            <div class="logo-ja" aria-label="Logo J-A">
                J-A
            </div>

            <div>
                <a href="index.php" class="brand-name">
                    Jardin des Agriculteurs
                </a>

                <div class="slogan">
                    Le prix, oui ! La qualité surtout !
                </div>
            </div>

        </div>

        <nav class="navigation">
            <a href="index.php">Accueil</a>
            <a href="products.php">Produits</a>
            <a href="panier.php">Panier</a>
            <a href="commande.php" class="active">Commande</a>
            <a href="connexion.php">Connexion</a>
        </nav>

    </div>
</header>


<!-- =====================================================
     CONTENU PRINCIPAL
     ===================================================== -->
<main class="page">

    <div class="page-heading">
        <h1>Finalisation de votre commande</h1>

        <p>
            Vérifiez votre panier puis renseignez vos informations
            de livraison.
        </p>
    </div>


    <?php if ($succes !== ''): ?>
        <div class="message success">
            <?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>


    <?php if ($erreur !== ''): ?>
        <div class="message error">
            <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>


    <div class="checkout-grid">

        <!-- =================================================
             COLONNE GAUCHE : COMMANDE
             ================================================= -->
        <section class="card">

            <h2 class="card-title">
                Votre commande
            </h2>

            <?php if (empty($articles)): ?>

                <div class="empty-cart">

                    <div class="empty-icon">
                        🛒
                    </div>

                    <p>
                        Votre panier est actuellement vide.
                    </p>

                    <a href="products.php" class="products-button">
                        Voir les produits
                    </a>

                </div>

            <?php else: ?>

                <?php foreach ($articles as $article): ?>

                    <div class="product">

                        <div>
                            <div class="product-name">
                                <?= htmlspecialchars(
                                    $article['nom'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </div>

                            <div class="product-info">
                                Quantité :
                                <?= (int)$article['quantite'] ?>
                                ×
                                <?= afficher_fcfa($article['prix']) ?>
                            </div>
                        </div>

                        <div class="product-total">
                            <?= afficher_fcfa(
                                $article['prix'] *
                                $article['quantite']
                            ) ?>
                        </div>

                    </div>

                <?php endforeach; ?>


                <div class="total-line">

                    <span class="total-label">
                        Total à payer
                    </span>

                    <span class="total-amount">
                        <?= afficher_fcfa($total) ?>
                    </span>

                </div>

            <?php endif; ?>

        </section>


        <!-- =================================================
             COLONNE DROITE : LIVRAISON
             ================================================= -->
        <section class="card">

            <h2 class="card-title">
                Informations de livraison
            </h2>

            <form
                id="commandeForm"
                method="POST"
                action="commande.php"
                novalidate
            >

                <div class="form-grid">

                    <div class="field">
                        <label for="nom">
                            Nom complet
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            placeholder="Votre nom complet"
                            autocomplete="name"
                            required
                        >
                    </div>


                    <div class="field">
                        <label for="telephone">
                            Téléphone
                            <span class="required">*</span>
                        </label>

                        <input
                            type="tel"
                            id="telephone"
                            name="telephone"
                            placeholder="Ex. 6XXXXXXXX"
                            autocomplete="tel"
                            required
                        >
                    </div>


                    <div class="field full">
                        <label for="email">
                            Adresse e-mail
                            <span class="required">*</span>
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="exemple@email.com"
                            autocomplete="email"
                            required
                        >
                    </div>


                    <div class="field full">
                        <label for="adresse">
                            Adresse de livraison
                            <span class="required">*</span>
                        </label>

                        <textarea
                            id="adresse"
                            name="adresse"
                            placeholder="Ville, quartier, lieu de livraison..."
                            required
                        ></textarea>
                    </div>


                    <div class="field full">
                        <label for="mode_livraison">
                            Mode de livraison
                        </label>

                        <select
                            id="mode_livraison"
                            name="mode_livraison"
                        >
                            <option value="Livraison à domicile">
                                Livraison à domicile
                            </option>

                            <option value="Retrait sur place">
                                Retrait sur place
                            </option>
                        </select>
                    </div>

                </div>


                <button
                    type="submit"
                    id="submitButton"
                    class="submit-button"
                    <?= empty($articles) ? 'disabled' : '' ?>
                >
                    Confirmer la commande
                </button>

                <p class="form-note">
                    * Tous les champs marqués d'un astérisque sont obligatoires.
                </p>

            </form>

        </section>

    </div>

</main>


<!-- =====================================================
     FOOTER
     ===================================================== -->
<footer class="footer">
    &copy; <?= date('Y') ?> Jardin des Agriculteurs —
    Tous droits réservés.
</footer>


<!-- =====================================================
     JAVASCRIPT
     ===================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('commandeForm');

    if (!form) {
        return;
    }

    const nom = document.getElementById('nom');
    const telephone = document.getElementById('telephone');
    const email = document.getElementById('email');
    const adresse = document.getElementById('adresse');
    const bouton = document.getElementById('submitButton');

    const champs = [nom, telephone, email, adresse];

    form.addEventListener('submit', function (event) {

        let valide = true;

        champs.forEach(function (champ) {
            champ.classList.remove('invalid');

            if (champ.value.trim() === '') {
                champ.classList.add('invalid');
                valide = false;
            }
        });

        if (email.value.trim() !== '') {
            const emailValide =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailValide.test(email.value.trim())) {
                email.classList.add('invalid');
                valide = false;
            }
        }

        if (telephone.value.trim() !== '') {
            const telephoneValide =
                /^[0-9 +().-]{8,20}$/;

            if (!telephoneValide.test(telephone.value.trim())) {
                telephone.classList.add('invalid');
                valide = false;
            }
        }

        if (!valide) {
            event.preventDefault();

            alert(
                'Veuillez remplir correctement tous les champs obligatoires.'
            );

            return;
        }

        /*
         * Empêche un double clic pendant l'envoi.
         * Le PHP reçoit toujours le formulaire normalement.
         */
        if (bouton) {
            bouton.disabled = true;
            bouton.textContent = 'Traitement en cours...';
        }
    });


    champs.forEach(function (champ) {

        champ.addEventListener('input', function () {

            if (champ.value.trim() !== '') {
                champ.classList.remove('invalid');
            }

        });

    });

});
</script>

</body>
</html>