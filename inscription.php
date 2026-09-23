<?php
session_start();

/* =========================================================
   JARDIN DES AGRICULTEURS
   inscription.php
   Page d'inscription premium - fichier unique
   Compatible avec la table utilisateurs du projet
   ========================================================= */

/* ---------------------------------------------------------
   CONNEXION À LA BASE DE DONNÉES
   --------------------------------------------------------- */
$host = "127.0.0.1";
$dbname = "jardin_des_agriculteurs";
$username = "root";
$password = "";

$message = "";
$messageType = "";

$old = array(
    "nom" => "",
    "prenom" => "",
    "email" => "",
    "telephone" => "",
    "adresse" => "",
    "ville" => ""
);

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch (PDOException $e) {
    $message = "Impossible de se connecter à la base de données.";
    $messageType = "error";
}

/* ---------------------------------------------------------
   TRAITEMENT DU FORMULAIRE
   --------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($pdo)) {

    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $adresse = trim($_POST["adresse"] ?? "");
    $ville = trim($_POST["ville"] ?? "");
    $motDePasse = $_POST["mot_de_passe"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";
    $conditions = isset($_POST["conditions"]);

    $old["nom"] = $nom;
    $old["prenom"] = $prenom;
    $old["email"] = $email;
    $old["telephone"] = $telephone;
    $old["adresse"] = $adresse;
    $old["ville"] = $ville;

    /* Validation */
    if (
        $nom === "" ||
        $prenom === "" ||
        $email === "" ||
        $motDePasse === "" ||
        $confirmation === ""
    ) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez saisir une adresse e-mail valide.";
        $messageType = "error";

    } elseif (strlen($nom) > 100 || strlen($prenom) > 100) {
        $message = "Le nom ou le prénom est trop long.";
        $messageType = "error";

    } elseif (strlen($email) > 150) {
        $message = "L'adresse e-mail est trop longue.";
        $messageType = "error";

    } elseif ($telephone !== "" && strlen($telephone) > 30) {
        $message = "Le numéro de téléphone est trop long.";
        $messageType = "error";

    } elseif (strlen($motDePasse) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères.";
        $messageType = "error";

    } elseif ($motDePasse !== $confirmation) {
        $message = "Les mots de passe ne correspondent pas.";
        $messageType = "error";

    } elseif (!$conditions) {
        $message = "Veuillez accepter les conditions d'utilisation.";
        $messageType = "error";

    } else {

        /* Vérifier si l'e-mail existe déjà */
        $check = $pdo->prepare(
            "SELECT id
             FROM utilisateurs
             WHERE email = :email
             LIMIT 1"
        );

        $check->execute(array(":email" => $email));

        if ($check->fetch()) {

            $message = "Cette adresse e-mail possède déjà un compte.";
            $messageType = "error";

        } else {

            /* Hachage sécurisé du mot de passe */
            $motDePasseHash = password_hash(
                $motDePasse,
                PASSWORD_DEFAULT
            );

            try {

                $insert = $pdo->prepare(
                    "INSERT INTO utilisateurs
                    (
                        nom,
                        prenom,
                        email,
                        telephone,
                        mot_de_passe,
                        adresse,
                        ville,
                        role,
                        statut
                    )
                    VALUES
                    (
                        :nom,
                        :prenom,
                        :email,
                        :telephone,
                        :mot_de_passe,
                        :adresse,
                        :ville,
                        'client',
                        'actif'
                    )"
                );

                $insert->execute(
                    array(
                        ":nom" => $nom,
                        ":prenom" => $prenom,
                        ":email" => $email,
                        ":telephone" => $telephone !== "" ? $telephone : null,
                        ":mot_de_passe" => $motDePasseHash,
                        ":adresse" => $adresse !== "" ? $adresse : null,
                        ":ville" => $ville !== "" ? $ville : null
                    )
                );

                /*
                   Inscription réussie.
                   On ne connecte pas automatiquement le client :
                   il pourra utiliser connexion.php.
                */
                header("Location: connexion.php?inscription=success");
                exit;

            } catch (PDOException $e) {

                if ((int)$e->errorInfo[1] === 1062) {
                    $message = "Cette adresse e-mail est déjà utilisée.";
                } else {
                    $message = "Une erreur est survenue lors de la création du compte.";
                }

                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Créer un compte | Jardin des Agriculteurs</title>

<meta
    name="description"
    content="Créez votre compte client sur Jardin des Agriculteurs."
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap"
    rel="stylesheet"
>

<style>

/* =========================================================
   VARIABLES
   ========================================================= */

:root {
    --forest-dark: #063218;
    --forest: #14532d;
    --forest-light: #2f7d4a;
    --green-soft: #eaf5ed;
    --gold: #c99a3e;
    --white: #ffffff;
    --cream: #f7faf7;
    --text: #17251b;
    --muted: #6b756d;
    --border: #dfe8e1;
    --red: #a93232;
    --shadow: 0 25px 70px rgba(6,50,24,.13);
}

/* =========================================================
   RESET
   ========================================================= */

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
    font-family: "Montserrat", sans-serif;
    color: var(--text);
    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(47,125,74,.13),
            transparent 28%
        ),
        radial-gradient(
            circle at 90% 90%,
            rgba(201,154,62,.10),
            transparent 25%
        ),
        linear-gradient(
            135deg,
            #f9fcfa,
            #edf6ef
        );
}

/* =========================================================
   HEADER
   ========================================================= */

.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: rgba(255,255,255,.93);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(20,83,45,.08);
}

