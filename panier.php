
<?php
session_start();

/*
 * JARDIN DES AGRICULTEURS
 * panier.php
 *
 * Version autonome :
 * - PHP + HTML + CSS + JavaScript dans UN SEUL fichier
 * - panier conservé dans $_SESSION
 * - aucun fichier CSS ou JS supplémentaire nécessaire
 *
 * Format attendu pour $_SESSION['panier'] :
 * [
 *   12 => [
 *      'nom' => 'Engrais NPK',
 *      'prix' => 8500,
 *      'image' => 'images/engrais.jpg',
 *      'categorie' => 'Engrais',
 *      'quantite' => 2
 *   ]
 * ]
 */

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value) {
    return number_format((float)$value, 0, ',', ' ') . ' FCFA';
}

/* Modifier les quantités */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'update' && isset($_POST['id'], $_POST['quantite'])) {
            $id = (int)$_POST['id'];
            $quantite = max(1, (int)$_POST['quantite']);

            if (isset($_SESSION['panier'][$id])) {
                $_SESSION['panier'][$id]['quantite'] = $quantite;
            }
        }

        if ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            unset($_SESSION['panier'][$id]);
        }

        if ($action === 'clear') {
            $_SESSION['panier'] = [];
        }

        header('Location: panier.php');
        exit;
    }
}

$panier = $_SESSION['panier'];
$sousTotal = 0;

foreach ($panier as $article) {
    $prix = (float)($article['prix'] ?? 0);
    $quantite = max(1, (int)($article['quantite'] ?? 1));
    $sousTotal += $prix * $quantite;
}

/*
 * Livraison :
 * Ici, un montant fixe est utilisé pour l'affichage.
 * Tu pourras ensuite le remplacer par le calcul selon la ville.
 */
