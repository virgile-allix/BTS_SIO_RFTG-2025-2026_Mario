// JavaScript pour la gestion des films

// Variable pour stocker le formulaire en attente de suppression
let pendingDeleteForm = null;

// Fonction pour créer et afficher la modal de confirmation personnalisée
function confirmDelete(filmTitle, event) {
    event.preventDefault();
    pendingDeleteForm = event.target;

    let overlay = document.getElementById('deleteModalOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'deleteModalOverlay';
        overlay.className = 'delete-modal-overlay';
        overlay.innerHTML = `
            <div class="delete-modal">
                <div class="delete-modal-header">
                    <div class="delete-modal-icon">⚠️</div>
                    <div class="delete-modal-title">Confirmation de suppression</div>
                </div>
                <div class="delete-modal-body">
                    <div class="delete-modal-text">
                        Êtes-vous sûr de vouloir supprimer ce film ?
                    </div>
                    <div class="delete-modal-film-title" id="deleteModalFilmTitle"></div>
                    <div class="delete-modal-warning">
                        <span>⚠️</span>
                        <span>Cette action est irréversible !</span>
                    </div>
                </div>
                <div class="delete-modal-footer">
                    <button class="delete-modal-btn delete-modal-btn-cancel" id="deleteModalCancel">
                        ❌ Annuler
                    </button>
                    <button class="delete-modal-btn delete-modal-btn-confirm" id="deleteModalConfirm">
                        🗑️ Supprimer
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeDeleteModal();
            }
        });

        document.getElementById('deleteModalCancel').addEventListener('click', closeDeleteModal);

        document.getElementById('deleteModalConfirm').addEventListener('click', function() {
            if (pendingDeleteForm) {
                handleDeleteFilm(pendingDeleteForm);
            }
            closeDeleteModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeDeleteModal();
            }
        });
    }

    document.getElementById('deleteModalFilmTitle').textContent = filmTitle;
    overlay.classList.add('active');

    return false;
}

// Fonction pour fermer la modal
function closeDeleteModal() {
    const overlay = document.getElementById('deleteModalOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
    pendingDeleteForm = null;
}

// Fonction pour supprimer un film avec AJAX
function handleDeleteFilm(form) {
    showLoader();

    const formData = new FormData(form);
    const url = form.action;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erreur lors de la suppression');
            });
        }
        return response.json();
    })
    .then(data => {
        hideLoader();
        showSuccessMessage(data.message || 'Film supprimé avec succès !');

        if (data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            setTimeout(() => {
                window.location.href = '/films';
            }, 1500);
        }
    })
    .catch(error => {
        hideLoader();
        showErrorMessage(error.message || 'Une erreur est survenue lors de la suppression');
    });
}

// Fonction de validation du formulaire
function validateFilmForm(form) {
    let isValid = true;
    let errors = [];

    // Validation du titre
    const title = form.querySelector('#title');
    if (title && title.value.trim().length === 0) {
        errors.push('Le titre est obligatoire');
        title.classList.add('is-invalid');
        isValid = false;
    } else if (title) {
        title.classList.remove('is-invalid');
    }

    // Validation de l'année
    const releaseYear = form.querySelector('#releaseYear');
    if (releaseYear && releaseYear.value) {
        const year = parseInt(releaseYear.value);
        const currentYear = new Date().getFullYear();
        if (year < 1900 || year > currentYear + 5) {
            errors.push(`L'année doit être entre 1900 et ${currentYear + 5}`);
            releaseYear.classList.add('is-invalid');
            isValid = false;
        } else {
            releaseYear.classList.remove('is-invalid');
        }
    }

    // Validation de la langue
    const languageId = form.querySelector('#languageId');
    if (languageId && !languageId.value) {
        errors.push('La langue est obligatoire');
        languageId.classList.add('is-invalid');
        isValid = false;
    } else if (languageId) {
        languageId.classList.remove('is-invalid');
    }

    // Validation de la durée de location
    const rentalDuration = form.querySelector('#rentalDuration');
    if (rentalDuration && (!rentalDuration.value || parseInt(rentalDuration.value) < 1)) {
        errors.push('La durée de location doit être au moins 1 jour');
        rentalDuration.classList.add('is-invalid');
        isValid = false;
    } else if (rentalDuration) {
        rentalDuration.classList.remove('is-invalid');
    }

    // Validation du tarif
    const rentalRate = form.querySelector('#rentalRate');
    if (rentalRate && (!rentalRate.value || parseFloat(rentalRate.value) < 0)) {
        errors.push('Le tarif doit être supérieur ou égal à 0');
        rentalRate.classList.add('is-invalid');
        isValid = false;
    } else if (rentalRate) {
        rentalRate.classList.remove('is-invalid');
    }

    // Validation de la durée du film
    const length = form.querySelector('#length');
    if (length && length.value && parseInt(length.value) < 1) {
        errors.push('La durée doit être au moins 1 minute');
        length.classList.add('is-invalid');
        isValid = false;
    } else if (length) {
        length.classList.remove('is-invalid');
    }

    // Afficher les erreurs si nécessaire
    if (!isValid) {
        showValidationErrors(errors);
    }

    return isValid;
}

