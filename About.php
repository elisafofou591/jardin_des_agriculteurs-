<?php
session_start();
 
$role = strtolower(trim($_SESSION['role'] ?? $_SESSION['user_role'] ?? ''));
$nomUtilisateur = trim($_SESSION['nom'] ?? $_SESSION['nom_utilisateur'] ?? $_SESSION['prenom'] ?? '');
 
$estAdmin = in_array($role, ['administrateur', 'admin'], true);
$estClient = ($role === 'client');
 
$espaceUrl = $estAdmin ? 'dashboard.php' : ($estClient ? 'espace_client.php' : 'connexion.php');
$espaceTexte = $estAdmin ? 'Tableau de bord' : ($estClient ? 'Espace client' : 'Se connecter');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Découvrez la vision, les valeurs et l'ambition du Jardin des Agriculteurs.">
<title>À propos de nous | Jardin des Agriculteurs</title>
 
<style>
:root{
    --vert:#176b3a;
    --vert-fonce:#0a3d23;
    --vert-clair:#39a96b;
    --bleu:#1261a0;
    --or:#d8a33d;
    --fond:#f4faf6;
    --blanc:#fff;
    --texte:#17352a;
    --gris:#667970;
    --ligne:#deebe3;
    --ombre:0 18px 50px rgba(10,61,35,.12);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
    font-family:Inter,Segoe UI,Arial,sans-serif;
    color:var(--texte);background:var(--fond);line-height:1.65;
}
a{text-decoration:none;color:inherit}
.container{width:min(1180px,92%);margin:auto}
 
