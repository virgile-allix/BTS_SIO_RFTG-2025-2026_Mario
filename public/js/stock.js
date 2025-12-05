// ===================================
// STOCK.JS - Gestion CRUD des Stocks
// Pattern: Copié depuis film.js
// ===================================

// Script chargé

// Cache global des statuts DVD (pour éviter de recharger)
window.dvdStatusCache = {};

// =============================
// TOGGLE DETAILS PAR FILM (Expandable Rows) - GLOBAL
// =============================

window.toggleFilmDetails = function(filmId) {
    const detailsRow = document.getElementById(`details-${filmId}`);
    const icon = document.getElementById(`icon-${filmId}`);
    const detailsBody = document.getElementById(`details-body-${filmId}`);

    if (!detailsRow) return;

    const isVisible = detailsRow.style.display !== 'none';

    if (isVisible) {
        // Fermer
        detailsRow.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    } else {
        // Ouvrir
        detailsRow.style.display = 'table-row';
        if (icon) icon.style.transform = 'rotate(90deg)';

        // Charger les données si pas encore chargées
        if (detailsBody && detailsBody.querySelector('em')) {
            loadFilmInventories(filmId, detailsBody);
        }
    }
};

function loadFilmInventories(filmId, detailsBody) {
    // Récupérer les inventaires depuis window.rawInventories
    const allInventories = window.rawInventories || [];
    let filmInventories = allInventories.filter(inv => {
        const invFilmId = inv.film?.filmId || inv.filmId;
        return invFilmId == filmId;
    });

    // Appliquer le filtre par magasin si actif
    const storeFilterSelect = document.getElementById('storeFilterSelect');
    const activeStoreFilter = storeFilterSelect ? storeFilterSelect.value : '';

    if (activeStoreFilter !== '') {
        filmInventories = filmInventories.filter(inv => {
            return inv.storeId == activeStoreFilter;
        });
    }


    if (filmInventories.length === 0) {
        detailsBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-warning" style="padding: 20px;">
                    ⚠️ Aucun DVD trouvé pour ce film
                </td>
            </tr>
        `;
        return;
    }

    // Construire le HTML des lignes avec un placeholder pour le statut
    let html = '';
    filmInventories.forEach(inv => {
        const invId = inv.inventoryId || inv.id;
        const storeId = inv.storeId;
        const filmTitle = inv.film?.title || 'Film';
        const lastUpdate = inv.lastUpdate ? new Date(inv.lastUpdate).toLocaleString('fr-FR') : 'N/A';

        // Get store label
        const stores = window.allStores || {};
        const storeLabel = stores[storeId] || `Boutique #${storeId}`;

        html += `
            <tr id="inv-row-${invId}">
                <td><strong>#${invId}</strong></td>
                <td>🏪 ${storeLabel}</td>
                <td class="text-center"><small>${lastUpdate}</small></td>
                <td class="text-center" id="status-${invId}">
                    <span class="badge" style="background: #ccc;">Chargement...</span>
                </td>
                <td class="text-center" id="actions-${invId}">
                    <a href="/stocks/${invId}/edit" class="btn btn-sm retro-btn-edit" title="Modifier" style="margin-right: 5px;">✏️</a>
                    <button onclick="confirmDeleteStock(${invId}, '${filmTitle.replace(/'/g, "\\'")}', event)" class="btn btn-sm retro-btn-delete-action" title="Supprimer">🗑️</button>
                </td>
            </tr>
        `;
    });

    detailsBody.innerHTML = html;

    // Charger le statut de TOUS les DVDs en une seule requête BATCH (RAPIDE!)
    const inventoryIds = filmInventories.map(inv => inv.inventoryId || inv.id);
    loadDVDStatusBatch(inventoryIds);
}

/**
 * Charger les statuts de plusieurs DVDs en une seule requête (BATCH - ULTRA RAPIDE avec CACHE)
 */
