<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

startAppSession();

try {
    $pdo = db();
    $action = (string) ($_GET['action'] ?? 'bootstrap');
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $data = requestData();

    if ($method !== 'GET') {
        verifyCsrf($data);
    }

    switch ($action) {
        case 'bootstrap':
            $user = currentUser($pdo);
            $response = [
                'ok' => true,
                'csrf_token' => csrfToken(),
                'user' => $user,
                'conversion' => ['rate' => POINT_CFA_RATE, 'minimum_withdrawal_points' => MIN_WITHDRAWAL_POINTS],
            ];

            $response['rewards'] = $pdo->query(
                'SELECT id, name, description, cost_points, icon, stock FROM rewards WHERE active = 1 ORDER BY cost_points ASC'
            )->fetchAll();

            if ($user) {
                $userId = (int) $user['id'];
                $count = $pdo->prepare('SELECT COUNT(*) FROM survey_responses WHERE user_id = ?');
                $count->execute([$userId]);
                $completed = (int) $count->fetchColumn();
                $total = (int) $pdo->query('SELECT COUNT(*) FROM surveys WHERE active = 1')->fetchColumn();

                $next = $pdo->prepare(
                    'SELECT s.id, s.question, s.options_json, s.reward_points, c.name AS category
                     FROM surveys s
                     JOIN survey_categories c ON c.id = s.category_id
                     LEFT JOIN survey_responses sr ON sr.survey_id = s.id AND sr.user_id = ?
                     WHERE s.active = 1 AND sr.id IS NULL
                     ORDER BY s.priority DESC, s.id ASC LIMIT 1'
                );
                $next->execute([$userId]);
                $survey = $next->fetch() ?: null;
                if ($survey) {
                    $survey['id'] = (int) $survey['id'];
                    $survey['reward_points'] = (int) $survey['reward_points'];
                    $survey['options'] = json_decode($survey['options_json'], true) ?: [];
                    unset($survey['options_json']);
                }

                $history = $pdo->prepare(
                    'SELECT amount, type, description, created_at FROM point_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 12'
                );
                $history->execute([$userId]);

                $withdrawals = $pdo->prepare(
                    'SELECT id, method, account_reference, points_amount, cfa_amount, status, created_at, processed_at
                     FROM withdrawals WHERE user_id = ? ORDER BY id DESC LIMIT 20'
                );
                $withdrawals->execute([$userId]);

                $response['survey'] = $survey;
                $response['survey_progress'] = ['completed' => $completed, 'total' => $total];
                $response['daily'] = dailyStatus($pdo, $userId);
                $response['history'] = $history->fetchAll();
                $response['withdrawals'] = $withdrawals->fetchAll();
            }

            jsonResponse($response);

        case 'register':
            if ($method !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $name = cleanText((string) ($data['full_name'] ?? ''), 120);
            $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
            $phone = cleanText((string) ($data['phone'] ?? ''), 30);
            $password = (string) ($data['password'] ?? '');
            $confirmation = (string) ($data['password_confirmation'] ?? '');

            if (mb_strlen($name) < 2 || !isValidEmail($email)) {
                jsonResponse(['ok' => false, 'message' => 'Renseignez un nom et une adresse e-mail valides.'], 422);
            }
            if (strlen($password) < 8 || $password !== $confirmation) {
                jsonResponse(['ok' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères et les deux saisies doivent être identiques.'], 422);
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
                $userId = (int) $pdo->lastInsertId();
                changePoints($pdo, $userId, 300, 'welcome', 'Bonus de bienvenue');
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() === '23000') {
                    jsonResponse(['ok' => false, 'message' => 'Cette adresse e-mail possède déjà un compte.'], 409);
                }
                throw $e;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            jsonResponse(['ok' => true, 'message' => 'Compte créé. Vos 300 points de bienvenue sont disponibles.']);

        case 'login':
            if ($method !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
            $password = (string) ($data['password'] ?? '');
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? AND active = 1 LIMIT 1');
            $stmt->execute([$email]);
            $account = $stmt->fetch();
            if (!$account || !password_verify($password, $account['password_hash'])) {
                jsonResponse(['ok' => false, 'message' => 'Adresse e-mail ou mot de passe incorrect.'], 401);
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $account['id'];
            jsonResponse(['ok' => true, 'message' => 'Connexion réussie.']);

        case 'logout':
            if ($method !== 'POST') {
                jsonResponse(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $_SESSION = [];
            session_destroy();
            jsonResponse(['ok' => true, 'message' => 'Vous êtes déconnecté.']);

        case 'answer_survey':
            $user = requireUser($pdo);
            $surveyId = (int) ($data['survey_id'] ?? 0);
            $answer = cleanText((string) ($data['answer'] ?? ''), 200);

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT id, question, options_json, reward_points FROM surveys WHERE id = ? AND active = 1 FOR UPDATE');
                $stmt->execute([$surveyId]);
                $survey = $stmt->fetch();
                if (!$survey) {
                    throw new RuntimeException('Ce sondage n’est plus disponible.');
                }
                $options = json_decode($survey['options_json'], true) ?: [];
                if (!in_array($answer, $options, true)) {
                    throw new RuntimeException('Choisissez une réponse proposée.');
                }

                $insert = $pdo->prepare('INSERT INTO survey_responses (survey_id, user_id, selected_option, points_earned) VALUES (?, ?, ?, ?)');
                $insert->execute([$surveyId, $user['id'], $answer, $survey['reward_points']]);
                changePoints(
                    $pdo,
                    (int) $user['id'],
                    (int) $survey['reward_points'],
                    'survey',
                    'Sondage complété : ' . mb_substr($survey['question'], 0, 120),
                    'survey',
                    $surveyId
                );
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() === '23000') {
                    jsonResponse(['ok' => false, 'message' => 'Vous avez déjà répondu à ce sondage.'], 409);
                }
                throw $e;
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            jsonResponse(['ok' => true, 'message' => '+' . (int) $survey['reward_points'] . ' points gagnés.']);

        case 'claim_daily':
            $user = requireUser($pdo);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT cycle_number, day_number, claimed_date FROM daily_claims WHERE user_id = ? ORDER BY claimed_date DESC, id DESC LIMIT 1 FOR UPDATE');
                $stmt->execute([$user['id']]);
                $last = $stmt->fetch();
                $today = new DateTimeImmutable('today');
                $cycle = 1;
                $day = 1;

                if ($last) {
                    $lastDate = new DateTimeImmutable($last['claimed_date']);
                    $difference = (int) $lastDate->diff($today)->format('%r%a');
                    if ($difference === 0) {
                        throw new RuntimeException('La récompense du jour a déjà été récupérée.');
                    }
                    $cycle = (int) $last['cycle_number'];
                    if ($difference === 1 && (int) $last['day_number'] < 10) {
                        $day = (int) $last['day_number'] + 1;
                    } else {
                        $cycle++;
                        $day = 1;
                    }
                }

                $reward = dailyRewards()[$day];
                $insert = $pdo->prepare('INSERT INTO daily_claims (user_id, cycle_number, day_number, reward_points, claimed_date) VALUES (?, ?, ?, ?, CURRENT_DATE)');
                $insert->execute([$user['id'], $cycle, $day, $reward]);
                changePoints($pdo, (int) $user['id'], $reward, 'daily', "Récompense quotidienne — jour {$day}", 'daily_claim', (int) $pdo->lastInsertId());
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() === '23000') {
                    throw new RuntimeException('La récompense du jour a déjà été récupérée.');
                }
                throw $e;
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            jsonResponse(['ok' => true, 'message' => "+{$reward} points reçus pour le jour {$day}."]);

        case 'redeem':
            $user = requireUser($pdo);
            $rewardId = (int) ($data['reward_id'] ?? 0);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT id, name, cost_points, stock FROM rewards WHERE id = ? AND active = 1 FOR UPDATE');
                $stmt->execute([$rewardId]);
                $reward = $stmt->fetch();
                if (!$reward) {
                    throw new RuntimeException('Récompense indisponible.');
                }
                if ($reward['stock'] !== null && (int) $reward['stock'] < 1) {
                    throw new RuntimeException('Cette récompense est épuisée.');
                }
                changePoints($pdo, (int) $user['id'], -(int) $reward['cost_points'], 'redemption', 'Échange : ' . $reward['name'], 'reward', $rewardId);
                $insert = $pdo->prepare('INSERT INTO redemptions (user_id, reward_id, points_spent) VALUES (?, ?, ?)');
                $insert->execute([$user['id'], $rewardId, $reward['cost_points']]);
                if ($reward['stock'] !== null) {
                    $pdo->prepare('UPDATE rewards SET stock = stock - 1 WHERE id = ?')->execute([$rewardId]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            jsonResponse(['ok' => true, 'message' => 'Récompense échangée : ' . $reward['name'] . '.']);

        case 'withdraw':
            $user = requireUser($pdo);
            $methodName = mb_strtolower((string) ($data['method'] ?? ''));
            $allowed = ['paypal', 'bitcoin', 'momo'];
            $account = cleanText((string) ($data['account'] ?? ''), 190);
            $points = (int) ($data['points'] ?? 0);
            if (!in_array($methodName, $allowed, true) || mb_strlen($account) < 5) {
                jsonResponse(['ok' => false, 'message' => 'Méthode ou compte de retrait invalide.'], 422);
            }
            if ($points < MIN_WITHDRAWAL_POINTS) {
                jsonResponse(['ok' => false, 'message' => 'Le retrait minimum est de ' . number_format(MIN_WITHDRAWAL_POINTS, 0, ',', ' ') . ' points.'], 422);
            }

            $pdo->beginTransaction();
            try {
                changePoints($pdo, (int) $user['id'], -$points, 'withdrawal', 'Demande de retrait ' . strtoupper($methodName), 'withdrawal');
                $stmt = $pdo->prepare('INSERT INTO withdrawals (user_id, method, account_reference, points_amount, cfa_amount) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$user['id'], $methodName, $account, $points, pointsToCfa($points)]);
                $withdrawalId = (int) $pdo->lastInsertId();
                $pdo->prepare("UPDATE point_transactions SET reference_id = ? WHERE user_id = ? AND reference_type = 'withdrawal' AND reference_id IS NULL ORDER BY id DESC LIMIT 1")
                    ->execute([$withdrawalId, $user['id']]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            jsonResponse(['ok' => true, 'message' => 'Demande enregistrée. Montant estimé : ' . number_format(pointsToCfa($points), 0, ',', ' ') . ' FCFA.']);

        case 'admin_data':
            requireAdmin($pdo);
            $stats = [
                'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
                'surveys' => (int) $pdo->query('SELECT COUNT(*) FROM surveys WHERE active = 1')->fetchColumn(),
                'responses' => (int) $pdo->query('SELECT COUNT(*) FROM survey_responses')->fetchColumn(),
                'pending_withdrawals' => (int) $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn(),
            ];
            $surveys = $pdo->query(
                'SELECT s.id, s.question, s.options_json, s.reward_points, s.active, c.name AS category
                 FROM surveys s JOIN survey_categories c ON c.id = s.category_id ORDER BY s.id DESC LIMIT 100'
            )->fetchAll();
            $withdrawals = $pdo->query(
                'SELECT w.id, w.method, w.account_reference, w.points_amount, w.cfa_amount, w.status, w.created_at,
                        u.full_name, u.email
                 FROM withdrawals w JOIN users u ON u.id = w.user_id ORDER BY w.id DESC LIMIT 100'
            )->fetchAll();
            $users = $pdo->query(
                'SELECT id, full_name, email, phone, role, points, active, created_at
                 FROM users ORDER BY id DESC LIMIT 200'
            )->fetchAll();
            jsonResponse(['ok' => true, 'stats' => $stats, 'surveys' => $surveys, 'withdrawals' => $withdrawals, 'users' => $users]);

        case 'admin_create_survey':
            requireAdmin($pdo);
            $question = cleanText((string) ($data['question'] ?? ''), 500);
            $categoryId = (int) ($data['category_id'] ?? 1);
            $rewardPoints = max(1, min(500, (int) ($data['reward_points'] ?? 25)));
            $options = is_array($data['options'] ?? null) ? $data['options'] : [];
            $options = array_values(array_unique(array_filter(array_map(fn ($item) => cleanText((string) $item, 120), $options))));
            if (mb_strlen($question) < 10 || count($options) < 2 || count($options) > 6) {
                jsonResponse(['ok' => false, 'message' => 'Ajoutez une question claire et entre 2 et 6 réponses.'], 422);
            }
            $stmt = $pdo->prepare('INSERT INTO surveys (category_id, question, options_json, reward_points) VALUES (?, ?, ?, ?)');
            $stmt->execute([$categoryId, $question, json_encode($options, JSON_UNESCAPED_UNICODE), $rewardPoints]);
            jsonResponse(['ok' => true, 'message' => 'Sondage ajouté.']);

        case 'admin_toggle_survey':
            requireAdmin($pdo);
            $surveyId = (int) ($data['survey_id'] ?? 0);
            $pdo->prepare('UPDATE surveys SET active = NOT active WHERE id = ?')->execute([$surveyId]);
            jsonResponse(['ok' => true, 'message' => 'État du sondage modifié.']);

        case 'admin_toggle_user':
            $admin = requireAdmin($pdo);
            $targetUserId = (int) ($data['user_id'] ?? 0);
            if ($targetUserId < 1) {
                jsonResponse(['ok' => false, 'message' => 'Utilisateur invalide.'], 422);
            }
            if ($targetUserId === (int) $admin['id']) {
                jsonResponse(['ok' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte administrateur.'], 422);
            }
            $stmt = $pdo->prepare('UPDATE users SET active = NOT active WHERE id = ?');
            $stmt->execute([$targetUserId]);
            if ($stmt->rowCount() !== 1) {
                jsonResponse(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
            }
            jsonResponse(['ok' => true, 'message' => 'État du compte utilisateur modifié.']);

        case 'admin_withdrawal_status':
            requireAdmin($pdo);
            $withdrawalId = (int) ($data['withdrawal_id'] ?? 0);
            $status = (string) ($data['status'] ?? '');
            if (!in_array($status, ['approved', 'paid', 'rejected'], true)) {
                jsonResponse(['ok' => false, 'message' => 'Statut invalide.'], 422);
            }
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id = ? FOR UPDATE');
                $stmt->execute([$withdrawalId]);
                $withdrawal = $stmt->fetch();
                if (!$withdrawal) {
                    throw new RuntimeException('Demande introuvable.');
                }
                $currentStatus = (string) $withdrawal['status'];
                $allowedTransitions = [
                    'pending' => ['approved', 'rejected'],
                    'approved' => ['paid', 'rejected'],
                    'paid' => [],
                    'rejected' => [],
                ];
                if (!in_array($status, $allowedTransitions[$currentStatus] ?? [], true)) {
                    throw new RuntimeException('Ce changement de statut n’est pas autorisé.');
                }
                if ($status === 'rejected' && $withdrawal['refunded_at'] === null) {
                    changePoints($pdo, (int) $withdrawal['user_id'], (int) $withdrawal['points_amount'], 'refund', 'Remboursement du retrait refusé', 'withdrawal', $withdrawalId);
                    $pdo->prepare('UPDATE withdrawals SET status = ?, processed_at = NOW(), refunded_at = NOW() WHERE id = ?')->execute([$status, $withdrawalId]);
                } else {
                    $pdo->prepare('UPDATE withdrawals SET status = ?, processed_at = NOW() WHERE id = ?')->execute([$status, $withdrawalId]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            jsonResponse(['ok' => true, 'message' => 'Statut du retrait mis à jour.']);

        default:
            jsonResponse(['ok' => false, 'message' => 'Action inconnue.'], 404);
    }
} catch (RuntimeException $e) {
    jsonResponse(['ok' => false, 'message' => $e->getMessage()], 422);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['ok' => false, 'message' => 'La base de données est indisponible. Vérifiez config/database.php et importez database/database.sql.'], 500);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse(['ok' => false, 'message' => 'Une erreur inattendue est survenue.'], 500);
}
