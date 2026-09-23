<?php

require_once 'connexion_bd.php';
if(session_status()==PHP_SESSION_NONE){
    session_start();
}

/* =========================================================
   JARDIN DES AGRICULTEURS
   clients.php — Gestion des clients
   Version PDO compatible avec connexion.php
   ========================================================= */

/* Vérification de la connexion PDO */
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Connexion à la base de données impossible.");
}

/* Protection de l'espace administrateur */
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== 'administrateur') {
    header("Location: index.php");
    exit();
}

/* Protection CSRF */
if (empty($_SESSION['csrf_client'])) {
    $_SESSION['csrf_client'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_client'];

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$typeMessage = 'success';

/* =========================================================
   ACTIONS : AJOUT / MODIFICATION / STATUT / SUPPRESSION
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $message = "Session de sécurité expirée. Rechargez la page.";
        $typeMessage = 'danger';
    } else {

        $action = $_POST['action'] ?? '';

        /* ================= AJOUT ================= */
        if ($action === 'ajouter') {

            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $motPasse = $_POST['mot_de_passe'] ?? '';
            $statut = ($_POST['statut'] ?? 'actif') === 'inactif'
                ? 'inactif'
                : 'actif';

            if ($nom === '' || $prenom === '' || $email === '' || $motPasse === '') {
                $message = "Veuillez remplir tous les champs obligatoires.";
                $typeMessage = 'danger';

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Adresse e-mail invalide.";
                $typeMessage = 'danger';

            } elseif (strlen($motPasse) < 6) {
                $message = "Le mot de passe doit contenir au moins 6 caractères.";
                $typeMessage = 'danger';

            } else {

                $check = $pdo->prepare(
                    "SELECT id FROM utilisateurs WHERE email = ? LIMIT 1"
                );
                $check->execute([$email]);

                if ($check->fetch()) {
                    $message = "Cet e-mail est déjà utilisé.";
                    $typeMessage = 'danger';

                } else {

                    $hash = password_hash($motPasse, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare(
                        "INSERT INTO utilisateurs
                        (nom, prenom, email, telephone, mot_de_passe,
                         adresse, ville, role, statut)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'client', ?)"
                    );

                    if ($stmt->execute([
                        $nom,
                        $prenom,
                        $email,
                        $telephone,
                        $hash,
                        $adresse,
                        $ville,
                        $statut
                    ])) {
                        $message = "Client ajouté avec succès.";
                    } else {
                        $message = "Impossible d'ajouter le client.";
                        $typeMessage = 'danger';
                    }
                }
            }
        }

        /* ================= MODIFICATION ================= */
        elseif ($action === 'modifier') {

            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $motPasse = $_POST['mot_de_passe'] ?? '';
            $statut = ($_POST['statut'] ?? 'actif') === 'inactif'
                ? 'inactif'
                : 'actif';

            if ($id <= 0 || $nom === '' || $prenom === '' || $email === '') {
                $message = "Les informations obligatoires sont incomplètes.";
                $typeMessage = 'danger';

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Adresse e-mail invalide.";
                $typeMessage = 'danger';

            } else {

                $check = $pdo->prepare(
                    "SELECT id FROM utilisateurs
                     WHERE email = ? AND id <> ? LIMIT 1"
                );
                $check->execute([$email, $id]);

                if ($check->fetch()) {
                    $message = "Cet e-mail appartient déjà à un autre compte.";
                    $typeMessage = 'danger';

                } elseif ($motPasse !== '' && strlen($motPasse) < 6) {
                    $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                    $typeMessage = 'danger';

                } else {

                    if ($motPasse !== '') {

                        $hash = password_hash($motPasse, PASSWORD_DEFAULT);

                        $stmt = $pdo->prepare(
                            "UPDATE utilisateurs SET
                             nom = ?, prenom = ?, email = ?, telephone = ?,
                             mot_de_passe = ?, adresse = ?, ville = ?,
                             statut = ?, date_modification = CURRENT_TIMESTAMP
                             WHERE id = ? AND role = 'client'"
                        );

                        $ok = $stmt->execute([
                            $nom,
                            $prenom,
                            $email,
                            $telephone,
                            $hash,
                            $adresse,
                            $ville,
                            $statut,
                            $id
                        ]);

                    } else {

                        $stmt = $pdo->prepare(
                            "UPDATE utilisateurs SET
                             nom = ?, prenom = ?, email = ?, telephone = ?,
                             adresse = ?, ville = ?, statut = ?,
                             date_modification = CURRENT_TIMESTAMP
                             WHERE id = ? AND role = 'client'"
                        );

                        $ok = $stmt->execute([
                            $nom,
                            $prenom,
                            $email,
                            $telephone,
                            $adresse,
                            $ville,
                            $statut,
                            $id
                        ]);
                    }

                    if ($ok) {
                        $message = "Client modifié avec succès.";
                    } else {
                        $message = "Impossible de modifier le client.";
                        $typeMessage = 'danger';
                    }
                }
            }
        }

        /* ================= CHANGEMENT DE STATUT ================= */
        elseif ($action === 'statut') {

            $id = (int)($_POST['id'] ?? 0);
            $nouveauStatut = ($_POST['nouveau_statut'] ?? '') === 'actif'
                ? 'actif'
                : 'inactif';

            if ($id <= 0) {

                $message = "Client invalide.";
                $typeMessage = 'danger';

            } else {

                $stmt = $pdo->prepare(
                    "UPDATE utilisateurs
                     SET statut = ?, date_modification = CURRENT_TIMESTAMP
                     WHERE id = ? AND role = 'client'"
                );

                if ($stmt->execute([$nouveauStatut, $id])) {
                    $message = $nouveauStatut === 'actif'
                        ? "Compte client activé."
                        : "Compte client désactivé.";
                } else {
                    $message = "Impossible de modifier le statut.";
                    $typeMessage = 'danger';
                }
            }
        }

        /* ================= SUPPRESSION ================= */
        elseif ($action === 'supprimer') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {

                $message = "Client invalide.";
                $typeMessage = 'danger';

            } else {

                try {

                    $stmt = $pdo->prepare(
                        "DELETE FROM utilisateurs
                         WHERE id = ? AND role = 'client'"
                    );

                    $stmt->execute([$id]);

                    if ($stmt->rowCount() > 0) {
                        $message = "Client supprimé avec succès.";
                    } else {
                        $message = "Client introuvable ou suppression impossible.";
                        $typeMessage = 'danger';
                    }

                } catch (PDOException $e) {

                    $message = "Suppression refusée. Vérifiez les commandes liées à ce client.";
                    $typeMessage = 'danger';
                }
            }
        }
    }
}

