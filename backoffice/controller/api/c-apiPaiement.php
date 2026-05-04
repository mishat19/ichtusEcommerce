<?php

function handlePaiements($id = null) {
    if ($id) {
        getPaiement($id);
    } else {
        getPaiements();
    }
}

function getPaiements() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT 
            p.*,
            c.numero_facture
        FROM paiement p
        JOIN commande c ON c.id = p.id_commande
        ORDER BY p.date_paiement DESC
        LIMIT 50
    ");

    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($paiements);
}

function getPaiement($id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT *
        FROM paiement
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paiement) {
        http_response_code(404);
        echo json_encode(['error' => 'Paiement not found']);
        return;
    }

    echo json_encode($paiement);
}