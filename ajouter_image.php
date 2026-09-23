<?php
session_start();
require_once 'connexion.php';

/*
 * Vérification simple de la connexion administrateur.
 * Si ton auth.php utilise déjà une autre méthode, garde ta méthode
 * d'authentification actuelle et adapte uniquement cette partie.
 */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: connexion.php");
    exit;
}

$message = "";
$typeMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $produit_id = isset($_POST['produit_id']) ? (int) $_POST['produit_id'] : 0;

    if ($produit_id <= 0) {
        $message = "Veuillez sélectionner un produit.";
        $typeMessage = "error";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = "Veuillez sélectionner une image.";
        $typeMessage = "error";
    } else {

        $fichier = $_FILES['image'];
        $nomOriginal = $fichier['name'];
        $tmpName = $fichier['tmp_name'];
        $taille = $fichier['size'];

        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees, true)) {
            $message = "Format non autorisé. Utilisez JPG, JPEG, PNG ou WEBP.";
            $typeMessage = "error";
        } elseif ($taille > 5 * 1024 * 1024) {
            $message = "L'image ne doit pas dépasser 5 Mo.";
            $typeMessage = "error";
        } else {

            $dossier = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;

            if (!is_dir($dossier)) {
                mkdir($dossier, 0775, true);
            }

            $nomFichier = 'produit_' . $produit_id . '_' . time() . '.' . $extension;
            $destination = $dossier . $nomFichier;

            if (move_uploaded_file($tmpName, $destination)) {

                /*
                 * IMPORTANT :
                 * Cette requête suppose que la table images contient
                 * au minimum les colonnes :
                 * - produit_id
                 * - chemin
                 *
                 * Si tes noms de colonnes sont différents, il faudra
                 * remplacer ces deux noms.
                 */
                $chemin = 'images/' . $nomFichier;

                $sql = "INSERT INTO images (produit_id, chemin)
                        VALUES (:produit_id, :chemin)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':produit_id' => $produit_id,
                    ':chemin' => $chemin
                ]);

                $message = "Image ajoutée avec succès. Elle peut maintenant apparaître dans le catalogue.";
                $typeMessage = "success";

            } else {
                $message = "Impossible d'enregistrer l'image.";
                $typeMessage = "error";
            }
        }
    }
}

/* Récupération des produits pour le menu déroulant */
$stmtProduits = $pdo->query("SELECT id, nom FROM produit ORDER BY nom ASC");
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter une image - Jardin des Agriculteurs</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f3f8f3;
    color: #1f3d2b;
}

.container {
    width: 92%;
    max-width: 650px;
    margin: 50px auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.10);
}

h1 {
    text-align: center;
    margin-bottom: 10px;
    color: #176b3a;
}

.description {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
}

label {
    display: block;
    margin: 18px 0 8px;
    font-weight: bold;
}

select,
input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccd8ce;
    border-radius: 10px;
    background: #fff;
}

button {
    width: 100%;
    margin-top: 25px;
    padding: 13px;
    border: none;
    border-radius: 10px;
    background: #176b3a;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #0e512b;
}

.message {
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.success {
    background: #e5f7e9;
    color: #176b3a;
}

.error {
    background: #fde8e8;
    color: #a51d1d;
}

.retour {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #176b3a;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">
    <div class="card">

        <h1>Ajouter une image</h1>

        <p class="description">
            Sélectionnez un produit puis choisissez son image.
        </p>

        <?php if ($message !== ""): ?>
            <div class="message <?= htmlspecialchars($typeMessage) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label for="produit_id">Produit</label>

            <select name="produit_id" id="produit_id" required>
                <option value="">-- Sélectionner un produit --</option>

                <?php foreach ($produits as $produit): ?>
                    <option value="<?= (int) $produit['id'] ?>">
                        <?= htmlspecialchars($produit['nom']) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label for="image">Image du produit</label>

            <input
                type="file"
                name="image"
                id="image"
                accept=".jpg,.jpeg,.png,.webp"
                required
            >

            <button type="submit">
                Ajouter l'image au catalogue
            </button>

        </form>

        <a class="retour" href="produit.php">
            ← Retour au catalogue
        </a>

    </div>
</div>

</body>
</html>
