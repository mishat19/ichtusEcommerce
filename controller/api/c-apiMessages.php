<?php

function APIMessages() {
    global $pdo;

    $isDirectApiCall = isset($_GET['pageAPI']) && $_GET['pageAPI'] === 'messages';

    if ($isDirectApiCall) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
            return;
        }

        $token = $_POST['token'] ?? '';

        if ($token !== 'WDIhUThWMz9aN0Y0VDFwOUE2') {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized'
            ]);
            return;
        }
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {

        case 'getUnreadCount':

            try {

                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM messages_contact
                    WHERE statut = 'unread'
                ");

                $stmt->execute();

                echo json_encode([
                    'success' => true,
                    'count' => (int)$stmt->fetchColumn()
                ]);

            } catch (PDOException $e) {

                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            break;

        case 'getMessages':

            try {

                $filter = $_POST['filter'] ?? 'all';

                $validFilters = [
                    'all',
                    'unread',
                    'read',
                    'processed',
                    'archived'
                ];

                if (!in_array($filter, $validFilters)) {
                    $filter = 'all';
                }

                $sql = "
                    SELECT
                        id,
                        nom,
                        email,
                        sujet,
                        message,
                        date_envoi,
                        statut
                    FROM messages_contact
                ";

                $params = [];

                if ($filter !== 'all') {
                    $sql .= " WHERE statut = ?";
                    $params[] = $filter;
                }

                $sql .= " ORDER BY date_envoi DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                echo json_encode([
                    'success' => true,
                    'messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)
                ]);

            } catch (PDOException $e) {

                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            break;

        case 'updateStatut':

            try {

                $id = (int)($_POST['id'] ?? 0);
                $statut = $_POST['statut'] ?? '';

                $validStatuts = [
                    'unread',
                    'read',
                    'processed',
                    'archived'
                ];

                if (
                    $id <= 0 ||
                    !in_array($statut, $validStatuts)
                ) {

                    echo json_encode([
                        'success' => false,
                        'error' => 'Paramètres invalides'
                    ]);

                    return;
                }

                $stmt = $pdo->prepare("
                    UPDATE messages_contact
                    SET statut = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $statut,
                    $id
                ]);



                echo json_encode([
                    'success' => true
                ]);

            } catch (PDOException $e) {

                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            break;

        case 'getMessageDetails':

            try {

                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {

                    echo json_encode([
                        'success' => false,
                        'error' => 'ID invalide'
                    ]);

                    return;
                }

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM messages_contact
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                $message = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$message) {

                    echo json_encode([
                        'success' => false,
                        'error' => 'Message introuvable'
                    ]);

                    return;
                }

                echo json_encode([
                    'success' => true,
                    'message' => $message
                ]);

            } catch (PDOException $e) {

                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            break;

        default:

            echo json_encode([
                'success' => false,
                'error' => 'Action inconnue'
            ]);

            break;
    }
}