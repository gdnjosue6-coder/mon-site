<?php
declare(strict_types=1);
require __DIR__ . '/includes/functions.php';
startAppSession();
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#ff5b30">
  <title>Mon profil — Mimi Rewards</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2793484494767138" crossorigin="anonymous"></script>
</head>
<body class="profile-body">
  <div class="profile-shell">
    <header class="profile-topbar">
      <div class="topbar-left">
        <button class="back-button" id="backButton" type="button" aria-label="Retour"><i class="fa-solid fa-arrow-left"></i></button>
        <a class="brand" href="index.php"><span class="brand-icon"><i class="fa-solid fa-coins"></i></span><span><strong>Mimi</strong> Rewards</span></a>
      </div>
      <div class="profile-top-actions">
        <button class="button button-soft hidden" id="profileAdminLink" type="button"><i class="fa-solid fa-sliders"></i><span>Contrôler le site</span></button>
        <a class="button button-soft" href="index.php"><i class="fa-solid fa-house"></i><span>Accueil</span></a>
      </div>
    </header>

    <main class="profile-main">
      <section class="profile-hero-card">
        <div class="profile-identity">
          <div class="profile-avatar" id="profileInitials">MR</div>
          <div><span class="eyebrow">Mon espace personnel</span><h1 id="profileName">Mon profil</h1><p id="profileEmail">Chargement…</p></div>
        </div>
        <div class="profile-balance">
          <span>Solde disponible</span>
          <strong><span id="profilePoints">0</span> P</strong>
          <small>≈ <span id="profileCfa">0</span> FCFA</small>
        </div>
        <button class="button profile-logout" id="profileLogoutButton" type="button"><i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span></button>
      </section>

      <nav class="profile-menu" aria-label="Sections du profil">
        <button class="profile-menu-button active" type="button" data-profile-tab="payments"><span class="profile-menu-icon payment"><i class="fa-solid fa-wallet"></i></span><span><strong>Paiements</strong><small>Retraits et historique</small></span><i class="fa-solid fa-chevron-right"></i></button>
        <button class="profile-menu-button" type="button" data-profile-tab="daily"><span class="profile-menu-icon daily"><i class="fa-solid fa-calendar-check"></i></span><span><strong>Récompense quotidienne</strong><small>Récupérer le bonus du jour</small></span><i class="fa-solid fa-chevron-right"></i></button>
        <button class="profile-menu-button" type="button" data-profile-tab="activities"><span class="profile-menu-icon activity"><i class="fa-solid fa-clock-rotate-left"></i></span><span><strong>Activités</strong><small>Voir tous vos mouvements</small></span><i class="fa-solid fa-chevron-right"></i></button>
        <button class="profile-menu-button hidden" type="button" id="profileAdminTabButton" data-profile-tab="admin"><span class="profile-menu-icon admin"><i class="fa-solid fa-shield-halved"></i></span><span><strong>Administration</strong><small>Gérer le site</small></span><i class="fa-solid fa-chevron-right"></i></button>
      </nav>

      <div class="profile-panels">
        <section class="profile-panel active" id="profilePanel-payments" data-profile-panel="payments" aria-labelledby="paymentsTitle">
          <div class="profile-panel-heading"><div><span class="section-kicker">Paiements</span><h2 id="paymentsTitle">Demandes de retrait</h2><p>Choisissez votre moyen de paiement et suivez le traitement de chaque demande.</p></div></div>
          <div class="profile-payment-grid">
            <article class="card">
              <div class="withdraw-tabs" role="tablist">
                <button class="withdraw-tab active" type="button" data-profile-method="momo"><i class="fa-solid fa-mobile-screen"></i> MoMo</button>
                <button class="withdraw-tab" type="button" data-profile-method="paypal"><i class="fa-brands fa-paypal"></i> PayPal</button>
                <button class="withdraw-tab" type="button" data-profile-method="bitcoin"><i class="fa-brands fa-bitcoin"></i> Bitcoin</button>
              </div>
              <form class="stack-form" id="profileWithdrawForm">
                <input type="hidden" name="method" id="profileWithdrawMethod" value="momo">
                <label><span id="profileAccountLabel">Numéro Mobile Money</span><input type="text" name="account" id="profileWithdrawAccount" placeholder="+229 01 XX XX XX XX XX" maxlength="190" required></label>
                <label>Nombre de points<input type="number" name="points" id="profileWithdrawPoints" min="30000" step="100" placeholder="30 000" required></label>
                <div class="conversion-preview">Montant estimé : <strong id="profileWithdrawCfa">0 FCFA</strong></div>
                <button class="button button-primary button-full" type="submit">Demander le retrait</button>
              </form>
            </article>

            <article class="card profile-payment-history">
              <h3>Historique des paiements</h3>
              <div class="profile-withdrawals-list" id="profileWithdrawalsList"><p class="empty-state">Aucune demande.</p></div>
            </article>
          </div>
        </section>

        <section class="profile-panel" id="profilePanel-daily" data-profile-panel="daily" aria-labelledby="dailyProfileTitle">
          <div class="profile-panel-heading"><div><span class="section-kicker">Fidélité</span><h2 id="dailyProfileTitle">Ma récompense quotidienne</h2><p>Connectez-vous chaque jour pour avancer dans le cycle de 10 jours et cumuler 4 500 points.</p></div></div>
          <article class="card profile-daily-card">
            <div class="days-row" id="profileDaysRow"></div>
            <button class="button button-primary button-full" id="profileDailyButton" type="button">Récupérer ma récompense</button>
            <p class="profile-daily-note"><i class="fa-solid fa-circle-info"></i> Une seule récompense peut être récupérée par jour. Une interruption recommence le cycle au jour 1.</p>
          </article>
        </section>

        <section class="profile-panel" id="profilePanel-activities" data-profile-panel="activities" aria-labelledby="activitiesTitle">
          <div class="profile-panel-heading"><div><span class="section-kicker">Historique</span><h2 id="activitiesTitle">Mes activités</h2><p>Retrouvez les sondages complétés, les bonus reçus, les échanges et les retraits.</p></div></div>
          <article class="card">
            <div class="profile-activity-list" id="profileActivityList"><p class="empty-state">Aucune activité enregistrée.</p></div>
          </article>
        </section>

        <section class="profile-panel" id="profilePanel-admin" data-profile-panel="admin" aria-labelledby="adminProfileTitle">
          <div class="profile-panel-heading with-action">
            <div><span class="section-kicker">Contrôle du site</span><h2 id="adminProfileTitle">Administration</h2><p>Gérez les utilisateurs, les sondages et les demandes de retrait, directement depuis votre profil.</p></div>
            <button class="button button-soft" id="refreshAdminButton" type="button"><i class="fa-solid fa-rotate"></i><span>Actualiser</span></button>
          </div>

          <div class="admin-stats" aria-label="Statistiques">
            <article><span class="stat-icon purple"><i class="fa-solid fa-users"></i></span><div><strong id="statUsers">0</strong><span>Utilisateurs</span></div></article>
            <article><span class="stat-icon orange"><i class="fa-solid fa-square-poll-vertical"></i></span><div><strong id="statSurveys">0</strong><span>Sondages actifs</span></div></article>
            <article><span class="stat-icon green"><i class="fa-solid fa-check-double"></i></span><div><strong id="statResponses">0</strong><span>Réponses reçues</span></div></article>
            <article><span class="stat-icon blue"><i class="fa-solid fa-money-bill-transfer"></i></span><div><strong id="statWithdrawals">0</strong><span>Retraits en attente</span></div></article>
          </div>

          <div class="admin-grid">
            <article class="card admin-form-card">
              <div class="section-heading compact"><div><span class="section-kicker">Publication</span><h2>Nouveau sondage</h2></div></div>
              <form class="stack-form" id="createSurveyForm">
                <label>Catégorie
                  <select name="category_id" required>
                    <option value="1">Technologie</option><option value="2">Formation</option><option value="3">Emploi et entrepreneuriat</option>
                    <option value="4">Services numériques</option><option value="5">Vie quotidienne</option><option value="6">Culture et loisirs</option>
                    <option value="7">Finance</option><option value="8">Société</option><option value="9">Environnement</option><option value="10">Consommation</option>
                  </select>
                </label>
                <label>Question<textarea name="question" rows="4" minlength="10" maxlength="500" required placeholder="Écrivez une question claire…"></textarea></label>
                <label>Réponse 1<input type="text" name="option_1" maxlength="120" required></label>
                <label>Réponse 2<input type="text" name="option_2" maxlength="120" required></label>
                <label>Réponse 3<input type="text" name="option_3" maxlength="120"></label>
                <label>Réponse 4<input type="text" name="option_4" maxlength="120"></label>
                <label>Points accordés<input type="number" name="reward_points" min="1" max="500" value="25" required></label>
                <button class="button button-primary button-full" type="submit"><i class="fa-solid fa-plus"></i> Publier le sondage</button>
              </form>
            </article>

            <article class="card admin-table-card">
              <div class="section-heading compact"><div><span class="section-kicker">Paiements</span><h2>Demandes de retrait</h2></div></div>
              <div class="table-scroll">
                <table>
                  <thead><tr><th>Utilisateur</th><th>Méthode</th><th>Montant</th><th>Statut</th><th>Actions</th></tr></thead>
                  <tbody id="withdrawalsTable"><tr><td colspan="5" class="empty-cell">Chargement…</td></tr></tbody>
                </table>
              </div>
            </article>
          </div>

          <article class="card admin-table-card">
            <div class="section-heading compact"><div><span class="section-kicker">Contenu</span><h2>100 derniers sondages</h2></div><input class="table-search" id="surveySearch" type="search" placeholder="Rechercher une question…"></div>
            <div class="table-scroll">
              <table>
                <thead><tr><th>ID</th><th>Question</th><th>Catégorie</th><th>Récompense</th><th>État</th><th>Action</th></tr></thead>
                <tbody id="surveysTable"><tr><td colspan="6" class="empty-cell">Chargement…</td></tr></tbody>
              </table>
            </div>
          </article>

          <article class="card admin-table-card admin-users-section">
            <div class="section-heading compact"><div><span class="section-kicker">Membres</span><h2>Gestion des utilisateurs</h2></div><input class="table-search" id="userSearch" type="search" placeholder="Rechercher un utilisateur…"></div>
            <div class="table-scroll">
              <table>
                <thead><tr><th>Utilisateur</th><th>Téléphone</th><th>Rôle</th><th>Solde</th><th>Inscription</th><th>État</th><th>Action</th></tr></thead>
                <tbody id="usersTable"><tr><td colspan="7" class="empty-cell">Chargement…</td></tr></tbody>
              </table>
            </div>
          </article>
        </section>
      </div>
    </main>
  </div>

  <div class="toast" id="profileToast" role="status" aria-live="polite"><i class="fa-solid fa-circle-check"></i><span id="profileToastText"></span></div>
  <div class="loading-overlay" id="profileLoading" aria-hidden="true"><span class="loader"></span></div>
  <script src="assets/js/profile.js" defer></script>
</body>
</html>
