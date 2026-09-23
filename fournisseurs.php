<?php
/*
|--------------------------------------------------------------------------
| JARDIN DES AGRICULTEURS
| Fichier : fournisseurs.php
| Module : Gestion des fournisseurs
|--------------------------------------------------------------------------
| Base : jardin_agriculteurs
| Connexion : PDO
|--------------------------------------------------------------------------
*/

session_start();

/* =========================
   1. CONTRÔLE D'ACCÈS ADMIN
   ========================= */



/* =========================
   2. CONNEXION À LA BASE
   ========================= */

$host = "localhost";
$dbname = "jardin_des_agriculteurs";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}

/* =========================
   3. CRÉATION AUTOMATIQUE
      DE LA TABLE
   ========================= */

$pdo->exec("
    CREATE TABLE IF NOT EXISTS fournisseurs (
        id_fournisseur INT AUTO_INCREMENT PRIMARY KEY,
        nom_fournisseur VARCHAR(150) NOT NULL,
        contact VARCHAR(150) DEFAULT NULL,
        telephone VARCHAR(30) DEFAULT NULL,
        email VARCHAR(150) DEFAULT NULL,
        adresse VARCHAR(255) DEFAULT NULL,
        ville VARCHAR(100) DEFAULT NULL,
        produits_fournis TEXT DEFAULT NULL,
        statut ENUM('Actif','Inactif') NOT NULL DEFAULT 'Actif',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

/* =========================
   4. PROTECTION CSRF
   ========================= */

if (empty($_SESSION['csrf_fournisseurs'])) {
    $_SESSION['csrf_fournisseurs'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_fournisseurs'];

function verifier_csrf($token, $csrf) {
    return isset($token) && hash_equals($csrf, $token);
}

/* =========================
   5. VARIABLES
   ========================= */

$message = "";
$messageType = "success";
$mode = "ajouter";
$fournisseurEdit = null;

/* =========================
   6. TRAITEMENT AJOUT /
      MODIFICATION /
      SUPPRESSION
   ========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verifier_csrf($token, $csrf)) {
        $message = "La requête n'est pas valide. Veuillez réessayer.";
        $messageType = "error";
    } else {

        /* -------- AJOUT -------- */
        if ($action === 'ajouter') {

            $nom = trim($_POST['nom_fournisseur'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $produits = trim($_POST['produits_fournis'] ?? '');
            $statut = ($_POST['statut'] ?? 'Actif') === 'Inactif'
                ? 'Inactif'
                : 'Actif';

            if ($nom === '') {
                $message = "Le nom du fournisseur est obligatoire.";
                $messageType = "error";
            } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "L'adresse e-mail n'est pas valide.";
                $messageType = "error";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO fournisseurs
                    (nom_fournisseur, contact,Téléphone , E-mail, adresse,
                     ville, produits_fournis, statut)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $nom, $contact, $telephone, $email,
                    $adresse, $ville, $produits, $statut
                ]);

                $message = "Le fournisseur a été ajouté avec succès.";
                $messageType = "success";
            }
        }

        /* -------- MODIFICATION -------- */
        elseif ($action === 'modifier') {

            $id = (int)($_POST['id_fournisseur'] ?? 0);
            $nom = trim($_POST['nom_fournisseur'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $produits = trim($_POST['produits_fournis'] ?? '');
            $statut = ($_POST['statut'] ?? 'Actif') === 'Inactif'
                ? 'Inactif'
                : 'Actif';

            if ($id <= 0 || $nom === '') {
                $message = "Les informations obligatoires sont incomplètes.";
                $messageType = "error";
            } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "L'adresse e-mail n'est pas valide.";
                $messageType = "error";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE fournisseurs
                    SET nom_fournisseur = ?,
                        contact = ?,
                        telephone = ?,
                        email = ?,
                        adresse = ?,
                        ville = ?,
                        produits_fournis = ?,
                        statut = ?
                    WHERE id_fournisseur = ?
                ");

                $stmt->execute([
                    $nom, $contact, $telephone, $email,
                    $adresse, $ville, $produits, $statut, $id
                ]);

                $message = "Le fournisseur a été modifié avec succès.";
                $messageType = "success";
            }
        }

        /* -------- SUPPRESSION -------- */
        elseif ($action === 'supprimer') {

            $id = (int)($_POST['id_fournisseur'] ?? 0);

            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "DELETE FROM fournisseurs WHERE id_fournisseur = ?"
                );
                $stmt->execute([$id]);

                $message = "Le fournisseur a été supprimé.";
                $messageType = "success";
            }
        }
    }
}

/* =========================
   7. FOURNISSEUR À MODIFIER
   ========================= */

