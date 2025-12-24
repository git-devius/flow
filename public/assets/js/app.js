/**
 * Flow - JavaScript principal
 */

// Système de toasts
const Toast = {
  container: null,
  
  init() {
    // Créer le conteneur de toasts s'il n'existe pas
    if (!document.getElementById('toast-container')) {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      // MODIFICATION : Changement de 'top-0' vers 'bottom-0' pour l'affichage en bas
      this.container.className = 'position-fixed bottom-0 end-0 p-3';
      this.container.style.zIndex = '9999';
      document.body.appendChild(this.container);
    } else {
      this.container = document.getElementById('toast-container');
    }
    
    // Convertir les alerts existants en toasts
    this.convertAlerts();
  },
  
  show(message, type = 'info', duration = 5000) {
    const toastId = 'toast-' + Date.now();
    
    const icons = {
      success: '<i class="bi bi-check-circle-fill"></i>',
      error: '<i class="bi bi-exclamation-triangle-fill"></i>',
      warning: '<i class="bi bi-exclamation-circle-fill"></i>',
      info: '<i class="bi bi-info-circle-fill"></i>'
    };
    
    const bgColors = {
      success: 'bg-success',
      error: 'bg-danger',
      warning: 'bg-warning',
      info: 'bg-info'
    };
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast align-items-center text-white ' + bgColors[type] + ' border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          ${icons[type]} ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    
    this.container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, { delay: duration });
    bsToast.show();
    
    // Supprimer après fermeture
    toast.addEventListener('hidden.bs.toast', () => {
      toast.remove();
    });
    
    return toastId;
  },
  
  success(message, duration) {
    return this.show(message, 'success', duration);
  },
  
  error(message, duration) {
    return this.show(message, 'error', duration);
  },
  
  warning(message, duration) {
    return this.show(message, 'warning', duration);
  },
  
  info(message, duration) {
    return this.show(message, 'info', duration);
  },
  
  convertAlerts() {
    // CORRECTION: Ajout de :not(.no-toast-convert) pour exclure les alertes statiques
    const alerts = document.querySelectorAll('.alert:not(.toast-converted):not(.no-toast-convert)');
    alerts.forEach(alert => {
      let type = 'info';
      if (alert.classList.contains('alert-success')) type = 'success';
      else if (alert.classList.contains('alert-danger')) type = 'error';
      else if (alert.classList.contains('alert-warning')) type = 'warning';
      
      // On retire tout contenu HTML pour le toast (pour éviter les boutons "Fermer")
      const message = alert.textContent.trim(); 
      if (message) {
        // On cherche le message en ignorant les boutons et autres éléments
        const rawMessage = message.split('\n').filter(line => line.trim().length > 0)[0];
        this.show(rawMessage || message, type);
      }
      
      // Masquer l'alert d'origine
      alert.style.display = 'none';
      alert.classList.add('toast-converted');
    });
  }
};

// Validation de formulaires
const FormValidator = {
  
  init() {
    // Validation HTML5 personnalisée
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
      form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          Toast.error('Veuillez remplir tous les champs obligatoires');
        }
        form.classList.add('was-validated');
      }, false);
    });
    
    // Validation en temps réel
    this.setupRealtimeValidation();
  },
  
  setupRealtimeValidation() {
    // Email
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
      input.addEventListener('blur', () => {
        if (input.value && !this.validateEmail(input.value)) {
          input.setCustomValidity('Email invalide');
          input.classList.add('is-invalid');
        } else {
          input.setCustomValidity('');
          input.classList.remove('is-invalid');
          if (input.value) input.classList.add('is-valid');
        }
      });
    });
    
    // Montant
    const amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(input => {
      input.addEventListener('input', () => {
        const value = parseFloat(input.value);
        if (value <= 0) {
          input.setCustomValidity('Le montant doit être supérieur à 0');
        } else if (value > 10000000) {
          input.setCustomValidity('Le montant ne peut pas dépasser 10 000 000 €');
        } else {
          input.setCustomValidity('');
        }
      });
    });
    
    // Fichiers
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
      input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          // Vérifier la taille (10MB max)
          const maxSize = 10 * 1024 * 1024;
          if (file.size > maxSize) {
            Toast.error('Fichier trop volumineux (max 10MB)');
            input.value = '';
            return;
          }
          
          // Vérifier l'extension
          const allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
          const ext = file.name.split('.').pop().toLowerCase();
          if (!allowedExt.includes(ext)) {
            Toast.error('Type de fichier non autorisé');
            input.value = '';
            return;
          }
          
          Toast.success('Fichier sélectionné : ' + file.name, 3000);
        }
      });
    });
  },
  
  validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
};

