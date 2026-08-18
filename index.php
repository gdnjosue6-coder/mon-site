<?php
declare(strict_types=1);
require __DIR__ . '/includes/functions.php';
startAppSession();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Répondez à des sondages, gagnez des points et échangez-les contre des récompenses.">
  <meta name="theme-color" content="#ff5b30">
  <title>Mimi Rewards — Sondages et récompenses</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2793484494767138" crossorigin="anonymous"></script>
</head>
<body>
  <div class="app-shell">
    <header class="topbar">
      <div class="topbar-left">
        <button class="back-button" id="backButton" type="button" aria-label="Retour"><i class="fa-solid fa-arrow-left"></i></button>
        <a class="brand" href="index.php" aria-label="Accueil Mimi Rewards">
          <span class="brand-icon"><i class="fa-solid fa-coins"></i></span>
          <span><strong>Mimi</strong> Rewards</span>
        </a>
      </div>
      <nav class="top-actions" aria-label="Actions du compte">
        <button class="button button-soft" id="earnTopButton" type="button">
          <i class="fa-solid fa-chart-simple"></i><span>Gagner</span>
        </button>
        <a class="button button-soft hidden" id="adminLink" href="admin.php">
          <i class="fa-solid fa-sliders"></i><span>Contrôle</span>
        </a>
        <button class="button button-primary" id="authOpenButton" type="button" aria-label="Connexion ou profil">
          <i class="fa-solid fa-user"></i><span id="authButtonText">Connexion</span>
        </button>
      </nav>
    </header>

    <main>
      <section class="hero">
        <div class="hero-copy">
          <span class="eyebrow"><i class="fa-solid fa-sparkles"></i> Votre avis a de la valeur</span>
          <h1>Répondez. Cumulez. Profitez.</h1>
          <p>Partagez votre opinion à travers plus de 300 sondages utiles, gagnez des points et demandez vos récompenses en toute simplicité.</p>
          <div class="hero-actions">
            <button class="button button-primary button-large" id="heroEarnButton" type="button">Commencer un sondage <i class="fa-solid fa-arrow-right"></i></button>
            <button class="text-button" id="learnMoreButton" type="button">Comment ça marche ?</button>
          </div>
        </div>
        <div class="hero-art" aria-hidden="true">
          <div class="art-orbit orbit-one"></div>
          <div class="art-orbit orbit-two"></div>
          <div class="art-phone">
            <i class="fa-solid fa-square-poll-vertical"></i>
            <span>+25 P</span>
          </div>
          <div class="floating-coin coin-one">P</div>
          <div class="floating-coin coin-two"><i class="fa-solid fa-star"></i></div>
        </div>
      </section>

      <section class="quick-stats" aria-label="Chiffres clés">
        <article><strong>300+</strong><span>Sondages disponibles</span></article>
        <article><strong>4 500 P</strong><span>Bonus sur 10 jours</span></article>
        <article><strong>3 moyens</strong><span>de retrait proposés</span></article>
      </section>

      <section class="dashboard-grid">
        <div class="main-column">
          <section class="balance-card" aria-labelledby="balanceTitle">
            <div>
              <span class="card-label" id="balanceTitle">Mon solde disponible</span>
              <div class="balance-value"><span id="pointsValue">0</span><span class="point-badge">P</span></div>
              <p>Valeur estimée : <strong><span id="pointsCfa">0</span> FCFA</strong></p>
            </div>
            <button class="button balance-action" id="rewardsOpenButton" type="button">Échanger <i class="fa-solid fa-chevron-right"></i></button>
          </section>

          <section class="survey-preview card" aria-labelledby="surveyTitle">
            <div class="section-heading">
              <div>
                <span class="section-kicker">Sondages rémunérés</span>
                <h2 id="surveyTitle">Une nouvelle question vous attend</h2>
              </div>
              <span class="reward-chip"><i class="fa-solid fa-coins"></i> jusqu’à 35 P</span>
            </div>
            <div class="survey-progress-wrap">
              <div class="progress-meta"><span>Votre progression</span><strong id="surveyProgressText">0 / 300</strong></div>
              <div class="progress-track"><span id="surveyProgressBar"></span></div>
            </div>
            <div class="survey-placeholder" id="surveyPreviewContent">
              <i class="fa-solid fa-lock"></i>
              <p>Connectez-vous pour accéder aux sondages et enregistrer vos gains.</p>
            </div>
            <button class="button button-primary button-full" id="surveyStartButton" type="button">Répondre au prochain sondage</button>
          </section>

          <section class="card" id="howItWorks" aria-labelledby="howTitle">
            <div class="section-heading">
              <div>
                <span class="section-kicker">Simple et transparent</span>
                <h2 id="howTitle">Comment ça marche ?</h2>
              </div>
            </div>
            <div class="steps-grid">
              <article class="step"><span>1</span><i class="fa-solid fa-user-plus"></i><h3>Créez votre compte</h3><p>Inscrivez-vous gratuitement et recevez 300 points de bienvenue.</p></article>
              <article class="step"><span>2</span><i class="fa-solid fa-list-check"></i><h3>Donnez votre avis</h3><p>Choisissez une réponse par sondage. Chaque participation est enregistrée une seule fois.</p></article>
              <article class="step"><span>3</span><i class="fa-solid fa-gift"></i><h3>Utilisez vos points</h3><p>Échangez vos points contre un avantage ou demandez un retrait lorsque le seuil est atteint.</p></article>
            </div>
          </section>
        </div>

        <aside class="side-column">
          <section class="card info-card">
            <div class="info-icon"><i class="fa-solid fa-shield-heart"></i></div>
            <h2>Vos participations sont protégées</h2>
            <p>Les mots de passe sont chiffrés, les soldes sont contrôlés côté serveur et les réponses ne peuvent pas être comptées deux fois.</p>
          </section>
        </aside>
      </section>

      <section class="faq-section" aria-labelledby="faqTitle">
        <div class="section-heading centered"><div><span class="section-kicker">Informations utiles</span><h2 id="faqTitle">Questions fréquentes</h2></div></div>
        <div class="faq-grid">
          <details><summary>Comment sont calculés les gains ?</summary><p>Chaque sondage affiche sa récompense avant la réponse. La conversion indicative utilisée sur la plateforme est de 100 points pour 15 FCFA.</p></details>
          <details><summary>Puis-je répondre plusieurs fois au même sondage ?</summary><p>Non. Une seule réponse par compte est acceptée afin de préserver la fiabilité des résultats.</p></details>
          <details><summary>Quand un retrait est-il payé ?</summary><p>La demande est d’abord vérifiée par l’administrateur. Son statut passe ensuite de « en attente » à « approuvé » puis « payé ».</p></details>
          <details><summary>Que se passe-t-il si un retrait est refusé ?</summary><p>Les points réservés pour ce retrait sont automatiquement recrédités une seule fois sur votre compte.</p></details>
        </div>
      </section>
    </main>

    <footer>
      <a class="brand footer-brand" href="index.php"><span class="brand-icon"><i class="fa-solid fa-coins"></i></span><span><strong>Mimi</strong> Rewards</span></a>
      <p>Une plateforme de sondages et de récompenses conçue pour recueillir des avis utiles.</p>
      <span>© <?= date('Y') ?> Mimi Rewards. Tous droits réservés.</span>
    </footer>
  </div>

  <div class="modal" id="authModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
    <div class="modal-backdrop" data-close-modal="authModal"></div>
    <div class="modal-panel auth-modal-panel">
      <button class="modal-close" type="button" data-close-modal="authModal" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
      <div class="modal-heading"><span class="modal-icon"><i class="fa-solid fa-user-lock"></i></span><h2 id="authModalTitle">Bienvenue</h2><p>Connectez-vous ou créez gratuitement votre compte.</p></div>
      <div class="auth-tabs" role="tablist">
        <button class="auth-tab active" type="button" data-auth-mode="login">Connexion</button>
        <button class="auth-tab" type="button" data-auth-mode="register">Inscription</button>
      </div>
      <form class="stack-form auth-form active" id="loginForm">
        <label>Adresse e-mail<input type="email" name="email" autocomplete="email" required placeholder="nom@exemple.com"></label>
        <label>Mot de passe<input type="password" name="password" autocomplete="current-password" required placeholder="Votre mot de passe"></label>
        <button class="button button-primary button-full" type="submit">Se connecter</button>
      </form>
      <form class="stack-form auth-form" id="registerForm">
        <label>Nom complet<input type="text" name="full_name" autocomplete="name" required minlength="2" maxlength="120" placeholder="Votre nom et prénom"></label>
        <label>Adresse e-mail<input type="email" name="email" autocomplete="email" required placeholder="nom@exemple.com"></label>
        <label>Numéro de téléphone <small>(facultatif)</small><input type="tel" name="phone" autocomplete="tel" maxlength="30" placeholder="+229 01 XX XX XX XX XX"></label>
        <label>Mot de passe<input type="password" name="password" autocomplete="new-password" required minlength="8" placeholder="Au moins 8 caractères"></label>
        <label>Confirmer le mot de passe<input type="password" name="password_confirmation" autocomplete="new-password" required minlength="8" placeholder="Répétez le mot de passe"></label>
        <button class="button button-primary button-full" type="submit">Créer mon compte</button>
      </form>
    </div>
  </div>

  <div class="modal" id="accountModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
    <div class="modal-backdrop" data-close-modal="accountModal"></div>
    <div class="modal-panel small-panel">
      <button class="modal-close" type="button" data-close-modal="accountModal" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
      <div class="account-avatar" id="accountInitials">MR</div>
      <h2 id="accountModalTitle">Mon compte</h2>
      <p class="account-name" id="accountName">Utilisateur</p>
      <p class="account-email" id="accountEmail"></p>
      <div class="account-balance"><span>Solde</span><strong><span id="accountPoints">0</span> P</strong></div>
      <a class="button button-soft button-full hidden" id="accountAdminLink" href="admin.php">Ouvrir l’administration</a>
      <button class="button button-danger button-full" id="logoutButton" type="button"><i class="fa-solid fa-right-from-bracket"></i> Se déconnecter</button>
    </div>
  </div>

  <div class="modal" id="surveyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="surveyModalTitle">
    <div class="modal-backdrop" data-close-modal="surveyModal"></div>
    <div class="modal-panel survey-modal-panel">
      <button class="modal-close" type="button" data-close-modal="surveyModal" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
      <div id="surveyModalContent"></div>
    </div>
  </div>

  <div class="modal" id="rewardsModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="rewardsModalTitle">
    <div class="modal-backdrop" data-close-modal="rewardsModal"></div>
    <div class="modal-panel rewards-modal-panel">
      <button class="modal-close" type="button" data-close-modal="rewardsModal" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
      <div class="modal-heading align-left"><span class="section-kicker">Catalogue</span><h2 id="rewardsModalTitle">Échanger mes points</h2><p>Solde : <strong><span id="modalPoints">0</span> points</strong></p></div>
      <div class="rewards-list" id="rewardsList"></div>
    </div>
  </div>

  <div class="toast" id="toast" role="status" aria-live="polite"><i class="fa-solid fa-circle-check"></i><span id="toastText"></span></div>
  <div class="loading-overlay" id="loadingOverlay" aria-hidden="true"><span class="loader"></span></div>

  <script src="assets/js/app.js" defer></script>
</body>
</html>
