'use strict';

const params = new URLSearchParams(window.location.search);
const id = params.get('id');

if (id) {
    fetch('../back/request.php?type=batiment_details&id=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(data => {
    if (data) {
      document.getElementById('details-lieu').textContent = data.nom_departement || '';
      document.getElementById('details-marque-panneau').textContent = data.marque_panneau || '';
      document.getElementById('details-nb-panneau').textContent = data.nb_panneaux || '';
      // ...et ainsi de suite pour chaque champ...
    }
  });
}