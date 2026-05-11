<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Étapes -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step">
                    <div class="step-icon">1</div>
                    <div class="step-label">Récapitulatif</div>
                </div>
                <div class="step">
                    <div class="step-icon">2</div>
                    <div class="step-label">Adresses</div>
                </div>
                <div class="step active">
                    <div class="step-icon">3</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step">
                    <div class="step-icon">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <h2 class="mb-4">Paiement sécurisé</h2>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>Finalisation de votre paiement</strong>
                </div>

                <div class="card-body p-0">

                    <!-- IFRAME paiement -->
                    <iframe
                            name="iframe_paiement"
                            style="width: 100%; height: 700px; border: none;"
                    ></iframe>

                    <!-- FORM caché qui cible l'iframe -->
                    <form id="formPaiement"
                          method="post"
                          action="https://recette-tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi"
                          target="iframe_paiement">

                        <input type="hidden" name="PBX_SITE" value="<?php e($PBX_SITE); ?>">
                        <input type="hidden" name="PBX_RANG" value="<?php e($PBX_RANG); ?>">
                        <input type="hidden" name="PBX_IDENTIFIANT" value="<?php e($PBX_IDENTIFIANT); ?>">
                        <input type="hidden" name="PBX_TOTAL" value="<?php e($PBX_TOTAL); ?>">
                        <input type="hidden" name="PBX_DEVISE" value="<?php e($PBX_DEVISE); ?>">
                        <input type="hidden" name="PBX_CMD" value="<?php e($PBX_CMD); ?>">
                        <input type="hidden" name="PBX_PORTEUR" value="<?php e($PBX_PORTEUR); ?>">
                        <input type="hidden" name="PBX_RUF1" value="POST">
                        <input type="hidden" name="PBX_RETOUR" value="<?php e($PBX_RETOUR); ?>">
                        <input type="hidden" name="PBX_EFFECTUE" value="<?php e($PBX_EFFECTUE); ?>">
                        <input type="hidden" name="PBX_REFUSE" value="<?php e($PBX_REFUSE); ?>">
                        <input type="hidden" name="PBX_ANNULE" value="<?php e($PBX_ANNULE); ?>">
                        <input type="hidden" name="PBX_REPONDRE_A" value="<?php e($PBX_REPONDRE_A); ?>">
                        <input type="hidden" name="PBX_TIME" value="<?php e($PBX_TIME); ?>">
                        <input type="hidden" name="PBX_HMAC" value="<?php e($hmac); ?>">
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Chargement automatique dans l'iframe (plus propre que submit auto invisible)
    window.onload = () => {
        document.getElementById('formPaiement').submit();
    };
</script>