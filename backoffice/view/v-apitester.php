<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Configurateur de requêtes Postman</h2>
        <p class="text-muted">Générateur automatique de requêtes et de corps de requêtes (Body) pour vos tests</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
            <i class="fas fa-key me-1"></i> Token API : WDIhUThWMz9aN0Y0VDFwOUE2
        </span>
    </div>
</div>

<!-- Alerte explicative -->
<div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert" style="background: rgba(59, 130, 246, 0.08); color: #1e3a8a;">
    <i class="fas fa-info-circle fs-4 text-primary"></i>
    <div>
        <strong>Comment fonctionne l'authentification ?</strong> Les APIs de cette application ne s'appuient pas sur la session PHP du Backoffice mais exigent une authentification par <strong>jeton POST</strong>. Toutes les requêtes HTTP doivent être envoyées en méthode <strong>POST</strong> avec le paramètre <code>token</code> renseigné dans le corps (Body) au format <code>x-www-form-urlencoded</code>.
    </div>
</div>

<div class="row g-4">
    <!-- Colonne Gauche : Configuration de la requête -->
    <div class="col-lg-5">
        <div class="bo-card h-100">
            <h5 class="bo-card-title mb-4">
                <i class="fas fa-sliders-h text-primary"></i> Choisir le Scénario
            </h5>

            <!-- Étape 1 : Choisir la ressource (Endpoint) -->
            <div class="mb-3">
                <label for="api-endpoint" class="form-label fw-bold text-secondary small text-uppercase">1. Ressource / Endpoint</label>
                <select id="api-endpoint" class="form-select border-2" onchange="onEndpointChange()">
                    <option value="liste">Liste générale des endpoints (?pageAPI=liste)</option>
                    <option value="commande">Commandes (?pageAPI=commande)</option>
                    <option value="paiement">Paiements (?pageAPI=paiement)</option>
                    <option value="stock">Stock (?pageAPI=stock)</option>
                    <option value="messages">Messages (?pageAPI=messages)</option>
                </select>
            </div>

            <!-- Étape 2 : Choisir le scénario (Action préconfigurée) -->
            <div class="mb-4">
                <label for="api-scenario" class="form-label fw-bold text-secondary small text-uppercase">2. Scénario / Action</label>
                <select id="api-scenario" class="form-select border-2" onchange="onScenarioChange()">
                    <!-- Rempli dynamiquement en JS -->
                </select>
            </div>

            <hr class="my-4">

            <!-- Étape 3 : Paramètres du Body (POST) -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label fw-bold text-secondary small text-uppercase mb-0">3. Modifier les valeurs</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="addParamRow()">
                        <i class="fas fa-plus me-1"></i> Paramètre
                    </button>
                </div>
                <p class="text-muted small mb-3">Modifiez les valeurs ci-dessous pour mettre à jour automatiquement le guide de configuration Postman à droite.</p>
                
                <div id="params-container" class="d-flex flex-column gap-2">
                    <!-- Rempli dynamiquement en JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Colonne Droite : Guide de configuration Postman -->
    <div class="col-lg-7">
        <div class="bo-card h-100">
            <h5 class="bo-card-title mb-4">
                <i class="fab fa-square-space text-primary"></i> Guide de Configuration Postman
            </h5>
            
            <p class="text-muted small mb-4">Suivez ces instructions pour configurer et tester avec succès votre requête dans Postman.</p>

            <!-- Étape 1 : Method & URL -->
            <div class="card border border-light shadow-sm mb-3">
                <div class="card-header bg-light fw-bold py-2 text-secondary small">
                    <span class="badge bg-secondary me-2">Étape 1</span> Méthode & URL de la requête
                </div>
                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <span class="badge bg-success py-2 px-3 fw-bold fs-6">POST</span>
                        </div>
                        <div class="col">
                            <input type="text" id="postman-url" class="form-control text-monospace bg-light" readonly value="">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('postman-url')" title="Copier l'URL">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Étape 2 : Headers -->
            <div class="card border border-light shadow-sm mb-3">
                <div class="card-header bg-light fw-bold py-2 text-secondary small">
                    <span class="badge bg-secondary me-2">Étape 2</span> En-têtes (Headers)
                </div>
                <div class="card-body py-2">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th>Clé (Key)</th>
                                <th>Valeur (Value)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>Content-Type</code></td>
                                <td><code>application/x-www-form-urlencoded</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Étape 3 : Body -->
            <div class="card border border-light shadow-sm mb-3">
                <div class="card-header bg-light fw-bold py-2 text-secondary small">
                    <span class="badge bg-secondary me-2">Étape 3</span> Corps de la requête (Body)
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Dans l'onglet <strong>Body</strong> de Postman, cochez l'option <strong>x-www-form-urlencoded</strong> et insérez les clés suivantes :</p>
                    
                    <table class="table table-striped table-bordered table-sm mb-0">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th>Clé (Key)</th>
                                <th>Valeur (Value)</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody id="postman-body-table">
                            <!-- Rempli dynamiquement en JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Astuce de connexion -->
            <div class="alert alert-warning border-0 small mb-0 d-flex gap-3" role="alert" style="background: rgba(245, 158, 11, 0.08); color: #78350f;">
                <i class="fas fa-exclamation-circle text-warning fs-5"></i>
                <div>
                    <strong>Astuce d'authentification :</strong> Contrairement au backoffice qui s'appuie sur une session cookie du navigateur, l'API authentifie vos appels uniquement via la clé <code>token</code> dans le Body. Inutile de configurer des cookies ou d'autres autorisations complexes dans Postman, le paramètre token suffit !
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuration des endpoints, scénarios et de leurs paramètres
    const API_CONFIG = {
        liste: {
            title: "Liste générale des endpoints",
            scenarios: [
                {
                    name: "Lister tous les endpoints disponibles",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire pour l'authentification." }
                    ]
                }
            ]
        },
        commande: {
            title: "API Commandes",
            scenarios: [
                {
                    name: "Lister toutes les commandes",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire pour l'authentification." }
                    ]
                },
                {
                    name: "Détail d'une commande spécifique",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire." },
                        { key: "id", value: "1", desc: "ID unique de la commande recherchée." }
                    ]
                }
            ]
        },
        paiement: {
            title: "API Paiements",
            scenarios: [
                {
                    name: "Lister tous les paiements",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire." }
                    ]
                },
                {
                    name: "Détail d'un paiement spécifique",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire." },
                        { key: "id", value: "1", desc: "ID du paiement recherché." }
                    ]
                },
                {
                    name: "Statistiques du Dashboard de paiements",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire." },
                        { key: "action", value: "dashboard", desc: "Action spéciale pour obtenir le CA et les derniers paiements." }
                    ]
                }
            ]
        },
        stock: {
            title: "API Stocks",
            scenarios: [
                {
                    name: "Consulter tous les stocks",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API obligatoire." },
                        { key: "action", value: "list", desc: "Action obligatoire pour lister les stocks." }
                    ]
                },
                {
                    name: "Consulter le stock d'un produit spécifique",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getStock", desc: "Action pour cibler un produit." },
                        { key: "id_produit", value: "1", desc: "ID du produit." }
                    ]
                },
                {
                    name: "Réserver du stock (Ajout panier)",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "reserver", desc: "Réserver du stock disponible." },
                        { key: "id_produit", value: "1", desc: "ID du produit concerné." },
                        { key: "quantite", value: "2", desc: "Quantité à réserver." }
                    ]
                },
                {
                    name: "Libérer du stock (Expiration panier)",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "liberer", desc: "Remettre en vente du stock réservé." },
                        { key: "id_produit", value: "1", desc: "ID du produit." },
                        { key: "quantite", value: "2", desc: "Quantité à libérer." }
                    ]
                },
                {
                    name: "Mise à jour du stock (Entrée / Mouvement)",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "updateStock", desc: "Enregistrer un mouvement de stock." },
                        { key: "id_produit", value: "1", desc: "ID du produit." },
                        { key: "quantite", value: "10", desc: "Quantité associée au mouvement." },
                        { key: "type_mouvement", value: "entree", desc: "Type de flux : 'entree' ou 'sortie'." },
                        { key: "commentaire", value: "Réapprovisionnement automatique", desc: "Optionnel." },
                        { key: "id_stack", value: "", desc: "ID optionnel de l'emplacement (stack)." }
                    ]
                },
                {
                    name: "Ajouter des produits dans un Stack (Batch)",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "addToStack", desc: "Ajouter en lot dans un emplacement." },
                        { key: "id_stack", value: "1", desc: "ID de l'emplacement meuble." },
                        { key: "produits", value: '[{"id_produit": 1, "quantite": 5}]', desc: "Tableau JSON structuré [{id_produit, quantite}, ...]" }
                    ]
                },
                {
                    name: "Lister les entrepôts logistiques",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getEntrepots", desc: "Liste les entrepôts avec leur meuble et taux d'occupation." }
                    ]
                },
                {
                    name: "Lister tous les Stacks (Emplacements)",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getStacks", desc: "Liste de tous les stacks." }
                    ]
                },
                {
                    name: "Lister les produits actifs",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getProduits", desc: "Liste épurée des produits." }
                    ]
                },
                {
                    name: "Obtenir le QR Code d'un produit",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getQrCode", desc: "Récupérer les informations QR Code d'un produit." },
                        { key: "id_produit", value: "1", desc: "ID du produit." }
                    ]
                }
            ]
        },
        messages: {
            title: "API Messages",
            scenarios: [
                {
                    name: "Nombre de messages non lus",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getUnreadCount", desc: "Récupère le décompte des messages contact non lus." }
                    ]
                },
                {
                    name: "Lister tous les messages de contact",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getMessages", desc: "Action obligatoire." },
                        { key: "filter", value: "all", desc: "Filtres autorisés : all, unread, read, processed, archived." }
                    ]
                },
                {
                    name: "Détail d'un message spécifique",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "getMessageDetails", desc: "Récupère le message par son ID." },
                        { key: "id", value: "1", desc: "ID unique du message." }
                    ]
                },
                {
                    name: "Mettre à jour le statut d'un message",
                    params: [
                        { key: "token", value: "WDIhUThWMz9aN0Y0VDFwOUE2", desc: "Token API." },
                        { key: "action", value: "updateStatut", desc: "Modifie l'état de lecture d'un message." },
                        { key: "id", value: "1", desc: "ID unique du message." },
                        { key: "statut", value: "read", desc: "Statuts autorisés : unread, read, processed, archived." }
                    ]
                }
            ]
        }
    };

    // Chargement de la page
    document.addEventListener("DOMContentLoaded", () => {
        // Initialise l'onglet et scénario
        onEndpointChange();
    });

    // Événement changement d'endpoint (étape 1)
    function onEndpointChange() {
        const endpointSelect = document.getElementById("api-endpoint");
        const scenarioSelect = document.getElementById("api-scenario");
        const endpointKey = endpointSelect.value;
        const config = API_CONFIG[endpointKey];

        // Vider et remplir les scénarios
        scenarioSelect.innerHTML = "";
        config.scenarios.forEach((scenario, index) => {
            const option = document.createElement("option");
            option.value = index;
            option.textContent = scenario.name;
            scenarioSelect.appendChild(option);
        });

        onScenarioChange();
    }

    // Événement changement de scénario (étape 2)
    function onScenarioChange() {
        const endpointSelect = document.getElementById("api-endpoint");
        const scenarioSelect = document.getElementById("api-scenario");
        const endpointKey = endpointSelect.value;
        const scenarioIndex = parseInt(scenarioSelect.value) || 0;
        
        const config = API_CONFIG[endpointKey];
        const scenario = config.scenarios[scenarioIndex];

        if (!scenario) return;

        // Vider et remplir les paramètres du Body
        const container = document.getElementById("params-container");
        container.innerHTML = "";

        scenario.params.forEach(param => {
            addParamRow(param.key, param.value, param.desc);
        });

        // Mettre à jour l'URL affichée pour Postman
        updatePostmanDetails();
    }

    // Ajouter une ligne de paramètre
    function addParamRow(key = "", value = "", desc = "") {
        const container = document.getElementById("params-container");
        
        const row = document.createElement("div");
        row.className = "row g-2 align-items-center param-row mb-1";
        row.innerHTML = `
            <div class="col-4">
                <input type="text" class="form-control form-control-sm text-monospace param-key fw-bold" placeholder="Clé" value="${key}" oninput="updatePostmanDetails()">
            </div>
            <div class="col-7">
                <input type="text" class="form-control form-control-sm param-value" placeholder="Valeur" value="${value}" oninput="updatePostmanDetails()">
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-sm text-danger px-1 py-0" onclick="this.closest('.row').remove(); updatePostmanDetails();">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${desc ? `<div class="col-12"><small class="text-muted d-block ps-1" style="font-size: 0.75rem; margin-top:-2px;">${desc}</small></div>` : ''}
        `;
        
        container.appendChild(row);
    }

    // Mettre à jour les détails affichés pour Postman
    function updatePostmanDetails() {
        const endpointKey = document.getElementById("api-endpoint").value;
        
        // URL Postman
        const localOrigin = window.location.origin;
        const path = window.location.pathname.replace(/\/backoffice\/.*/, ''); // Supprime la fin '/backoffice/...' du chemin actuel pour avoir la racine
        const absoluteApiUrl = `${localOrigin}${path}/index.php?pageAPI=${endpointKey}`;
        
        document.getElementById("postman-url").value = absoluteApiUrl;

        // Table du corps Postman
        const bodyTable = document.getElementById("postman-body-table");
        bodyTable.innerHTML = "";

        // Récupérer les paramètres actuels
        const rows = document.querySelectorAll(".param-row");
        
        if (rows.length === 0) {
            bodyTable.innerHTML = `<tr><td colspan="3" class="text-center text-muted small">Aucun paramètre (requête vide)</td></tr>`;
            return;
        }

        rows.forEach(row => {
            const keyInput = row.querySelector(".param-key");
            const valInput = row.querySelector(".param-value");
            if (!keyInput) return;

            const key = keyInput.value.trim();
            const value = valInput ? valInput.value.trim() : "";
            
            if (!key) return;

            // Trouver si une description existe dans notre config
            let desc = "Paramètre personnalisé";
            const config = API_CONFIG[endpointKey];
            if (config) {
                config.scenarios.forEach(sc => {
                    const match = sc.params.find(p => p.key === key);
                    if (match) desc = match.desc;
                });
            }

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><strong class="text-primary">${key}</strong></td>
                <td><code class="text-secondary">${value || '(vide)'}</code></td>
                <td class="small text-muted">${desc}</td>
            `;
            bodyTable.appendChild(tr);
        });
    }

    // Copier n'importe quelle chaîne par ID d'élément
    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                // Utilisation d'un feedback visuel discret plutôt qu'un alert bloquant
                const btn = input.closest('.row').querySelector('button');
                const origHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-success"></i>';
                btn.classList.add('btn-outline-success');
                setTimeout(() => {
                    btn.innerHTML = origHtml;
                    btn.classList.remove('btn-outline-success');
                }, 1500);
            });
        }
    }
</script>