async function loadDVDStatusBatch(inventoryIds) {
    if (inventoryIds.length === 0) return;

    // Séparer les IDs en: déjà en cache vs à charger
    const idsToLoad = [];
    const cachedIds = [];

    inventoryIds.forEach(id => {
        if (window.dvdStatusCache[id] !== undefined) {
            // Déjà en cache, afficher directement
            cachedIds.push(id);
            updateDVDStatusUI(id, window.dvdStatusCache[id]);
        } else {
            // Pas en cache, à charger
            idsToLoad.push(id);
        }
    });

    if (cachedIds.length > 0) {
    }

    if (idsToLoad.length === 0) {
        return; // Tout est déjà en cache, pas besoin d'appel API
    }

    try {

        const response = await fetch('/stocks/availability/batch', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                inventory_ids: idsToLoad
            })
        });

        if (response.ok) {
            const data = await response.json();
            const results = data.results || {};


            // Mettre à jour tous les statuts ET les sauvegarder dans le cache
            for (const [inventoryId, isAvailable] of Object.entries(results)) {
                const id = parseInt(inventoryId);
                window.dvdStatusCache[id] = isAvailable; // Sauvegarder en cache
                updateDVDStatusUI(id, isAvailable);
            }
        } else {
            console.error('Erreur API batch:', response.status);
            // Fallback: afficher "Inconnu" pour tous
            idsToLoad.forEach(id => {
                const statusCell = document.getElementById(`status-${id}`);
                if (statusCell) {
                    statusCell.innerHTML = '<span class="badge" style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 3px;">Inconnu</span>';
                }
            });
        }
    } catch (error) {
        console.error('Erreur chargement batch:', error);
        // Fallback: afficher "Erreur" pour tous
        idsToLoad.forEach(id => {
            const statusCell = document.getElementById(`status-${id}`);
            if (statusCell) {
                statusCell.innerHTML = '<span class="badge" style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 3px;">Erreur</span>';
            }
        });
    }
}

/**
 * Mettre à jour l'UI pour un DVD spécifique
 */
function updateDVDStatusUI(inventoryId, isAvailable) {
    const statusCell = document.getElementById(`status-${inventoryId}`);
    const actionsCell = document.getElementById(`actions-${inventoryId}`);

    if (statusCell) {
        if (isAvailable) {
            // Libre (vert)
            statusCell.innerHTML = '<span class="badge" style="background: #28a745; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold;">Libre</span>';
        } else {
            // En cours (bleu)
            statusCell.innerHTML = '<span class="badge" style="background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold;">En cours</span>';

            // Désactiver le bouton supprimer si en cours
            if (actionsCell) {
                const deleteBtn = actionsCell.querySelector('.retro-btn-delete-action');
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.style.opacity = '0.5';
                    deleteBtn.style.cursor = 'not-allowed';
                    deleteBtn.title = 'Impossible de supprimer : DVD en cours de location';
                }
            }
        }
    }
}


// =============================
// SUPPRESSION - MODAL CUSTOM (GLOBAL)
// =============================

window.confirmDeleteStock = async function(inventoryId, filmTitle, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Afficher directement le modal de confirmation (pas de vérification préalable)
    showDeleteConfirmationModal(inventoryId, filmTitle, 0, null);
};

// Fonctions showLoadingModal et showBlockedModal supprimées (non utilisées)

