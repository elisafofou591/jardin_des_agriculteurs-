<?php
session_start();

/*
 * JARDIN DES AGRICULTEURS
 * produits.php = EXPOSITION / CATALOGUE
 * gerer_produit.php = ADMINISTRATION
 * Un clic sur un produit ouvre detail_produit.php?id=...
 */

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
    die("Connexion à la base de données impossible. Vérifiez XAMPP et la base jardin_des_agriculteurs.");
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function fcfa($value): string {
    return number_format((float)$value, 0, ',', ' ') . ' FCFA';
}

/* Catégories du catalogue */
$categories = [
    'toutes' => ['nom' => 'Tous les produits', 'icone' => '🌱'],
    'insecticides' => ['nom' => 'Insecticides', 'icone' => '🐛'],
    'herbicides' => ['nom' => 'Herbicides', 'icone' => '🌿'],
    'fongicides' => ['nom' => 'Fongicides', 'icone' => '🍃'],
    'outils' => ['nom' => 'Outils agricoles', 'icone' => '🛠️'],
    'semences' => ['nom' => 'Semences', 'icone' => '🌾'],
    'engrais' => ['nom' => 'Engrais', 'icone' => '🌱']
];

$recherche = trim($_GET['recherche'] ?? '');
$categorieSlug = strtolower(trim($_GET['categorie'] ?? 'toutes'));
$tri = $_GET['tri'] ?? 'recent';

if (!array_key_exists($categorieSlug, $categories)) {
    $categorieSlug = 'toutes';
}

$where = ["p.statut = 'disponible'", "p.stock > 0"];
$params = [];

if ($recherche !== '') {
    $where[] = "(p.nom_produit LIKE ? OR p.reference LIKE ? OR p.description LIKE ? OR p.marque LIKE ? OR c.nom_categorie LIKE ?)";
    $like = "%{$recherche}%";
    $params = [$like, $like, $like, $like, $like];
}

if ($categorieSlug !== 'toutes') {
    $where[] = "LOWER(c.nom_categorie) = ?";
    $params[] = strtolower($categories[$categorieSlug]['nom']);
}

switch ($tri) {
    case 'prix_croissant':
        $orderSql = "p.prix ASC, p.nom_produit ASC";
        break;
    case 'prix_decroissant':
        $orderSql = "p.prix DESC, p.nom_produit ASC";
        break;
    case 'nom':
        $orderSql = "p.nom_produit ASC";
        break;
    default:
        $orderSql = "p.id_produit DESC";
        $tri = 'recent';
}

$sql = "
    SELECT
        p.id_produit, p.id_categorie, p.id_fournisseur,
        p.nom_produit, p.reference, p.description, p.marque,
        p.prix, p.stock, p.unite, p.images, p.statut,
        c.nom_categorie,
        f.nom_fournisseur
    FROM produits p
    LEFT JOIN categories c ON p.id_categorie = c.id_categorie
    LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id_fournisseur
    WHERE " . implode(" AND ", $where) . "
    ORDER BY {$orderSql}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();