.navbar {
    max-width: 1250px;
    min-height: 76px;
    margin: auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--forest);
    font-family: "Playfair Display", serif;
    font-size: 20px;
    font-weight: 800;
}

.logo-mark {
    width: 43px;
    height: 43px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    background: linear-gradient(
        135deg,
        var(--forest-dark),
        var(--forest-light)
    );
    box-shadow: 0 8px 20px rgba(20,83,45,.23);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 28px;
}

.nav-links a {
    color: #344239;
    font-size: 12px;
    font-weight: 700;
    transition: .3s;
}

.nav-links a:hover {
    color: var(--forest);
}

.login-link {
    padding: 11px 17px;
    border: 1px solid var(--forest);
    border-radius: 50px;
    color: var(--forest) !important;
}

.login-link:hover {
    color: white !important;
    background: var(--forest);
}

/* =========================================================
   PAGE
   ========================================================= */

.page {
    min-height: 100vh;
    padding: 120px 20px 55px;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* =========================================================
   CARD
   ========================================================= */

.register-card {
    width: 100%;
    max-width: 1050px;
    display: grid;
    grid-template-columns: .85fr 1.15fr;
    overflow: hidden;
    border-radius: 28px;
    background: white;
    box-shadow: var(--shadow);
    animation: fadeUp .7s ease;
}

/* =========================================================
   PANNEAU GAUCHE
   ========================================================= */

.brand-panel {
    position: relative;
    padding: 55px 42px;
    overflow: hidden;
    color: white;
    background:
        radial-gradient(
            circle at 15% 20%,
            rgba(87,151,103,.35),
            transparent 28%
        ),
        linear-gradient(
            145deg,
            var(--forest-dark),
            var(--forest)
        );
}

.brand-panel:before {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    right: -120px;
    top: -90px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50%;
}

.brand-panel:after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    left: -90px;
    bottom: -70px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 50%;
}

.brand-content {
    position: relative;
    z-index: 2;
}

.brand-icon {
    width: 65px;
    height: 65px;
    margin-bottom: 28px;
    border-radius: 19px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.20);
    font-size: 30px;
}

.brand-panel h1 {
    margin-bottom: 18px;
    font-family: "Playfair Display", serif;
    font-size: 39px;
    line-height: 1.1;
}

.brand-panel p {
    margin-bottom: 30px;
    color: rgba(255,255,255,.78);
    font-size: 12px;
    line-height: 1.9;
}

.benefit {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-top: 18px;
}

.benefit-icon {
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.13);
    font-size: 12px;
}

.benefit strong {
    display: block;
    margin-bottom: 3px;
    font-size: 11px;
}

.benefit span {
    color: rgba(255,255,255,.62);
    font-size: 9px;
    line-height: 1.5;
}

/* =========================================================
   FORMULAIRE
   ========================================================= */

.form-panel {
    padding: 45px 45px 40px;
}

.form-header {
    margin-bottom: 28px;
}

.form-header .eyebrow {
    color: var(--forest-light);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.7px;
    text-transform: uppercase;
}

.form-header h2 {
    margin-top: 7px;
    color: var(--forest-dark);
    font-family: "Playfair Display", serif;
    font-size: 31px;
}

.form-header p {
    margin-top: 7px;
    color: var(--muted);
    font-size: 10px;
    line-height: 1.6;
}

