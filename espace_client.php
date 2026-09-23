<?php

 
/*
==========================================================
JARDIN DES AGRICULTEURS — ESPACE CLIENT
==========================================================
Connexion à la base de données en PDO.
La structure visuelle de la page reste inchangée.
*/
 
require_once "connexion_bd.php";
 
/*
IMPORTANT :
connexion_bd.php doit créer une connexion PDO dans la variable $pdo.
Exemple :
$pdo = new PDO("mysql:host=localhost;dbname=jardin_agriculteurs;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
*/
 
/* ---------- Sécurité de session ---------- */

/* Vérification de la connexion PDO */
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erreur : la connexion PDO n'est pas disponible. Vérifiez connexion_bd.php.");
}
 
$idClient = (int)$_SESSION["id"];
 
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
 
function fcfa($value) {
    return number_format((float)$value, 0, ",", " ") . " FCFA";
}
 
/* ---------- Profil ---------- */
$client = null;
$erreur = "";
 
try {
    $sql = "SELECT id_utilisateur, nom, prenom, email, role
            FROM utilisateurs
            WHERE id_utilisateur = :id
              AND role = 'client'
            LIMIT 1";
 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":id" => $idClient]);
    $client = $stmt->fetch();
 
    if (!$client) {
        session_unset();
        session_destroy();
        header("Location: connexion.php");
        exit();
    }
 
} catch (PDOException $e) {
    $erreur = "Impossible de charger les informations du compte.";
}
 
$prenom = $client["prenom"] ?? "";
$nom = $client["nom"] ?? "";
$email = $client["email"] ?? "";
$nomComplet = trim($prenom . " " . $nom) ?: "Client";
 
$initiales = "";
foreach (preg_split('/\s+/', $nomComplet) as $mot) {
    if ($mot !== "") {
        $initiales .= strtoupper(substr($mot, 0, 1));
    }
}
$initiales = substr($initiales, 0, 2);
 
/* ---------- Statistiques et commandes ---------- */
$nombreCommandes = 0;
$totalAchats = 0;
$commandesEnCours = 0;
$dernieresCommandes = [];
 
try {
    /* Nombre total de commandes du client */
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total
         FROM commandes
         WHERE id_utilisateur = :id"
    );
    $stmt->execute([":id" => $idClient]);
    $row = $stmt->fetch();
    $nombreCommandes = (int)($row["total"] ?? 0);
 
    /* Total des achats hors commandes annulées */
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(total), 0) AS total
         FROM commandes
         WHERE id_utilisateur = :id
           AND LOWER(statut) NOT LIKE '%annul%'"
    );
    $stmt->execute([":id" => $idClient]);
    $row = $stmt->fetch();
    $totalAchats = (float)($row["total"] ?? 0);
 
    /* Commandes en cours : ni annulées ni livrées */
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total
         FROM commandes
         WHERE id_utilisateur = :id
           AND LOWER(statut) NOT LIKE '%annul%'
           AND LOWER(statut) NOT LIKE '%livr%'"
    );
    $stmt->execute([":id" => $idClient]);
    $row = $stmt->fetch();
    $commandesEnCours = (int)($row["total"] ?? 0);
 
    /* Les 5 dernières commandes */
    $stmt = $pdo->prepare(
        "SELECT id_commande, date_commande, total, statut
         FROM commandes
         WHERE id_utilisateur = :id
         ORDER BY date_commande DESC
         LIMIT 5"
    );
    $stmt->execute([":id" => $idClient]);
    $dernieresCommandes = $stmt->fetchAll();
 
} catch (PDOException $e) {
    /* La page reste accessible même si les données secondaires manquent. */
}
 
/* ---------- Panier de session ---------- */
$nombrePanier = 0;
 
if (isset($_SESSION["panier"]) && is_array($_SESSION["panier"])) {
    foreach ($_SESSION["panier"] as $article) {
        $nombrePanier += is_array($article)
            ? max(1, (int)($article["quantite"] ?? 1))
            : 1;
    }
}
 