$totalProduits = count($produits);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produits | Jardin des Agriculteurs</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--green:#087443;--dark:#063b2a;--soft:#eaf6ee;--text:#19382a;--muted:#708078;--border:#e2ebe5;--bg:#f6faf7}
body{font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
a{text-decoration:none;color:inherit}.container{width:min(1180px,calc(100% - 34px));margin:auto}
.navbar{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.97);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
.nav-inner{min-height:76px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.logo{display:flex;align-items:center;gap:11px;font-weight:900;color:var(--dark)}
.logo-mark{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;color:#fff;background:linear-gradient(135deg,var(--dark),var(--green));font-size:21px;box-shadow:0 8px 20px #08744338}
.logo-text small{display:block;color:var(--muted);font-size:10px;margin-top:2px;font-weight:600}
.nav-links{display:flex;align-items:center;gap:7px}.nav-links a{padding:10px 13px;border-radius:10px;font-size:14px;font-weight:700;color:#426052;transition:.2s}
.nav-links a:hover,.nav-links a.active{background:var(--soft);color:var(--green)}.cart-btn{background:var(--green)!important;color:#fff!important}
.hero{margin-top:28px;border-radius:26px;min-height:285px;padding:42px;display:flex;align-items:center;justify-content:space-between;gap:30px;overflow:hidden;position:relative;background:radial-gradient(circle at 90% 20%,#ffffff33,transparent 28%),linear-gradient(135deg,#063b2a,#087443 58%,#0c8a50);color:#fff;box-shadow:0 12px 35px #103e2714}
.hero-content{position:relative;z-index:1;max-width:720px}.eyebrow{display:inline-block;background:#ffffff21;border:1px solid #ffffff2e;padding:7px 12px;border-radius:30px;font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;margin-bottom:13px}
.hero h1{font-size:clamp(30px,4vw,46px);line-height:1.08;margin-bottom:13px}.hero p{color:#ffffffd6;max-width:650px}
.hero-badge{position:relative;z-index:1;width:145px;height:145px;flex:0 0 145px;border-radius:50%;display:grid;place-items:center;text-align:center;background:#ffffff1a;border:1px solid #ffffff33;font-size:14px;font-weight:800}
.hero-badge span{display:block;font-size:40px;margin-bottom:2px}
.toolbar{margin-top:22px;background:#fff;border:1px solid var(--border);border-radius:20px;padding:18px;box-shadow:0 12px 35px #103e2714}
.search-form{display:grid;grid-template-columns:1fr 180px 125px;gap:10px}.search-box,.select-box{border:1px solid var(--border);border-radius:12px;background:#fbfdfb;padding:12px 14px;outline:none;color:var(--text);font-size:14px}
.search-box:focus,.select-box:focus{border-color:var(--green);box-shadow:0 0 0 3px #08744317}.btn{border:0;cursor:pointer;border-radius:12px;padding:12px 16px;font-weight:800}.btn-primary{background:var(--green);color:#fff}
.categories{display:flex;flex-wrap:wrap;gap:9px;margin-top:15px}.category{padding:9px 13px;border:1px solid var(--border);border-radius:30px;background:#fff;color:#466354;font-size:12px;font-weight:800;transition:.2s}
.category:hover,.category.active{background:var(--soft);border-color:#bde0ca;color:var(--green)}
.section-head{margin:32px 0 18px;display:flex;align-items:end;justify-content:space-between}.section-head h2{color:var(--dark);font-size:25px}.section-head p{color:var(--muted);font-size:13px;margin-top:4px}
.products{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding-bottom:45px}
.product-card{background:#fff;border:1px solid var(--border);border-radius:20px;overflow:hidden;box-shadow:0 8px 28px #103e270e;transition:.25s}.product-card:hover{transform:translateY(-5px);box-shadow:0 18px 42px #103e271c}
.product-image{height:205px;background:linear-gradient(135deg,#eaf6ee,#f6fbf7);display:flex;align-items:center;justify-content:center;overflow:hidden}
.product-image img{width:100%;height:100%;object-fit:cover}.no-image{text-align:center;color:#5d806c}.no-image span{display:block;font-size:50px}
.product-body{padding:18px}.product-category{display:inline-block;padding:6px 9px;background:var(--soft);color:var(--green);border-radius:20px;font-size:10px;font-weight:900;text-transform:uppercase}
.product-body h3{margin:11px 0 6px;color:#173e2c;font-size:18px}.reference{color:#8a978f;font-size:11px}
.description{color:#718078;font-size:13px;margin:11px 0;min-height:39px}.meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:13px 0}
.meta-item{background:#f7faf8;border-radius:10px;padding:9px;font-size:11px}.meta-item strong{display:block;color:#375846;margin-top:2px}
.price-row{display:flex;justify-content:space-between;align-items:center;gap:10px;border-top:1px solid var(--border);padding-top:14px}.price{color:var(--green);font-weight:900;font-size:18px}.price small{display:block;color:#7a8980;font-size:10px;font-weight:500}
.detail-btn{padding:10px 13px;border-radius:10px;background:var(--green);color:#fff;font-size:12px;font-weight:900}
.empty{grid-column:1/-1;background:#fff;border:1px dashed #cbdad0;border-radius:20px;padding:55px 20px;text-align:center}.empty-icon{font-size:50px}.empty h3{color:var(--dark);margin:8px 0 5px}.empty p{color:var(--muted);font-size:13px}
footer{background:var(--dark);color:#fff}.footer{padding:35px 0 20px;display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:30px}.footer h3{margin-bottom:10px}.footer p,.footer a{color:#ffffffb8;font-size:13px}.footer a:hover{color:#fff}.footer-bottom{border-top:1px solid #ffffff1f;padding:15px 0;color:#ffffff94;font-size:11px}
@media(max-width:950px){.products{grid-template-columns:repeat(2,1fr)}.search-form{grid-template-columns:1fr 1fr}.search-form .btn{grid-column:1/-1}}
@media(max-width:720px){.nav-inner{flex-direction:column;padding:13px 0}.nav-links{width:100%;overflow-x:auto;justify-content:flex-start}.hero{padding:30px 24px}.hero-badge{display:none}.products{grid-template-columns:1fr}.footer{grid-template-columns:1fr}}
@media(max-width:520px){.search-form{grid-template-columns:1fr}.search-form .btn{grid-column:auto}.container{width:min(100% - 24px,1180px)}}
</style>
</head>
<body>

<header class="navbar">
<div class="container nav-inner">
<a href="index.php" class="logo">
<span class="logo-mark">JA</span>
<span class="logo-text">Jardin des Agriculteurs<small>Le prix, oui ! La qualité surtout !</small></span>
</a>
<nav class="nav-links">
<a href="index.php">Accueil</a>
<a href="produits.php" class="active">Produits</a>
<a href="a_propos.php">À propos</a>
<a href="contact.php">Contact</a>
<a href="espace_client.php">Mon espace</a>
<a href="panier.php" class="cart-btn">🛒 Panier</a>
<?php if(isset($_SESSION['id_utilisateur'])): ?>
    <?php if(in_array(strtolower(trim($_SESSION['role'] ?? '')),['administrateur','admin'],true)): ?>
        <a href="dashboard.php">Dashboard</a>
    <?php else: ?>
        
        <a href="espace_client.php">Mon espace</a>
    <?php endif; ?>
<?php else: ?>
    <a href="connexion.php">Connexion</a>
<?php endif; ?>
</nav>
</div>
</header>

<main class="container">

<section class="hero">
<div class="hero-content">
<span class="eyebrow">🌱 Exposition des produits</span>
<h1>Découvrez nos solutions agricoles</h1>
<p>Parcourez les produits disponibles au Jardin des Agriculteurs, consultez leurs informations, leurs prix, leur disponibilité et leur fournisseur avant de passer votre commande.</p>
</div>
<div class="hero-badge"><div><span>🌾</span>Catalogue<br>agricole</div></div>
</section>

<section class="toolbar">
<form method="GET" action="produits.php" class="search-form">
<input class="search-box" type="search" name="recherche" value="<?=h($recherche)?>" placeholder="Rechercher un produit, une référence, une marque...">
<select class="select-box" name="tri">
<option value="recent" <?=$tri==='recent'?'selected':''?>>Plus récents</option>
<option value="nom" <?=$tri==='nom'?'selected':''?>>Nom A → Z</option>
<option value="prix_croissant" <?=$tri==='prix_croissant'?'selected':''?>>Prix croissant</option>
<option value="prix_decroissant" <?=$tri==='prix_decroissant'?'selected':''?>>Prix décroissant</option>
</select>
<button class="btn btn-primary" type="submit">🔎 Rechercher</button>
</form>
<div class="categories">
<?php foreach($categories as $slug=>$cat): ?>
<a href="produits.php?categorie=<?=urlencode($slug)?>" class="category <?=$categorieSlug===$slug?'active':''?>">
<?=h($cat['icone'])?> <?=h($cat['nom'])?>
</a>
<?php endforeach; ?>
</div>
</section>

<section class="section-head">
<div>
<h2><?=h($categories[$categorieSlug]['nom'])?></h2>
<p><?=$totalProduits?> produit<?=($totalProduits>1?'s':'')?> disponible<?=($totalProduits>1?'s':'')?> dans le catalogue.</p>
</div>
</section>

<section class="products">
<?php if(empty($produits)): ?>
<div class="empty">
<div class="empty-icon">🌱</div>
<h3>Aucun produit trouvé</h3>
<p>Aucun produit disponible ne correspond à votre recherche ou à cette catégorie.</p>
</div>
<?php else: ?>
<?php foreach($produits as $produit): ?>
<?php



$image=trim((string)($produit['images']??''));
if($image!==''){$image='uploads/produits/'.$image;}
$description=trim((string)($produit['description']??''));
if($description==='') $description='Produit agricole disponible au Jardin des Agriculteurs.';
if(strlen($description)>105) $description=substr($description,0,105).'...';
$nomCategorie=$produit['nom_categorie']??'Produit agricole';
$iconeCategorie='🌱';
foreach($categories as $cat){if(strtolower($cat['nom'])===strtolower($nomCategorie)){$iconeCategorie=$cat['icone'];break;}}
$stock=(int)($produit['stock']??0);
$unite=trim((string)($produit['unite']??'unité'));
?>
<article class="product-card">
<a href="detail_produit.php?id=<?=intval($produit['id_produit'])?>">
<div class="product-image">
<?php if($image!==''): ?>
<img src="<?=h($image)?>" alt="<?=h($produit['nom_produit'])?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
<div class="no-image" style="display:none"><span><?=h($iconeCategorie)?></span>Image indisponible</div>
<?php else: ?>
<div class="no-image"><span><?=h($iconeCategorie)?></span>Produit agricole</div>
<?php endif; ?>
</div>
</a>
<div class="product-body">
<span class="product-category"><?=h($nomCategorie)?></span>
<h3><?=h($produit['nom_produit'])?></h3>
<?php if(!empty($produit['reference'])): ?><div class="reference">Réf. <?=h($produit['reference'])?></div><?php endif; ?>
<p class="description"><?=h($description)?></p>
<div class="meta">
<div class="meta-item">Disponibilité<strong><?=$stock>0?'Disponible':'Rupture'?></strong></div>
<div class="meta-item">Fournisseur<strong><?=h($produit['nom_fournisseur']??'Non renseigné')?></strong></div>
</div>
<div class="price-row">
<div class="price"><?=fcfa($produit['prix'])?><small><?=h($unite)?></small></div>
<a href="detail_produit.php?id=<?=intval($produit['id_produit'])?>" class="detail-btn">Voir le produit →</a>
</div>
</div>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>
</main>

<footer>
<div class="container">
<div class="footer">
<div><h3>Jardin des Agriculteurs</h3><p>Votre espace de découverte et de commande de produits et solutions agricoles.</p></div>
<div><h3>Navigation</h3><p><a href="index.php">Accueil</a></p><p><a href="produits.php">Produits</a></p><p><a href="a_propos.php">À propos</a></p><p><a href="contact.php">Contact</a></p></div>
<div><h3>Mon espace</h3><p><a href="panier.php">Mon panier</a></p>     <p><a href="espace_client.php">Mon espace</a></p>              <p><a href="commande.php">Mes commandes</a></p><p><a href="connexion.php">Connexion</a></p></div>

</div>
<div class="footer-bottom">© <?=date('Y')?> Jardin des Agriculteurs — « Le prix, oui ! La qualité surtout ! »</div>
</div>
</footer>
</body>
</html>