/* NAVIGATION */
header{
    position:fixed;top:0;left:0;width:100%;z-index:1000;
    background:rgba(255,255,255,.93);
    backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(23,107,58,.08);
}
.nav{
    height:78px;display:flex;align-items:center;
    justify-content:space-between;gap:20px;
}
.brand{display:flex;align-items:center;gap:11px;font-weight:900}
.logo{
    width:48px;height:48px;border-radius:15px;display:grid;place-items:center;
    color:#fff;font-size:21px;font-weight:950;letter-spacing:-1px;
    background:linear-gradient(145deg,var(--vert),var(--bleu));
    box-shadow:0 9px 22px rgba(18,97,160,.20);
}
.brandName{font-size:17px;color:var(--vert-fonce)}
.brand small{display:block;font-size:10px;color:var(--gris);font-weight:600}
.navlinks{display:flex;align-items:center;gap:6px;list-style:none}
.navlinks a{
    padding:10px 12px;border-radius:11px;font-weight:750;font-size:14px;
    transition:.25s;
}
.navlinks a:hover{background:#eaf6ee;color:var(--vert)}
.navcta{background:var(--vert)!important;color:#fff!important}
.menu{
    display:none;border:0;background:#eaf6ee;color:var(--vert);
    width:44px;height:44px;border-radius:12px;font-size:22px;cursor:pointer;
}
 
/* HERO */
.hero{
    min-height:90vh;padding:145px 0 90px;position:relative;overflow:hidden;
    background:
      radial-gradient(circle at 8% 22%,rgba(57,169,107,.16),transparent 30%),
      radial-gradient(circle at 90% 18%,rgba(18,97,160,.13),transparent 28%),
      linear-gradient(135deg,#f9fdf9,#eef8f2 52%,#edf6fb);
}
.heroGrid{
    min-height:560px;display:grid;grid-template-columns:1fr .95fr;
    gap:65px;align-items:center;
}
.eyebrow{
    display:inline-flex;padding:8px 13px;border-radius:999px;
    background:#fff;color:var(--vert);font-size:11px;font-weight:900;
    letter-spacing:1.4px;text-transform:uppercase;box-shadow:0 8px 25px rgba(10,61,35,.07);
}
h1{
    font-size:clamp(42px,6vw,73px);line-height:1.03;
    letter-spacing:-3px;color:var(--vert-fonce);margin:19px 0 18px;
}
h1 span{color:var(--bleu)}
.heroLead{font-size:18px;color:#566a61;max-width:680px}
.slogan{
    margin-top:22px;padding-left:15px;border-left:4px solid var(--or);
    color:#38584a;font-weight:850;font-size:16px;
}
.heroActions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
.btn{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:14px 20px;border-radius:14px;font-weight:850;transition:.25s;
}
.btn:hover{transform:translateY(-3px)}
.primary{background:var(--vert);color:#fff;box-shadow:0 12px 25px rgba(23,107,58,.20)}
.secondary{background:#fff;color:var(--vert);border:1px solid var(--ligne)}
 
/* VISUEL */
.visual{position:relative;min-height:520px;display:grid;place-items:center}
.glow{
    position:absolute;width:430px;height:430px;border-radius:50%;
    background:radial-gradient(circle,rgba(57,169,107,.18),transparent 68%);
}
.storyCard{
    position:relative;width:min(450px,90vw);height:430px;
    border-radius:34px;background:rgba(255,255,255,.82);
    border:1px solid rgba(255,255,255,.9);box-shadow:0 28px 70px rgba(10,61,35,.18);
    overflow:hidden;padding:30px;
}
.storyCard:before{
    content:"";position:absolute;width:270px;height:270px;border-radius:50%;
    right:-95px;top:-100px;background:rgba(18,97,160,.10);
}
.ja{
    width:78px;height:78px;border-radius:24px;display:grid;place-items:center;
    color:#fff;font-weight:950;font-size:31px;
    background:linear-gradient(145deg,var(--vert),var(--bleu));
}
.storyTitle{margin-top:24px;max-width:290px;font-size:29px;line-height:1.15;color:var(--vert-fonce)}
.storyText{margin-top:10px;max-width:330px;color:var(--gris);font-size:14px}
.land{
    position:absolute;left:0;right:0;bottom:0;height:180px;
    background:#dcefdc;
    clip-path:polygon(0 48%,17% 27%,33% 45%,53% 12%,71% 39%,100% 8%,100% 100%,0 100%);
}
.plant{
    position:absolute;bottom:49px;left:48px;font-size:68px;
    animation:float 4s ease-in-out infinite;
}
.quote{
    position:absolute;right:24px;bottom:35px;max-width:205px;
    background:#fff;border-radius:17px;padding:14px 16px;
    box-shadow:0 12px 28px rgba(10,61,35,.14);font-size:12px;font-weight:750;
}
.quote strong{display:block;color:var(--vert);font-size:16px;margin-bottom:3px}
 
/* STORY */
section{padding:95px 0}
.sectionHead{text-align:center;max-width:750px;margin:0 auto 45px}
.kicker{
    color:var(--bleu);font-size:11px;font-weight:950;
    text-transform:uppercase;letter-spacing:2px;
}
.sectionHead h2{
    font-size:clamp(31px,4vw,48px);line-height:1.12;
    color:var(--vert-fonce);margin:9px 0 12px;
}
.sectionHead p{color:var(--gris)}
.storyGrid{display:grid;grid-template-columns:1fr 1fr;gap:25px;align-items:stretch}
.panel{
    background:#fff;border:1px solid var(--ligne);border-radius:27px;
    padding:34px;box-shadow:var(--ombre);
}
.panel h3{font-size:25px;color:var(--vert-fonce);margin-bottom:11px}
.panel p{color:var(--gris);font-size:15px}
.panel + .panel{background:linear-gradient(145deg,#0a3d23,#176b3a);color:#fff;border:0}
.panel + .panel h3,.panel + .panel p{color:#fff}
.panel + .panel p{opacity:.86}
 
/* VALUES */
.values{background:#fff}
.valueGrid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.value{
    padding:27px 22px;border:1px solid var(--ligne);border-radius:22px;
    background:#fbfefc;transition:.3s;
}
.value:hover{transform:translateY(-7px);box-shadow:var(--ombre)}
.valueIcon{
    width:53px;height:53px;border-radius:16px;background:#eaf6ee;
    display:grid;place-items:center;font-size:24px;margin-bottom:16px;
}
.value h3{font-size:17px;color:var(--vert-fonce);margin-bottom:6px}
.value p{font-size:13px;color:var(--gris)}
 
/* PARCOURS */
.timeline{
    display:grid;grid-template-columns:repeat(3,1fr);gap:20px;position:relative;
}
.step{
    background:#fff;border-radius:23px;border:1px solid var(--ligne);
    padding:28px;box-shadow:0 12px 35px rgba(10,61,35,.07);
}
.number{
    width:43px;height:43px;border-radius:50%;display:grid;place-items:center;
    background:var(--vert);color:#fff;font-weight:900;margin-bottom:17px;
}
.step h3{color:var(--vert-fonce);margin-bottom:7px}
.step p{font-size:14px;color:var(--gris)}
 
/* CTA */
.cta{
    padding:72px 0;color:#fff;
    background:linear-gradient(135deg,var(--vert-fonce),var(--vert),var(--bleu));
}
.ctaBox{
    display:flex;align-items:center;justify-content:space-between;gap:30px;
}
.cta h2{font-size:clamp(30px,4vw,48px);line-height:1.1}
.cta p{margin-top:8px;opacity:.84}
.btnLight{background:#fff;color:var(--vert-fonce)}
 
/* FOOTER */
footer{background:#082b19;color:#d7e9dd;padding:45px 0 25px}
.footerGrid{display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:35px}
footer h3{color:#fff;margin-bottom:12px}
footer p,footer a{font-size:13px;color:#a8c2b1}
footer a:hover{color:#fff}
.footerBottom{
    margin-top:30px;padding-top:18px;border-top:1px solid rgba(255,255,255,.12);
    display:flex;justify-content:space-between;gap:15px;font-size:12px;
}
 
/* ANIMATION */
.reveal{opacity:0;transform:translateY(25px);transition:opacity .7s ease,transform .7s ease}
.reveal.show{opacity:1;transform:none}
@keyframes float{50%{transform:translateY(-13px)}}
 
/* MOBILE */
@media(max-width:920px){
    .navlinks{
        display:none;position:absolute;top:78px;left:4%;right:4%;
        background:#fff;border-radius:18px;padding:12px;box-shadow:var(--ombre);
        flex-direction:column;align-items:stretch;
    }
    .navlinks.open{display:flex}
    .navlinks a{text-align:center;display:block}
    .menu{display:block}
    .heroGrid{grid-template-columns:1fr;text-align:center;gap:25px}
    .heroLead{margin:auto}
    .slogan{display:inline-block;text-align:left}
    .heroActions{justify-content:center}
    .visual{min-height:420px}
    .storyGrid{grid-template-columns:1fr}
    .valueGrid{grid-template-columns:repeat(2,1fr)}
    .timeline{grid-template-columns:1fr}
    .ctaBox{flex-direction:column;text-align:center}
    .footerGrid{grid-template-columns:1fr 1fr}
}
@media(max-width:560px){
    .nav{height:70px}
    .logo{width:43px;height:43px}
    .brandName{font-size:14px}
    .brand small{font-size:8px}
    .hero{padding-top:112px}
    h1{letter-spacing:-2px}
    section{padding:68px 0}
    .visual{min-height:365px}
    .storyCard{height:340px;padding:22px}
    .storyTitle{font-size:23px}
    .glow{width:300px;height:300px}
    .valueGrid{grid-template-columns:1fr}
    .footerGrid{grid-template-columns:1fr}
    .footerBottom{flex-direction:column;text-align:center}
}
</style>
</head>
 
<body>
 
<header>
<nav class="nav container">
    <a href="index.php" class="brand">
        <div class="logo">JA</div>
        <div>
            <div class="brandName">Jardin des Agriculteurs</div>
            <small>Le prix, oui ! La qualité surtout !</small>
        </div>
    </a>
 
    <button class="menu" id="menuBtn" aria-label="Menu">☰</button>
 
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
 
<section class="hero">
<div class="container heroGrid">
    <div class="reveal">
        <span class="eyebrow">🌿 Notre histoire • Notre vision • Notre ambition</span>
        <h1>Plus qu'une plateforme,<br>une <span>nouvelle façon</span><br>de découvrir l'agriculture.</h1>
        <p class="heroLead">
            Bienvenue au <strong>Jardin des Agriculteurs</strong>, un univers où
            l'agriculture rencontre le numérique pour rendre l'information,
            l'exposition des produits et la commande plus simples, plus claires
            et plus accessibles.
        </p>
        <div class="slogan">« Le prix, oui ! La qualité surtout ! »</div>
        <div class="heroActions">
            <a href="produits.php" class="btn primary">🌱 Découvrir nos produits</a>
            <a href="#notre-histoire" class="btn secondary">Notre histoire ↓</a>
        </div>
    </div>
 
    <div class="visual reveal">
        <div class="glow"></div>
        <div class="storyCard">
            <div class="ja">JA</div>
            <h2 class="storyTitle">Cultiver la confiance. Faire grandir l'expérience.</h2>
            <p class="storyText">
                Une identité tournée vers la qualité, la simplicité et la
                valorisation des produits agricoles.
            </p>
            <div class="land"></div>
            <div class="plant">🌱</div>
            <div class="quote">
                <strong>Notre promesse</strong>
                Donner envie de découvrir, comprendre et commander autrement.
            </div>
        </div>
    </div>
</div>
</section>
 
<section id="notre-histoire">
<div class="container">
    <div class="sectionHead reveal">
        <div class="kicker">Qui sommes-nous ?</div>
        <h2>Une idée simple, une ambition qui voit grand</h2>
        <p>Mettre le numérique au service d'une expérience agricole plus moderne et plus humaine.</p>
    </div>
 
    <div class="storyGrid">
        <article class="panel reveal">
            <h3>🌾 Notre histoire</h3>
            <p>
                Le Jardin des Agriculteurs est pensé comme un espace numérique
                permettant de présenter les produits agricoles de manière
                structurée et attractive. Notre objectif est de rapprocher
                les produits, les informations et les utilisateurs dans une
                même expérience.
            </p>
            <br>
            <p>
                Nous voulons que chaque visite soit une découverte : découvrir
                un produit, comprendre son univers, connaître sa disponibilité
                et poursuivre naturellement vers la commande.
            </p>
        </article>
 
        <article class="panel reveal">
            <h3>✨ Notre ambition</h3>
            <p>
                Construire une plateforme moderne, intuitive et accessible,
                capable d'accompagner aussi bien la présentation des produits
                que leur gestion et leur commande.
            </p>
            <br>
            <p>
                Derrière chaque fonctionnalité, une priorité : offrir une
                expérience claire, fiable et agréable, aussi bien sur ordinateur
                que sur smartphone.
            </p>
        </article>
    </div>
</div>
</section>
 
<section class="values">
<div class="container">
    <div class="sectionHead reveal">
        <div class="kicker">Ce qui nous guide</div>
        <h2>Des valeurs visibles dans chaque détail</h2>
        <p>Notre identité repose sur une expérience qui inspire confiance dès le premier regard.</p>
    </div>
 
    <div class="valueGrid">
        <article class="value reveal">
            <div class="valueIcon">⭐</div>
            <h3>Qualité</h3>
            <p>Mettre en avant des informations et une présentation soignées pour une expérience crédible.</p>
        </article>
        <article class="value reveal">
            <div class="valueIcon">🤝</div>
            <h3>Confiance</h3>
            <p>Créer un parcours transparent entre découverte des produits et commande.</p>
        </article>
        <article class="value reveal">
            <div class="valueIcon">💡</div>
            <h3>Innovation</h3>
            <p>Utiliser le numérique pour rendre les opérations agricoles plus simples et modernes.</p>
        </article>
        <article class="value reveal">
            <div class="valueIcon">🌍</div>
            <h3>Accessibilité</h3>
            <p>Une plateforme pensée pour être confortable sur ordinateur, tablette et téléphone.</p>
        </article>
    </div>
</div>
</section>
 
<section>
<div class="container">
    <div class="sectionHead reveal">
        <div class="kicker">Une expérience en quelques étapes</div>
        <h2>Du premier regard à la commande</h2>
        <p>Chaque étape a été pensée pour rendre le parcours naturel.</p>
    </div>
 
    <div class="timeline">
        <article class="step reveal">
            <div class="number">01</div>
            <h3>Découvrir</h3>
            <p>Le visiteur arrive sur une interface accueillante et explore l'univers du Jardin des Agriculteurs.</p>
        </article>
        <article class="step reveal">
            <div class="number">02</div>
            <h3>Explorer</h3>
            <p>Le catalogue permet de parcourir les catégories et de consulter les informations des produits.</p>
        </article>
        <article class="step reveal">
            <div class="number">03</div>
            <h3>Commander</h3>
            <p>Le client poursuit son parcours vers le panier et la commande depuis une interface adaptée au mobile.</p>
        </article>
    </div>
</div>
</section>
 
<section class="cta">
<div class="container ctaBox">
    <div>
        <h2>Et si votre prochaine<br>découverte commençait ici ?</h2>
        <p>Entrez dans l'univers du Jardin des Agriculteurs.</p>
    </div>
    <a href="produits.php" class="btn btnLight">Explorer le catalogue →</a>
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
}, {threshold:0.12});
 
document.querySelectorAll('.reveal').forEach(element => observer.observe(element));
</script>
 
</body>
</html>