$message = $_GET["message"] ?? "";
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Espace client du Jardin des Agriculteurs">
<title>Mon espace | Jardin des Agriculteurs</title>
 
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
 --vf:#0f3d28;--v:#237447;--vc:#eaf7ef;--fond:#f5f8f6;
 --blanc:#fff;--txt:#193328;--gris:#718078;--ligne:#e8eee9;
 --or:#d6a63c;--ombre:0 12px 35px rgba(15,61,40,.08)
}
html{scroll-behavior:smooth}
body{font-family:Inter,"Segoe UI",Arial,sans-serif;background:var(--fond);color:var(--txt);min-height:100vh}
a{text-decoration:none;color:inherit}
.navbar{
 height:76px;background:rgba(255,255,255,.96);border-bottom:1px solid var(--ligne);
 display:flex;align-items:center;justify-content:space-between;padding:0 5%;
 position:sticky;top:0;z-index:1000;backdrop-filter:blur(12px)
}
.logo{display:flex;align-items:center;gap:11px;font-weight:800;color:var(--vf)}
.logo-mark{
 width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,var(--vf),var(--v));
 color:#fff;display:grid;place-items:center;font-weight:900;box-shadow:0 8px 20px rgba(15,61,40,.2)
}
.nav-links{display:flex;align-items:center;gap:7px}
.nav-links a{
 padding:10px 12px;border-radius:10px;font-size:13px;font-weight:650;color:#405249;transition:.25s
}
.nav-links a:hover,.nav-links a.active{background:var(--vc);color:var(--vf)}
.cart-link{position:relative}
.cart-count{
 position:absolute;top:1px;right:1px;min-width:17px;height:17px;padding:0 4px;
 border-radius:20px;background:var(--or);color:#fff;font-size:9px;display:grid;place-items:center;font-weight:800
}
.logout{background:var(--vf)!important;color:#fff!important;margin-left:4px}
.logout:hover{background:var(--v)!important}
.container{width:min(1180px,90%);margin:0 auto}
.hero{
 margin-top:32px;border-radius:28px;padding:36px;background:linear-gradient(135deg,#0f3d28,#237447);
 color:#fff;box-shadow:0 18px 50px rgba(15,61,40,.18);display:flex;
 justify-content:space-between;align-items:center;gap:30px;position:relative;overflow:hidden
}
.hero:before,.hero:after{content:"";position:absolute;border:1px solid rgba(255,255,255,.13);border-radius:50%}
.hero:before{width:330px;height:330px;right:-100px;top:-160px}
.hero:after{width:210px;height:210px;right:80px;bottom:-155px}
.hero-content{position:relative;z-index:2}
.hero-label{font-size:11px;text-transform:uppercase;letter-spacing:1.6px;color:#d9f0e2;font-weight:800}
.hero h1{margin:9px 0 8px;font-size:clamp(27px,4vw,42px)}
.hero p{max-width:650px;color:#e7f4ec;line-height:1.7;font-size:14px}
.avatar{
 position:relative;z-index:2;flex-shrink:0;width:100px;height:100px;border-radius:50%;
 background:#fff;color:var(--vf);display:grid;place-items:center;font-size:29px;font-weight:900;
 box-shadow:0 15px 35px rgba(0,0,0,.18)
}
.message{
 margin-top:18px;padding:14px 17px;border-left:4px solid var(--v);border-radius:10px;
 background:var(--vc);color:var(--vf);font-size:13px
}
.section{margin-top:38px}
.section-heading{margin-bottom:17px}
.section-heading h2{font-size:22px;color:var(--vf)}
.section-heading p{margin-top:5px;color:var(--gris);font-size:13px}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:17px}
.stat{
 background:#fff;border-radius:18px;padding:20px;box-shadow:var(--ombre);
 border:1px solid rgba(232,238,233,.8);transition:.25s
}
.stat:hover{transform:translateY(-4px)}
.stat-icon{
 width:43px;height:43px;display:grid;place-items:center;border-radius:13px;
 background:var(--vc);font-size:20px;margin-bottom:14px
}
.stat p{color:var(--gris);font-size:12px}
.stat strong{display:block;margin-top:4px;color:var(--vf);font-size:24px}
.actions{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}
.action{
 background:#fff;border:1px solid var(--ligne);border-radius:17px;padding:20px;min-height:155px;
 box-shadow:0 7px 25px rgba(15,61,40,.05);transition:.25s
}
.action:hover{transform:translateY(-5px);border-color:#b8d9c2;box-shadow:var(--ombre)}
.action-icon{font-size:27px;margin-bottom:13px}
.action h3{color:var(--vf);font-size:15px}
.action p{color:var(--gris);font-size:12px;line-height:1.55;margin-top:6px}
.action span{display:block;color:var(--v);font-size:11px;font-weight:800;margin-top:13px}
.main-grid{display:grid;grid-template-columns:1.55fr .9fr;gap:20px}
.panel{
 background:#fff;border-radius:20px;padding:23px;box-shadow:var(--ombre);
 border:1px solid rgba(232,238,233,.8)
}
.panel-header{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:17px}
.panel-header h3{color:var(--vf);font-size:16px}
.panel-header a{color:var(--v);font-size:11px;font-weight:800}
.table-wrapper{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:500px}
th{
 text-align:left;color:#89948d;font-size:10px;text-transform:uppercase;padding:11px;
 border-bottom:1px solid var(--ligne)
}
td{padding:13px 11px;font-size:12px;border-bottom:1px solid #f0f3f0}
tr:last-child td{border-bottom:none}
.badge{display:inline-block;padding:5px 9px;border-radius:20px;font-size:9px;font-weight:800}
.waiting{background:#fff4d8;color:#9b6b00}
.confirmed{background:#e5f5e8;color:#267238}
.cancelled{background:#ffe8e8;color:#a33131}
.neutral{background:#eef1ef;color:#607067}
.profile{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.info{background:#f8faf8;border:1px solid var(--ligne);border-radius:13px;padding:15px}
.info span{display:block;color:var(--gris);font-size:10px;margin-bottom:5px}
.info strong{font-size:13px;color:var(--txt);word-break:break-word}
.cart-box{
 background:linear-gradient(135deg,#f8fcf9,#edf8f0);border:1px solid #dcebe0;
 border-radius:17px;padding:20px
}
.cart-number{font-size:31px;font-weight:900;color:var(--vf)}
.cart-box p{color:var(--gris);font-size:12px;margin-top:4px}
.btn{
 display:inline-block;margin-top:15px;padding:11px 16px;border-radius:10px;background:var(--vf);
 color:#fff;font-size:11px;font-weight:800;transition:.25s
}
.btn:hover{background:var(--v);transform:translateY(-2px)}
.advice{
 margin-top:20px;background:#fff;border-radius:20px;padding:23px;box-shadow:var(--ombre);
 border:1px solid var(--ligne)
}
.advice-inner{display:flex;align-items:center;gap:17px}
.advice-icon{
 width:52px;height:52px;flex-shrink:0;display:grid;place-items:center;
 border-radius:16px;background:var(--vc);font-size:25px
}
.advice h3{color:var(--vf);font-size:15px}
.advice p{color:var(--gris);font-size:12px;line-height:1.6;margin-top:5px}
footer{margin-top:48px;padding:26px 5%;text-align:center;background:var(--vf);color:#dcebe3;font-size:12px}
footer strong{color:#fff}
@media(max-width:1000px){
 .actions{grid-template-columns:repeat(2,1fr)}.main-grid{grid-template-columns:1fr}
}
@media(max-width:700px){
 .navbar{height:auto;padding:13px 5%;flex-wrap:wrap;gap:9px}
 .nav-links{width:100%;justify-content:center;flex-wrap:wrap}
 .hero{padding:27px 22px;flex-direction:column;align-items:flex-start}
 .avatar{width:78px;height:78px;font-size:23px}.stats{grid-template-columns:1fr}
 .profile{grid-template-columns:1fr}
}
@media(max-width:480px){
 .container{width:92%}.actions{grid-template-columns:1fr}.panel{padding:17px}
 .action{min-height:auto}.hero h1{font-size:28px}
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
        <a href="index.php">Accueil 🏠</a>
        <a href="products.php">Produits 🌱</a>
        <a href="espace_client.php" class="active">Mon espace 👤</a>
        <a href="commande.php">Mes commandes</a>
 
        <a href="panier.php" class="cart-link">
            Panier 🛒
            <?php if ($nombrePanier > 0): ?>
                <span class="cart-count"><?= $nombrePanier ?></span>
            <?php endif; ?>
        </a>
 
        <a href="deconnexion.php" class="logout">Déconnexion</a>
    </nav>
</header>
 
<main class="container">
 
<section class="hero">
    <div class="hero-content">
        <div class="hero-label">Mon espace personnel</div>
 
        <h1>Bonjour <?= h($prenom ?: $nom) ?> 👋</h1>
 
        <p>
            Bienvenue dans votre espace client du
            <strong>Jardin des Agriculteurs</strong>.
            Gérez votre profil, consultez vos commandes, retrouvez votre panier
            et découvrez nos produits agricoles.
        </p>
    </div>
 
    <div class="avatar"><?= h($initiales) ?></div>
</section>
 
<?php if ($message !== ""): ?>
    <div class="message"><?= h($message) ?> ✅</div>
<?php endif; ?>
 
<?php if ($erreur !== ""): ?>
    <div class="message"><?= h($erreur) ?> ⚠️</div>
<?php endif; ?>
 
<section class="section">
    <div class="section-heading">
        <h2>Mon activité</h2>
        <p>Un aperçu de votre activité sur la plateforme.</p>
    </div>
 
    <div class="stats">
        <div class="stat">
            <div class="stat-icon">📦</div>
            <p>Mes commandes</p>
            <strong><?= $nombreCommandes ?></strong>
        </div>
 
        <div class="stat">
            <div class="stat-icon">🚚</div>
            <p>Commandes en cours</p>
            <strong><?= $commandesEnCours ?></strong>
        </div>
 
        <div class="stat">
            <div class="stat-icon">💰</div>
            <p>Total de mes achats</p>
            <strong><?= fcfa($totalAchats) ?></strong>
        </div>
    </div>
</section>
 
<section class="section">
    <div class="section-heading">
        <h2>Que souhaitez-vous faire ?</h2>
        <p>Accédez aux fonctionnalités réservées aux clients.</p>
    </div>
 
    <div class="actions">
        <a href="products.php" class="action">
            <div class="action-icon">🌱</div>
            <h3>Découvrir les produits</h3>
            <p>Parcourez les produits agricoles disponibles et leurs informations.</p>
            <span>Explorer →</span>
        </a>
 
        <a href="panier.php" class="action">
            <div class="action-icon">🛒</div>
            <h3>Mon panier</h3>
            <p>Retrouvez les articles sélectionnés avant de finaliser votre commande.</p>
            <span>Voir mon panier →</span>
        </a>
 
        <a href="#commandes" class="action">
            <div class="action-icon">📋</div>
            <h3>Mes commandes</h3>
            <p>Consultez l'historique et le statut de vos commandes.</p>
            <span>Consulter →</span>
        </a>
 
        <a href="#profil" class="action">
            <div class="action-icon">👤</div>
            <h3>Mon profil</h3>
            <p>Consultez les informations de votre compte client.</p>
            <span>Voir mon profil →</span>
        </a>
    </div>
</section>
 
<section class="section" id="commandes">
    <div class="main-grid">
 
        <section class="panel">
            <div class="panel-header">
                <h3>Mes dernières commandes 📦</h3>
                <a href="commande.php">Voir la page commande →</a>
            </div>
 
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
 
                    <tbody>
                    <?php if (!empty($dernieresCommandes)): ?>
 
                        <?php foreach ($dernieresCommandes as $commande): ?>
 
                            <?php
                            $statut = $commande["statut"] ?? "En attente";
                            $normalise = strtolower($statut);
                            $classe = "neutral";
 
                            if (strpos($normalise, "attente") !== false) {
                                $classe = "waiting";
                            } elseif (strpos($normalise, "confirm") !== false) {
                                $classe = "confirmed";
                            } elseif (strpos($normalise, "annul") !== false) {
                                $classe = "cancelled";
                            }
                            ?>
 
                            <tr>
                                <td>
                                    <strong>#<?= h($commande["id_commande"]) ?></strong>
                                </td>
 
                                <td>
                                    <?= !empty($commande["date_commande"])
                                        ? h(date("d/m/Y", strtotime($commande["date_commande"])))
                                        : "-" ?>
                                </td>
 
                                <td>
                                    <strong><?= fcfa($commande["total"] ?? 0) ?></strong>
                                </td>
 
                                <td>
                                    <span class="badge <?= h($classe) ?>">
                                        <?= h($statut) ?>
                                    </span>
                                </td>
                            </tr>
 
                        <?php endforeach; ?>
 
                    <?php else: ?>
 
                        <tr>
                            <td colspan="4" style="text-align:center;padding:30px;">
                                Vous n'avez pas encore passé de commande.
                            </td>
                        </tr>
 
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
 
        <aside>
            <section class="panel">
                <div class="panel-header">
                    <h3>Mon panier 🛒</h3>
                </div>
 
                <div class="cart-box">
                    <div class="cart-number"><?= $nombrePanier ?></div>
                    <p>article(s) actuellement dans votre panier.</p>
                    <a href="panier.php" class="btn">Ouvrir mon panier</a>
                </div>
            </section>
        </aside>
 
    </div>
</section>
 
<section class="section" id="profil">
    <div class="section-heading">
        <h2>Mon profil 👤</h2>
        <p>Informations de votre compte client.</p>
    </div>
 
    <section class="panel">
        <div class="profile">
 
            <div class="info">
                <span>Nom</span>
                <strong><?= h($nom) ?: "Non renseigné" ?></strong>
            </div>
 
            <div class="info">
                <span>Prénom</span>
                <strong><?= h($prenom) ?: "Non renseigné" ?></strong>
            </div>
 
            <div class="info">
                <span>Adresse e-mail</span>
                <strong><?= h($email) ?></strong>
            </div>
 
            <div class="info">
                <span>Type de compte</span>
                <strong>Client</strong>
            </div>
 
            <div class="info">
                <span>Identifiant client</span>
                <strong>#<?= $idClient ?></strong>
            </div>
 
            <div class="info">
                <span>Accès</span>
                <strong>Espace client sécurisé</strong>
            </div>
 
        </div>
    </section>
</section>
 
<section class="advice">
    <div class="advice-inner">
        <div class="advice-icon">🌿</div>
 
        <div>
            <h3>Le prix, oui ! La qualité surtout !</h3>
            <p>
                Explorez notre catalogue pour trouver les produits adaptés
                à vos besoins agricoles et passez votre commande depuis votre espace client.
            </p>
        </div>
    </div>
</section>
 
</main>
 
<footer>
    © <?= date("Y") ?> <strong>Jardin des Agriculteurs</strong>
    — Le prix, oui ! La qualité surtout !
</footer>
 
<script>
document.addEventListener("DOMContentLoaded", function () {
    const elements = document.querySelectorAll(".action, .stat, .panel");
 
    elements.forEach(function (element, index) {
        element.style.opacity = "0";
        element.style.transform = "translateY(10px)";
 
        setTimeout(function () {
            element.style.transition = "opacity .45s ease, transform .45s ease";
            element.style.opacity = "1";
            element.style.transform = "translateY(0)";
        }, 60 * index);
    });
});
</script>
 
</body>
</html>

