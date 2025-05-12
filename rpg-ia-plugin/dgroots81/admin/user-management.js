

/**
 * JS pour la gestion admin des utilisateurs (suppression via API OSE)
 */
document.addEventListener('DOMContentLoaded', function () {
   
  document.body.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('supprimer-api-btn')) {
      e.preventDefault();
      if (!confirm('Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.')) return;

      var btn = e.target;
      var userId = btn.getAttribute('data-user-id');
      var username = btn.getAttribute('data-username');
      var email = btn.getAttribute('data-email');

      btn.disabled = true;
      btn.textContent = 'Suppression...';

      fetch(dgroots81AdminData.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'supprimer_api_ose',
          user_id: userId,
          username: username,
          email: email,
          _ajax_nonce:  typeof dgroots81AdminData !== 'undefined' ? dgroots81AdminData.nonce : ''

        })
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message || (data.success ? 'Suppression réussie.' : 'Erreur lors de la suppression.'));
        if (data.success) {
          // Ne plus supprimer la ligne, réactiver le bouton "Inscrire à l'API"
          var row = btn.closest('tr');
          if (row) {
            var inscrireBtn = row.querySelector('.inscrire-api-btn');
            if (inscrireBtn) {
              inscrireBtn.disabled = false;
              inscrireBtn.textContent = 'Inscrire à l\'API';
            }
          }
        } else {
          btn.disabled = false;
          btn.textContent = 'Delete';
        }
      })
      .catch(() => {
        alert('Erreur AJAX lors de la suppression.');
        btn.disabled = false;
        btn.textContent = 'Delete';
      });
    }
  });
});