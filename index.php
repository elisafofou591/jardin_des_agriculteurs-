<?php
session_start();
 
$role = strtolower(trim($_SESSION['role'] ?? $_SESSION['user_role'] ?? ''));
$nomUtilisateur = trim($_SESSION['nom'] ?? $_SESSION['nom_utilisateur'] ?? $_SESSION['prenom'] ?? '');
 
$estAdmin = in_array($role, ['administrateur', 'admin'], true);
$estClient = in_array($role, ['client'], true);
 
if ($nomUtilisateur === '') {
    $nomUtilisateur = $estAdmin ? 'Administrateur' : ($estClient ? 'Client' : '');
}
 
$espaceUrl = $estAdmin ? 'dashboard.php' : ($estClient ? 'espace_client.php' : 'connexion.php');
$espaceTexte = $estAdmin ? 'Mon tableau de bord' : ($estClient ? 'Mon espace client' : 'Se connecter');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Jardin des Agriculteurs — présentation, exposition et commande de produits agricoles.">
<title>Jardin des Agriculteurs | Accueil</title>
 
<style>
:root{
    --vert:#176b3a;
    --vert-fonce:#0b4325;
    --vert-clair:#2e9b5f;
    --bleu:#1261a0;
    --or:#d9a441;
    --blanc:#fff;
    --texte:#183329;
    --gris:#6b7d74;
    --fond:#f5faf7;
    --ombre:0 18px 45px rgba(11,67,37,.13);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
    font-family:Inter,Segoe UI,Arial,sans-serif;
    color:var(--texte);
    background:var(--fond);
    line-height:1.6;
}
a{text-decoration:none;color:inherit}
.container{width:min(1180px,92%);margin:auto}
 
