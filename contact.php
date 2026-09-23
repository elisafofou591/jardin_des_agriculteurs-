<?php
// contact.php
// Jardin des Agriculteurs — Page Contact Premium
 
$messageEnvoye = false;
$erreur = "";
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $sujet = trim($_POST["sujet"] ?? "");
    $message = trim($_POST["message"] ?? "");
 
    if ($nom === "" || $email === "" || $message === "") {
        $erreur = "Veuillez remplir les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Veuillez entrer une adresse e-mail valide.";
    } else {
        // Pour le moment, on simule l'envoi.
        // Plus tard, cette partie pourra enregistrer le message en MySQL
        // ou utiliser un système d'envoi d'e-mails sécurisé.
        $messageEnvoye = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contactez Jardin des Agriculteurs pour vos produits et besoins agricoles.">
    <title>Contact | Jardin des Agriculteurs</title>
 
    <style>
        :root {
            --forest: #123c2b;
            --forest-2: #1d5a3f;
            --green: #2f7d4f;
            --light-green: #eaf5ee;
            --gold: #d6a84f;
            --cream: #f8faf8;
            --white: #ffffff;
            --text: #1d2923;
            --muted: #6b7771;
            --border: #dfe8e2;
            --shadow: 0 18px 45px rgba(18, 60, 43, .12);
            --radius: 22px;
        }
 
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        html {
            scroll-behavior: smooth;
        }
 
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 10%, rgba(214,168,79,.08), transparent 25%),
                linear-gradient(180deg, #ffffff 0%, var(--cream) 100%);
            line-height: 1.6;
            overflow-x: hidden;
        }
 
        a {
            text-decoration: none;
            color: inherit;
        }
 
        button, input, textarea, select {
            font: inherit;
        }
 
        /* NAVBAR */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,.90);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(18,60,43,.08);
        }
 
        .nav-inner {
            max-width: 1200px;
            margin: auto;
            min-height: 76px;
            padding: 0 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }
 
        .logo {
            display: flex;
            align-items: center;
            gap: 11px;
            font-weight: 800;
            color: var(--forest);
            letter-spacing: -.4px;
        }
 
        .logo-mark {
            width: 43px;
            height: 43px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: white;
            background: linear-gradient(135deg, var(--forest), var(--green));
            box-shadow: 0 10px 25px rgba(18,60,43,.20);
        }
 
        .nav-links {
            display: flex;
            gap: 7px;
            list-style: none;
        }
 
        .nav-links a {
            padding: 10px 14px;
            border-radius: 12px;
            color: #405047;
            transition: .3s ease;
        }
 
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--forest);
            background: var(--light-green);
        }
 
        .menu-btn {
            display: none;
            border: 0;
            background: var(--forest);
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            cursor: pointer;
        }
 
        /* HERO */
        .hero {
            position: relative;
            min-height: 330px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 75px 22px;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(18,60,43,.98), rgba(29,90,63,.92));
            color: white;
        }
 
        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(2px);
            opacity: .20;
            animation: float 8s ease-in-out infinite;
        }
 
        .hero::before {
            width: 280px;
            height: 280px;
            background: var(--gold);
            top: -150px;
            left: -80px;
        }
 
        .hero::after {
            width: 330px;
            height: 330px;
            background: #8bd1a3;
            right: -140px;
            bottom: -180px;
            animation-delay: -3s;
        }
 
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 780px;
            animation: fadeUp .9s ease both;
        }
 
        .eyebrow {
            display: inline-flex;
            padding: 7px 14px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            font-size: .88rem;
            margin-bottom: 16px;
        }
 
        .hero h1 {
            font-size: clamp(2.1rem, 5vw, 4rem);
            line-height: 1.05;
            margin-bottom: 18px;
        }
 
        .hero p {
            max-width: 650px;
            margin: auto;
            color: rgba(255,255,255,.86);
            font-size: 1.05rem;
        }
 
        /* MAIN */
        .container {
            width: min(1200px, calc(100% - 40px));
            margin: auto;
        }
 
        .contact-section {
            padding: 80px 0;
        }
 
        .contact-grid {
            display: grid;
            grid-template-columns: .85fr 1.15fr;
            gap: 28px;
            align-items: start;
        }
 
        .info-card,
        .form-card {
            background: rgba(255,255,255,.92);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
 
        .info-card {
            padding: 30px;
            position: sticky;
            top: 105px;
        }
 
        .section-label {
            color: var(--green);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: .78rem;
            margin-bottom: 8px;
        }
 
        .info-card h2,
        .form-card h2 {
            color: var(--forest);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
 
        .intro {
            color: var(--muted);
            margin-bottom: 25px;
        }
 
        .info-item {
            display: flex;
            gap: 15px;
            padding: 17px 0;
            border-bottom: 1px solid var(--border);
        }
 
        .info-item:last-of-type {
            border-bottom: 0;
        }
 
        .icon {
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--light-green);
            color: var(--forest);
            font-size: 1.2rem;
        }
 
        .info-item strong {
            display: block;
            color: var(--forest);
            margin-bottom: 2px;
        }
 
        .info-item span,
        .info-item a {
            color: var(--muted);
            font-size: .94rem;
        }
 
        .whatsapp {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            padding: 13px 18px;
            border-radius: 14px;
            background: var(--forest);
            color: white;
            font-weight: 700;
            transition: .3s ease;
        }
 
        .whatsapp:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(18,60,43,.22);
        }
 
        .form-card {
            padding: 34px;
        }
 
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 17px;
        }
 
        .field {
            margin-bottom: 17px;
        }
 
        .field.full {
            grid-column: 1 / -1;
        }
 
        label {
            display: block;
            font-weight: 700;
            font-size: .9rem;
            color: var(--forest);
            margin-bottom: 7px;
        }
 
        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid var(--border);
            background: #fbfdfb;
            color: var(--text);
            border-radius: 13px;
            padding: 13px 14px;
            outline: none;
            transition: .25s ease;
        }
 
        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 4px rgba(47,125,79,.10);
            transform: translateY(-1px);
        }
 
        textarea {
            min-height: 150px;
            resize: vertical;
        }
 
        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 15px 20px;
            cursor: pointer;
            color: white;
            font-weight: 800;
            background: linear-gradient(135deg, var(--forest), var(--green));
            box-shadow: 0 12px 25px rgba(18,60,43,.18);
            transition: .3s ease;
        }
 
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 17px 30px rgba(18,60,43,.25);
        }
 
        .alert {
            padding: 13px 15px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 600;
        }
 
        .success {
            background: #e9f7ee;
            color: #1d6b3d;
            border: 1px solid #bfe4ca;
        }
 
        .error {
            background: #fff0f0;
            color: #9a2f2f;
            border: 1px solid #efc4c4;
        }
 
        /* FAQ */
        .faq {
            padding: 0 0 80px;
        }
 
        .faq-title {
            text-align: center;
            max-width: 700px;
            margin: auto auto 30px;
        }
 
        .faq-title h2 {
            color: var(--forest);
            font-size: 2rem;
        }
 
        .faq-list {
            max-width: 850px;
            margin: auto;
            display: grid;
            gap: 12px;
        }
 
        .faq-item {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }
 
        .faq-question {
            width: 100%;
            border: 0;
            background: white;
            padding: 18px 20px;
            text-align: left;
            font-weight: 800;
            color: var(--forest);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
 
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            padding: 0 20px;
            color: var(--muted);
            transition: max-height .35s ease, padding .35s ease;
        }
 
        .faq-item.open .faq-answer {
            max-height: 180px;
            padding: 0 20px 18px;
        }
 
        .faq-item.open .plus {
            transform: rotate(45deg);
        }
 
        .plus {
            font-size: 1.4rem;
            transition: .3s ease;
        }
 
        /* FOOTER */
        footer {
            background: var(--forest);
            color: rgba(255,255,255,.78);
            padding: 28px 20px;
            text-align: center;
        }
 
        footer strong {
            color: white;
        }
 
        /* ANIMATIONS */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
 
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(25px) translateX(15px); }
        }
 
        .reveal {
            opacity: 0;
            transform: translateY(35px);
            transition: opacity .75s ease, transform .75s ease;
        }
 
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
 
        /* RESPONSIVE */
        @media (max-width: 850px) {
            .menu-btn {
                display: block;
            }
 
            .nav-links {
                position: absolute;
                top: 76px;
                left: 20px;
                right: 20px;
                display: none;
                flex-direction: column;
                padding: 12px;
                background: white;
                border: 1px solid var(--border);
                border-radius: 16px;
                box-shadow: var(--shadow);
            }
 
            .nav-links.show {
                display: flex;
                animation: fadeUp .3s ease both;
            }
 
            .contact-grid {
                grid-template-columns: 1fr;
            }
 
            .info-card {
                position: static;
            }
        }
 
        @media (max-width: 600px) {
            .container {
                width: min(100% - 24px, 1200px);
            }
 
            .nav-inner {
                padding: 0 12px;
            }
 
            .hero {
                min-height: 290px;
                padding: 60px 18px;
            }
 
            .contact-section {
                padding: 55px 0;
            }
 
            .form-card,
            .info-card {
                padding: 23px;
            }
 
            .form-grid {
                grid-template-columns: 1fr;
            }
 
            .field.full {
                grid-column: auto;
            }
        }
    </style>
