<div class="container py-5">

    <h1 class="mb-4">Gestion des paiements</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php if (empty($paiements)): ?>
                <div class="alert alert-info">Aucun paiement trouvé</div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transaction</th>
                            <th>Commande</th>
                            <th>Montant</th>
                            <th>Moyen</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($paiements as $paiement): ?>

                            <?php
                            $badge = "bg-secondary";
                            $label = $paiement['statut'];

                            if ($paiement['statut'] === 'accepte') {
                                $badge = "bg-success";
                                $label = "Accepté";
                            } elseif ($paiement['statut'] === 'refuse') {
                                $badge = "bg-danger";
                                $label = "Refusé";
                            }
                            ?>

                            <tr>
                                <td>#<?= $paiement['id'] ?></td>

                                <td><?= htmlspecialchars($paiement['numero_transaction']) ?></td>

                                <td>
                                    <a href="/bo/commande/<?= $paiement['id_commande'] ?>">
                                        #<?= $paiement['id_commande'] ?>
                                    </a>
                                </td>

                                <td>
                                    <?= number_format($paiement['montant'] / 100, 2, ',', ' ') ?> €
                                </td>

                                <td><?= htmlspecialchars($paiement['moyen_paiement']) ?></td>

                                <td>
                                    <span class="badge <?= $badge ?>">
                                        <?= $label ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (new DateTime($paiement['date_paiement']))->format('d/m/Y H:i') ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>