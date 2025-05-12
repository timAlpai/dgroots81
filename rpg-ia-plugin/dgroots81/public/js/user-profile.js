/**
 * JS pour édition inline du nom d'utilisateur sur la page profil utilisateur (public)
 * - Cible le champ input[readonly].js-edit-username dans le wrapper .dgroots81-editable-username-wrapper
 * - Affiche un loader et feedback lors de la sauvegarde AJAX
 * - Ne s’exécute que sur la page profil utilisateur (body.hasClass('dgroots81-user-profile'))
 */

/**
 * Initialisation de l'édition du nom d'utilisateur, réutilisable et compatible chargement dynamique (ex : tabs)
 */
function initDgroots81UserProfileInlineEdit(context) {
  var root = context || document;
  var wrapper = root.querySelector('.dgroots81-editable-username-wrapper');
  if (!wrapper || wrapper.dataset.initialized === "1") { alert('toto est core en panne'); return; }

  var input = wrapper.querySelector('input.js-edit-username[data-editable="username"]');
  var pencil = wrapper.querySelector('.dgroots81-edit-username-pencil');
  if (!input || !pencil) return;

  // Marquer comme initialisé pour éviter les doublons
  wrapper.dataset.initialized = "1";

  // Création des éléments feedback/loader
  var loader = document.createElement('span');
  loader.className = 'dgroots81-username-loader';
  loader.style.display = 'none';
  loader.innerHTML = '⏳';

  var feedback = document.createElement('span');
  feedback.className = 'dgroots81-username-feedback';
  feedback.style.display = 'none';

  wrapper.appendChild(loader);
  wrapper.appendChild(feedback);

  // 3. Clic sur le crayon : rendre éditable et focus
  pencil.addEventListener('click', function () {
    input.readOnly = false;
    input.focus();
    input.select();
  });

  // 4. Validation : blur ou touche entrée
  function validateAndSave() {
    var newValue = input.value.trim();
    if (!newValue) {
      showFeedback('Le nom ne peut pas être vide.', false);
      resetInput();
      return;
    }
    showLoader(true);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.ajaxurl || '/wp-admin/admin-ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        showLoader(false);
        if (xhr.status === 200) {
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
              showFeedback('Nom d’utilisateur mis à jour !', true);
            } else {
              showFeedback(resp.data && resp.data.message ? resp.data.message : 'Erreur lors de la mise à jour.', false);
            }
          } catch (e) {
            showFeedback('Erreur inattendue.', false);
          }
        } else {
          showFeedback('Erreur réseau.', false);
        }
        resetInput();
      }
    };
    var params = 'action=dgroots81_update_username&new_username=' + encodeURIComponent(newValue);
    // Ajout du nonce si disponible
    if (window.dgroots81UserProfile && dgroots81UserProfile.nonce) {
      params += '&_wpnonce=' + encodeURIComponent(dgroots81UserProfile.nonce);
    }
    xhr.send(params);
  }

  input.addEventListener('blur', function () {
    if (!input.readOnly) {
      validateAndSave();
    }
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !input.readOnly) {
      e.preventDefault();
      input.blur();
    }
    if (e.key === 'Escape' && !input.readOnly) {
      e.preventDefault();
      resetInput();
    }
  });

  // Utiliser l'URL AJAX passée par PHP si disponible
  if (window.dgroots81UserProfile && dgroots81UserProfile.ajaxurl) {
    window.ajaxurl = dgroots81UserProfile.ajaxurl;
  }

  function showLoader(show) {
    loader.style.display = show ? 'inline-block' : 'none';
  }

  function showFeedback(msg, success) {
    feedback.textContent = msg;
    feedback.style.display = 'inline-block';
    feedback.style.color = success ? 'green' : 'red';
    setTimeout(function () {
      feedback.style.display = 'none';
    }, 2500);
  }

  function resetInput() {
    input.readOnly = true;
    input.blur();
  }
}

// Initialisation sur DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
  // On tente d'initialiser sur la page courante
  initDgroots81UserProfileInlineEdit(document);

  // MutationObserver pour gérer le chargement dynamique (ex : tabs)
  var observer = new MutationObserver(function (mutationsList) {
    mutationsList.forEach(function (mutation) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            // Si un wrapper est ajouté, on initialise dessus
            if (node.classList && node.classList.contains('dgroots81-editable-username-wrapper')) {
              initDgroots81UserProfileInlineEdit(node.ownerDocument || document);
            } else if (node.querySelectorAll) {
              // Ou si un wrapper est dans les descendants
              node.querySelectorAll('.dgroots81-editable-username-wrapper').forEach(function (el) {
                initDgroots81UserProfileInlineEdit(el.ownerDocument || document);
              });
            }
          }
        });
      }
    });
  });

  observer.observe(document.body, { childList: true, subtree: true });
});