</head>
 
<body>
 
<header class="navbar">
    <div class="nav-inner">
        <a class="logo" href="index.php" aria-label="Jardin des Agriculteurs">
            <span class="logo-mark">JA</span>
            <span>Jardin des Agriculteurs</span>
        </a>
 
        <button class="menu-btn" id="menuBtn" aria-label="Ouvrir le menu">☰</button>
 
        <nav>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
                <li><a href="panier.php">Panier</a></li>
            </ul>
        </nav>
    </div>
</header>
 
<section class="hero">
    <div class="hero-content">
        <span class="eyebrow">🌿 Jardin des Agriculteurs</span>
        <h1>Nous sommes à votre écoute</h1>
        <p>
            Une question sur un produit, une commande ou un besoin agricole ?
            Notre équipe est là pour vous accompagner.
        </p>
    </div>
</section>
 
<main>
    <section class="contact-section">
        <div class="container contact-grid">
 
            <aside class="info-card reveal">
                <div class="section-label">Contact</div>
                <h2>Parlons de votre projet</h2>
                <p class="intro">
                    Contactez Jardin des Agriculteurs pour obtenir des informations
                    sur nos produits agricoles, vos commandes et nos services.
                </p>
 
                <div class="info-item">
                    <div class="icon">📍</div>
                    <div>
                        <strong>Notre adresse</strong>
                        <span>Votre adresse professionnelle</span>
                    </div>
                </div>
 
                <div class="info-item">
                    <div class="icon">📞</div>
                    <div>
                        <strong>Téléphone</strong>
                        <a href="tel:+237600000000">+237 6 XX XX XX XX</a>
                    </div>
                </div>
 
                <div class="info-item">
                    <div class="icon">✉️</div>
                    <div>
                        <strong>E-mail</strong>
                        <a href="mailto:contact@jardindesagriculteurs.com">
                            contact@jardindesagriculteurs.com
                        </a>
                    </div>
                </div>
 
                <div class="info-item">
                    <div class="icon">🕒</div>
                    <div>
                        <strong>Horaires</strong>
                        <span>Lundi — Samedi : 08h00 — 18h00</span>
                    </div>
                </div>
 
                <a class="whatsapp" href="https://wa.me/237600000000" target="_blank">
                    💬 Nous contacter sur WhatsApp
                </a>
            </aside>
 
            <section class="form-card reveal">
                <div class="section-label">Message</div>
                <h2>Envoyez-nous un message</h2>
                <p class="intro">
                    Remplissez le formulaire et nous vous répondrons dans les meilleurs délais.
                </p>
 
                <?php if ($messageEnvoye): ?>
                    <div class="alert success">
                        Votre message a bien été pris en compte. Merci pour votre confiance !
                    </div>
                <?php endif; ?>
 
                <?php if ($erreur): ?>
                    <div class="alert error">
                        <?= htmlspecialchars($erreur) ?>
                    </div>
                <?php endif; ?>
 
                <form method="POST" action="contact.php" id="contactForm">
                    <div class="form-grid">
 
                        <div class="field">
                            <label for="nom">Nom complet *</label>
                            <input type="text" id="nom" name="nom"
                                   placeholder="Votre nom" required>
                        </div>
 
                        <div class="field">
                            <label for="email">Adresse e-mail *</label>
                            <input type="email" id="email" name="email"
                                   placeholder="exemple@email.com" required>
                        </div>
 
                        <div class="field">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone"
                                   placeholder="+237 6 XX XX XX XX">
                        </div>
 
                        <div class="field">
                            <label for="sujet">Sujet</label>
                            <select id="sujet" name="sujet">
                                <option value="">Choisir un sujet</option>
                                <option value="Produit">Question sur un produit</option>
                                <option value="Commande">Commande</option>
                                <option value="Livraison">Livraison</option>
                                <option value="Partenariat">Partenariat</option>
                                <option value="Autre">Autre demande</option>
                            </select>
                        </div>
 
                        <div class="field full">
                            <label for="message">Votre message *</label>
                            <textarea id="message" name="message"
                                      placeholder="Écrivez votre message ici..."
                                      required></textarea>
                        </div>
 
                        <div class="field full">
                            <button type="submit" class="submit-btn" id="submitBtn">
                                Envoyer mon message
                            </button>
                        </div>
 
                    </div>
                </form>
            </section>
        </div>
    </section>
 
    <section class="faq">
        <div class="container">
            <div class="faq-title reveal">
                <div class="section-label">Besoin d'aide ?</div>
                <h2>Questions fréquentes</h2>
            </div>
 
            <div class="faq-list">
 
                <div class="faq-item reveal">
                    <button class="faq-question">
                        Comment commander un produit ?
                        <span class="plus">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Parcourez notre catalogue, ajoutez les produits au panier
                            puis suivez les étapes de commande.
                        </p>
                    </div>
                </div>
 
                <div class="faq-item reveal">
                    <button class="faq-question">
                        Proposez-vous la livraison ?
                        <span class="plus">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Oui. Les modalités de livraison pourront être précisées
                            selon la zone et la commande.
                        </p>
                    </div>
                </div>
 
                <div class="faq-item reveal">
                    <button class="faq-question">
                        Puis-je demander des informations sur un produit ?
                        <span class="plus">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Oui. Utilisez le formulaire ci-dessus ou contactez-nous
                            directement par téléphone ou WhatsApp.
                        </p>
                    </div>
                </div>
 
            </div>
        </div>
    </section>