/* =========================================================
   STATISTIQUES
   ========================================================= */

$stats = [
    'total' => 0,
    'actifs' => 0,
    'inactifs' => 0
];

$stmtStats = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(statut = 'actif'), 0) AS actifs,
        COALESCE(SUM(statut = 'inactif'), 0) AS inactifs
     FROM utilisateurs
     WHERE role = 'client'"
);

if ($stmtStats) {
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: $stats;
}

/* =========================================================
   RECHERCHE ET FILTRE
   ========================================================= */

$recherche = trim($_GET['recherche'] ?? '');
$filtreStatut = $_GET['statut'] ?? '';

$sql = "SELECT
            id, nom, prenom, email, telephone,
            adresse, ville, statut,
            date_creation, date_modification
        FROM utilisateurs
        WHERE role = 'client'";

$params = [];

if ($recherche !== '') {

    $sql .= " AND (
        nom LIKE ?
        OR prenom LIKE ?
        OR email LIKE ?
        OR telephone LIKE ?
        OR ville LIKE ?
    )";

    $like = "%{$recherche}%";

    $params = [
        $like,
        $like,
        $like,
        $like,
        $like
    ];
}

if ($filtreStatut === 'actif' || $filtreStatut === 'inactif') {
    $sql .= " AND statut = ?";
    $params[] = $filtreStatut;
}

$sql .= " ORDER BY id DESC";

$stmtListe = $pdo->prepare($sql);
$stmtListe->execute($params);

$resultClients = $stmtListe->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Gestion des clients | Jardin des Agriculteurs</title>

<style>

