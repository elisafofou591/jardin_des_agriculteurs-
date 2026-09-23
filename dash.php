<?php
session_start();
 
/*
==========================================================
JARDIN DES AGRICULTEURS
dashboard.php — Tableau de bord administrateur PREMIUM
==========================================================
 
Ce tableau de bord est réservé au rôle :
    administrateur
 
Connexion attendue :
    connexion_bd.php
    variable mysqli : $connexion
 
Tables utilisées par ce dashboard :
    utilisateurs
    produits
    commandes
    fournisseurs
 
Pages de gestion prévues :
    clients.php
    produits.php
    commandes.php
    fournisseurs.php
    ajouter_produit.php
    ajouter_fournisseur.php
    espace_client.php
    deconnexion.php
*/
 
// Connexion à la base de données

/* ==========================================================
   PROTECTION ADMINISTRATEUR
   ========================================================== */
 

  
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
 
function fcfa($montant) {
    return number_format((float)$montant, 0, ",", " ") . " FCFA";
}
 
/* ==========================================================
   VARIABLES PAR DÉFAUT
   ========================================================== */
 
$nbProduits = 0;
$nbCommandes = 0;
$nbClients = 0;
$nbFournisseurs = 0;
$nbAdministrateurs = 0;
$commandesAttente = 0;
$commandesConfirmees = 0;
$commandesAnnulees = 0;
$chiffreAffaires = 0;
$stocksFaibles = [];
$commandesRecentes = [];
$clientsRecents = [];
$erreurBDD = "";
 
/* ==========================================================
   STATISTIQUES ET DONNÉES DU DASHBOARD
   ========================================================== */
 