/* MESSAGE */

.alert {
    margin-bottom: 20px;
    padding: 13px 15px;
    border-radius: 11px;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.5;
}

.alert.error {
    color: var(--red);
    background: #fff1f1;
    border: 1px solid #f3cccc;
}

/* GRILLE */

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 17px 15px;
}

.field {
    position: relative;
}

.field.full {
    grid-column: 1 / -1;
}

.field label {
    display: block;
    margin-bottom: 7px;
    color: #344239;
    font-size: 10px;
    font-weight: 800;
}

.required {
    color: var(--gold);
}

.field input,
.field textarea {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 11px;
    outline: none;
    color: var(--text);
    background: #fbfdfb;
    transition: .3s;
    font-size: 11px;
}

.field input {
    height: 46px;
    padding: 0 14px;
}

.field textarea {
    min-height: 72px;
    padding: 13px 14px;
    resize: vertical;
}

.field input:focus,
.field textarea:focus {
    border-color: var(--forest-light);
    background: white;
    box-shadow:
        0 0 0 4px rgba(47,125,74,.08);
}

/* MOT DE PASSE */

.password-wrap {
    position: relative;
}

.password-wrap input {
    padding-right: 48px;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 15px;
    opacity: .65;
}

.password-help {
    margin-top: 6px;
    color: var(--muted);
    font-size: 8px;
}

/* FORCE MOT DE PASSE */

.password-strength {
    height: 4px;
    margin-top: 7px;
    overflow: hidden;
    border-radius: 10px;
    background: #e9eeea;
}

.password-strength span {
    display: block;
    width: 0;
    height: 100%;
    transition: .3s;
}

/* CONDITIONS */

.conditions {
    grid-column: 1 / -1;
    display: flex;
    gap: 9px;
    align-items: flex-start;
    margin-top: 2px;
}

.conditions input {
    margin-top: 2px;
    accent-color: var(--forest);
}

.conditions label {
    color: var(--muted);
    font-size: 9px;
    line-height: 1.6;
}

.conditions a {
    color: var(--forest);
    font-weight: 700;
}

/* BOUTON */

.submit-button {
    width: 100%;
    height: 52px;
    margin-top: 20px;
    border: 0;
    border-radius: 13px;
    color: white;
    background:
        linear-gradient(
            135deg,
            var(--forest-dark),
            var(--forest-light)
        );
    cursor: pointer;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .2px;
    box-shadow:
        0 10px 25px rgba(20,83,45,.20);
    transition: .3s;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow:
        0 15px 30px rgba(20,83,45,.26);
}

.bottom-link {
    margin-top: 19px;
    text-align: center;
    color: var(--muted);
    font-size: 10px;
}

.bottom-link a {
    color: var(--forest);
    font-weight: 800;
}

/* =========================================================
   FOOTER
   ========================================================= */

.footer {
    padding: 20px;
    color: rgba(255,255,255,.60);
    background: #062812;
    text-align: center;
    font-size: 9px;
}

/* =========================================================
   ANIMATIONS
   ========================================================= */

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 850px) {

    .register-card {
        grid-template-columns: 1fr;
        max-width: 650px;
    }

    .brand-panel {
        padding: 38px 32px;
    }

    .brand-panel h1 {
        font-size: 34px;
    }

    .form-panel {
        padding: 38px 30px;
    }
}