$fraisLivraison = empty($panier) ? 0 : 1500;
$total = $sousTotal + $fraisLivraison;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mon panier | Jardin des Agriculteurs</title>

    <style>
        :root {
            --forest: #14532d;
            --forest-dark: #0b3b20;
            --green: #22a455;
            --light-green: #eef7f0;
            --cream: #f7faf6;
            --white: #ffffff;
            --text: #172018;
            --muted: #6b756d;
            --border: #e2eae3;
            --danger: #b42318;
            --danger-bg: #fff1f1;
            --shadow: 0 18px 50px rgba(20, 83, 45, .08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--cream);
            color: var(--text);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        /* HEADER */
        .cart-header {
            height: 78px;
            padding: 0 7%;
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .logo {
            color: var(--forest);
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -.3px;
        }

        .logo span {
            color: var(--green);
        }

        .continue {
            color: var(--forest);
            font-weight: 700;
            transition: .25s ease;
        }

        .continue:hover {
            color: var(--green);
            transform: translateX(-3px);
        }

        /* PAGE */
        .page {
            width: min(1180px, 92%);
            margin: auto;
            padding: 60px 0 90px;
        }

        .hero {
            margin-bottom: 35px;
            animation: reveal .7s ease both;
        }

        .eyebrow {
            display: inline-block;
            color: var(--green);
            font-size: .75rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 9px;
        }

        .hero h1 {
            color: var(--forest-dark);
            font-size: clamp(2.3rem, 6vw, 4rem);
            line-height: 1;
            margin-bottom: 14px;
        }

        .hero p {
            color: var(--muted);
            font-size: 1rem;
        }

        /* GRID */
        .cart-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 370px;
            gap: 28px;
            align-items: start;
        }

        /* ARTICLES */
        .cart-items {
            display: grid;
            gap: 15px;
        }

        .cart-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 17px;
            display: grid;
            grid-template-columns: 90px minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            box-shadow: var(--shadow);
            transition: transform .25s ease, box-shadow .25s ease;
            animation: reveal .5s ease both;
        }

        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 55px rgba(20,83,45,.12);
        }

        .product-image {
            width: 90px;
            height: 90px;
            border-radius: 16px;
            object-fit: cover;
            background: var(--light-green);
        }

        .category {
            color: var(--green);
            font-size: .7rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .product-name {
            color: var(--forest-dark);
            font-size: 1.08rem;
            margin: 5px 0;
        }

        .unit-price {
            color: var(--forest);
            font-weight: 900;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .qty-btn,
        .quantity-input {
            width: 38px;
            height: 38px;
            border: 0;
            background: white;
            text-align: center;
        }

        .qty-btn {
            color: var(--forest);
            font-size: 1.15rem;
            cursor: pointer;
            transition: .2s ease;
        }

        .qty-btn:hover {
            background: var(--light-green);
        }

        .quantity-input {
            outline: none;
            font-weight: 800;
        }

        .delete-form {
            display: inline;
        }

        .delete-btn {
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 12px;
            background: var(--danger-bg);
            color: var(--danger);
            cursor: pointer;
            transition: .2s ease;
        }

        .delete-btn:hover {
            transform: scale(1.07);
        }

        /* SUMMARY */
        .summary {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 27px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
        }

        .summary h2 {
            color: var(--forest-dark);
            margin-bottom: 18px;
        }

        .summary-line,
        .total-line {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-line span {
            color: var(--muted);
        }

        .summary-line strong {
            color: var(--text);
        }

        .promo {
            margin: 22px 0;
        }

        .promo label {
            display: block;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .promo-row {
            display: flex;
            gap: 8px;
        }

        .promo-row input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
        }

        .promo-row input:focus {
            border-color: var(--green);
        }

        .promo-row button {
            border: 0;
            border-radius: 11px;
            padding: 0 16px;
            background: var(--light-green);
            color: var(--forest);
            font-weight: 800;
            cursor: pointer;
        }

        .total-line {
            border: 0;
            padding-top: 5px;
            font-size: 1.2rem;
            color: var(--forest-dark);
        }

        .checkout {
            display: block;
            width: 100%;
            border: 0;
            border-radius: 13px;
            padding: 15px 18px;
            background: var(--forest);
            color: white;
            text-align: center;
            font-size: 1rem;
            font-weight: 900;
            cursor: pointer;
            transition: .25s ease;
        }

        .checkout:hover {
            background: var(--forest-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(20,83,45,.22);
        }

        .secure {
            text-align: center;
            color: var(--muted);
            font-size: .78rem;
            margin-top: 13px;
        }

        .clear-cart {
            margin-top: 14px;
            width: 100%;
            border: 1px solid #f0d6d6;
            border-radius: 12px;
            background: white;
            color: var(--danger);
            padding: 11px;
            cursor: pointer;
            font-weight: 700;
        }

        /* EMPTY */
        .empty {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 80px 25px;
            text-align: center;
            box-shadow: var(--shadow);
            animation: reveal .6s ease both;
        }

        .empty-icon {
            font-size: 4.5rem;
            margin-bottom: 15px;
        }

        .empty h2 {
            color: var(--forest-dark);
            margin-bottom: 10px;
        }

        .empty p {
            color: var(--muted);
            margin-bottom: 25px;
        }

        .shop-button {
            display: inline-block;
            background: var(--forest);
            color: white;
            padding: 13px 22px;
            border-radius: 12px;
            font-weight: 900;
            transition: .25s ease;
        }

        .shop-button:hover {
            background: var(--forest-dark);
            transform: translateY(-2px);
        }

        /* TOAST */
        .toast {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: var(--forest-dark);
            color: white;
            padding: 13px 18px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,.18);
            z-index: 999;
            animation: toastIn .3s ease both;
        }

        /* ANIMATIONS */
        @keyframes reveal {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 850px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .summary {
                position: static;
            }
        }

        @media (max-width: 600px) {
            .cart-header {
                padding: 0 5%;
            }

            .logo {
                font-size: 1rem;
            }

            .continue {
                font-size: .85rem;
            }

            .page {
                padding-top: 40px;
            }

            .cart-item {
                grid-template-columns: 70px minmax(0,1fr);
                gap: 13px;
            }

            .product-image {
                width: 70px;
                height: 70px;
            }

            .item-actions {
                grid-column: 2;
            }

            .summary {
                padding: 21px;
            }
        }
    </style>
</head>

<body>

<header class="cart-header">
    <a href="index.php" class="logo">
        🌿 Jardin des <span>Agriculteurs</span>
    </a>

    <a href="produits.php" class="continue">
        ← Continuer mes achats
    </a>
</header>

<main class="page">

    <section class="hero">
        <span class="eyebrow">Votre sélection</span>
        <h1>Mon panier</h1>
        <p>Vérifiez vos produits avant de passer votre commande.</p>
    </section>

    <?php if (empty($panier)): ?>

        <section class="empty">
            <div class="empty-icon">🛒</div>
            <h2>Votre panier est vide</h2>
            <p>Découvrez nos semences, engrais, outils et produits agricoles.</p>
            <a href="produits.php" class="shop-button">
                Découvrir les produits
            </a>
        </section>

    <?php else: ?>

        <section class="cart-layout">

            <div class="cart-items">

                <?php foreach ($panier as $id => $article): ?>

                    <?php
                    $nom = $article['nom'] ?? 'Produit agricole';
                    $prix = (float)($article['prix'] ?? 0);
                    $image = $article['image'] ?? 'images/produit.jpg';
                    $categorie = $article['categorie'] ?? 'Produit agricole';
                    $quantite = max(1, (int)($article['quantite'] ?? 1));
                    ?>

                    <article class="cart-item">

                        <img
                            class="product-image"
                            src="<?= e($image) ?>"
                            alt="<?= e($nom) ?>"
                            onerror="this.src='images/produit.jpg';"
                        >

                        <div>
                            <span class="category">
                                <?= e($categorie) ?>
                            </span>

                            <h2 class="product-name">
                                <?= e($nom) ?>
                            </h2>

                            <span class="unit-price">
                                <?= money($prix) ?>
                            </span>
                        </div>

                        <div class="item-actions">

                            <form method="POST" class="quantity-form">

                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int)$id ?>">

                                <button
                                    type="button"
                                    class="qty-btn minus"
                                >−</button>

                                <input
                                    class="quantity-input"
                                    type="number"
                                    name="quantite"
                                    value="<?= $quantite ?>"
                                    min="1"
                                >

                                <button
                                    type="button"
                                    class="qty-btn plus"
                                >+</button>

                            </form>

                            <form method="POST" class="delete-form">

                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$id ?>">

                                <button
                                    type="submit"
                                    class="delete-btn"
                                    title="Supprimer"
                                >🗑</button>

                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

            <aside class="summary">

                <h2>Résumé de la commande</h2>

                <div class="summary-line">
                    <span>Sous-total</span>
                    <strong><?= money($sousTotal) ?></strong>
                </div>

                <div class="summary-line">
                    <span>Livraison</span>
                    <strong><?= money($fraisLivraison) ?></strong>
                </div>

                <div class="promo">
                    <label for="promo">Code promotionnel</label>

                    <div class="promo-row">
                        <input
                            type="text"
                            id="promo"
                            placeholder="Votre code"
                        >

                        <button type="button" id="promoButton">
                            OK
                        </button>
                    </div>
                </div>

                <div class="total-line">
                    <span>Total</span>
                    <strong><?= money($total) ?></strong>
                </div>

                <a href="commande.php" class="checkout" id="checkout">
                    Passer la commande →
                </a>

                <p class="secure">
                    🔒 Commande sécurisée • Livraison disponible
                </p>

                <form method="POST">
                    <input type="hidden" name="action" value="clear">

                    <button
                        type="submit"
                        class="clear-cart"
                        onclick="return confirm('Voulez-vous vraiment vider le panier ?');"
                    >
                        Vider le panier
                    </button>
                </form>

            </aside>

        </section>

    <?php endif; ?>

</main>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /*
     * Bouton PLUS
     */
    document.querySelectorAll(".plus").forEach(function(button) {

        button.addEventListener("click", function() {

            const form = button.closest("form");
            const input = form.querySelector(".quantity-input");

            input.value = parseInt(input.value || 1) + 1;

            form.submit();
        });
    });

    /*
     * Bouton MOINS
     */
    document.querySelectorAll(".minus").forEach(function(button) {

        button.addEventListener("click", function() {

            const form = button.closest("form");
            const input = form.querySelector(".quantity-input");

            let quantity = parseInt(input.value || 1);

            if (quantity > 1) {
                quantity--;
                input.value = quantity;
                form.submit();
            }

        });
    });

    /*
     * Modification manuelle de la quantité
     */
    document.querySelectorAll(".quantity-input").forEach(function(input) {

        input.addEventListener("change", function() {

            if (parseInt(input.value) < 1 || isNaN(parseInt(input.value))) {
                input.value = 1;
            }

            input.closest("form").submit();
        });

    });

    /*
     * Code promo
     */
    const promoButton = document.getElementById("promoButton");

    if (promoButton) {

        promoButton.addEventListener("click", function() {

            const code = document.getElementById("promo").value.trim();

            if (code === "") {
                showToast("Veuillez saisir un code promotionnel.");
                return;
            }

            showToast("Code enregistré. Validation lors de la commande.");
        });
    }

    /*
     * Animation lors du passage à la commande
     */
    const checkout = document.getElementById("checkout");

    if (checkout) {

        checkout.addEventListener("click", function(event) {

            checkout.style.transform = "scale(.98)";

            setTimeout(function() {
                checkout.style.transform = "";
            }, 150);

        });
    }

    /*
     * Petit message animé
     */
    function showToast(message) {

        const oldToast = document.querySelector(".toast");

        if (oldToast) {
            oldToast.remove();
        }

        const toast = document.createElement("div");

        toast.className = "toast";
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(function() {
            toast.remove();
        }, 2200);
    }

});
</script>

</body>
</html>
