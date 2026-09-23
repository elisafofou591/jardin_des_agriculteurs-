<?php
session_start();

/* =========================================================
   JARDIN DES AGRICULTEURS
   Fichier : gerer_produit.php
   ========================================================= */



/* CONNEXION PDO */
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
    die("Connexion à la base de données impossible.");
}

/* DOSSIER DES IMAGES */
$dossierImages = __DIR__ . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;
$urlImages = "images/";

if (!is_dir($dossierImages)) {
    mkdir($dossierImages, 0755, true);
}

/* CSRF */
if (empty($_SESSION['csrf_produit'])) {
    $_SESSION['csrf_produit'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_produit'];

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$message = "";
$typeMessage = "success";

/* =========================================================
   TRAITEMENT AJOUT / MODIFICATION / SUPPRESSION
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $message = "Session de sécurité expirée. Rechargez la page.";
        $typeMessage = "error";
    } else {

        $action = $_POST['action'] ?? '';

        try {

            /* ---------------- AJOUT / MODIFICATION ---------------- */
            if ($action === 'ajouter' || $action === 'modifier') {

                $nom = trim($_POST['nom_produit'] ?? '');
                $reference = trim($_POST['reference'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $marque = trim($_POST['marque'] ?? '');
                $prix = (float)($_POST['prix'] ?? 0);
                $stock = (int)($_POST['stock'] ?? 0);
                $unite = trim($_POST['unite'] ?? 'unité');
                $seuil = (int)($_POST['seuil_alerte'] ?? 0);
                $statut = trim($_POST['statut'] ?? 'disponible');
                $idCategorie = (int)($_POST['id_categorie'] ?? 0);
                $idFournisseur = (int)($_POST['id_fournisseur'] ?? 0);

                if ($nom === '') {
                    throw new Exception("Le nom du produit est obligatoire.");
                }

                if ($prix < 0 || $stock < 0 || $seuil < 0) {
                    throw new Exception("Le prix, le stock et le seuil doivent être positifs.");
                }

                /* Image actuelle */
                $ancienneImage = trim($_POST['ancienne_image'] ?? '');
                $nomImage = $ancienneImage !== '' ? $ancienneImage : null;

                /* Upload nouvelle image */
                if (
                    isset($_FILES['image_produit']) &&
                    $_FILES['image_produit']['error'] !== UPLOAD_ERR_NO_FILE
                ) {

                    $fichier = $_FILES['image_produit'];

                    if ($fichier['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Erreur pendant l'envoi de l'image.");
                    }

                    if ($fichier['size'] > 5 * 1024 * 1024) {
                        throw new Exception("L'image ne doit pas dépasser 5 Mo.");
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($fichier['tmp_name']);

                    $typesAutorises = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($typesAutorises[$mime])) {
                        throw new Exception("Format non autorisé. Utilisez JPG, PNG ou WEBP.");
                    }

                    $extension = $typesAutorises[$mime];

                    $nomImage = 'produit_' . date('Ymd_His') . '_' .
                        bin2hex(random_bytes(5)) . '.' . $extension;

                    $destination = $dossierImages . $nomImage;

                    if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
                        throw new Exception("Impossible d'enregistrer l'image dans images.");
                    }

                    /* Supprime l'ancienne image uniquement en modification */
                    if (
                        $action === 'modifier' &&
                        $ancienneImage !== '' &&
                        strpos($ancienneImage, 'images/') === 0
                    ) {
                        $ancienNom = basename($ancienneImage);
                        $ancienFichier = $dossierImages . $ancienNom;

                        if (is_file($ancienFichier)) {
                            @unlink($ancienFichier);
                        }
                    }

                    $imageBDD = $urlImages . $nomImage;

                } else {
                    $imageBDD = $nomImage;
                }

                /* ---------------- AJOUT ---------------- */
                if ($action === 'ajouter') {
                    if (isset($_POST['ajouter_produit'])) {
 
                        $nomImage = null;
                     
                        if (isset($_FILES['image_produit']) && $_FILES['image_produit']['error'] === UPLOAD_ERR_OK) {
                     
                            $dossierCible = __DIR__ . '/uploads/produits/';
                            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
                     
                            $infosFichier = pathinfo($_FILES['image_produit']['name']);
                            $extension = strtolower($infosFichier['extension']);
                     
                            if (in_array($extension, $extensionsAutorisees)) {
                     
                                $nomImage = uniqid('produit_') . '.' . $extension;
                                $cheminDestination = $dossierCible . $nomImage;
                     
                                move_uploaded_file($_FILES['image_produit']['tmp_name'], $cheminDestination);
                            }
                        }
                     
                        // ... récupération des autres champs POST (nom_produit, prix, stock, etc.) ...
                     
                        $requete = $pdo->prepare(
                            "INSERT INTO produits (nom_produit, prix, stock, images, id_categorie)
                             VALUES (:nom_produit, :prix, :stock, :images, :id_categorie)"
                        );
                     
                        $requete->execute([
                            ':nom_produit'  => $nom_produit,
                            ':prix'         => $prix,
                            ':stock'        => $stock,
                            ':images'       => $nomImage,
                            ':id_categorie' => $id_categorie,
                        ]);
                    }

                    $sql = "INSERT INTO produits
                    (
                        id_categorie,
                        id_fournisseur,
                        nom_produit,
                        reference,
                        description,
                        marque,
                        prix,
                        stock,
                        unite,
                        image,
                        seuil_alerte,
                        statut
                    )
                    VALUES
                    (
                        :id_categorie,
                        :id_fournisseur,
                        :nom_produit,
                        :reference,
                        :description,
                        :marque,
                        :prix,
                        :stock,
                        :unite,
                        :image,
                        :seuil_alerte,
                        :statut
                    )";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':id_categorie' => $idCategorie > 0 ? $idCategorie : null,
                        ':id_fournisseur' => $idFournisseur > 0 ? $idFournisseur : null,
                        ':nom_produit' => $nom,
                        ':reference' => $reference !== '' ? $reference : null,
                        ':description' => $description !== '' ? $description : null,
                        ':marque' => $marque !== '' ? $marque : null,
                        ':prix' => $prix,
                        ':stock' => $stock,
                        ':unite' => $unite !== '' ? $unite : 'unité',
                        ':image' => $imageBDD,
                        ':seuil_alerte' => $seuil,
                        ':statut' => $statut
                    ]);

                    header("Location: gerer_produit.php?ok=ajout");
                    exit;
                }

                /* ---------------- MODIFICATION ---------------- */
                if ($action === 'modifier') {

                    $id = (int)($_POST['id_produit'] ?? 0);

                    if ($id <= 0) {
                        throw new Exception("Produit invalide.");
                    }

                    $sql = "UPDATE produits SET
                        id_categorie = :id_categorie,
                        id_fournisseur = :id_fournisseur,
                        nom_produit = :nom_produit,
                        reference = :reference,
                        description = :description,
                        marque = :marque,
                        prix = :prix,
                        stock = :stock,
                        unite = :unite,
                        image = :image,
                        seuil_alerte = :seuil_alerte,
                        statut = :statut
                    WHERE id_produit = :id_produit";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':id_categorie' => $idCategorie > 0 ? $idCategorie : null,
                        ':id_fournisseur' => $idFournisseur > 0 ? $idFournisseur : null,
                        ':nom_produit' => $nom,
                        ':reference' => $reference !== '' ? $reference : null,
                        ':description' => $description !== '' ? $description : null,
                        ':marque' => $marque !== '' ? $marque : null,
                        ':prix' => $prix,
                        ':stock' => $stock,
                        ':unite' => $unite !== '' ? $unite : 'unité',
                        ':image' => $imageBDD,
                        ':seuil_alerte' => $seuil,
                        ':statut' => $statut,
                        ':id_produit' => $id
                    ]);

                    header("Location: gerer_produit.php?ok=modification");
                    exit;
                }
            }

            /* ---------------- SUPPRESSION ---------------- */
            if ($action === 'supprimer') {

                $id = (int)($_POST['id_produit'] ?? 0);

                if ($id <= 0) {
                    throw new Exception("Produit invalide.");
                }

                $stmt = $pdo->prepare(
                    "SELECT image FROM produits WHERE id_produit = ? LIMIT 1"
                );
                $stmt->execute([$id]);
                $produit = $stmt->fetch();

                $stmt = $pdo->prepare(
                    "DELETE FROM produits WHERE id_produit = ?"
                );
                $stmt->execute([$id]);

                if ($produit && !empty($produit['image'])) {

                    $image = $produit['image'];

                    if (strpos($image, 'images/') === 0) {

                        $nomFichier = basename($image);
                        $fichierImage = $dossierImages . $nomFichier;

                        if (is_file($fichierImage)) {
                            @unlink($fichierImage);
                        }
                    }
                }

                header("Location: gerer_produit.php?ok=suppression");
                exit;
            }

        } catch (PDOException $e) {

            if ($action === 'supprimer') {
                $message =
                    "Impossible de supprimer ce produit. Il est peut-être déjà utilisé dans une commande.";
            } else {
                $message =
                    "Opération impossible. Vérifiez la structure de la table produits.";
            }

            $typeMessage = "error";

        } catch (Exception $e) {

            $message = $e->getMessage();
            $typeMessage = "error";
        }
    }
}

