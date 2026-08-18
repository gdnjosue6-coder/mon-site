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
  <title>Administration — Mimi Rewards</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2793484494767138" crossorigin="anonymous"></script>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="back-button" id="backButton" type="button" aria-label="Retour"><i class="fa-solid fa-arrow-left"></i></button>
        <a class="brand" href="index.php"><span class="brand-icon"><i class="fa-solid fa-coins"></i></span><span><strong>Mimi</strong> Rewards</span></a>
      </div>
      <div><span class="admin-badge"><i class="fa-solid fa-shield-halved"></i> Contrôle du site</span><a class="button button-soft" href="profil.php"><i class="fa-solid fa-user"></i> Mon profil</a></div>
    </header>

    <main class="admin-main">
      <section class="admin-welcome">
        <div><span class="eyebrow">Centre de contrôle</span><h1>Contrôlez tout votre site</h1><p>Gérez les utilisateurs, les sondages, les participations et toutes les demandes de retrait.</p></div>
        <button class="button button-primary" id="refreshAdminButton" type="button"><i class="fa-solid fa-rotate"></i> Actualiser</button>
      </section>

      <section class="admin-stats" aria-label="Statistiques">
        <article><span class="stat-icon purple"><i class="fa-solid fa-users"></i></span><div><strong id="statUsers">0</strong><span>Utilisateurs</span></div></article>
        <article><span class="stat-icon orange"><i class="fa-solid fa-square-poll-vertical"></i></span><div><strong id="statSurveys">0</strong><span>Sondages actifs</span></div></article>
        <article><span class="stat-icon green"><i class="fa-solid fa-check-double"></i></span><div><strong id="statResponses">0</strong><span>Réponses reçues</span></div></article>
        <article><span class="stat-icon blue"><i class="fa-solid fa-money-bill-transfer"></i></span><div><strong id="statWithdrawals">0</strong><span>Retraits en attente</span></div></article>
      </section>

      <section class="admin-grid">
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
      </section>

      <section class="card admin-table-card">
        <div class="section-heading compact"><div><span class="section-kicker">Contenu</span><h2>100 derniers sondages</h2></div><input class="table-search" id="surveySearch" type="search" placeholder="Rechercher une question…"></div>
        <div class="table-scroll">
          <table>
            <thead><tr><th>ID</th><th>Question</th><th>Catégorie</th><th>Récompense</th><th>État</th><th>Action</th></tr></thead>
            <tbody id="surveysTable"><tr><td colspan="6" class="empty-cell">Chargement…</td></tr></tbody>
          </table>
        </div>
      </section>

      <section class="card admin-table-card admin-users-section">
        <div class="section-heading compact"><div><span class="section-kicker">Membres</span><h2>Gestion des utilisateurs</h2></div><input class="table-search" id="userSearch" type="search" placeholder="Rechercher un utilisateur…"></div>
        <div class="table-scroll">
          <table>
            <thead><tr><th>Utilisateur</th><th>Téléphone</th><th>Rôle</th><th>Solde</th><th>Inscription</th><th>État</th><th>Action</th></tr></thead>
            <tbody id="usersTable"><tr><td colspan="7" class="empty-cell">Chargement…</td></tr></tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <div class="toast" id="toast" role="status" aria-live="polite"><i class="fa-solid fa-circle-check"></i><span id="toastText"></span></div>
  <div class="loading-overlay" id="loadingOverlay" aria-hidden="true"><span class="loader"></span></div>
  <script src="assets/js/admin.js" defer></script>
</body>
</html>
