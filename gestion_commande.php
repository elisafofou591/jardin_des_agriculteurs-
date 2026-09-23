<?php
session_start();
$host = "127.0.0.1";
$dbname = "jardin_des_agriculteurs";
$dbuser = "root";
$dbpass = "";
try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connexion à la base de données impossible. Vérifiez XAMPP et la base jardin_agriculteurs.");
}

/* =========================================================
   JARDIN DES AGRICULTEURS
   gestion_commande.php
   Espace réservé à l'administrateur
   ========================================================= */

/* Protection de la page */
if (!isset($_SESSION['id_utilisateur']) || ($_SESSION['role'] ?? '') !== 'administrateur') {
    header('Location: gestion commande.php');
    exit;
}

/* ---------------------------------------------------------
   Mise à jour du statut d'une commande
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_statut'])) {
    $id_commande = filter_input(INPUT_POST, 'id_commande', FILTER_VALIDATE_INT);
    $statut = trim($_POST['statut'] ?? '');

    $statuts_autorises = [
        'En attente',
        'Validée',
        'En préparation',
        'Livrée',
        'Annulée'
    ];

    if ($id_commande && in_array($statut, $statuts_autorises, true)) {
        $stmt = $pdo->prepare(
            "UPDATE commande
             SET statut = :statut
             WHERE id_commande = :id_commande"
        );
        $stmt->execute([
            ':statut' => $statut,
            ':id_commande' => $id_commande
        ]);
    }

    header('Location: gestion_commande.php?maj=1');
    exit;
}

/* ---------------------------------------------------------
   Suppression d'une commande
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_commande'])) {
    $id_commande = filter_input(INPUT_POST, 'id_commande', FILTER_VALIDATE_INT);

    if ($id_commande) {
        try {
            $stmt = $pdo->prepare(
                "DELETE FROM commande WHERE id_commande = :id_commande"
            );
            $stmt->execute([':id_commande' => $id_commande]);
        } catch (PDOException $e) {
            /* Si des détails de commande empêchent la suppression,
               on conserve les données et on affiche un message. */
        }
    }

    header('Location: gestion_commande.php?supprime=1');
    exit;
}

/* ---------------------------------------------------------
   Recherche et filtre
   --------------------------------------------------------- */
$recherche = trim($_GET['recherche'] ?? '');
$filtre_statut = trim($_GET['statut'] ?? '');

$statuts_autorises = [
    'En attente',
    'Validée',
    'En préparation',
    'Livrée',
    'Annulée'
];

$where = [];
$params = [];

if ($recherche !== '') {
    $where[] = "(
        CAST(c.id_commande AS CHAR) LIKE :recherche
        OR u.nom LIKE :recherche
        OR u.prenom LIKE :recherche
        OR u.email LIKE :recherche
    )";
    $params[':recherche'] = '%' . $recherche . '%';
}

if ($filtre_statut !== '' && in_array($filtre_statut, $statuts_autorises, true)) {
    $where[] = "c.statut = :statut_filtre";
    $params[':statut_filtre'] = $filtre_statut;
}

$sql = "
    SELECT
        c.id_commande,
        c.id_client,
        c.date_commande,
        c.montant_total,
        c.statut,
        u.nom,
        u.prenom,
        u.email
    FROM commande c
    LEFT JOIN utilisateurs u
        ON u.id_utilisateur = c.id_client
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.date_commande DESC, c.id_commande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------------
   Statistiques générales
   --------------------------------------------------------- */
$stats = [
    'total' => 0,
    'attente' => 0,
    'validee' => 0,
    'preparation' => 0,
    'livree' => 0,
    'annulee' => 0,
    'chiffre' => 0
];

$statStmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN statut = 'En attente' THEN 1 ELSE 0 END), 0) AS attente,
        COALESCE(SUM(CASE WHEN statut = 'Validée' THEN 1 ELSE 0 END), 0) AS validee,
        COALESCE(SUM(CASE WHEN statut = 'En préparation' THEN 1 ELSE 0 END), 0) AS preparation,
        COALESCE(SUM(CASE WHEN statut = 'Livrée' THEN 1 ELSE 0 END), 0) AS livree,
        COALESCE(SUM(CASE WHEN statut = 'Annulée' THEN 1 ELSE 0 END), 0) AS annulee,
        COALESCE(SUM(CASE WHEN statut <> 'Annulée' THEN montant_total ELSE 0 END), 0) AS chiffre
    FROM commande
