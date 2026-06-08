<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Testeur d'API Interactif</h2>
        <p class="text-muted">Simulateur de requêtes en direct et guide de configuration pour Postman</p>
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
                <i class="fas fa-sliders-h text-primary"></i> Configurer la Requête
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
                    <label class="form-label fw-bold text-secondary small text-uppercase mb-0">3. Paramètres du Corps (POST Body)</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1" onclick="addParamRow()">
                        <i class="fas fa-plus me-1"></i> Paramètre
                    </button>
                </div>
                <div class="text-muted small mb-3">Envoyé sous format <code>application/x-www-form-urlencoded</code>.</div>
                
                <div id="params-container" class="d-flex flex-column gap-2">
                    <!-- Rempli dynamiquement en JS -->
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="button" id="send-btn" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm" onclick="sendRequest()">
                    <i class="fas fa-paper-plane me-2"></i> Envoyer la requête
                </button>
            </div>
        </div>
    </div>

    <!-- Colonne Droite : Visualisation des résultats et guide Postman -->
    <div class="col-lg-7">
        <div class="bo-card h-100 d-flex flex-column">
            <!-- Navigation des onglets -->
            <ul class="nav nav-tabs border-bottom mb-4" id="apiTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-uppercase small py-3" id="response-tab" data-bs-toggle="tab" data-bs-target="#response-content" type="button" role="tab" aria-controls="response-content" aria-selected="true">
                        <i class="fas fa-terminal me-2"></i> Console de Réponse
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-uppercase small py-3" id="postman-tab" data-bs-toggle="tab" data-bs-target="#postman-content" type="button" role="tab" aria-controls="postman-content" aria-selected="false">
                        <i class="fab fa-square-space me-2"></i> Configurer Postman
                    </button>
                </li>
            </ul>

            <div class="tab-content flex-grow-1 d-flex flex-column" id="apiTabContent">
                <!-- CONTENU : CONSOLE DE REPONSE -->
                <div class="tab-pane fade show active flex-grow-1 d-flex flex-column" id="response-content" role="tabpanel" aria-labelledby="response-tab">
                    
                    <!-- Métriques de réponse -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded shadow-sm border border-light">
                                <span class="text-muted small d-block">Statut HTTP</span>
                                <span id="res-status" class="fw-bold fs-5 text-secondary">-</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded shadow-sm border border-light">
                                <span class="text-muted small d-block">Temps de Réponse</span>
                                <span id="res-time" class="fw-bold fs-5 text-secondary">-</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded shadow-sm border border-light">
                                <span class="text-muted small d-block">Méthode & URL</span>
                                <span class="fw-bold text-success fs-6 d-block text-truncate">POST <span id="res-endpoint-name" class="text-dark">/index.php</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Requête envoyée (Debug) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Corps envoyé :</label>
                        <div id="request-payload" class="bg-light p-2 rounded text-monospace small text-muted border border-light text-break" style="font-family: monospace;">
                            (Aucune requête envoyée)
                        </div>
                    </div>

                    <!-- Code JSON de réponse -->
                    <div class="flex-grow-1 d-flex flex-column position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-secondary small text-uppercase mb-0">Réponse brute (JSON) :</label>
                            <button class="btn btn-sm btn-outline-secondary py-0" onclick="copyResponse()">
                                <i class="far fa-copy me-1"></i> Copier
                            </button>
                        </div>
                        <div class="flex-grow-1 position-relative" style="min-height: 250px;">
                            <pre class="bg-dark text-light p-3 rounded h-100 overflow-auto m-0 shadow-sm border border-secondary" style="font-family: 'Consolas', 'Courier New', monospace; font-size: 0.85rem; max-height: 400px;"><code id="response-code" class="text-success">// Les résultats s'afficheront ici après envoi.</code></pre>
                        </div>
                    </div>
                </div>

                <!-- CONTENU : GUIDE POSTMAN -->
                <div class="tab-pane fade" id="postman-content" role="tabpanel" aria-labelledby="postman-tab">
                    
                    <!-- Section Titre -->
                    <div class="mb-4">
                        <h6 class="fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i>Guide étape par étape pour Postman</h6>
                        <p class="text-muted small">Voici les paramètres exacts à renseigner dans Postman pour reproduire le scénario sélectionné à gauche.</p>
                    </div>

                    <!-- Étape 1 : Method & URL -->
                    <div class="card border border-light shadow-sm mb-3">
                        <div class="card-header bg-light fw-bold py-2">
                            <span class="badge bg-secondary me-2">Étape 1</span> Méthode & URL
                        </div>
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <span class="badge bg-success py-2 px-3 fw-bold fs-6">POST</span>
                                </div>
                                <div class="col">
                                    <input type="text" id="postman-url" class="form-control" readonly value="">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('postman-url')">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Étape 2 : Headers -->
                    <div class="card border border-light shadow-sm mb-3">
                        <div class="card-header bg-light fw-bold py-2">
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
                        <div class="card-header bg-light fw-bold py-2">
                            <span class="badge bg-secondary me-2">Étape 3</span> Corps de la requête (Body)
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">Dans l'onglet <strong>Body</strong> de Postman, cochez l'option <strong>x-www-form-urlencoded</strong> et insérez les clés suivantes :</p>
                            
                            <table class="table table-striped table-bordered table-sm">
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
                            <strong>Astuce :</strong> Contrairement au backoffice qui utilise une session cookie après connexion, l'API utilise uniquement le <code>token</code>. Inutile d'importer des cookies ou des en-têtes d'autorisation complexes dans Postman, le paramètre token dans le Body suffit pour s'authentifier !
                        </div>
                    </div>
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
        // Définir la base de l'URL Postman
        const postmanUrlInput = document.getElementById("postman-url");
        const currentOrigin = window.location.origin;
        
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

    // Envoyer la requête fetch
    function sendRequest() {
        const sendBtn = document.getElementById("send-btn");
        const responseCode = document.getElementById("response-code");
        const resStatus = document.getElementById("res-status");
        const resTime = document.getElementById("res-time");
        const reqPayload = document.getElementById("request-payload");
        const endpointKey = document.getElementById("api-endpoint").value;

        // Désactiver le bouton pendant le chargement
        sendBtn.disabled = true;
        sendBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Envoi en cours...`;

        // Construire les paramètres du body
        const params = new URLSearchParams();
        const rows = document.querySelectorAll(".param-row");
        rows.forEach(row => {
            const keyInput = row.querySelector(".param-key");
            const valInput = row.querySelector(".param-value");
            if (keyInput) {
                const key = keyInput.value.trim();
                const val = valInput ? valInput.value : "";
                if (key) {
                    params.append(key, val);
                }
            }
        });

        // Debug de la charge utile envoyée
        reqPayload.textContent = params.toString() || "(Aucun paramètre)";
        document.getElementById("res-endpoint-name").textContent = `?pageAPI=${endpointKey}`;

        // Configurer l'URL locale relative
        const targetUrl = `./index.php?pageAPI=${endpointKey}`;

        // Mesurer le temps
        const startTime = performance.now();

        fetch(targetUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: params
        })
        .then(response => {
            const endTime = performance.now();
            const duration = Math.round(endTime - startTime);
            resTime.textContent = `${duration} ms`;

            // Statut HTTP
            resStatus.textContent = `${response.status} ${response.statusText}`;
            resStatus.className = "fw-bold fs-5 " + (response.ok ? "text-success" : "text-danger");

            return response.text().then(text => ({
                ok: response.ok,
                text: text
            }));
        })
        .then(result => {
            try {
                // Tenter de parser en JSON
                const json = JSON.parse(result.text);
                responseCode.textContent = JSON.stringify(json, null, 4);
                responseCode.className = result.ok ? "text-success" : "text-warning";
            } catch (e) {
                // Si ce n'est pas du JSON, afficher brut
                responseCode.textContent = result.text || "(Réponse vide)";
                responseCode.className = "text-danger";
            }
        })
        .catch(error => {
            const endTime = performance.now();
            resTime.textContent = `${Math.round(endTime - startTime)} ms`;
            resStatus.textContent = "Erreur de connexion";
            resStatus.className = "fw-bold fs-5 text-danger";
            
            responseCode.textContent = `Erreur réseau ou CORS:\n${error.message}`;
            responseCode.className = "text-danger";
        })
        .finally(() => {
            // Rétablir le bouton
            sendBtn.disabled = false;
            sendBtn.innerHTML = `<i class="fas fa-paper-plane me-2"></i> Envoyer la requête`;
            
            // Revenir sur l'onglet console de réponse si on était ailleurs
            const responseTabEl = document.getElementById("response-tab");
            const tab = bootstrap.Tab.getOrCreateInstance(responseTabEl);
            tab.show();
        });
    }

    // Copier la réponse dans le presse-papiers
    function copyResponse() {
        const codeElement = document.getElementById("response-code");
        const text = codeElement.textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert("Réponse copiée dans le presse-papiers !");
        });
    }

    // Copier n'importe quelle chaîne par ID d'élément
    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                alert("URL copiée !");
            });
        }
    }
</script>