/* NAVIGATION */
header{
    position:fixed;
    top:0;left:0;width:100%;
    z-index:1000;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(23,107,58,.09);
}
.nav{
    height:78px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:22px;
}
.brand{display:flex;align-items:center;gap:11px;font-weight:900}
.logo{
    width:48px;height:48px;border-radius:15px;
    display:grid;place-items:center;
    color:#fff;font-size:21px;letter-spacing:-1px;
    background:linear-gradient(145deg,var(--vert),var(--bleu));
    box-shadow:0 9px 20px rgba(18,97,160,.2);
}
.brand span{font-size:17px;color:var(--vert-fonce)}
.brand small{display:block;font-size:10px;color:var(--gris);font-weight:600;letter-spacing:.5px}
.navlinks{display:flex;align-items:center;gap:7px;list-style:none}
.navlinks a{
    padding:10px 12px;border-radius:11px;font-weight:700;font-size:14px;
    transition:.25s;
}
.navlinks a:hover{background:#eaf6ee;color:var(--vert)}
.navcta{
    background:var(--vert)!important;color:#fff!important;
    box-shadow:0 8px 18px rgba(23,107,58,.2);
}
.menu{
    display:none;border:0;background:#eaf6ee;color:var(--vert);
    width:44px;height:44px;border-radius:12px;font-size:23px;cursor:pointer;
}
 
/* HERO */
.hero{
    min-height:100vh;
    padding:135px 0 80px;
    position:relative;overflow:hidden;
    background:
      radial-gradient(circle at 8% 20%,rgba(46,155,95,.17),transparent 30%),
      radial-gradient(circle at 90% 20%,rgba(18,97,160,.14),transparent 28%),
      linear-gradient(135deg,#f8fdf9 0%,#eef8f2 48%,#edf6fb 100%);
}
.hero:before,.hero:after{
    content:"";position:absolute;border-radius:50%;filter:blur(2px);
    pointer-events:none;
}
.hero:before{width:280px;height:280px;background:rgba(46,155,95,.12);right:-100px;bottom:-80px}
.hero:after{width:190px;height:190px;background:rgba(217,164,65,.10);left:-80px;top:120px}
.heroGrid{
    min-height:610px;display:grid;grid-template-columns:1.02fr .98fr;
    gap:55px;align-items:center;position:relative;z-index:1;
}
.badge{
    display:inline-flex;align-items:center;gap:8px;
    padding:8px 13px;border-radius:999px;background:#fff;
    color:var(--vert);font-weight:800;font-size:12px;
    box-shadow:0 8px 25px rgba(23,107,58,.09);
}
h1{
    margin:20px 0 18px;
    font-size:clamp(42px,6vw,76px);
    line-height:1.02;letter-spacing:-3px;color:var(--vert-fonce);
}
h1 .accent{color:var(--bleu)}
.heroText{font-size:18px;color:#536960;max-width:650px}
.slogan{
    margin:20px 0 28px;padding-left:15px;border-left:4px solid var(--or);
    font-weight:800;color:#355449;font-size:16px;
}
.actions{display:flex;gap:12px;flex-wrap:wrap}
.btn{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:14px 20px;border-radius:14px;font-weight:800;
    transition:.25s;border:1px solid transparent;
}
.btn:hover{transform:translateY(-3px)}
.btn-primary{background:var(--vert);color:#fff;box-shadow:0 12px 25px rgba(23,107,58,.22)}
.btn-secondary{background:#fff;color:var(--vert);border-color:#d8e8de}
 
/* VISUEL HERO */
.heroVisual{position:relative;min-height:520px;display:grid;place-items:center}
.orbit{
    position:absolute;width:440px;height:440px;border:1px dashed rgba(23,107,58,.23);
    border-radius:50%;animation:spin 24s linear infinite;
}
.orbit i{
    position:absolute;width:18px;height:18px;border-radius:50%;
    background:var(--or);box-shadow:0 0 0 7px rgba(217,164,65,.12)
}
.orbit i:nth-child(1){top:30px;left:95px}.orbit i:nth-child(2){right:15px;top:205px}.orbit i:nth-child(3){bottom:35px;left:125px}
.farmCard{
    width:min(430px,88vw);min-height:390px;border-radius:32px;
    background:linear-gradient(145deg,#ffffff,#edf8f1);
    border:1px solid rgba(255,255,255,.9);
    box-shadow:0 28px 70px rgba(11,67,37,.18);
    position:relative;overflow:hidden;padding:28px;
}
.farmCard:before{
    content:"";position:absolute;width:260px;height:260px;border-radius:50%;
    background:rgba(46,155,95,.14);right:-80px;top:-80px;
}
.cardTop{display:flex;justify-content:space-between;align-items:center;position:relative}
.jaBig{
    width:76px;height:76px;border-radius:24px;display:grid;place-items:center;
    color:#fff;font-weight:950;font-size:31px;
    background:linear-gradient(145deg,var(--vert),var(--bleu));
}
.leaf{font-size:42px}
.field{
    position:absolute;left:0;right:0;bottom:0;height:170px;
    background:linear-gradient(175deg,transparent 2%,#dcefdc 3%);
    clip-path:polygon(0 40%,18% 20%,37% 40%,57% 13%,76% 37%,100% 10%,100% 100%,0 100%);
}
.crop{
    position:absolute;bottom:58px;left:58px;font-size:62px;filter:drop-shadow(0 8px 8px rgba(0,0,0,.1));
    animation:float 4s ease-in-out infinite;
}
.infoPill{
    position:absolute;right:22px;bottom:35px;background:#fff;padding:12px 15px;
    border-radius:16px;box-shadow:0 10px 25px rgba(11,67,37,.13);font-size:12px;font-weight:800;
}
.infoPill strong{display:block;color:var(--vert);font-size:17px}
 
/* SECTIONS */
section{padding:90px 0}
.sectionHead{text-align:center;max-width:700px;margin:0 auto 42px}
.kicker{text-transform:uppercase;letter-spacing:2px;font-size:11px;font-weight:900;color:var(--bleu)}
.sectionHead h2{font-size:clamp(30px,4vw,46px);color:var(--vert-fonce);margin:8px 0 10px}
.sectionHead p{color:var(--gris)}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.card{
    background:#fff;padding:28px;border-radius:23px;border:1px solid #e3eee8;
    box-shadow:var(--ombre);transition:.3s;
}
.card:hover{transform:translateY(-7px)}
.icon{
    width:55px;height:55px;border-radius:17px;display:grid;place-items:center;
    background:#eaf6ee;font-size:25px;margin-bottom:18px
}
.card h3{margin-bottom:8px;color:var(--vert-fonce)}
.card p{color:var(--gris);font-size:14px}
.categories{background:#fff}
.catGrid{display:grid;grid-template-columns:repeat(6,1fr);gap:13px}
.cat{
    padding:20px 10px;text-align:center;border:1px solid #e2eee7;border-radius:18px;
    background:#fbfefc;transition:.25s;font-weight:800;font-size:13px
}
.cat:hover{transform:translateY(-5px);border-color:#a8d4b8;background:#eff9f2}
.cat span{display:block;font-size:30px;margin-bottom:8px}
 
.cta{
    padding:70px 0;
    background:linear-gradient(135deg,var(--vert-fonce),var(--vert),var(--bleu));
    color:#fff;position:relative;overflow:hidden
}
.ctaBox{display:flex;justify-content:space-between;align-items:center;gap:30px}
.cta h2{font-size:clamp(30px,4vw,48px);line-height:1.1}
.cta p{opacity:.85;margin-top:8px}
.btn-light{background:#fff;color:var(--vert-fonce)}
 
footer{background:#092d1a;color:#d8eadf;padding:45px 0 25px}
.footerGrid{display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:35px}
footer h3{color:#fff;margin-bottom:12px}
footer p,footer a{font-size:13px;color:#a9c3b3}
footer a:hover{color:#fff}
.footerBottom{margin-top:30px;padding-top:18px;border-top:1px solid rgba(255,255,255,.12);font-size:12px;display:flex;justify-content:space-between;gap:15px}
 
/* ANIMATIONS */
@keyframes float{50%{transform:translateY(-13px)}}
@keyframes spin{to{transform:rotate(360deg)}}
.reveal{opacity:0;transform:translateY(25px);transition:opacity .7s,transform .7s}
.reveal.show{opacity:1;transform:none}
 
/* RESPONSIVE */
@media(max-width:920px){
    .navlinks{
        display:none;position:absolute;top:78px;left:4%;right:4%;
        background:#fff;border-radius:18px;padding:12px;box-shadow:var(--ombre);
        flex-direction:column;align-items:stretch
    }
    .navlinks.open{display:flex}
    .navlinks a{display:block;text-align:center}
    .menu{display:block}
    .heroGrid{grid-template-columns:1fr;text-align:center;gap:25px}
    .heroText{margin:auto}
    .slogan{display:inline-block;text-align:left}
    .actions{justify-content:center}
    .heroVisual{min-height:430px}
    .cards{grid-template-columns:1fr}
    .catGrid{grid-template-columns:repeat(3,1fr)}
    .ctaBox{flex-direction:column;text-align:center}
}
@media(max-width:560px){
    .nav{height:70px}
    .logo{width:43px;height:43px}
    .brand span{font-size:14px}
    .brand small{font-size:8px}
    .hero{padding-top:110px}
    h1{letter-spacing:-2px}
    .heroVisual{min-height:370px}
    .farmCard{min-height:330px;padding:22px}
    .orbit{width:310px;height:310px}
    .catGrid{grid-template-columns:repeat(2,1fr)}
    section{padding:65px 0}
    .footerGrid{grid-template-columns:1fr}
    .footerBottom{flex-direction:column;text-align:center}
}
</style>
</head>
 
<body>
 
<header>
<nav class="nav container">
    <a href="index.php" class="brand" aria-label="Accueil Jardin des Agriculteurs">
        <div class="logo">JA</div>
        <div>
            <span>Jardin des Agriculteurs</span>
            <small>Le prix, oui ! La qualité surtout !</small>
        </div>
    </a>
 
    <button class="menu" id="menuBtn" aria-label="Ouvrir le menu">☰</button>
 
    <ul class="navlinks" id="navLinks">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="produits.php">Produits</a></li>
        <li><a href="a_propos.php">À propos</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="panier.php">Panier 🛒</a></li>
        <li><a href="<?= htmlspecialchars($espaceUrl) ?>" class="navcta"><?= htmlspecialchars($espaceTexte) ?></a></li>
    </ul>
</nav>
</header>
 
<main>
 
<section class="hero" id="accueil">
<div class="container heroGrid">
    <div class="reveal">
        <span class="badge">🌱 Une agriculture connectée, simple et accessible</span>
 
        <h1>Bienvenue au <span class="accent">Jardin des Agriculteurs</span></h1>
 
        <p class="heroText">
            Découvrez un espace moderne dédié à l'exposition et à la commande
            de produits agricoles. Explorez notre catalogue, consultez les
            informations essentielles et préparez vos commandes en quelques clics.
        </p>
 
        <div class="slogan">« Le prix, oui ! La qualité surtout ! »</div>
 
        <div class="actions">
            <a href="produits.php" class="btn btn-primary">🌿 Découvrir nos produits</a>
            <a href="a_propos.php" class="btn btn-secondary">En savoir plus →</a>
        </div>
    </div>
 
    <div class="heroVisual reveal">
        <div class="orbit"><i></i><i></i><i></i></div>
        <div class="farmCard">
            <div class="cardTop">
                <div class="jaBig">JA</div>
                <div class="leaf">🌿</div>
            </div>
            <div style="position:relative;margin-top:28px">
                <h2 style="font-size:28px;color:var(--vert-fonce)">Du jardin à votre commande</h2>
                <p style="color:var(--gris);margin-top:8px">
                    Des produits présentés clairement, un parcours simple et
                    une expérience pensée pour le client.
                </p>
            </div>
            <div class="field"></div>
            <div class="crop">🌾</div>
            <div class="infoPill"><strong>100%</strong> expérience digitale</div>
        </div>
    </div>
</div>
</section>
 
<section>
<div class="container">
    <div class="sectionHead reveal">
        <div class="kicker">Notre vision</div>
        <h2>Une plateforme pensée pour rapprocher agriculture et numérique</h2>
        <p>Une interface claire pour présenter les produits, faciliter les commandes et améliorer la circulation de l'information.</p>
    </div>
 
    <div class="cards">
        <article class="card reveal">
            <div class="icon">🌱</div>
            <h3>Exposition des produits</h3>
            <p>Un catalogue moderne pour découvrir les produits agricoles, leurs catégories, prix, disponibilités et informations utiles.</p>
        </article>
        <article class="card reveal">
            <div class="icon">🛒</div>
            <h3>Commande simplifiée</h3>
            <p>Le client peut parcourir le catalogue, consulter les détails et poursuivre son parcours vers le panier et la commande.</p>
        </article>
        <article class="card reveal">
            <div class="icon">📊</div>
            <h3>Gestion centralisée</h3>
            <p>Les espaces administrateur et client sont séparés afin de conserver une gestion organisée et sécurisée.</p>
        </article>
    </div>
</div>
</section>
 
<section class="categories">
<div class="container">
    <div class="sectionHead reveal">
        <div class="kicker">Catalogue</div>
        <h2>Explorez nos univers agricoles</h2>
        <p>Retrouvez rapidement les grandes familles de produits proposées par le Jardin des Agriculteurs.</p>
    </div>
 
    <div class="catGrid">
        <a class="cat reveal" href="produits.php?categorie=insecticides"><span>🐛</span>Insecticides</a>
        <a class="cat reveal" href="produits.php?categorie=herbicides"><span>🌿</span>Herbicides</a>
        <a class="cat reveal" href="produits.php?categorie=fongicides"><span>🍃</span>Fongicides</a>
        <a class="cat reveal" href="produits.php?categorie=outils"><span>🛠️</span>Outils agricoles</a>
        <a class="cat reveal" href="produits.php?categorie=semences"><span>🌾</span>Semences</a>
        <a class="cat reveal" href="produits.php?categorie=engrais"><span>🌱</span>Engrais</a>
    </div>
</div>
</section>
 
<section class="cta">
<div class="container ctaBox">
    <div>
        <h2>Prêt à découvrir<br>le Jardin des Agriculteurs ?</h2>
        <p>Commencez par explorer notre catalogue.</p>
    </div>
    <a href="produits.php" class="btn btn-light">Voir les produits →</a>
</div>
</section>
 
</main>
 
<footer>
<div class="container">
    <div class="footerGrid">
        <div>
            <h3><span class="logo" style="display:inline-grid;width:38px;height:38px;font-size:16px;border-radius:11px;margin-right:8px;vertical-align:middle">JA</span> Jardin des Agriculteurs</h3>
            <p>Une solution web moderne pour l'exposition, la consultation et la commande de produits agricoles.</p>
        </div>
        <div>
            <h3>Navigation</h3>
            <p><a href="index.php">Accueil</a></p>
            <p><a href="produits.php">Produits</a></p>
            <p><a href="a_propos.php">À propos</a></p>
            <p><a href="contact.php">Contact</a></p>
        </div>
        <div>
            <h3>Votre espace</h3>
            <p><a href="panier.php">Mon panier</a></p>
            <p><a href="<?= htmlspecialchars($espaceUrl) ?>"><?= htmlspecialchars($espaceTexte) ?></a></p>
        </div>
    </div>
 
    <div class="footerBottom">
        <span>© <?= date('Y') ?> Jardin des Agriculteurs — Tous droits réservés.</span>
        <span>« Le prix, oui ! La qualité surtout ! »</span>
    </div>
</div>
</footer>
 
<script>
const menuBtn = document.getElementById('menuBtn');
const navLinks = document.getElementById('navLinks');
 
menuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    menuBtn.textContent = navLinks.classList.contains('open') ? '✕' : '☰';
});
 
document.querySelectorAll('#navLinks a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        menuBtn.textContent = '☰';
    });
});
 
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
            observer.unobserve(entry.target);
        }
    });
}, {threshold: 0.12});
 
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
 
</body>
</html>