:root{
    --vert:#15803d;
    --vert2:#22c55e;
    --bleu:#0f4c81;
    --fond:#f4f8f5;
    --texte:#17231b;
    --muted:#718096;
    --danger:#dc2626;
    --ombre:0 12px 35px rgba(15,76,129,.10);
}

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:Inter,Segoe UI,Arial,sans-serif;
    background:linear-gradient(135deg,#f4f8f5,#eef6ff);
    color:var(--texte);
}

button,input,select,textarea{
    font:inherit;
}

.page{
    width:min(1450px,94%);
    margin:30px auto 50px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:25px;
}

.brand{
    display:flex;
    align-items:center;
    gap:15px;
}

.logo{
    width:58px;
    height:58px;
    display:grid;
    place-items:center;
    border-radius:18px;
    background:linear-gradient(135deg,var(--vert),var(--bleu));
    color:#fff;
    font-size:23px;
    font-weight:900;
    box-shadow:var(--ombre);
}

.brand h1{
    font-size:27px;
}

.brand p{
    color:var(--muted);
    margin-top:4px;
}

.back{
    background:#fff;
    border:1px solid #e5e7eb;
    padding:12px 17px;
    border-radius:13px;
    font-weight:700;
    text-decoration:none;
    color:inherit;
}

.hero{
    background:linear-gradient(135deg,var(--bleu),#167b55);
    color:#fff;
    border-radius:24px;
    padding:30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:25px;
    box-shadow:0 18px 45px rgba(15,76,129,.20);
    margin-bottom:24px;
}

.hero h2{
    font-size:29px;
    margin-bottom:8px;
}

.hero p{
    opacity:.9;
    line-height:1.6;
}

.btn{
    border:0;
    border-radius:12px;
    padding:12px 17px;
    cursor:pointer;
    font-weight:800;
    transition:.2s;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}

.btn:hover{
    transform:translateY(-2px);
}

.btn-primary{
    background:#fff;
    color:var(--bleu);
}

.btn-green{
    background:var(--vert);
    color:#fff;
}

.btn-danger{
    background:#fee2e2;
    color:#b91c1c;
}

.btn-edit{
    background:#e8f1ff;
    color:#0f4c81;
}

.btn-warning{
    background:#fff7ed;
    color:#c2410c;
}

.stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:24px;
}

.stat{
    background:#fff;
    border-radius:18px;
    padding:20px;
    box-shadow:var(--ombre);
    display:flex;
    align-items:center;
    gap:15px;
}

.stat-icon{
    width:48px;
    height:48px;
    border-radius:14px;
    display:grid;
    place-items:center;
    background:#eaf6ee;
    font-size:22px;
}

.stat strong{
    font-size:26px;
    display:block;
}

.stat span{
    color:var(--muted);
    font-size:14px;
}

.panel{
    background:#fff;
    border-radius:20px;
    box-shadow:var(--ombre);
    overflow:hidden;
}

.toolbar{
    padding:20px;
    border-bottom:1px solid #edf0f2;
    display:flex;
    gap:12px;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
}

.filters{
    display:flex;
    gap:10px;
    flex:1;
    flex-wrap:wrap;
}

.input,.select{
    border:1px solid #dce4df;
    border-radius:11px;
    padding:12px 13px;
    outline:none;
    background:#fff;
    min-width:180px;
}

.input:focus,.select:focus{
    border-color:var(--vert);
    box-shadow:0 0 0 3px rgba(34,197,94,.10);
}

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1100px;
}

th,td{
    padding:15px 17px;
    text-align:left;
    border-bottom:1px solid #eef1ef;
}

th{
    background:#f8faf9;
    color:#50615a;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

tr:hover td{
    background:#fbfefc;
}

.client-name{
    font-weight:800;
}

.client-email{
    color:var(--muted);
    font-size:13px;
    margin-top:3px;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
}

.badge.actif{
    background:#dcfce7;
    color:#166534;
}

.badge.inactif{
    background:#fee2e2;
    color:#991b1b;
}

.actions{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}

.actions form{
    margin:0;
}

.alert{
    margin-bottom:20px;
    padding:14px 17px;
    border-radius:13px;
    font-weight:700;
}

.alert.success{
    background:#dcfce7;
    color:#166534;
}

.alert.danger{
    background:#fee2e2;
    color:#991b1b;
}

.empty{
    padding:45px;
    text-align:center;
    color:var(--muted);
}

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(8,20,14,.58);
    backdrop-filter:blur(5px);
    z-index:1000;
    padding:20px;
    overflow:auto;
}