</main>
 
<footer>
    <p>
        © <span id="year"></span> <strong>Jardin des Agriculteurs</strong>.
        Tous droits réservés.
    </p>
</footer>
 
<script>
    // Menu mobile
    const menuBtn = document.getElementById("menuBtn");
    const navLinks = document.getElementById("navLinks");
 
    menuBtn.addEventListener("click", () => {
        navLinks.classList.toggle("show");
        menuBtn.textContent = navLinks.classList.contains("show") ? "✕" : "☰";
    });
 
    // Fermer le menu après sélection
    document.querySelectorAll("#navLinks a").forEach(link => {
        link.addEventListener("click", () => {
            navLinks.classList.remove("show");
            menuBtn.textContent = "☰";
        });
    });
 
    // Animation d'apparition au défilement
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
 
    document.querySelectorAll(".reveal").forEach(element => {
        observer.observe(element);
    });
 
    // FAQ accordéon
    document.querySelectorAll(".faq-question").forEach(button => {
        button.addEventListener("click", () => {
            const item = button.parentElement;
            const wasOpen = item.classList.contains("open");
 
            document.querySelectorAll(".faq-item").forEach(other => {
                other.classList.remove("open");
            });
 
            if (!wasOpen) {
                item.classList.add("open");
            }
        });
    });
 
    // Petite protection UX : animation du bouton pendant l'envoi
    const form = document.getElementById("contactForm");
    const submitBtn = document.getElementById("submitBtn");
 
    form.addEventListener("submit", () => {
        submitBtn.textContent = "Envoi en cours...";
        submitBtn.disabled = true;
        submitBtn.style.opacity = "0.75";
    });
 
    // Année automatique
    document.getElementById("year").textContent = new Date().getFullYear();
</script> 
</body>
</html>