");
$statsDb = $statStmt->fetch(PDO::FETCH_ASSOC);

if ($statsDb) {
    foreach ($stats as $key => $value) {
        if (isset($statsDb[$key])) {
            $stats[$key] = $statsDb[$key];
        }
    }
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function classeStatut(string $statut): string {
    return match ($statut) {
        'Validée' => 'validee',
        'En préparation' => 'preparation',
        'Livrée' => 'livree',
        'Annulée' => 'annulee',
        default => 'attente'
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des commandes | Jardin des Agriculteurs</title>

<style>
:root{
    --vert:#14532d;
    --vert2:#166534;
    --vert3:#22c55e;
    --bleu:#0f4c81;
    --fond:#f4f8f5;
    --blanc:#ffffff;
    --texte:#17301f;
    --gris:#6b7c72;
    --bordure:#e2ebe5;
    --ombre:0 12px 35px rgba(20,83,45,.10);
}

*{box-sizing:border-box;margin:0;padding:0}

body{
    font-family:Arial,Helvetica,sans-serif;
    background:linear-gradient(135deg,#f4f8f5,#eef7f1);
    color:var(--texte);
    min-height:100vh;
}

a{text-decoration:none;color:inherit}

.sidebar{
    position:fixed;
    left:0;
    top:0;
    bottom:0;
    width:245px;
    background:linear-gradient(180deg,var(--vert),#0b3d21);
    color:white;
    padding:24px 17px;
    z-index:20;
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;
    padding:8px 8px 25px;
    border-bottom:1px solid rgba(255,255,255,.16);
    margin-bottom:22px;
}

.logo-mark{
    width:48px;height:48px;border-radius:14px;
    display:flex;align-items:center;justify-content:center;
    background:white;color:var(--vert);
    font-size:20px;font-weight:900;
}

.logo strong{display:block;font-size:15px}
.logo small{opacity:.75}

.nav a{
    display:flex;align-items:center;gap:12px;
    padding:13px 14px;
    border-radius:12px;
    margin:6px 0;
    color:rgba(255,255,255,.84);
    transition:.25s;
}

.nav a:hover,.nav a.active{
    background:rgba(255,255,255,.13);
    color:white;
    transform:translateX(3px);
}

.main{
    margin-left:245px;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:26px;
}

.topbar h1{
    font-size:29px;
    color:var(--vert);
}

.topbar p{color:var(--gris);margin-top:6px}

.admin{
    background:white;
    border:1px solid var(--bordure);
    border-radius:15px;
    padding:10px 14px;
    box-shadow:var(--ombre);
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:22px;
}

.card{
    background:white;
    border:1px solid var(--bordure);
    border-radius:18px;
    padding:20px;
    box-shadow:var(--ombre);
}

.card .label{
    color:var(--gris);
    font-size:13px;
    margin-bottom:9px;
}

.card .value{
    font-size:27px;
    font-weight:800;
    color:var(--vert);
}

.filters{
    background:white;
    border:1px solid var(--bordure);
    border-radius:18px;
    padding:18px;
    box-shadow:var(--ombre);
    margin-bottom:20px;
}

.filters form{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

input,select,button{
    font:inherit;
}

input,select{
    border:1px solid #d7e3db;
    border-radius:11px;
    padding:12px 13px;
    background:#fbfdfc;
    outline:none;
}

input:focus,select:focus{
    border-color:var(--vert3);
    box-shadow:0 0 0 3px rgba(34,197,94,.10);
}

.search{
    flex:1;
    min-width:220px;
}

.btn{
    border:0;
    border-radius:11px;
    padding:12px 17px;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

.btn:hover{transform:translateY(-1px)}

.btn-primary{
    background:var(--vert);
    color:white;
}

.btn-light{
    background:#edf5ef;
    color:var(--vert);
}

.table-box{
    background:white;
    border:1px solid var(--bordure);
    border-radius:20px;
    box-shadow:var(--ombre);
    overflow:hidden;
}

.table-head{
    padding:20px;
    border-bottom:1px solid var(--bordure);
}

.table-head h2{font-size:19px;color:var(--vert)}

.table-wrap{overflow-x:auto}

table{
    width:100%;
    border-collapse:collapse;
    min-width:920px;
}

th,td{
    padding:15px 16px;
    text-align:left;
    border-bottom:1px solid #edf2ee;
    vertical-align:middle;
}

th{
    background:#f7faf8;
    color:#557064;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.4px;
}

td{font-size:13px}

.client-name{
    font-weight:700;
    color:var(--vert);
}

.client-email{
    color:var(--gris);
    font-size:12px;
    margin-top:3px;
}

.status{
    display:inline-flex;
    padding:6px 10px;
    border-radius:30px;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
}

.status.attente{background:#fff7df;color:#986c00}
.status.validee{background:#e8f5ff;color:#075985}
.status.preparation{background:#f0eaff;color:#6d28d9}
.status.livree{background:#e8f8ee;color:#166534}
.status.annulee{background:#ffe9e9;color:#b42318}

.actions{
    display:flex;
    align-items:center;
    gap:7px;
}

.actions select{
    padding:8px;
    font-size:12px;
}

.icon-btn{
    border:0;
    border-radius:9px;
    padding:8px 10px;
    cursor:pointer;
    background:#fff0f0;
    color:#b42318;
    font-weight:700;
}

.amount{
    font-weight:800;
    white-space:nowrap;
}

.empty{
    text-align:center;
    padding:55px 20px;
    color:var(--gris);
}

.empty strong{
    display:block;
    color:var(--vert);
    margin-bottom:6px;
}

.alert{
    background:#eaf8ef;
    color:#166534;
    border:1px solid #ccebd6;
    padding:12px 15px;
    border-radius:12px;
    margin-bottom:18px;
}

@media(max-width:1050px){
    .stats{grid-template-columns:repeat(2,1fr)}
}

@media(max-width:780px){
    .sidebar{
        position:static;
        width:100%;
        min-height:auto;
    }

    .nav{
        display:flex;
        overflow-x:auto;
        gap:5px;
    }

    .nav a{
        white-space:nowrap;
    }

    .main{
        margin-left:0;
        padding:18px;
    }

    .topbar{
        align-items:flex-start;
        flex-direction:column;
    }

    .stats{grid-template-columns:1fr 1fr}
}

@media(max-width:500px){
    .stats{grid-template-columns:1fr}
    .topbar h1{font-size:23px}
    .filters form{display:block}
    .filters input,.filters select,.filters button{
        width:100%;
        margin-bottom:9px;
    }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="logo">
        <div class="logo-mark">JA</div>
        <div>
            <strong>Jardin des Agriculteurs</strong>
            <small>Administration</small>
        </div>
    </div>

    <nav class="nav">
        <a href="dashboard.php">🏠 Tableau de bord</a>
        <a href="produit.php">🌱 Produits</a>
        <a href="gestion_commande.php" class="active">📦 Commandes</a>
        <a href="clients.php">👥 Clients</a>
        <a href="fournisseurs.php">🚚 Fournisseurs</a>
        <a href="deconnexion.php">↪ Déconnexion</a>
    </nav>
</aside>

<main class="main">

    <header class="topbar">
        <div>
            <h1>Gestion des commandes</h1>
            <p>Consultez, suivez et traitez les commandes de vos clients.</p>
        </div>

        <div class="admin">
            👤 <strong><?= e(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? 'Administrateur')) ?></strong>
        </div>
    </header>

    <?php if (isset($_GET['maj'])): ?>
        <div class="alert">✓ Le statut de la commande a été mis à jour.</div>
    <?php endif; ?>

    <?php if (isset($_GET['supprime'])): ?>
        <div class="alert">✓ La commande a été supprimée si aucune contrainte ne l'empêche.</div>
    <?php endif; ?>

    <section class="stats">
        <div class="card">
            <div class="label">Total commandes</div>
            <div class="value"><?= e($stats['total']) ?></div>
        </div>

        <div class="card">
            <div class="label">En attente</div>
            <div class="value"><?= e($stats['attente']) ?></div>
        </div>

        <div class="card">
            <div class="label">En préparation</div>
            <div class="value"><?= e($stats['preparation']) ?></div>
        </div>

        <div class="card">
            <div class="label">Livrées</div>
            <div class="value"><?= e($stats['livree']) ?></div>
        </div>

        <div class="card">
            <div class="label">Validées</div>
            <div class="value"><?= e($stats['validee']) ?></div>
        </div>

        <div class="card">
            <div class="label">Annulées</div>
            <div class="value"><?= e($stats['annulee']) ?></div>
        </div>

        <div class="card">
            <div class="label">Montant des commandes non annulées</div>
            <div class="value"><?= number_format((float)$stats['chiffre'], 0, ',', ' ') ?> FCFA</div>
        </div>

        <div class="card">
            <div class="label">Résultats affichés</div>
            <div class="value"><?= count($commandes) ?></div>
        </div>
    </section>

    <section class="filters">
        <form method="GET" action="gestion_commande.php">
            <input
                class="search"
                type="search"
                name="recherche"
                value="<?= e($recherche) ?>"
                placeholder="Rechercher par N° commande, nom, prénom ou e-mail..."
            >

            <select name="statut">
                <option value="">Tous les statuts</option>
                <?php foreach ($statuts_autorises as $s): ?>
                    <option value="<?= e($s) ?>" <?= $filtre_statut === $s ? 'selected' : '' ?>>
                        <?= e($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">Rechercher</button>
            <a class="btn btn-light" href="gestion_commande.php">Réinitialiser</a>
        </form>
    </section>

    <section class="table-box">
        <div class="table-head">
            <h2>Liste des commandes</h2>
        </div>

        <?php if (!$commandes): ?>

            <div class="empty">
                <strong>Aucune commande trouvée</strong>
                Vérifiez les filtres ou ajoutez une commande depuis l'espace client.
            </div>

        <?php else: ?>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Traitement</th>
                            <th>Suppression</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><strong>#<?= e($commande['id_commande']) ?></strong></td>

                            <td>
                                <div class="client-name">
                                    <?= e(trim(($commande['prenom'] ?? '') . ' ' . ($commande['nom'] ?? ''))) ?: 'Client inconnu' ?>
                                </div>
                                <div class="client-email">
                                    <?= e($commande['email'] ?? '') ?>
                                </div>
                            </td>

                            <td>
                                <?= e(date('d/m/Y H:i', strtotime($commande['date_commande']))) ?>
                            </td>

                            <td class="amount">
                                <?= number_format((float)$commande['montant_total'], 0, ',', ' ') ?> FCFA
                            </td>

                            <td>
                                <span class="status <?= e(classeStatut($commande['statut'] ?? 'En attente')) ?>">
                                    <?= e($commande['statut'] ?? 'En attente') ?>
                                </span>
                            </td>

                            <td>
                                <form class="actions" method="POST" action="gestion_commande.php">
                                    <input type="hidden" name="id_commande" value="<?= e($commande['id_commande']) ?>">

                                    <select name="statut" aria-label="Modifier le statut">
                                        <?php foreach ($statuts_autorises as $s): ?>
                                            <option value="<?= e($s) ?>"
                                                <?= ($commande['statut'] ?? '') === $s ? 'selected' : '' ?>>
                                                <?= e($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button class="btn btn-primary" type="submit" name="modifier_statut">
                                        Mettre à jour
                                    </button>
                                </form>
                            </td>

                            <td>
                                <form method="POST"
                                      action="gestion_commande.php"
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer cette commande ?');">
                                    <input type="hidden" name="id_commande" value="<?= e($commande['id_commande']) ?>">
                                    <button class="icon-btn" type="submit" name="supprimer_commande">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

</main>

</body>
</html>