function showDeleteConfirmationModal(inventoryId, filmTitle, historicalCount, lastRentalDate) {
    const warningHTML = historicalCount > 0 ? `
        <div class="alert alert-warning">
            ⚠️ Ce DVD a <strong>${historicalCount}</strong> location(s) historique(s).
            ${lastRentalDate ? `<br>Dernière location: ${new Date(lastRentalDate).toLocaleDateString('fr-FR')}` : ''}
        </div>
    ` : '';

    const modalHTML = `
        <div id="deleteStockModal" class="retro-modal-overlay" style="display: flex;">
            <div class="retro-modal" style="max-width: 500px;">
                <div class="retro-modal-header" style="background: #f39c12; color: white;">
                    <h4>⚠️ CONFIRMATION DE SUPPRESSION</h4>
                </div>
                <div class="retro-modal-body">
                    <p><strong>Film:</strong> ${filmTitle}</p>
                    <p><strong>Inventory ID:</strong> #${inventoryId}</p>
                    ${warningHTML}
                    <p class="text-danger"><strong>⚠️ Cette action est IRRÉVERSIBLE.</strong></p>
                    <hr>
                    <label for="deleteConfirmInput" style="font-weight: bold;">
                        Pour confirmer, tapez <span style="color: #dc3545; font-family: monospace;">DELETE</span> :
                    </label>
                    <input type="text" id="deleteConfirmInput" class="form-control retro-form-control mt-2" placeholder="DELETE" autocomplete="off" style="text-transform: uppercase;">
                    <small id="deleteInputError" class="text-danger" style="display: none;">❌ Vous devez taper "DELETE"</small>
                </div>
                <div class="retro-modal-footer">
                    <button onclick="closeDeleteModal()" class="retro-btn-secondary">❌ Annuler</button>
                    <button id="confirmDeleteBtn" onclick="handleDeleteStock(${inventoryId}, '${filmTitle.replace(/'/g, "\\'")}', event)" class="retro-btn-delete" disabled>🗑️ Confirmer la suppression</button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Validation en temps réel
    const input = document.getElementById('deleteConfirmInput');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const error = document.getElementById('deleteInputError');

    if (input && confirmBtn) {
        input.addEventListener('input', function() {
            const value = this.value.toUpperCase();
            if (value === 'DELETE') {
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = '1';
                error.style.display = 'none';
            } else {
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.5';
                if (value.length > 0) {
                    error.style.display = 'block';
                }
            }
        });

        input.focus();
    }
}

window.handleDeleteStock = async function(inventoryId, filmTitle, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const input = document.getElementById('deleteConfirmInput');
    if (input && input.value.toUpperCase() !== 'DELETE') {
        const error = document.getElementById('deleteInputError');
        if (error) error.style.display = 'block';
        return;
    }

    showLoader();
    closeDeleteModal();

    try {
        const response = await fetch(`/stocks/${inventoryId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        hideLoader();

        const data = await response.json();

        if (data.success) {
            showToast(data.message || 'Stock supprimé avec succès !', 'success');

            // Supprimer les lignes du tableau (film row + details row)
            const filmRows = document.querySelectorAll(`tr[data-film-id]`);
            filmRows.forEach(row => {
                const filmId = row.getAttribute('data-film-id');
                const detailsRow = document.getElementById(`details-${filmId}`);

                // Vérifier si ce film contient l'inventory supprimé
                if (detailsRow) {
                    const invRow = detailsRow.querySelector(`a[href="/stocks/${inventoryId}/edit"]`);
                    if (invRow) {
                        // Retirer la ligne de l'inventory supprimé
                        const trToRemove = invRow.closest('tr');
                        if (trToRemove) trToRemove.remove();

                        // Si plus de DVDs, fermer les détails et recharger la page
                        const remainingDvds = detailsRow.querySelectorAll('tbody tr').length;
                        if (remainingDvds === 0) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    }
                }
            });
        } else {
            showToast(data.message || 'Erreur lors de la suppression', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Erreur:', error);
        showToast('Erreur lors de la suppression', 'error');
    }
};

window.closeDeleteModal = function() {
    const modal = document.getElementById('deleteStockModal');
    if (modal) modal.remove();
};

function showLoader() {
    const loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.className = 'retro-loader-overlay';
    loader.innerHTML = '<div class="retro-loader"></div>';
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) loader.remove();
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `retro-toast retro-toast-${type}`;

    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    toast.innerHTML = `${icons[type] || icons.info} ${message}`;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {

    // =============================
    // FORMULAIRES - VALIDATION
    // =============================

    const stockForm = document.getElementById('stockForm');
    const stockEditForm = document.getElementById('stockEditForm');

    if (stockForm) {
        stockForm.addEventListener('submit', handleStockFormSubmit);
    }

    if (stockEditForm) {
        stockEditForm.addEventListener('submit', handleStockFormSubmit);
    }

    // =============================
    // ANCIENNE VERSION - Plus utilisée mais gardée pour référence
    // =============================

    window.toggleFilmDetailsOLD = function(filmId) {
        const detailsRow = document.getElementById(`details-${filmId}`);
        const icon = document.getElementById(`icon-${filmId}`);
        const detailsBody = document.getElementById(`details-body-${filmId}`);

        if (!detailsRow) return;

        const isVisible = detailsRow.style.display !== 'none';

        if (isVisible) {
            // Fermer
            detailsRow.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            // Ouvrir
            detailsRow.style.display = 'table-row';
            if (icon) icon.style.transform = 'rotate(90deg)';

            // Charger les données si pas encore chargées
            if (detailsBody && detailsBody.querySelector('em')) {
                loadFilmInventories(filmId, detailsBody);
            }
        }
    };

    // =============================
    // FONCTION loadFilmInventories - SUPPRIMÉE (utilise version globale en haut du fichier)
    // =============================
    // Cette fonction est définie en haut du fichier avec le support du filtre par magasin

    // =============================
    // MODAL GESTION INVENTAIRES PAR FILM (Legacy - garder pour compatibilité)
    // =============================

    window.showFilmInventories = async function(filmId, filmTitle) {

        try {
            // Fetch all inventories and filter by filmId
            const response = await fetch('/stocks', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                showToast('Erreur lors du chargement des inventaires', 'error');
                return;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Find all inventory rows for this film
            // We need to fetch from the API instead
            fetchInventoriesByFilm(filmId, filmTitle);

        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement', 'error');
        }
    };

    async function fetchInventoriesByFilm(filmId, filmTitle) {
        showLoader();

        try {
            // Get all inventories from the backend
            const response = await fetch(`/api/inventories?film_id=${filmId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            hideLoader();

            if (!response.ok) {
                // For now, show a simple message
                showInventoriesModal(filmId, filmTitle, []);
                return;
            }

            const inventories = await response.json();
            showInventoriesModal(filmId, filmTitle, inventories);

        } catch (error) {
            hideLoader();
            console.error('Erreur:', error);
            // Show modal anyway with empty data
            showInventoriesModal(filmId, filmTitle, []);
        }
    }

    function showInventoriesModal(filmId, filmTitle, inventories) {
        // Create modal HTML
        let inventoriesHTML = '';

        if (inventories.length === 0) {
            inventoriesHTML = '<p class="text-center text-muted">Chargement des DVDs...</p>';
        } else {
            inventoriesHTML = '<table class="table table-sm retro-table"><thead><tr><th>ID</th><th>Magasin</th><th>Actions</th></tr></thead><tbody>';

            inventories.forEach(inv => {
                const invId = inv.inventoryId || inv.id;
                const storeId = inv.storeId;

                inventoriesHTML += `
                    <tr>
                        <td>#${invId}</td>
                        <td>Boutique #${storeId}</td>
                        <td>
                            <a href="/stocks/${invId}/edit" class="btn btn-sm retro-btn-edit" title="Modifier">✏️</a>
                            <button onclick="confirmDeleteStock(${invId}, '${filmTitle.replace(/'/g, "\\'")}', event)" class="btn btn-sm retro-btn-delete-action" title="Supprimer">🗑️</button>
                        </td>
                    </tr>
                `;
            });

            inventoriesHTML += '</tbody></table>';
        }

        const modalHTML = `
            <div id="inventoriesModal" class="retro-modal-overlay" style="display: flex;">
                <div class="retro-modal" style="max-width: 600px;">
                    <div class="retro-modal-header">
                        <h4>📋 DVDs de "${filmTitle}"</h4>
                    </div>
                    <div class="retro-modal-body">
                        ${inventoriesHTML}
                    </div>
                    <div class="retro-modal-footer">
                        <button onclick="closeInventoriesModal()" class="retro-btn-secondary">Fermer</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Load real data if we showed placeholder
        if (inventories.length === 0) {
            loadInventoriesForModal(filmId, filmTitle);
        }
    }

    async function loadInventoriesForModal(filmId, filmTitle) {
        try {
            // Since we don't have a direct API endpoint, we'll fetch from the controller
            // and parse the data from the session/view variables
            // For now, let's create a workaround by adding the data as a data attribute

            // Get inventories from the page data
            const allInventories = window.pageInventories || [];
            const filmInventories = allInventories.filter(inv => inv.filmId == filmId);

            if (filmInventories.length > 0) {
                updateInventoriesModalContent(filmId, filmTitle, filmInventories);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    function updateInventoriesModalContent(filmId, filmTitle, inventories) {
        const modal = document.getElementById('inventoriesModal');
        if (!modal) return;

        let inventoriesHTML = '<table class="table table-sm retro-table"><thead><tr><th>ID</th><th>Magasin</th><th>Actions</th></tr></thead><tbody>';

        inventories.forEach(inv => {
            const invId = inv.inventoryId || inv.id;
            const storeId = inv.storeId;

            inventoriesHTML += `
                <tr>
                    <td>#${invId}</td>
                    <td>Boutique #${storeId}</td>
                    <td>
                        <a href="/stocks/${invId}/edit" class="btn btn-sm retro-btn-edit" title="Modifier">✏️</a>
                        <button onclick="confirmDeleteStock(${invId}, '${filmTitle.replace(/'/g, "\\'")}', event)" class="btn btn-sm retro-btn-delete-action" title="Supprimer">🗑️</button>
                    </td>
                </tr>
            `;
        });

        inventoriesHTML += '</tbody></table>';

        const modalBody = modal.querySelector('.retro-modal-body');
        if (modalBody) {
            modalBody.innerHTML = inventoriesHTML;
        }
    }

    window.closeInventoriesModal = function() {
        const modal = document.getElementById('inventoriesModal');
        if (modal) {
            modal.remove();
        }
    };

    // =============================
    // SUPPRESSION - MODAL CUSTOM (SUPPRIMÉ - Utilise la version globale en haut du fichier)
    // =============================
    // Cette section est désormais gérée par la fonction globale window.confirmDeleteStock définie en haut du fichier

    // =============================
    // MODAL - SUPPRESSION BLOQUÉE (SUPPRIMÉ - utilise version globale)
    // MODAL - CONFIRMATION (SUPPRIMÉ - utilise version globale)
    // SUPPRESSION - EXÉCUTION (SUPPRIMÉ - utilise version globale)
    // =============================
    // Ces sections sont désormais gérées par les fonctions globales en haut du fichier

    // =============================
    // FORMULAIRE - SOUMISSION AJAX
    // =============================

    function handleStockFormSubmit(event) {
        event.preventDefault();
        const form = event.target;

        // Validation client
        if (!validateStockForm(form)) {
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = form.id === 'stockEditForm' ? '⏳ Mise à jour...' : '⏳ Création...';

        showLoader();

        const formData = new FormData(form);
        const method = form.querySelector('input[name="_method"]')?.value || 'POST';

        fetch(form.action, {
            method: method === 'PUT' ? 'POST' : method,
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': formData.get('_token') || ''
            }
        })
        .then(async response => {
            const data = await response.json();
            hideLoader();

            if (response.ok) {
                showToast(data.message || 'Opération réussie !', 'success');
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/stocks';
                    }
                }, 1500);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.errors) {
                    displayValidationErrors(data.errors, form);
                } else {
                    showToast(data.message || 'Erreur lors de l\'opération', 'error');
                }
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Erreur soumission:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            showToast('Erreur réseau lors de l\'opération', 'error');
        });
    }

    // =============================
    // VALIDATION FORMULAIRE
    // =============================

    function validateStockForm(form) {
        let isValid = true;
        const errors = [];

        // Nettoyer les erreurs précédentes
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        // Valider film_id (si présent - création uniquement)
        const filmInput = form.querySelector('#film_id');
        if (filmInput && (!filmInput.value || filmInput.value === '')) {
            isValid = false;
            errors.push('Le champ Film est obligatoire');
            markFieldAsInvalid(filmInput, 'Le champ Film est obligatoire');
        }

        // Valider store_id
        const storeInput = form.querySelector('#store_id');
        if (storeInput && (!storeInput.value || storeInput.value === '')) {
            isValid = false;
            errors.push('Le champ Magasin est obligatoire');
            markFieldAsInvalid(storeInput, 'Le champ Magasin est obligatoire');
        }

        if (!isValid) {
            showToast('⚠️ Veuillez remplir tous les champs obligatoires', 'error');
        }

        return isValid;
    }

    function markFieldAsInvalid(input, message) {
        input.classList.add('is-invalid');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = message;
        input.parentElement.appendChild(feedback);
    }

    function displayValidationErrors(errors, form) {
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                markFieldAsInvalid(input, errors[field][0]);
            }
        });
        showToast('⚠️ Erreurs de validation', 'error');
    }

    // =============================
    // UTILITAIRES - MODAL
    // =============================

    window.closeDeleteModal = function() {
        const overlay = document.getElementById('deleteModalOverlay');
        if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => overlay.remove(), 300);
        }
    };

    // =============================
    // UTILITAIRES - LOADER
    // =============================

    function showLoader() {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideLoader() {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    // =============================
    // UTILITAIRES - TOAST
    // =============================

    function showToast(message, type = 'info') {
        // Supprimer les anciens toasts
        document.querySelectorAll('.retro-toast').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = `retro-toast retro-toast-${type}`;

        let icon = 'ℹ️';
        if (type === 'success') icon = '✅';
        if (type === 'error') icon = '❌';
        if (type === 'warning') icon = '⚠️';

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
        `;

        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // =============================
    // FILTRAGE EN TEMPS RÉEL
    // =============================

    const filmSearchInput = document.getElementById('filmSearchInput');
    const storeFilterSelect = document.getElementById('storeFilterSelect');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const stocksTableBody = document.getElementById('stocksTableBody');

    function applyFilters() {
        const filmSearch = filmSearchInput ? filmSearchInput.value.toLowerCase().trim() : '';
        const storeFilter = storeFilterSelect ? storeFilterSelect.value : '';

        if (!stocksTableBody) return;

        // Sélectionner UNIQUEMENT les lignes principales des films (pas les lignes de détails)
        const rows = stocksTableBody.querySelectorAll('tr.film-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const filmTitle = row.getAttribute('data-film-title') || '';
            const filmId = row.getAttribute('data-film-id');

            let showRow = true;

            // Filter by film title
            if (filmSearch !== '' && !filmTitle.includes(filmSearch)) {
                showRow = false;
            }

            // Filter by store (check if the store column has stock > 0)
            if (storeFilter !== '' && showRow) {
                // Find the store column index
                const table = document.getElementById('stocksTable');
                if (table) {
                    const headers = table.querySelectorAll('thead th');
                    let storeColumnIndex = -1;

                    headers.forEach((header, index) => {
                        const headerText = header.textContent || '';
                        if (headerText.includes('Magasin #' + storeFilter)) {
                            storeColumnIndex = index;
                        }
                    });

                    if (storeColumnIndex !== -1) {
                        const cells = row.querySelectorAll('td');
                        const storeCell = cells[storeColumnIndex];
                        if (storeCell) {
                            const stockCount = parseInt(storeCell.textContent.trim()) || 0;
                            if (stockCount === 0) {
                                showRow = false;
                            }
                        }
                    }
                }
            }

            // Afficher/masquer la ligne principale du film
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
                // Fermer aussi les détails si la ligne est masquée
                if (filmId) {
                    const detailsRow = document.getElementById(`details-${filmId}`);
                    if (detailsRow) {
                        detailsRow.style.display = 'none';
                        const icon = document.getElementById(`icon-${filmId}`);
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });

        // Show "no results" message if needed
        updateNoResultsMessage(visibleCount);
    }

    function updateNoResultsMessage(visibleCount) {
        const table = document.getElementById('stocksTable');
        if (!table) return;

        let noResultsDiv = document.getElementById('noResultsMessage');

        if (visibleCount === 0) {
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.id = 'noResultsMessage';
                noResultsDiv.className = 'alert alert-warning retro-alert mt-3';
                noResultsDiv.innerHTML = '🤷 Aucun résultat pour cette recherche. Essayez de modifier vos filtres.';
                table.parentElement.insertBefore(noResultsDiv, table.nextSibling);
            }
            table.style.display = 'none';
        } else {
            if (noResultsDiv) {
                noResultsDiv.remove();
            }
            table.style.display = '';
        }
    }

    // Real-time search on input
    if (filmSearchInput) {
        let searchTimeout;
        filmSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
    } else {
    }

    // Real-time filter on store change
    if (storeFilterSelect) {
        storeFilterSelect.addEventListener('change', function() {
            applyFilters();

            // Recharger les détails ouverts pour appliquer le filtre
            const openDetails = document.querySelectorAll('tr.film-details[style*="table-row"]');
            openDetails.forEach(detailRow => {
                const filmId = detailRow.id.replace('details-', '');
                const detailsBody = document.getElementById(`details-body-${filmId}`);
                if (detailsBody) {
                    loadFilmInventories(filmId, detailsBody);
                }
            });
        });
    } else {
    }

    // Clear filters button
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (filmSearchInput) filmSearchInput.value = '';
            if (storeFilterSelect) storeFilterSelect.value = '';
            applyFilters();

            // Recharger les détails ouverts pour retirer le filtre
            const openDetails = document.querySelectorAll('tr.film-details[style*="table-row"]');
            openDetails.forEach(detailRow => {
                const filmId = detailRow.id.replace('details-', '');
                const detailsBody = document.getElementById(`details-body-${filmId}`);
                if (detailsBody) {
                    loadFilmInventories(filmId, detailsBody);
                }
            });
        });
    } else {
    }
});