try {
    /* Produits */
    $result = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM produits");
    if ($result) {
        $nbProduits = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Commandes */
    $result = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM commandes");
    if ($result) {
        $nbCommandes = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Clients uniquement */
    $result = mysqli_query(
        $connexion,
        "SELECT COUNT(*) AS total
         FROM utilisateurs
         WHERE role = 'client'"
    );
    if ($result) {
        $nbClients = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Administrateurs */
    $result = mysqli_query(
        $connexion,
        "SELECT COUNT(*) AS total
         FROM utilisateurs
         WHERE role = 'administrateur'"
    );
    if ($result) {
        $nbAdministrateurs = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Fournisseurs */
    $result = mysqli_query($connexion, "SELECT COUNT(*) AS total FROM fournisseurs");
    if ($result) {
        $nbFournisseurs = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Chiffre d'affaires */
    $result = mysqli_query(
        $connexion,
        "SELECT COALESCE(SUM(total), 0) AS total
         FROM commandes
         WHERE LOWER(statut) NOT IN ('annulée', 'annulee', 'annulée(s)', 'annulee(s)')"
    );
    if ($result) {
        $chiffreAffaires = (float)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Commandes en attente */
    $result = mysqli_query(
        $connexion,
        "SELECT COUNT(*) AS total
         FROM commandes
         WHERE LOWER(statut) IN ('en attente', 'en_attente', 'attente')"
    );
    if ($result) {
        $commandesAttente = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Commandes confirmées */
    $result = mysqli_query(
        $connexion,
        "SELECT COUNT(*) AS total
         FROM commandes
         WHERE LOWER(statut) IN ('confirmée', 'confirmee', 'confirmé', 'confirme')"
    );
    if ($result) {
        $commandesConfirmees = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* Commandes annulées */
    $result = mysqli_query(
        $connexion,
        "SELECT COUNT(*) AS total
         FROM commandes
         WHERE LOWER(statut) IN ('annulée', 'annulee', 'annulée(s)', 'annulee(s)')"
    );
    if ($result) {
        $commandesAnnulees = (int)mysqli_fetch_assoc($result)["total"];
    }
 
    /* ------------------------------------------------------
       COMMANDES RÉCENTES
       ------------------------------------------------------ */
    $sqlCommandes = "
        SELECT
            c.id_commande,
            c.date_commande,
            c.total,
            c.statut,
            u.nom,
            u.prenom
        FROM commandes c
        LEFT JOIN utilisateurs u
            ON c.id_utilisateur = u.id_utilisateur
        ORDER BY c.date_commande DESC
        LIMIT 7
    ";
 
    $result = mysqli_query($connexion, $sqlCommandes);
 
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $commandesRecentes[] = $row;
        }
    }
 
    /* ------------------------------------------------------
       PRODUITS À STOCK FAIBLE
       ------------------------------------------------------ */
    $sqlStock = "
        SELECT id_produit, nom_produit, stock, prix
        FROM produits
        WHERE stock <= 5
        ORDER BY stock ASC
        LIMIT 6
    ";
 
    $result = mysqli_query($connexion, $sqlStock);
 
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $stocksFaibles[] = $row;
        }
    }
 
    /* ------------------------------------------------------
       CLIENTS RÉCENTS
       ------------------------------------------------------ */
    $sqlClients = "
        SELECT id_utilisateur, nom, prenom, email
        FROM utilisateurs
        WHERE role = 'client'
        ORDER BY id_utilisateur DESC
        LIMIT 5
    ";
 
    $result = mysqli_query($connexion, $sqlClients);
 
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clientsRecents[] = $row;
        }
    }
 
} catch (Throwable $e) {
    $erreurBDD = "Une erreur est survenue lors du chargement des données. "
               . "Vérifiez la connexion et les noms des tables/colonnes.";
}
 
/* Initiales administrateur */
$initiales = "";
foreach (preg_split('/\s+/', trim($nomAdmin)) as $mot) {
    if ($mot !== "") {
        $initiales .= strtoupper(substr($mot, 0, 1));
    }
}
$initiales = substr($initiales, 0, 2);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Tableau de bord administrateur - Jardin des Agriculteurs">
<title>Dashboard | Jardin des Agriculteurs</title>
 
<style>
*{margin:0;padding:0;box-sizing:border-box}
 
:root{
    --vert-fonce:#103d22;
    --vert:#2e7d32;
    --vert-clair:#eaf5ec;
    --or:#d6a63c;
    --fond:#f3f7f3;
    --blanc:#fff;
    --texte:#243329;
    --gris:#718078;
    --rouge:#a33131;
    --ombre:0 8px 28px rgba(20,60,35,.07);
}
 
body{
    font-family:Inter,"Segoe UI",Arial,sans-serif;
    background:var(--fond);
    color:var(--texte);
}
 
a{text-decoration:none;color:inherit}
 
.dashboard{
    display:flex;
    min-height:100vh;
}
 
.sidebar{
    width:260px;
    background:linear-gradient(180deg,#103d22,#174d2b);
    color:#fff;
    padding:24px 18px;
    position:fixed;
    top:0;left:0;bottom:0;
    z-index:100;
    box-shadow:5px 0 25px rgba(0,0,0,.08);
    overflow-y:auto;
}
 
.brand{
    padding:5px 10px 25px;
    border-bottom:1px solid rgba(255,255,255,.15);
}
 
.brand h1{font-size:20px}
.brand h1 span{color:#b7e4bd}
.brand p{margin-top:5px;font-size:12px;opacity:.75}
 
.menu{margin-top:22px}
.menu-title{
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:1px;
    opacity:.55;
    padding:0 12px 10px;
}
 
.menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    margin-bottom:5px;
    border-radius:10px;
    transition:.25s ease;
    font-size:13px;
}
 
.menu a:hover,.menu a.active{
    background:rgba(255,255,255,.13);
    transform:translateX(3px);
}
 
.icon{width:23px;text-align:center;font-size:17px}
 
.logout{
    margin-top:25px;
    padding-top:18px;
    border-top:1px solid rgba(255,255,255,.12);
}
 
.logout a{
    display:block;
    padding:12px;
    border-radius:10px;
    text-align:center;
    background:rgba(255,255,255,.08);
}
 
.logout a:hover{background:rgba(255,255,255,.16)}
 
.main{
    margin-left:260px;
    width:calc(100% - 260px);
    padding:28px 35px 45px;
}
 
.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:25px;
}
 
.welcome h2{font-size:27px;color:#143e23}
.welcome p{color:var(--gris);margin-top:5px;font-size:14px}
 
.admin{
    display:flex;
    align-items:center;
    gap:12px;
    background:#fff;
    padding:8px 14px 8px 8px;
    border-radius:40px;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}
 
.avatar{
    width:42px;height:42px;border-radius:50%;
    background:linear-gradient(135deg,#2e7d32,#6aa96f);
    display:grid;place-items:center;
    color:#fff;font-weight:bold;
}
 
.admin strong{display:block;font-size:13px}
.admin small{color:#849087}
 
.error-box{
    background:#fff0f0;
    color:#9d2c2c;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}
 
.stats{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:17px;
    margin-bottom:18px;
}
 
.stat-card{
    background:#fff;
    border-radius:17px;
    padding:20px;
    box-shadow:var(--ombre);
    transition:.3s ease;
    position:relative;
    overflow:hidden;
}
 
.stat-card:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 35px rgba(20,60,35,.11);
}
 
.stat-card::after{
    content:"";
    position:absolute;
    width:80px;height:80px;
    border-radius:50%;
    background:#eef7ef;
    right:-25px;top:-25px;
}
 
.stat-icon{font-size:23px;margin-bottom:10px}
.stat-card p{color:#7b887f;font-size:12px}
.stat-card h3{margin-top:5px;font-size:25px;color:#173f25}
 
.finance{
    display:grid;
    grid-template-columns:2fr 1fr 1fr;
    gap:17px;
    margin-bottom:20px;
}
 
.finance-card{
    background:#fff;
    border-radius:17px;
    padding:21px;
    box-shadow:var(--ombre);
}
 
.finance-card p{color:var(--gris);font-size:12px}
.finance-card h2{margin-top:7px;color:#14532d;font-size:27px}
.finance-card .small{font-size:12px;color:#849087;margin-top:5px}
 
.content-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}
 
.panel{
    background:#fff;
    border-radius:17px;
    padding:22px;
    box-shadow:var(--ombre);
    margin-bottom:20px;
}
 
.panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:17px;
}
 
.panel-header h3{color:#173f25;font-size:17px}
.view-all{font-size:12px;color:#2e7d32;font-weight:bold}
 
.table-wrapper{overflow-x:auto}
 
table{
    width:100%;
    border-collapse:collapse;
    min-width:620px;
}
 
th{
    text-align:left;
    font-size:10px;
    text-transform:uppercase;
    color:#89948d;
    padding:11px;
    border-bottom:1px solid #edf1ed;
}
 
td{
    padding:13px 11px;
    border-bottom:1px solid #f0f3f0;
    font-size:12px;
}
 
tr:last-child td{border-bottom:none}
 
.client{font-weight:bold;color:#27392c}
.client small{display:block;color:#8a958e;font-weight:normal;margin-top:2px}
 
.badge{
    display:inline-block;
    padding:5px 9px;
    border-radius:20px;
    font-size:10px;
    font-weight:bold;
}
 
.waiting{background:#fff4d8;color:#9b6b00}
.confirmed{background:#e5f5e8;color:#267238}
.cancelled{background:#ffe8e8;color:#a33131}
 
.stock-item{
    padding:13px 0;
    border-bottom:1px solid #edf1ed;
}
 
.stock-item:last-child{border-bottom:none}
 
.stock-top{
    display:flex;
    justify-content:space-between;
    gap:10px;
    font-size:12px;
    font-weight:bold;
}
 
.stock-number{color:#b3261e}
 
.progress{
    height:6px;
    margin-top:8px;
    background:#edf1ed;
    border-radius:10px;
    overflow:hidden;
}
 
.progress span{
    display:block;
    height:100%;
    background:#d89b2b;
    border-radius:inherit;
}
 
.quick-actions{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}
 
.quick{
    border:1px solid #e4ebe5;
    border-radius:12px;
    padding:14px;
    text-align:center;
    transition:.25s;
    background:#fff;
}
 
.quick:hover{
    border-color:#76a87d;
    transform:translateY(-2px);
    background:#f9fcfa;
}
 
.quick div{font-size:21px;margin-bottom:6px}
.quick span{display:block;font-size:11px;font-weight:bold}
 
.client-card{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:12px 0;
    border-bottom:1px solid #edf1ed;
}
 
.client-card:last-child{border-bottom:none}
 
.client-avatar{
    width:36px;height:36px;border-radius:50%;
    display:grid;place-items:center;
    background:var(--vert-clair);
    color:var(--vert-fonce);
    font-size:12px;
    font-weight:bold;
    flex-shrink:0;
}
 
.client-info{flex:1;min-width:0}
.client-info strong{font-size:12px;display:block}
.client-info small{
    color:#89948d;
    font-size:10px;
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
 
.manage-link{
    color:var(--vert);
    font-size:11px;
    font-weight:bold;
}
 
.section-note{
    color:#7c8981;
    font-size:12px;
    line-height:1.6;
}
 
@media(max-width:1100px){
    .stats{grid-template-columns:repeat(2,1fr)}
    .finance{grid-template-columns:1fr 1fr}
    .finance-card:first-child{grid-column:1/-1}
    .content-grid{grid-template-columns:1fr}
}
 
@media(max-width:750px){
    .sidebar{
        width:70px;
        padding:20px 10px;
    }
 
    .brand h1,.brand p,.menu-title,.menu a span,.logout span{
        display:none;
    }
 
    .brand{text-align:center;padding-bottom:20px}
    .menu a{justify-content:center;padding:13px 5px}
 
    .logout{margin-top:25px}
    .main{
        margin-left:70px;
        width:calc(100% - 70px);
        padding:20px 15px 35px;
    }
 
    .topbar{align-items:flex-start}
    .welcome h2{font-size:21px}
    .admin{padding:5px}
    .admin>div:last-child{display:none}
}
 
@media(max-width:520px){
    .stats,.finance{grid-template-columns:1fr}
    .finance-card:first-child{grid-column:auto}
    .quick-actions{grid-template-columns:1fr}
    .panel{padding:17px}
    .topbar{flex-direction:column}
    .admin{align-self:flex-end}
}
</style>
</head>
 
<body>
<div class="dashboard">
 
    <!-- =====================================================
         SIDEBAR ADMINISTRATEUR
         ===================================================== -->
    <aside class="sidebar">
 
        <div class="brand">
            <h1>Jardin des <span>Agriculteurs</span> 🌿</h1>
            <p>Administration • Premium</p>
        </div>
 
        <nav class="menu">
 
            <div class="menu-title">Navigation</div>
 
            <a href="dashboard.php" class="active">
                <span class="icon">📊</span>
                <span>Tableau de bord</span>
            </a>
 
            <a href="produits.php">
                <span class="icon">📦</span>
                <span>Gestion des produits</span>
            </a>
 
            <a href="commandes.php">
                <span class="icon">🛒</span>
                <span>Gestion des commandes</span>
            </a>
 
            <a href="clients.php">
                <span class="icon">👥</span>
                <span>Gestion des clients</span>
            </a>
 
            <a href="fournisseurs.php">
                <span class="icon">🚜</span>
                <span>Gestion des fournisseurs</span>
            </a>
 
            <div class="menu-title" style="margin-top:18px;">Boutique</div>
 
            <a href="index.php">
                <span class="icon">🏠</span>
                <span>Voir la boutique</span>
            </a>
 
            <a href="espace_client.php">
                <span class="icon">👤</span>
                <span>Espace client</span>
            </a>
 
        </nav>
 
        <div class="logout">
            <a href="deconnexion.php">
                <span class="icon">↪️</span>
                <span>Déconnexion</span>
            </a>
        </div>
 
    </aside>
 
    <!-- =====================================================
         CONTENU PRINCIPAL
         ===================================================== -->
    <main class="main">
 
        <div class="topbar">
            <div class="welcome">
                <h2>Bonjour, <?= h($nomAdmin) ?> 👋</h2>
                <p>Voici l'aperçu de votre marketplace aujourd'hui.</p>
            </div>
 
            <div class="admin">
                <div class="avatar"><?= h($initiales) ?></div>
                <div>
                    <strong><?= h($nomAdmin) ?></strong>
                    <small>Administrateur</small>
                </div>
            </div>
        </div>
 
        <?php if ($erreurBDD !== ""): ?>
            <div class="error-box">
                ⚠️ <?= h($erreurBDD) ?>
            </div>
        <?php endif; ?>
 
        <!-- =================================================
             STATISTIQUES PRINCIPALES
             ================================================= -->
        <section class="stats">
 
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <p>Produits</p>
                <h3><?= $nbProduits ?></h3>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <p>Commandes</p>
                <h3><?= $nbCommandes ?></h3>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <p>Clients</p>
                <h3><?= $nbClients ?></h3>
            </div>
 
            <div class="stat-card">
                <div class="stat-icon">🚜</div>
                <p>Fournisseurs</p>
                <h3><?= $nbFournisseurs ?></h3>
            </div>
 
        </section>
 
        <!-- =================================================
             INDICATEURS COMMERCIAUX
             ================================================= -->
        <section class="finance">
 
            <div class="finance-card">
                <p>Chiffre d'affaires</p>
                <h2><?= fcfa($chiffreAffaires) ?></h2>
                <div class="small">Hors commandes annulées</div>
            </div>
 
            <div class="finance-card">
                <p>Commandes en attente</p>
                <h2><?= $commandesAttente ?></h2>
                <div class="small">À traiter rapidement</div>
            </div>
 
            <div class="finance-card">
                <p>Commandes confirmées</p>
                <h2><?= $commandesConfirmees ?></h2>
                <div class="small"><?= $commandesAnnulees ?> annulée(s)</div>
            </div>
 
        </section>
 
        <!-- =================================================
             COLONNE PRINCIPALE + COLONNE LATERALE
             ================================================= -->
        <div class="content-grid">
 
            <!-- COLONNE PRINCIPALE -->
            <div>
 
                <!-- COMMANDES RECENTES -->
                <section class="panel">
 
                    <div class="panel-header">
                        <h3>Commandes récentes 🛒</h3>
                        <a href="commandes.php" class="view-all">Gérer toutes →</a>
                    </div>
 
                    <div class="table-wrapper">
 
                        <table>
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
 
                            <tbody>
 
                            <?php if (!empty($commandesRecentes)): ?>
 
                                <?php foreach ($commandesRecentes as $commande): ?>
 
                                    <?php
                                    $statut = $commande["statut"] ?? "En attente";
                                    $statutNormalise = strtolower($statut);
 
                                    $classeStatut = "waiting";
 
                                    if (
                                        strpos($statutNormalise, "confirm") !== false
                                    ) {
                                        $classeStatut = "confirmed";
                                    }
 
                                    if (
                                        strpos($statutNormalise, "annul") !== false
                                    ) {
                                        $classeStatut = "cancelled";
                                    }
 
                                    $nomClientCommande = trim(
                                        ($commande["prenom"] ?? "") . " " .
                                        ($commande["nom"] ?? "")
                                    );
                                    ?>
 
                                    <tr>
                                        <td>
                                            <strong>
                                                #<?= h($commande["id_commande"]) ?>
                                            </strong>
                                        </td>
 
                                        <td>
                                            <div class="client">
                                                <?= h($nomClientCommande ?: "Client") ?>
                                            </div>
                                        </td>
 
                                        <td>
                                            <?= !empty($commande["date_commande"])
                                                ? h(date("d/m/Y", strtotime($commande["date_commande"])))
                                                : "-"
                                            ?>
                                        </td>
 
                                        <td>
                                            <strong>
                                                <?= fcfa($commande["total"] ?? 0) ?>
                                            </strong>
                                        </td>
 
                                        <td>
                                            <span class="badge <?= h($classeStatut) ?>">
                                                <?= h($statut) ?>
                                            </span>
                                        </td>
                                    </tr>
 
                                <?php endforeach; ?>
 
                            <?php else: ?>
 
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:30px;">
                                        Aucune commande récente.
                                    </td>
                                </tr>
 
                            <?php endif; ?>
 
                            </tbody>
                        </table>
 
                    </div>
                </section>
 
                <!-- CLIENTS RECENTS -->
                <section class="panel">
 
                    <div class="panel-header">
                        <h3>Clients récents 👥</h3>
                        <a href="clients.php" class="view-all">Gérer les clients →</a>
                    </div>
 
                    <?php if (!empty($clientsRecents)): ?>
 
                        <?php foreach ($clientsRecents as $client): ?>
 
                            <?php
                            $nomComplet = trim(
                                ($client["prenom"] ?? "") . " " .
                                ($client["nom"] ?? "")
                            );
 
                            $iniClient = "";
                            foreach (preg_split('/\s+/', $nomComplet) as $mot) {
                                if ($mot !== "") {
                                    $iniClient .= strtoupper(substr($mot, 0, 1));
                                }
                            }
                            $iniClient = substr($iniClient, 0, 2);
                            ?>
 
                            <div class="client-card">
 
                                <div class="client-avatar">
                                    <?= h($iniClient ?: "CL") ?>
                                </div>
 
                                <div class="client-info">
                                    <strong><?= h($nomComplet ?: "Client") ?></strong>
                                    <small><?= h($client["email"] ?? "") ?></small>
                                </div>
 
                                <a href="clients.php" class="manage-link">
                                    Gérer
                                </a>
 
                            </div>
 
                        <?php endforeach; ?>
 
                    <?php else: ?>
 
                        <p class="section-note">
                            Aucun client récent à afficher.
                        </p>
 
                    <?php endif; ?>
 
                </section>
 
            </div>
 
            <!-- COLONNE LATERALE -->
            <div>
 
                <!-- STOCK FAIBLE -->
                <section class="panel">
 
                    <div class="panel-header">
                        <h3>Stocks faibles ⚠️</h3>
                        <a href="produits.php" class="view-all">Gérer →</a>
                    </div>
 
                    <?php if (!empty($stocksFaibles)): ?>
 
                        <?php foreach ($stocksFaibles as $produit): ?>
 
                            <?php
                            $stock = (int)($produit["stock"] ?? 0);
                            $largeur = max(5, min(100, $stock * 20));
                            ?>
 
                            <div class="stock-item">
 
                                <div class="stock-top">
                                    <span><?= h($produit["nom_produit"]) ?></span>
                                    <span class="stock-number">
                                        <?= $stock ?> restant(s)
                                    </span>
                                </div>
 
                                <div class="progress">
                                    <span style="width:<?= $largeur ?>%;"></span>
                                </div>
 
                            </div>
 
                        <?php endforeach; ?>
 
                    <?php else: ?>
 
                        <p class="section-note">
                            Aucun produit en stock critique.
                        </p>
 
                    <?php endif; ?>
 
                </section>
 
                <!-- GESTION CLIENTS -->
                <section class="panel">
 
                    <div class="panel-header">
                        <h3>Gestion des clients 👥</h3>
                        <a href="clients.php" class="view-all">Ouvrir →</a>
                    </div>
 
                    <p class="section-note">
                        Ajoutez, consultez, modifiez ou supprimez les comptes
                        clients depuis le module de gestion.
                    </p>
 
                    <div style="margin-top:15px;">
                        <a
                            href="clients.php"
                            style="
                                display:block;
                                background:#103d22;
                                color:#fff;
                                text-align:center;
                                padding:12px;
                                border-radius:10px;
                                font-weight:bold;
                                font-size:12px;
                            "
                        >
                            👥 Gérer les clients
                        </a>
                    </div>
 
                </section>
 
                <!-- ACTIONS RAPIDES -->
                <section class="panel">
 
                    <div class="panel-header">
                        <h3>Actions rapides ⚡</h3>
                    </div>
 
                    <div class="quick-actions">
 
                        <a href="ajouter_produit.php" class="quick">
                            <div>➕</div>
                            <span>Ajouter produit</span>
                        </a>
 
                        <a href="ajouter_fournisseur.php" class="quick">
                            <div>🚜</div>
                            <span>Ajouter fournisseur</span>
                        </a>
 
                        <a href="commandes.php" class="quick">
                            <div>🛒</div>
                            <span>Gérer commandes</span>
                        </a>
 
                        <a href="clients.php" class="quick">
                            <div>👥</div>
                            <span>Gérer clients</span>
                        </a>
 
                        <a href="produits.php" class="quick">
                            <div>📦</div>
                            <span>Gérer produits</span>
                        </a>
 
                        <a href="fournisseurs.php" class="quick">
                            <div>🏭</div>
                            <span>Gérer fournisseurs</span>
                        </a>
 
                    </div>
 
                </section>
 
            </div>
 
        </div>
 
    </main>
 
</div>
</body>
</html>