.modal.show{
    display:flex;
    align-items:center;
    justify-content:center;
}

.modal-card{
    background:#fff;
    width:min(720px,100%);
    border-radius:24px;
    padding:27px;
    box-shadow:0 25px 80px rgba(0,0,0,.25);
    animation:pop .2s ease;
}

@keyframes pop{
    from{
        opacity:0;
        transform:translateY(15px) scale(.98);
    }

    to{
        opacity:1;
        transform:none;
    }
}

.modal-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.close{
    border:0;
    background:#f1f5f3;
    width:40px;
    height:40px;
    border-radius:50%;
    cursor:pointer;
    font-size:20px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

.field{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.field.full{
    grid-column:1/-1;
}

.field label{
    font-weight:750;
    font-size:14px;
}

.field input,
.field select,
.field textarea{
    width:100%;
    border:1px solid #dbe4df;
    border-radius:11px;
    padding:12px;
    outline:none;
}

.field textarea{
    min-height:90px;
    resize:vertical;
}

.form-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:20px;
}

@media(max-width:850px){

    .stats{
        grid-template-columns:1fr;
    }

    .hero{
        align-items:flex-start;
        flex-direction:column;
    }
}

@media(max-width:600px){

    .page{
        width:94%;
        margin-top:15px;
    }

    .brand h1{
        font-size:21px;
    }

    .hero h2{
        font-size:23px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .field.full{
        grid-column:auto;
    }

    .topbar{
        align-items:flex-start;
        flex-direction:column;
    }
}

</style>

</head>

<body>

<div class="page">

<div class="topbar">

    <div class="brand">

        <div class="logo">JA</div>

        <div>
            <h1>Jardin des Agriculteurs</h1>
            <p>Administration · Gestion des clients</p>
        </div>

    </div>

    <a class="back" href="dashboard.php">
        ← Retour au dashboard
    </a>

</div>

<?php if ($message !== ''): ?>

<div class="alert <?=e($typeMessage)?>">
    <?=e($message)?>
</div>

<?php endif; ?>

<section class="hero">

    <div>

        <h2>Gestion des clients</h2>

        <p>
            Consultez, ajoutez, modifiez, activez,
            désactivez ou supprimez les comptes clients
            de votre marketplace.
        </p>

    </div>

    <button
        class="btn btn-primary"
        type="button"
        onclick="ouvrirAjout()"
    >
        ＋ Ajouter un client
    </button>

</section>

<section class="stats">

    <div class="stat">

        <div class="stat-icon">👥</div>

        <div>
            <strong><?= (int)$stats['total']?></strong>
            <span>Clients enregistrés</span>
        </div>

    </div>

    <div class="stat">

        <div class="stat-icon">✓</div>

        <div>
            <strong><?= (int)$stats['actifs']?></strong>
            <span>Comptes actifs</span>
        </div>

    </div>

    <div class="stat">

        <div class="stat-icon">⏸</div>

        <div>
            <strong><?= (int)$stats['inactifs']?></strong>
            <span>Comptes inactifs</span>
        </div>

    </div>

</section>

<section class="panel">

<form class="toolbar" method="GET">

    <div class="filters">

        <input
            class="input"
            type="search"
            name="recherche"
            placeholder="Rechercher par nom, e-mail, téléphone..."
            value="<?=e($recherche)?>"
        >

        <select class="select" name="statut">

            <option value="">
                Tous les statuts
            </option>

            <option
                value="actif"
                <?=$filtreStatut === 'actif' ? 'selected' : ''?>
            >
                Actifs
            </option>

            <option
                value="inactif"
                <?=$filtreStatut === 'inactif' ? 'selected' : ''?>
            >
                Inactifs
            </option>

        </select>

        <button class="btn btn-green" type="submit">
            Rechercher
        </button>

        <a
            class="btn"
            href="clients.php"
            style="background:#f1f5f3"
        >
            Réinitialiser
        </a>

    </div>

</form>

<div class="table-wrap">

<?php if (count($resultClients) > 0): ?>

<table>

<thead>

<tr>
    <th>Client</th>
    <th>Contact</th>
    <th>Localisation</th>
    <th>Statut</th>
    <th>Création</th>
    <th>Modification</th>
    <th>Actions</th>
</tr>

</thead>

<tbody>

<?php foreach ($resultClients as $client): ?>

<tr>

<td>

    <div class="client-name">
        <?=e($client['nom'].' '.$client['prenom'])?>
    </div>

    <div class="client-email">
        <?=e($client['email'])?>
    </div>

</td>

<td>
    <?=e($client['telephone'] ?: 'Non renseigné')?>
</td>

<td>

    <?=e($client['ville'] ?: '—')?>

    <?php if (!empty($client['adresse'])): ?>

        <div class="client-email">
            <?=e($client['adresse'])?>
        </div>

    <?php endif; ?>

</td>

<td>

<span class="badge <?=$client['statut'] === 'actif' ? 'actif' : 'inactif'?>">

    <?=$client['statut'] === 'actif'
        ? '● Actif'
        : '● Inactif'?>

</span>

</td>

<td>

<?=$client['date_creation']
    ? e(date('d/m/Y H:i', strtotime($client['date_creation'])))
    : '—'?>

</td>

<td>

<?=$client['date_modification']
    ? e(date('d/m/Y H:i', strtotime($client['date_modification'])))
    : '—'?>

</td>

<td>

<div class="actions">

<button
    type="button"
    class="btn btn-edit"
    onclick='ouvrirModification(
        <?=json_encode([
            "id"=>(int)$client["id"],
            "nom"=>$client["nom"],
            "prenom"=>$client["prenom"],
            "email"=>$client["email"],
            "telephone"=>$client["telephone"],
            "adresse"=>$client["adresse"],
            "ville"=>$client["ville"],
            "statut"=>$client["statut"]
        ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>
    )'
>
    ✎ Modifier
</button>

<form method="POST"
      onsubmit="return confirmerSuppression()">

    <input
        type="hidden"
        name="csrf"
        value="<?=e($csrf)?>"
    >

    <input
        type="hidden"
        name="action"
        value="supprimer"
    >

    <input
        type="hidden"
        name="id"
        value="<?=(int)$client['id']?>"
    >

    <button
        class="btn btn-danger"
        type="submit"
    >
        🗑 Supprimer
    </button>

</form>

<form method="POST">

    <input
        type="hidden"
        name="csrf"
        value="<?=e($csrf)?>"
    >

    <input
        type="hidden"
        name="action"
        value="statut"
    >

    <input
        type="hidden"
        name="id"
        value="<?=(int)$client['id']?>"
    >

    <input
        type="hidden"
        name="nouveau_statut"
        value="<?=$client['statut'] === 'actif'
            ? 'inactif'
            : 'actif'?>"
    >

    <button
        class="btn btn-warning"
        type="submit"
        onclick="return confirm(
            'Confirmer le changement de statut de ce client ?'
        )"
    >

        <?=$client['statut'] === 'actif'
            ? '⏸ Désactiver'
            : '✓ Activer'?>

    </button>

</form>

</div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<div class="empty">

    <div style="font-size:45px">👤</div>

    <h3>Aucun client trouvé</h3>

    <p>
        Modifiez votre recherche ou ajoutez
        un nouveau client.
    </p>

</div>

<?php endif; ?>

</div>

</section>

</div>

<!-- ================= MODALE AJOUT / MODIFICATION ================= -->

<div class="modal" id="clientModal">

<div class="modal-card">

<div class="modal-head">

    <div>

        <h2 id="modalTitre">
            Ajouter un client
        </h2>

        <p style="color:#718096;margin-top:5px">
            Les champs marqués * sont obligatoires.
        </p>

    </div>

    <button
        class="close"
        type="button"
        onclick="fermerModal()"
    >
        ×
    </button>

</div>

<form method="POST" id="clientForm">

<input
    type="hidden"
    name="csrf"
    value="<?=e($csrf)?>"
>

<input
    type="hidden"
    name="action"
    id="action"
    value="ajouter"
>

<input
    type="hidden"
    name="id"
    id="id"
>

<div class="form-grid">

<div class="field">

    <label>Nom *</label>

    <input
        type="text"
        name="nom"
        id="nom"
        required
    >

</div>

<div class="field">

    <label>Prénom *</label>

    <input
        type="text"
        name="prenom"
        id="prenom"
        required
    >

</div>

<div class="field">

    <label>E-mail *</label>

    <input
        type="email"
        name="email"
        id="email"
        required
    >

</div>

<div class="field">

    <label>Téléphone</label>

    <input
        type="text"
        name="telephone"
        id="telephone"
    >

</div>

<div class="field">

    <label>Ville</label>

    <input
        type="text"
        name="ville"
        id="ville"
    >

</div>

<div class="field">

    <label>Statut</label>

    <select name="statut" id="statut">

        <option value="actif">
            Actif
        </option>

        <option value="inactif">
            Inactif
        </option>

    </select>

</div>

<div class="field full">

    <label>Adresse</label>

    <textarea
        name="adresse"
        id="adresse"
    ></textarea>

</div>

<div class="field full">

    <label>
        Mot de passe
        <span id="passwordHelp">*</span>
    </label>

    <input
        type="password"
        name="mot_de_passe"
        id="mot_de_passe"
        minlength="6"
    >

    <small
        id="passwordNote"
        style="color:#718096"
    >
        Minimum 6 caractères.
    </small>

</div>

</div>

<div class="form-actions">

<button
    class="btn"
    style="background:#f1f5f3"
    type="button"
    onclick="fermerModal()"
>
    Annuler
</button>

<button
    class="btn btn-green"
    id="submitBtn"
    type="submit"
>
    Enregistrer le client
</button>

</div>

</form>

</div>

</div>

<script>

const modal = document.getElementById('clientModal');
const form = document.getElementById('clientForm');

function ouvrirAjout(){

    form.reset();

    document.getElementById('action').value = 'ajouter';
    document.getElementById('id').value = '';

    document.getElementById('modalTitre').textContent =
        'Ajouter un client';

    document.getElementById('submitBtn').textContent =
        'Enregistrer le client';

    document.getElementById('mot_de_passe').required = true;

    document.getElementById('passwordHelp').textContent = '*';

    document.getElementById('passwordNote').textContent =
        'Minimum 6 caractères.';

    modal.classList.add('show');

    document.getElementById('nom').focus();
}

function ouvrirModification(client){

    document.getElementById('action').value = 'modifier';
    document.getElementById('id').value = client.id;

    document.getElementById('nom').value = client.nom || '';
    document.getElementById('prenom').value = client.prenom || '';
    document.getElementById('email').value = client.email || '';
    document.getElementById('telephone').value = client.telephone || '';
    document.getElementById('adresse').value = client.adresse || '';
    document.getElementById('ville').value = client.ville || '';
    document.getElementById('statut').value =
        client.statut || 'actif';

    document.getElementById('mot_de_passe').value = '';

    document.getElementById('modalTitre').textContent =
        'Modifier le client';

    document.getElementById('submitBtn').textContent =
        'Enregistrer les modifications';

    document.getElementById('mot_de_passe').required = false;

    document.getElementById('passwordHelp').textContent = '';

    document.getElementById('passwordNote').textContent =
        'Laissez vide pour conserver le mot de passe actuel.';

    modal.classList.add('show');

    document.getElementById('nom').focus();
}

function fermerModal(){

    modal.classList.remove('show');
}

modal.addEventListener('click', function(e){

    if(e.target === modal){
        fermerModal();
    }

});

document.addEventListener('keydown', function(e){

    if(e.key === 'Escape'){
        fermerModal();
    }

});

function confirmerSuppression(){

    return confirm(
        "Voulez-vous vraiment supprimer ce client ?\n\n" +
        "Cette action est définitive. Si des commandes " +
        "sont liées, la base de données peut refuser " +
        "la suppression."
    );
}

</script>

</body>
</html>