/* MESSAGES */
if (isset($_GET['ok'])) {

    $messages = [
        'ajout' => "Produit ajouté avec succès au catalogue.",
        'modification' => "Produit modifié avec succès.",
        'suppression' => "Produit supprimé avec succès."
    ];

    if (isset($messages[$_GET['ok']])) {
        $message = $messages[$_GET['ok']];
        $typeMessage = "success";
    }
}

/* PRODUIT À MODIFIER */
$produitEdit = null;

if (isset($_GET['modifier'])) {

    $idEdit = (int)$_GET['modifier'];

    if ($idEdit > 0) {

        $stmt = $pdo->prepare(
            "SELECT * FROM produits WHERE id_produit = ? LIMIT 1"
        );

        $stmt->execute([$idEdit]);
        $produitEdit = $stmt->fetch();
    }
}

/* CATÉGORIES */
$categories = [];

try {

    $stmt = $pdo->query(
        "SELECT id_categorie, nom_categorie
         FROM categories
         ORDER BY nom_categorie ASC"
    );

    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    $categories = [];
}

/* FOURNISSEURS */
$fournisseurs = [];

try {

    $stmt = $pdo->query(
        "SELECT id_fournisseur, nom_fournisseur
         FROM fournisseurs
         ORDER BY nom_fournisseur ASC"
    );

    $fournisseurs = $stmt->fetchAll();

} catch (PDOException $e) {
    $fournisseurs = [];
}