// Confirmations
const Confirm = {
  init() {
    // Confirmation pour les suppressions
    document.querySelectorAll('[data-confirm]').forEach(element => {
      element.addEventListener('click', (e) => {
        const message = element.getAttribute('data-confirm') || 'Êtes-vous sûr ?';
        if (!confirm(message)) {
          e.preventDefault();
          return false;
        }
      });
    });
  }
};

// Animations au chargement
const Animations = {
  init() {
    // Fade in des cartes
    const cards = document.querySelectorAll('.card, .table-responsive');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      
      setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 50 * index);
    });
  }
};

// Auto-submit des filtres après un délai
const AutoSubmit = {
  init() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    
    let timeout = null;
    const inputs = filterForm.querySelectorAll('input[type="text"], input[type="number"]');
    
    inputs.forEach(input => {
      input.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
          filterForm.submit();
        }, 800); // Soumettre après 800ms d'inactivité
      });
    });
    
    // Submit immédiat pour les selects et checkboxes
    const selects = filterForm.querySelectorAll('select, input[type="checkbox"]');
    selects.forEach(select => {
      select.addEventListener('change', () => {
        filterForm.submit();
      });
    });
  }
};

// Compteurs animés
const AnimatedCounters = {
  init() {
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
      const text = badge.textContent;
      const number = parseInt(text);
      
      if (!isNaN(number) && number > 0) {
        this.animateValue(badge, 0, number, 1000);
      }
    });
  },
  
  animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
      current += increment;
      if (current >= end) {
        current = end;
        clearInterval(timer);
      }
      element.textContent = Math.floor(current);
    }, 16);
  }
};

// Loader overlay
const Loader = {
  show(message = 'Chargement...') {
    if (document.getElementById('app-loader')) return;
    
    const loader = document.createElement('div');
    loader.id = 'app-loader';
    loader.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
    loader.style.backgroundColor = 'rgba(0,0,0,0.5)';
    loader.style.zIndex = '10000';
    
    loader.innerHTML = `
      <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <div>${message}</div>
      </div>
    `;
    
    document.body.appendChild(loader);
  },
  
  hide() {
    const loader = document.getElementById('app-loader');
    if (loader) {
      loader.remove();
    }
  }
};

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', () => {
  Toast.init();
  FormValidator.init();
  Confirm.init();
  Animations.init();
  AutoSubmit.init();
  AnimatedCounters.init();
  
  console.log('✅ Flow initialisé');
});

// Export global
window.Toast = Toast;
window.Loader = Loader;

document.addEventListener('DOMContentLoaded', function () {
    // Cibler toutes les modales d'édition d'utilisateur basées sur l'ID générique
    const userModals = document.querySelectorAll('[id^="editModal"]');

    userModals.forEach(modalElement => {
        // Option 1: Initialisation manuelle pour désactiver le backdrop.
        // Utiliser la fonction de constructeur de modale Bootstrap
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            
            // Créer une nouvelle instance de modale avec backdrop: false
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: false, // DÉSACTIVE LE BACKDROP EN CONFLIT
                keyboard: true,
                focus: true
            });

            // Gérer l'ouverture manuelle (pour s'assurer que le backdrop n'est pas réactivé par data-bs-toggle)
            const triggerButton = document.querySelector(`[data-bs-target="#${modalElement.id}"]`);
            if (triggerButton) {
                // Surcharger le comportement du bouton par défaut (qui utilise data-bs-toggle)
                triggerButton.addEventListener('click', function(event) {
                    event.preventDefault(); // Empêche l'action par défaut de data-bs-toggle
                    modal.show();
                });
            }
        }
    });

    // Option 2 (Ajouter cette règle CSS si la Modale est toujours grisée après l'Option 1)
    // C'est un dernier recours pour neutraliser l'arrière-plan grisé manuellement
    // const style = document.createElement('style');
    // style.textContent = '.modal-backdrop { opacity: 0 !important; }';
    // document.head.append(style);
});