// Fonction pour afficher les erreurs de validation
function showValidationErrors(errors) {
    // Retirer les anciennes alertes de validation
    const oldAlerts = document.querySelectorAll('.validation-alert');
    oldAlerts.forEach(alert => alert.remove());

    // Créer une nouvelle alerte
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger validation-alert';
    alertDiv.style.cssText = 'border: 3px solid #000; box-shadow: 4px 4px 0px #000; font-weight: bold; margin-bottom: 20px;';

    let errorHtml = '<strong>⚠️ Erreurs de validation :</strong><ul style="margin-bottom: 0; margin-top: 10px;">';
    errors.forEach(error => {
        errorHtml += `<li>${error}</li>`;
    });
    errorHtml += '</ul>';

    alertDiv.innerHTML = errorHtml;

    // Insérer l'alerte au début du formulaire
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(alertDiv, form.firstChild);

        // Faire défiler vers le haut pour voir l'erreur
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Fonction pour afficher un message de succès
function showSuccessMessage(message) {
    const toast = showToast(message, 'success');

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Fonction d'enregistrement du formulaire avec AJAX
function handleFilmFormSubmit(event) {
    event.preventDefault();

    const form = event.target;

    if (!validateFilmForm(form)) {
        return false;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton ? submitButton.innerHTML : '';

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '⏳ Enregistrement en cours...';
    }

    showLoader();

    const formData = new FormData(form);
    const url = form.action;
    const method = form.method || 'POST';

    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erreur lors de l\'enregistrement');
            });
        }
        return response.json();
    })
    .then(data => {
        hideLoader();
        showSuccessMessage(data.message || 'Film enregistré avec succès !');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }

        if (data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 2000);
        } else if (data.film_id) {
            setTimeout(() => {
                window.location.href = `/films/${data.film_id}`;
            }, 2000);
        }
    })
    .catch(error => {
        hideLoader();
        showErrorMessage(error.message || 'Une erreur est survenue lors de l\'enregistrement');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    });

    return false;
}

// Fonction pour afficher une notification toast
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';

    let icon = '⏳';
    let bgColor = '#3498db';

    if (type === 'success') {
        icon = '✅';
        bgColor = '#27ae60';
    } else if (type === 'error') {
        icon = '❌';
        bgColor = '#e74c3c';
    } else if (type === 'loading') {
        icon = '⏳';
        bgColor = '#3498db';
    }

    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
        min-width: 250px;
        max-width: 400px;
    `;

    toast.innerHTML = `
        <span style="font-size: 20px;">${icon}</span>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    return toast;
}

// Fonction pour afficher le loader
function showLoader() {
    const toast = showToast('Enregistrement en cours...', 'loading');
    toast.id = 'loading-toast';
    return toast;
}

// Fonction pour cacher le loader
function hideLoader() {
    const toast = document.getElementById('loading-toast');
    if (toast) {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }
}

// Fonction pour afficher un message d'erreur
function showErrorMessage(message) {
    const toast = showToast(message, 'error');

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}
// Ajouter les animations CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialisation lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fermeture des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            // Fermer l'alerte en la retirant du DOM avec animation
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Attacher la fonction de validation au formulaire
    const filmForm = document.querySelector('form[action*="films"]');
    if (filmForm) {
        filmForm.addEventListener('submit', handleFilmFormSubmit);

        // Validation en temps réel des champs
        const title = filmForm.querySelector('#title');
        if (title) {
            title.addEventListener('blur', function() {
                if (this.value.trim().length === 0) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }

        const releaseYear = filmForm.querySelector('#releaseYear');
        if (releaseYear) {
            releaseYear.addEventListener('blur', function() {
                if (this.value) {
                    const year = parseInt(this.value);
                    const currentYear = new Date().getFullYear();
                    if (year < 1900 || year > currentYear + 5) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        }

        const languageId = filmForm.querySelector('#languageId');
        if (languageId) {
            languageId.addEventListener('change', function() {
                if (!this.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }

        const rentalDuration = filmForm.querySelector('#rentalDuration');
        if (rentalDuration) {
            rentalDuration.addEventListener('blur', function() {
                if (!this.value || parseInt(this.value) < 1) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }

        const rentalRate = filmForm.querySelector('#rentalRate');
        if (rentalRate) {
            rentalRate.addEventListener('blur', function() {
                if (!this.value || parseFloat(this.value) < 0) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    }

    // Filtre de recherche pour les catégories
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.category-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Filtre de recherche pour les acteurs
    const actorSearch = document.getElementById('actorSearch');
    if (actorSearch) {
        actorSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.actor-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Filtre de recherche pour les réalisateurs
    const directorSearch = document.getElementById('directorSearch');
    if (directorSearch) {
        directorSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.director-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