/* LISTE DES PRODUITS */
$recherche = trim($_GET['recherche'] ?? '');

if ($recherche !== '') {

    $stmt = $pdo->prepare(
        "SELECT p.*, f.nom_fournisseur
         FROM produits p
         LEFT JOIN fournisseurs f
         ON p.id_fournisseur = f.id_fournisseur
         WHERE p.nom_produit LIKE ?
            OR p.reference LIKE ?
            OR p.marque LIKE ?
            OR p.description LIKE ?
         ORDER BY p.id_produit DESC"
    );

    $mot = "%" . $recherche . "%";
    $stmt->execute([$mot, $mot, $mot, $mot]);

} else {

    $stmt = $pdo->query(
        "SELECT p.*, f.nom_fournisseur
         FROM produits p
         LEFT JOIN fournisseurs f
         ON p.id_fournisseur = f.id_fournisseur
         ORDER BY p.id_produit DESC"
    );
}

$produits = $stmt->fetchAll();

/* STATISTIQUES */
$totalProduits = count($produits);
$totalDisponibles = 0;
$totalRupture = 0;
$totalStock = 0;

foreach ($produits as $p) {

    $totalStock += (int)$p['stock'];

    if (($p['statut'] ?? '') === 'disponible' && (int)$p['stock'] > 0) {
        $totalDisponibles++;
    } else {
        $totalRupture++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gérer les produits | Jardin des Agriculteurs</title>

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f7f4;
    color:#17351f;
}

.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:245px;
    height:100vh;
    background:#0b5d3b;
    color:white;
    padding:25px 18px;
    overflow-y:auto;
}

.logo{
    width:65px;
    height:65px;
    margin:auto;
    border:3px solid white;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:bold;
}

.brand{
    text-align:center;
    margin:14px 0 28px;
    font-size:18px;
    font-weight:bold;
}

.brand small{
    display:block;
    margin-top:5px;
    font-size:12px;
    opacity:.8;
}

.menu a{
    display:block;
    padding:13px 12px;
    margin:7px 0;
    color:white;
    text-decoration:none;
    border-radius:10px;
    transition:.25s;
}

.menu a:hover,
.menu a.active{
    background:#14804f;
}

.menu .logout{
    margin-top:30px;
    background:#8b2635;
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
    margin-bottom:25px;
}

.topbar h1{
    margin:0;
    color:#0b5d3b;
}

.topbar p{
    margin:8px 0 0;
    color:#66756b;
}

