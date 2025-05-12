document.addEventListener('DOMContentLoaded', () => {
  console.groupCollapsed('🔍 DEBUG RPG-IA ADMIN');

  // 1. Vérifie si la variable JS injectée existe
  console.log('typeof dgroots81AdminData:', typeof dgroots81AdminData);
  if (typeof dgroots81AdminData !== 'undefined') {
    console.log('✅ Nonce récupéré :', dgroots81AdminData.nonce);
    console.log(dgroots81AdminData)
  } else {
    console.warn('❌ Le nonce dgroots81UserNonce est undefined ! Vérifie wp_localize_script()');
  }

  // 2. Vérifie si le bouton est détecté
  const btns = document.querySelectorAll('.supprimer-api-btn');
  console.log(`🧮 Boutons détectés : ${btns.length}`);

  btns.forEach((btn, index) => {
    console.log(`Bouton[${index}]`, {
      userId: btn.dataset.userId,
      username: btn.dataset.username,
      email: btn.dataset.email
    });

    btn.addEventListener('click', () => {
      console.group(`🗑️ Suppression utilisateur ID=${btn.dataset.userId}`);
      const userId = btn.dataset.userId;
      const username = btn.dataset.username;
      const email = btn.dataset.email;
      const nonce = typeof dgroots81AdminData !== 'undefined' ? dgroots81AdminData.nonce : '';

      console.log('→ user_id:', userId);
      console.log('→ username:', username);
      console.log('→ email:', email);
      console.log('→ nonce utilisé :', nonce);

      // Fetch (appel AJAX)
      fetch(ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'supprimer_api_ose',
          user_id: userId,
          username: username,
          email: email,
          _ajax_nonce: nonce
        })
      })
        .then(response => {
          console.log('↪️ HTTP status:', response.status);
          return response.json().catch(() => null);
        })
        .then(data => {
          console.log('📦 Réponse JSON :', data);
          if (data?.success) {
            console.log('✅ Suppression réussie côté API');
            const row = btn.closest('tr');
            if (row) row.remove();
          } else {
            console.warn('❗ Erreur côté API ou plugin :', data);
          }
        })
        .catch(err => {
          console.error('💥 Erreur réseau / fetch :', err);
        });

      console.groupEnd();
    });
  });

  console.groupEnd();
});





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
          // Optionnel : retirer la ligne utilisateur du tableau
          var row = btn.closest('tr');
          if (row) row.remove();
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