@media (max-width: 600px) {

    .navbar {
        min-height: 68px;
        padding: 0 14px;
    }

    .logo {
        font-size: 15px;
    }

    .logo-mark {
        width: 37px;
        height: 37px;
    }

    .nav-links a:not(.login-link) {
        display: none;
    }

    .page {
        padding: 95px 12px 35px;
    }

    .brand-panel {
        padding: 32px 25px;
    }

    .brand-panel h1 {
        font-size: 30px;
    }

    .form-panel {
        padding: 32px 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .field.full,
    .conditions {
        grid-column: auto;
    }

    .form-header h2 {
        font-size: 27px;
    }
}

</style>
</head>

<body>

<!-- =====================================================
     HEADER
     ===================================================== -->

<header class="header">

<nav class="navbar">

<a href="index.php" class="logo">

<span class="logo-mark">
    🌿
</span>

Jardin des Agriculteurs

</a>

<div class="nav-links">

<a href="index.php">
    Accueil
</a>

<a href="products.php">
    Produits
</a>

<a href="apropos.php">
    À propos
</a>

<a href="contact.php">
    Contact
</a>

<a href="connexion.php" class="login-link">
    Se connecter
</a>

</div>

</nav>

</header>


<!-- =====================================================
     PAGE
     ===================================================== -->

<main class="page">

<section class="register-card">


<!-- PANNEAU GAUCHE -->

<div class="brand-panel">

<div class="brand-content">

<div class="brand-icon">
    🌱
</div>

<h1>
    Bienvenue dans
    votre espace agricole.
</h1>

<p>
    Créez votre compte Jardin des Agriculteurs
    et profitez d'une expérience simple,
    moderne et adaptée à vos besoins.
</p>


<div class="benefit">

<div class="benefit-icon">
    ✓
</div>

<div>

<strong>
    Commandez facilement
</strong>

<span>
    Retrouvez vos produits et passez vos commandes
    depuis votre espace client.
</span>

</div>

</div>


<div class="benefit">

<div class="benefit-icon">
    ✓
</div>

<div>

<strong>
    Suivez vos commandes
</strong>

<span>
    Consultez l'évolution de vos achats et
    de vos livraisons.
</span>

</div>

</div>


<div class="benefit">

<div class="benefit-icon">
    ✓
</div>

<div>

<strong>
    Un espace sécurisé
</strong>

<span>
    Votre mot de passe est enregistré sous forme
    sécurisée dans la base de données.
</span>

</div>

</div>

</div>

</div>


<!-- FORMULAIRE -->

<div class="form-panel">

<div class="form-header">

<span class="eyebrow">
    Jardin des Agriculteurs
</span>

<h2>
    Créer un compte
</h2>

<p>
    Remplissez vos informations pour rejoindre
    notre marketplace agricole.
</p>

</div>


<?php if ($message !== ""): ?>

<div class="alert error">

<?= htmlspecialchars(
    $message,
    ENT_QUOTES,
    "UTF-8"
) ?>

</div>

<?php endif; ?>


<form
    method="POST"
    action="inscription.php"
    id="registerForm"
    autocomplete="on"
>

<div class="form-grid">


<!-- NOM -->

<div class="field">

<label for="nom">
    Nom <span class="required">*</span>
</label>

<input
    type="text"
    id="nom"
    name="nom"
    maxlength="100"
    required
    autocomplete="family-name"
    value="<?= htmlspecialchars(
        $old["nom"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- PRÉNOM -->

<div class="field">

<label for="prenom">
    Prénom <span class="required">*</span>
</label>

<input
    type="text"
    id="prenom"
    name="prenom"
    maxlength="100"
    required
    autocomplete="given-name"
    value="<?= htmlspecialchars(
        $old["prenom"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- EMAIL -->

<div class="field">

<label for="email">
    Adresse e-mail <span class="required">*</span>
</label>

<input
    type="email"
    id="email"
    name="email"
    maxlength="150"
    required
    autocomplete="email"
    placeholder="exemple@email.com"
    value="<?= htmlspecialchars(
        $old["email"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- TÉLÉPHONE -->

<div class="field">

<label for="telephone">
    Téléphone
</label>

<input
    type="tel"
    id="telephone"
    name="telephone"
    maxlength="30"
    autocomplete="tel"
    placeholder="+237 ..."
    value="<?= htmlspecialchars(
        $old["telephone"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- VILLE -->

<div class="field">

<label for="ville">
    Ville
</label>

<input
    type="text"
    id="ville"
    name="ville"
    maxlength="100"
    autocomplete="address-level2"
    placeholder="Votre ville"
    value="<?= htmlspecialchars(
        $old["ville"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- ADRESSE -->

<div class="field">

<label for="adresse">
    Adresse
</label>

<input
    type="text"
    id="adresse"
    name="adresse"
    autocomplete="street-address"
    placeholder="Quartier, rue..."
    value="<?= htmlspecialchars(
        $old["adresse"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>

</div>


<!-- MOT DE PASSE -->

<div class="field">

<label for="mot_de_passe">
    Mot de passe <span class="required">*</span>
</label>

<div class="password-wrap">

<input
    type="password"
    id="mot_de_passe"
    name="mot_de_passe"
    minlength="8"
    required
    autocomplete="new-password"
    placeholder="Minimum 8 caractères"
>

<button
    type="button"
    class="password-toggle"
    data-target="mot_de_passe"
    aria-label="Afficher ou masquer le mot de passe"
>
    👁
</button>

</div>

<div class="password-strength">
    <span id="strengthBar"></span>
</div>

<div class="password-help" id="strengthText">
    Minimum 8 caractères.
</div>

</div>


<!-- CONFIRMATION -->

<div class="field">

<label for="confirmation">
    Confirmer le mot de passe
    <span class="required">*</span>
</label>

<div class="password-wrap">

<input
    type="password"
    id="confirmation"
    name="confirmation"
    minlength="8"
    required
    autocomplete="new-password"
    placeholder="Répétez le mot de passe"
>

<button
    type="button"
    class="password-toggle"
    data-target="confirmation"
    aria-label="Afficher ou masquer la confirmation"
>
    👁
</button>

</div>

<div
    class="password-help"
    id="matchText"
>
</div>

</div>


<!-- CONDITIONS -->

<div class="conditions">

<input
    type="checkbox"
    id="conditions"
    name="conditions"
    required
>

<label for="conditions">

J'accepte les conditions d'utilisation
et la politique de confidentialité de
<strong>Jardin des Agriculteurs</strong>.

</label>

</div>

</div>


<button
    type="submit"
    class="submit-button"
>
    Créer mon compte
</button>

</form>


<div class="bottom-link">

Vous avez déjà un compte ?

<a href="connexion.php">
    Se connecter
</a>

</div>

</div>

</section>

</main>


<footer class="footer">

© <?= date("Y") ?>
Jardin des Agriculteurs —
Le prix, oui ! La qualité surtout !

</footer>


<script>

/* =========================================================
   AFFICHER / MASQUER LES MOTS DE PASSE
   ========================================================= */

const toggleButtons =
    document.querySelectorAll(".password-toggle");

toggleButtons.forEach(function(button) {

    button.addEventListener("click", function() {

        const targetId =
            button.getAttribute("data-target");

        const input =
            document.getElementById(targetId);

        if (input.type === "password") {

            input.type = "text";
            button.textContent = "🙈";

        } else {

            input.type = "password";
            button.textContent = "👁";

        }

    });

});


/* =========================================================
   FORCE DU MOT DE PASSE
   ========================================================= */

const passwordInput =
    document.getElementById("mot_de_passe");

const strengthBar =
    document.getElementById("strengthBar");

const strengthText =
    document.getElementById("strengthText");

passwordInput.addEventListener("input", function() {

    const password = passwordInput.value;

    let score = 0;

    if (password.length >= 8) {
        score++;
    }

    if (/[A-Z]/.test(password)) {
        score++;
    }

    if (/[0-9]/.test(password)) {
        score++;
    }

    if (/[^A-Za-z0-9]/.test(password)) {
        score++;
    }

    const widths = [
        "0%",
        "25%",
        "50%",
        "75%",
        "100%"
    ];

    strengthBar.style.width =
        widths[score];

    if (password.length === 0) {

        strengthText.textContent =
            "Minimum 8 caractères.";

    } else if (score <= 1) {

        strengthText.textContent =
            "Mot de passe faible.";

    } else if (score === 2) {

        strengthText.textContent =
            "Mot de passe moyen.";

    } else if (score === 3) {

        strengthText.textContent =
            "Mot de passe bon.";

    } else {

        strengthText.textContent =
            "Mot de passe fort.";

    }

});


/* =========================================================
   VÉRIFICATION DE LA CONFIRMATION
   ========================================================= */

const confirmationInput =
    document.getElementById("confirmation");

const matchText =
    document.getElementById("matchText");

function checkPasswordMatch() {

    if (confirmationInput.value === "") {

        matchText.textContent = "";
        return;
    }

    if (
        confirmationInput.value ===
        passwordInput.value
    ) {

        matchText.textContent =
            "✓ Les mots de passe correspondent.";

    } else {

        matchText.textContent =
            "✕ Les mots de passe ne correspondent pas.";

    }

}

confirmationInput.addEventListener(
    "input",
    checkPasswordMatch
);

passwordInput.addEventListener(
    "input",
    checkPasswordMatch
);


/* =========================================================
   VALIDATION AVANT ENVOI
   ========================================================= */

document
    .getElementById("registerForm")
    .addEventListener("submit", function(event) {

        if (
            passwordInput.value !==
            confirmationInput.value
        ) {

            event.preventDefault();

            alert(
                "Les deux mots de passe ne correspondent pas."
            );

            confirmationInput.focus();
        }

    });

</script>

</body>
</html>