.admin-badge{
    background:white;
    padding:12px 18px;
    border-radius:30px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.alert{
    padding:14px 18px;
    margin-bottom:20px;
    border-radius:10px;
    font-weight:bold;
}

.alert.success{
    background:#dff5e8;
    color:#12613e;
}

.alert.error{
    background:#fde2e2;
    color:#a21b1b;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:25px;
}

.stat{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
}

.stat span{
    display:block;
    color:#718078;
    font-size:14px;
}

.stat strong{
    display:block;
    margin-top:8px;
    color:#0b5d3b;
    font-size:27px;
}

.card{
    background:white;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
    box-shadow:0 6px 25px rgba(0,0,0,.07);
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.card-header h2{
    margin:0;
    color:#0b5d3b;
}

.badge{
    background:#e3f4ea;
    color:#0b5d3b;
    padding:8px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.field{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.field.full{
    grid-column:1/-1;
}

label{
    font-weight:bold;
    color:#294c37;
}

input,textarea,select{
    width:100%;
    padding:12px 13px;
    border:1px solid #d5dfd8;
    border-radius:9px;
    outline:none;
    font-size:15px;
}

input:focus,textarea:focus,select:focus{
    border-color:#0b5d3b;
}

textarea{
    min-height:100px;
    resize:vertical;
}

.image-preview{
    width:110px;
    height:110px;
    object-fit:cover;
    border-radius:12px;
    border:2px solid #dce8df;
    margin-top:8px;
}

.btn{
    border:0;
    padding:13px 22px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    font-size:15px;
}

.btn-primary{
    background:#0b5d3b;
    color:white;
}

.btn-secondary{
    background:#e8eee9;
    color:#294c37;
    text-decoration:none;
    display:inline-block;
}

.search{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.search input{flex:1}

.table-wrapper{overflow-x:auto}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1100px;
}

th{
    background:#0b5d3b;
    color:white;
    padding:13px;
    text-align:left;
}

td{
    padding:12px;
    border-bottom:1px solid #e5ebe6;
    vertical-align:middle;
}

.product-image{
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #dbe5de;
}

.no-image{
    width:70px;
    height:70px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    background:#edf2ee;
    color:#738078;
    font-size:12px;
    text-align:center;
}

.price{
    font-weight:bold;
    color:#0b5d3b;
}

.status{
    display:inline-block;
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.available{
    background:#dff5e8;
    color:#12613e;
}

.unavailable{
    background:#fde2e2;
    color:#a21b1b;
}

.actions{
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}

.action{
    border:0;
    padding:8px 10px;
    border-radius:8px;
    cursor:pointer;
    text-decoration:none;
    font-size:12px;
    font-weight:bold;
}

.edit{
    background:#e5f0ff;
    color:#1558a6;
}

.delete{
    background:#ffe5e5;
    color:#a21b1b;
}

@media(max-width:1000px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
    }

    .main{
        margin-left:0;
        padding:18px;
    }

    .stats{
        grid-template-columns:repeat(2,1fr);
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

@media(max-width:600px){
    .stats{
        grid-template-columns:1fr;
    }

    .card{
        padding:17px;
    }

    .search{
        flex-direction:column;
    }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="logo">JA</div>

    <div class="brand">
        Jardin des Agriculteurs
        <small>Administration</small>
    </div>

    <nav class="menu">
        <a href="dashboard.php">Dashboard 🏠</a>
        <a href="gerer_produit.php" class="active">Gérer les produits 📦</a>
        <a href="produits.php">Catalogue 🌱</a>
        <a href="clients.php">Gérer les clients 👥</a>
        <a href="fournisseurs.php">Fournisseurs 🚜</a>
        <a href="gestion_commande.php">Gestion des commandes 🛒</a>
        <a href="deconnexion.php" class="logout">Déconnexion 🚪</a>
    </nav>
</aside>

<main class="main">

<header class="topbar">
    <div>
        <h1>Gestion des produits</h1>
        <p>Ajoutez, modifiez, supprimez et contrôlez les produits du catalogue.</p>
    </div>

    <div class="admin-badge">
        Administrateur 👤
    </div>
</header>

<?php if ($message !== ''): ?>
    <div class="alert <?= h($typeMessage) ?>">
        <?= h($message) ?>
    </div>
<?php endif; ?>

<section class="stats">

    <div class="stat">
        <span>Total produits</span>
        <strong><?= $totalProduits ?></strong>
    </div>

    <div class="stat">
        <span>Produits disponibles</span>
        <strong><?= $totalDisponibles ?></strong>
    </div>

    <div class="stat">
        <span>Indisponibles / rupture</span>
        <strong><?= $totalRupture ?></strong>
    </div>

    <div class="stat">
        <span>Articles en stock</span>
        <strong><?= $totalStock ?></strong>
    </div>

</section>

<section class="card">

<div class="card-header">
    <h2>
        <?= $produitEdit ? "Modifier le produit ✏️" : "Ajouter un produit ➕" ?>
    </h2>

    <span class="badge">
        <?= $produitEdit ? "MODIFICATION" : "NOUVEAU PRODUIT" ?>
    </span>
</div>

<form method="POST"
      action="gerer_produit.php"
      enctype="multipart/form-data">

    <input type="hidden"
           name="csrf_token"
           value="<?= h($csrf) ?>">
           <form action="gerer_produit.php" method="post" enctype="multipart/form-data">
 
    <label for="image_produit">Image du produit</label>
    <input type="file" name="image_produit" id="image_produit" accept="image/png, image/jpeg, image/webp">
 
    <!-- ... les autres champs existants (nom_produit, prix, stock, etc.) restent inchangés ... -->
 
    <button type="submit" name="ajouter_produit">Ajouter le produit</button>
</form>

    <?php if ($produitEdit): ?>

        <input type="hidden"
               name="action"
               value="modifier">

        <input type="hidden"
               name="id_produit"
               value="<?= (int)$produitEdit['id_produit'] ?>">

        <input type="hidden"
               name="ancienne_image"
               value="<?= h($produitEdit['image'] ?? '') ?>">

    <?php else: ?>

        <input type="hidden"
               name="action"
               value="ajouter">

    <?php endif; ?>

    <div class="form-grid">

        <div class="field">
            <label>Nom du produit *</label>
            <input type="text"
                   name="nom_produit"
                   required
                   value="<?= h($produitEdit['nom_produit'] ?? '') ?>"
                   placeholder="Ex. Engrais agricole">
        </div>

        <div class="field">
            <label>Référence</label>
            <input type="text"
                   name="reference"
                   value="<?= h($produitEdit['reference'] ?? '') ?>"
                   placeholder="Ex. ENG-001">
        </div>

        <div class="field">
            <label>Catégorie</label>

            <select name="id_categorie">
                <option value="">-- Choisir une catégorie --</option>

                <?php foreach ($categories as $cat): ?>

                    <option value="<?= (int)$cat['id_categorie'] ?>"
                        <?= isset($produitEdit['id_categorie']) &&
                        (int)$produitEdit['id_categorie'] ===
                        (int)$cat['id_categorie']
                        ? 'selected'
                        : '' ?>>

                        <?= h($cat['nom_categorie']) ?>

                    </option>

                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Fournisseur</label>

            <select name="id_fournisseur">
                <option value="">-- Choisir un fournisseur --</option>

                <?php foreach ($fournisseurs as $f): ?>

                    <option value="<?= (int)$f['id_fournisseur'] ?>"
                        <?= isset($produitEdit['id_fournisseur']) &&
                        (int)$produitEdit['id_fournisseur'] ===
                        (int)$f['id_fournisseur']
                        ? 'selected'
                        : '' ?>>

                        <?= h($f['nom_fournisseur']) ?>

                    </option>

                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Marque</label>

            <input type="text"
                   name="marque"
                   value="<?= h($produitEdit['marque'] ?? '') ?>"
                   placeholder="Marque du produit">
        </div>

        <div class="field">
            <label>Prix (FCFA) *</label>

            <input type="number"
                   name="prix"
                   min="0"
                   step="1"
                   required
                   value="<?= h($produitEdit['prix'] ?? '') ?>"
                   placeholder="Ex. 15000">
        </div>

        <div class="field">
            <label>Stock *</label>

            <input type="number"
                   name="stock"
                   min="0"
                   required
                   value="<?= h($produitEdit['stock'] ?? 0) ?>">
        </div>

        <div class="field">
            <label>Unité</label>

            <input type="text"
                   name="unite"
                   value="<?= h($produitEdit['unite'] ?? 'unité') ?>"
                   placeholder="sac, litre, unité...">
        </div>

        <div class="field">
            <label>Seuil d'alerte</label>

            <input type="number"
                   name="seuil_alerte"
                   min="0"
                   value="<?= h($produitEdit['seuil_alerte'] ?? 0) ?>">
        </div>

        <div class="field">
            <label>Statut</label>

            <select name="statut">

                <option value="disponible"
                    <?= ($produitEdit['statut'] ?? 'disponible') === 'disponible'
                    ? 'selected'
                    : '' ?>>
                    Disponible
                </option>

                <option value="indisponible"
                    <?= ($produitEdit['statut'] ?? '') === 'indisponible'
                    ? 'selected'
                    : '' ?>>
                    Indisponible
                </option>

            </select>
        </div>

        <div class="field full">
            <label>Image du produit</label>

            <input type="file"
                   name="image_produit"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <small>
                Formats acceptés : JPG, PNG, WEBP — taille maximale : 5 Mo.
            </small>

            <?php if ($produitEdit && !empty($produitEdit['image'])): ?>

                <img src="<?= h($produitEdit['image']) ?>"
                     alt="Image actuelle"
                     class="image-preview">

                <small>
                    Laissez le champ vide pour conserver l'image actuelle.
                </small>

            <?php endif; ?>
        </div>

        <div class="field full">
            <label>Description</label>

            <textarea name="description"
                      placeholder="Description du produit..."><?= h($produitEdit['description'] ?? '') ?></textarea>
        </div>

        <div class="field full">

            <div style="display:flex;gap:10px;flex-wrap:wrap;">

                <button type="submit"
                        class="btn btn-primary">

                    <?= $produitEdit
                        ? "Enregistrer les modifications 💾"
                        : "Ajouter le produit ➕" ?>

                </button>

                <?php if ($produitEdit): ?>

                    <a href="gerer_produit.php"
                       class="btn btn-secondary">
                        Annuler
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>
</form>

</section>

<section class="card">

<div class="card-header">
    <h2>Produits enregistrés 📦</h2>
</div>

<form method="GET"
      action="gerer_produit.php"
      class="search">

    <input type="search"
           name="recherche"
           value="<?= h($recherche) ?>"
           placeholder="Rechercher un produit, une référence, une marque...">

    <button type="submit"
            class="btn btn-primary">
        Rechercher 🔎
    </button>

    <?php if ($recherche !== ''): ?>

        <a href="gerer_produit.php"
           class="btn btn-secondary">
            Réinitialiser
        </a>

    <?php endif; ?>

</form>

<div class="table-wrapper">

<table>

<thead>
<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Produit</th>
    <th>Référence</th>
    <th>Prix</th>
    <th>Stock</th>
    <th>Fournisseur</th>
    <th>Statut</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php if (empty($produits)): ?>

<tr>
    <td colspan="9" style="text-align:center;padding:35px;">
        Aucun produit enregistré.
    </td>
</tr>

<?php else: ?>

<?php foreach ($produits as $produit): ?>

<tr>

<td>#<?= (int)$produit['id_produit'] ?></td>

<td>

<?php if (!empty($produit['image'])): ?>

<img src="<?= h($produit['image']) ?>"
     alt="<?= h($produit['nom_produit']) ?>"
     class="product-image">

<?php else: ?>

<div class="no-image">
    Pas d'image
</div>

<?php endif; ?>

</td>

<td>
<strong><?= h($produit['nom_produit']) ?></strong>

<?php if (!empty($produit['marque'])): ?>
<br>
<small><?= h($produit['marque']) ?></small>
<?php endif; ?>

</td>

<td><?= h($produit['reference'] ?? '-') ?></td>

<td class="price">
<?= number_format((float)$produit['prix'], 0, ',', ' ') ?> FCFA
</td>

<td>
<?= (int)$produit['stock'] ?>
<?= h($produit['unite'] ?? '') ?>
</td>

<td>
<?= h($produit['nom_fournisseur'] ?? 'Non renseigné') ?>
</td>

<td>

<?php if (
    ($produit['statut'] ?? '') === 'disponible' &&
    (int)$produit['stock'] > 0
): ?>

<span class="status available">
    Disponible
</span>

<?php else: ?>

<span class="status unavailable">
    Indisponible
</span>

<?php endif; ?>

</td>

<td>

<div class="actions">

<a href="gerer_produit.php?modifier=<?= (int)$produit['id_produit'] ?>"
   class="action edit">
    Modifier ✏️
</a>

<form method="POST"
      action="gerer_produit.php"
      onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">

    <input type="hidden"
           name="csrf_token"
           value="<?= h($csrf) ?>">

    <input type="hidden"
           name="action"
           value="supprimer">

    <input type="hidden"
           name="id_produit"
           value="<?= (int)$produit['id_produit'] ?>">

    <button type="submit"
            class="action delete">
        Supprimer 🗑️
    </button>

</form>

</div>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>

</div>
</section>

</main>
</body>
</html>
 
