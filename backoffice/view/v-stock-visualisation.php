<?php
// Récupère l'ID du stack depuis l'URL
$id_stack = (int)($_GET['id_stack'] ?? 0);

// Récupère les infos du stack
$stmtStack = $pdo->prepare("
    SELECT s.*, m.nom AS meuble_nom, e.nom AS entrepot_nom
    FROM stack s
    JOIN meuble m ON m.id = s.id_meuble
    JOIN entrepot e ON e.id = m.id_entrepot
    WHERE s.id = ?
");
$stmtStack->execute([$id_stack]);
$stack = $stmtStack->fetch(PDO::FETCH_ASSOC);

// Si le stack n'existe pas, redirige
if (!$stack) {
    header('Location: /backoffice/stock');
    exit;
}

// Récupère les produits du stack
$stmtProduits = $pdo->prepare("
    SELECT sp.*, p.nom AS produit_nom, p.identifiant, p.image
    FROM stack_produit sp
    JOIN produit p ON p.id = sp.id_produit
    WHERE sp.id_stack = ?
    ORDER BY p.nom ASC
");
$stmtProduits->execute([$id_stack]);
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

// Calcule le taux d'occupation
$taux_occupation = $stack['capacite_max'] > 0
    ? round(($stack['capacite_utilisee'] / $stack['capacite_max']) * 100, 1)
    : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">
            <i class="fas fa-box-open me-2" style="color: var(--bo-primary);"></i>
            <?php echo htmlspecialchars($stack['entrepot_nom']); ?> → <?php echo htmlspecialchars($stack['meuble_nom']); ?> → <?php echo htmlspecialchars($stack['nom']); ?>
        </h2>
        <p class="text-muted small mb-0">
            Visualisation du contenu du stack (<?php echo htmlspecialchars($stack['capacite_utilisee']); ?>/<?php echo htmlspecialchars($stack['capacite_max']); ?> produits)
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="/backoffice/stock" class="btn btn-outline-secondary" style="border-radius: 10px;">
            <i class="fas fa-arrow-left me-2"></i> Retour
        </a>
        <a href="/backoffice/stock?id_stack=<?php echo $stack['id']; ?>" class="btn"
           style="background: var(--bo-primary); color: #fff; border-radius: 10px; font-weight: 600;">
            <i class="fas fa-plus me-2"></i> Ajouter du stock
        </a>
    </div>
</div>

<!-- Barre de progression globale -->
<div class="bo-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold">Taux d'occupation</span>
        <span class="fw-bold fs-5" style="color: <?php echo $taux_occupation >= 80 ? '#ef4444' : ($taux_occupation >= 50 ? '#f59e0b' : '#10b981'); ?>">
            <?php echo $taux_occupation; ?>%
        </span>
    </div>
    <div class="progress" style="height: 12px; border-radius: 6px;">
        <div class="progress-bar"
             style="width: <?php echo $taux_occupation; ?>%; border-radius: 6px; background: <?php echo $taux_occupation >= 80 ? '#ef4444' : ($taux_occupation >= 50 ? '#f59e0b' : '#10b981'); ?>;">
        </div>
    </div>
    <div class="d-flex justify-content-between mt-2 small text-muted">
        <span>0</span>
        <span><?php echo htmlspecialchars($stack['capacite_max']); ?></span>
    </div>
</div>

<!-- Liste des produits -->
<div class="bo-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <i class="fas fa-list me-2" style="color: var(--bo-primary);"></i>
            Produits dans ce stack (<?php echo count($produits); ?>)
        </h5>
    </div>

    <?php if (empty($produits)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fs-1 text-muted mb-3"></i>
            <p class="text-muted">Ce stack est vide.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($produits as $produit): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-3 h-100" style="border-color: var(--bo-border);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($produit['produit_nom']); ?></h6>
                                <div class="small text-muted"><?php echo htmlspecialchars($produit['identifiant']); ?></div>
                            </div>
                            <span class="badge"
                                  style="background: var(--bo-primary); color: #fff; font-size: 0.8rem; border-radius: 6px;">
                                <?php echo htmlspecialchars($produit['quantite']); ?> unité(s)
                            </span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                            <div class="progress-bar"
                                 style="width: <?php echo min(100, ($produit['quantite'] / $stack['capacite_max']) * 100); ?>%;
                                     border-radius: 4px;
                                     background: var(--bo-primary);">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small text-muted">
                            <span>
                                <i class="fas fa-weight-hanging me-1"></i>
                                <?php echo htmlspecialchars($produit['quantite']); ?> / <?php echo htmlspecialchars($stack['capacite_max']); ?>
                            </span>
                            <div class="d-flex gap-1">
                                <button class="btn btn-xs btn-outline-secondary" style="border-radius: 6px;">
                                    <i class="fas fa-edit" style="font-size: 0.7rem;"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger" style="border-radius: 6px;">
                                    <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Statistiques -->
<div class="bo-card mt-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-chart-bar me-2" style="color: var(--bo-primary);"></i>
        Statistiques
    </h5>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-3" style="background: #f8fafc; border-radius: 10px;">
                <div style="width: 48px; height: 48px; background: rgba(79, 70, 229, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-boxes" style="color: var(--bo-primary); font-size: 1.2rem;"></i>
                </div>
                <div>
                    <div class="small text-muted">Produits différents</div>
                    <div class="fw-bold fs-4"><?php echo count($produits); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-3" style="background: #f8fafc; border-radius: 10px;">
                <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-weight-hanging" style="color: var(--bo-success); font-size: 1.2rem;"></i>
                </div>
                <div>
                    <div class="small text-muted">Quantité totale</div>
                    <div class="fw-bold fs-4"><?php echo $stack['capacite_utilisee']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-xs {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
        line-height: 1;
    }
</style>