if (isset($_GET['modifier'])) {

    $id = (int)$_GET['modifier'];

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "SELECT * FROM fournisseurs WHERE id_fournisseur = ?"
        );
        $stmt->execute([$id]);
        $fournisseurEdit = $stmt->fetch();

        if ($fournisseurEdit) {
            $mode = "modifier";
        }
    }
}

/* =========================
   8. RECHERCHE
   ========================= */

$recherche = trim($_GET['recherche'] ?? '');

if ($recherche !== '') {

    $stmt = $pdo->prepare("
        SELECT *
        FROM fournisseurs
        WHERE nom_fournisseur LIKE ?
           OR contact LIKE ?
           OR telephone LIKE ?
           OR email LIKE ?
           OR ville LIKE ?
        ORDER BY id_fournisseur DESC
    ");

    $like = "%$recherche%";

    $stmt->execute([
        $like, $like, $like, $like, $like
    ]);

    $fournisseurs = $stmt->fetchAll();

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM fournisseurs
        ORDER BY id_fournisseur DESC
    ");

    $fournisseurs = $stmt->fetchAll();
}

/* =========================
   9. STATISTIQUES
   ========================= */

$total = (int)$pdo->query(
    "SELECT COUNT(*) FROM fournisseurs"
)->fetchColumn();

$actifs = (int)$pdo->query(
    "SELECT COUNT(*) FROM fournisseurs WHERE statut = 'Actif'"
)->fetchColumn();

$inactifs = (int)$pdo->query(
    "SELECT COUNT(*) FROM fournisseurs WHERE statut = 'Inactif'"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Fournisseurs | Jardin des Agriculteurs</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f8f6;
    color: #18372a;
}

a {
    text-decoration: none;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 245px;
    height: 100vh;
    padding: 25px 16px;
    background: linear-gradient(180deg, #063b2a, #087443);
    color: white;
    z-index: 20;
}

.logo {
    width: 58px;
    height: 58px;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.25);
    font-size: 23px;
    font-weight: 900;
}

.brand {
    text-align: center;
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 28px;
}

.nav a {
    display: block;
    padding: 13px 14px;
    margin: 7px 0;
    color: rgba(255,255,255,.86);
    border-radius: 12px;
    transition: .25s;
    font-size: 14px;
}

.nav a:hover,
.nav a.active {
    color: white;
    background: rgba(255,255,255,.14);
    transform: translateX(3px);
}

.nav .logout {
    margin-top: 28px;
    background: rgba(220,70,70,.16);
}

.main {
    margin-left: 245px;
    min-height: 100vh;
    padding: 28px;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 25px;
}

.title h1 {
    font-size: 28px;
    margin-bottom: 5px;
}

.title p {
    color: #72827a;
    font-size: 13px;
}

.admin {
    padding: 11px 15px;
    border-radius: 12px;
    background: white;
    box-shadow: 0 5px 18px rgba(0,0,0,.06);
    font-size: 13px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 17px;
    margin-bottom: 23px;
}

.stat {
    background: white;
    padding: 21px;
    border-radius: 17px;
    box-shadow: 0 7px 22px rgba(0,0,0,.055);
}

.stat span {
    color: #77857f;
    font-size: 12px;
}

.stat strong {
    display: block;
    margin-top: 7px;
    font-size: 27px;
    color: #087443;
}

.card {
    background: white;
    border-radius: 19px;
    padding: 22px;
    margin-bottom: 23px;
    box-shadow: 0 7px 24px rgba(0,0,0,.055);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.card-header h2 {
    font-size: 18px;
}

.badge {
    padding: 7px 11px;
    border-radius: 30px;
    background: #eaf8f0;
    color: #087443;
    font-size: 11px;
    font-weight: 700;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.field.full {
    grid-column: 1 / -1;
}

.field label {
    display: block;
    margin-bottom: 7px;
    font-size: 12px;
    font-weight: 700;
    color: #52635b;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    padding: 12px 13px;
    border: 1px solid #dbe7e1;
    border-radius: 10px;
    outline: none;
    font-family: inherit;
    font-size: 13px;
    background: #fbfdfc;
    transition: .2s;
}

.field textarea {
    min-height: 82px;
    resize: vertical;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: #15915a;
    box-shadow: 0 0 0 3px rgba(21,145,90,.09);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 17px;
    flex-wrap: wrap;
}

.btn {
    border: 0;
    cursor: pointer;
    border-radius: 10px;
    padding: 12px 17px;
    font-weight: 700;
    font-size: 12px;
    transition: .22s;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    color: white;
    background: linear-gradient(135deg,#087443,#18a35d);
}

.btn-light {
    color: #087443;
    background: #eaf7f0;
}

.btn-danger {
    color: #b52c2c;
    background: #fff0f0;
}

.search {
    display: flex;
    gap: 10px;
    margin-bottom: 17px;
}

.search input {
    flex: 1;
    padding: 12px 14px;
    border: 1px solid #dbe7e1;
    border-radius: 10px;
    outline: none;
}

.alert {
    padding: 13px 16px;
    border-radius: 11px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 600;
}

.alert.success {
    color: #166b45;
    background: #eaf8f0;
    border: 1px solid #ccebd9;
}

.alert.error {
    color: #9b3030;
    background: #fff0f0;
    border: 1px solid #f2caca;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 920px;
}

th {
    text-align: left;
    padding: 13px;
    background: #f3f8f5;
    color: #52635b;
    font-size: 11px;
    text-transform: uppercase;
}

td {
    padding: 14px 13px;
    border-bottom: 1px solid #edf1ee;
    font-size: 12px;
    vertical-align: middle;
}

.name {
    font-weight: 800;
    color: #163d2c;
}

.status {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
}

.status.active {
    background: #e7f8ef;
    color: #087443;
}

.status.inactive {
    background: #f3f3f3;
    color: #777;
}

.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.empty {
    text-align: center;
    padding: 35px 15px;
    color: #7d8984;
}

@media (max-width: 950px) {
    .sidebar {
        width: 210px;
    }

    .main {
        margin-left: 210px;
        padding: 20px;
    }

    .stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 720px) {
    .sidebar {
        position: static;
        width: 100%;
        height: auto;
        padding: 17px;
    }

    .nav {
        display: flex;
        gap: 6px;
        overflow-x: auto;
    }

    .nav a {
        white-space: nowrap;
    }

    .nav .logout {
        margin-top: 7px;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .topbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .field.full {
        grid-column: auto;
    }

    .search {
        flex-direction: column;
    }

    .card {
        padding: 17px;
    }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="logo">JA</div>

    <div class="brand">
        Jardin des Agriculteurs
    </div>

    <nav class="nav">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="produits.php">🌱 Produits</a>
        <a href="client.php">👥 Clients</a>
        <a href="fournisseurs.php" class="active">🚜 Fournisseurs</a>
        <a href="commande.php">📦 Commandes</a>
        <a href="index.php">🌿 Accueil</a>
        <a href="deconnexion.php" class="logout">🚪 Déconnexion</a>
    </nav>
</aside>

<main class="main">

    <header class="topbar">
        <div class="title">
            <h1>Gestion des fournisseurs</h1>
            <p>Consultez et gérez les partenaires qui approvisionnent le Jardin des Agriculteurs.</p>
        </div>

        <div class="admin">
            👤 Administrateur
        </div>
    </header>

    <?php if ($message !== ""): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <section class="stats">

        <div class="stat">
            <span>Total fournisseurs</span>
            <strong><?php echo $total; ?></strong>
        </div>

        <div class="stat">
            <span>Fournisseurs actifs</span>
            <strong><?php echo $actifs; ?></strong>
        </div>

        <div class="stat">
            <span>Fournisseurs inactifs</span>
            <strong><?php echo $inactifs; ?></strong>
        </div>

    </section>

    <section class="card">

        <div class="card-header">
            <h2>
                <?php echo $mode === "modifier"
                    ? "Modifier le fournisseur"
                    : "Ajouter un fournisseur"; ?>
            </h2>

            <span class="badge">
                <?php echo $mode === "modifier" ? "ÉDITION" : "NOUVEAU"; ?>
            </span>
        </div>

        <form method="POST" action="fournisseurs.php">

            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($csrf); ?>">

            <input type="hidden" name="action"
                   value="<?php echo $mode === "modifier"
                       ? "modifier"
                       : "ajouter"; ?>">

            <?php if ($mode === "modifier"): ?>
                <input type="hidden" name="id_fournisseur"
                       value="<?php echo (int)$fournisseurEdit['id_fournisseur']; ?>">
            <?php endif; ?>

            <div class="form-grid">

                <div class="field">
                    <label>Nom du fournisseur *</label>
                    <input type="text" name="nom_fournisseur" required
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['nom_fournisseur'] ?? ''
                           ); ?>">
                </div>

                <div class="field">
                    <label>Contact principal</label>
                    <input type="text" name="contact"
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['contact'] ?? ''
                           ); ?>">
                </div>

                <div class="field">
                    <label>Téléphone</label>
                    <input type="text" name="telephone"
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['telephone'] ?? ''
                           ); ?>">
                </div>

                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="email"
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['email'] ?? ''
                           ); ?>">
                </div>

                <div class="field">
                    <label>Adresse</label>
                    <input type="text" name="adresse"
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['adresse'] ?? ''
                           ); ?>">
                </div>

                <div class="field">
                    <label>Ville</label>
                    <input type="text" name="ville"
                           value="<?php echo htmlspecialchars(
                               $fournisseurEdit['ville'] ?? ''
                           ); ?>">
                </div>

                <div class="field full">
                    <label>Produits fournis</label>
                    <textarea name="produits_fournis"
                              placeholder="Ex. semences, engrais, outils agricoles..."><?php
                        echo htmlspecialchars(
                            $fournisseurEdit['produits_fournis'] ?? ''
                        );
                    ?></textarea>
                </div>

                <div class="field">
                    <label>Statut</label>

                    <select name="statut">
                        <option value="Actif"
                            <?php echo (($fournisseurEdit['statut'] ?? 'Actif')
                                === 'Actif') ? 'selected' : ''; ?>>
                            Actif
                        </option>

                        <option value="Inactif"
                            <?php echo (($fournisseurEdit['statut'] ?? '')
                                === 'Inactif') ? 'selected' : ''; ?>>
                            Inactif
                        </option>
                    </select>
                </div>

            </div>

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    <?php echo $mode === "modifier"
                        ? "✓ Enregistrer les modifications"
                        : "＋ Ajouter le fournisseur"; ?>
                </button>

                <?php if ($mode === "modifier"): ?>
                    <a href="fournisseurs.php" class="btn btn-light">
                        Annuler
                    </a>
                <?php endif; ?>

            </div>

        </form>

    </section>

    <section class="card">

        <div class="card-header">
            <h2>Liste des fournisseurs</h2>
            <span class="badge">
                <?php echo count($fournisseurs); ?> résultat(s)
            </span>
        </div>

        <form class="search" method="GET" action="fournisseurs.php">

            <input type="search"
                   name="recherche"
                   placeholder="Rechercher par nom, contact, téléphone, e-mail ou ville..."
                   value="<?php echo htmlspecialchars($recherche); ?>">

            <button class="btn btn-primary" type="submit">
                🔎 Rechercher
            </button>

            <?php if ($recherche !== ''): ?>
                <a href="fournisseurs.php" class="btn btn-light">
                    Réinitialiser
                </a>
            <?php endif; ?>

        </form>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fournisseur</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>E-mail</th>
                        <th>Ville</th>
                        <th>Produits fournis</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($fournisseurs)): ?>

                    <tr>
                        <td colspan="9" class="empty">
                            Aucun fournisseur trouvé.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($fournisseurs as $f): ?>

                        <tr>

                            <td>
                                #<?php echo (int)$f['id_fournisseur']; ?>
                            </td>

                            <td class="name">
                                <?php echo htmlspecialchars(
                                    $f['nom_fournisseur']
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $f['contact'] ?: '—'
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $f['telephone'] ?: '—'
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $f['email'] ?: '—'
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $f['ville'] ?: '—'
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $f['produits_fournis'] ?: '—'
                                ); ?>
                            </td>

                            <td>
                                <span class="status <?php
                                    echo $f['statut'] === 'Actif'
                                        ? 'active'
                                        : 'inactive';
                                ?>">
                                    <?php echo htmlspecialchars($f['statut']); ?>
                                </span>
                            </td>

                            <td>

                                <div class="actions">

                                    <a class="btn btn-light"
                                       href="fournisseurs.php?modifier=<?php
                                           echo (int)$f['id_fournisseur'];
                                       ?>">
                                        Modifier
                                    </a>

                                    <form method="POST"
                                          action="fournisseurs.php"
                                          onsubmit="return confirmerSuppression();">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?php echo htmlspecialchars($csrf); ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="supprimer">

                                        <input type="hidden"
                                               name="id_fournisseur"
                                               value="<?php echo (int)$f['id_fournisseur']; ?>">

                                        <button type="submit"
                                                class="btn btn-danger">
                                            Supprimer
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

<script>
function confirmerSuppression() {
    return confirm(
        "Voulez-vous vraiment supprimer ce fournisseur ?\n\n" +
        "Cette opération est définitive."
    );
}

/* Faire disparaître les messages après quelques secondes */
setTimeout(function () {
    const alert = document.querySelector(".alert");

    if (alert) {
        alert.style.transition = "opacity .5s";
        alert.style.opacity = "0";

        setTimeout(function () {
            alert.remove();
        }, 500);
    }
}, 4500);
</script>

</body>
</html>