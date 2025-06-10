// Script pour afficher les détails d'une installation sur la page de détails
'use strict';

// Récupère l'id de l'installation depuis l'URL
const params = new URLSearchParams(window.location.search);
const id = params.get('id');

// Si un id est présent, on va chercher les infos détaillées
if (id) {
    fetch('../php/request.php?type=batiment_details&id=' + encodeURIComponent(id))
    .then(response => response.json())
    .then(data => {
    if (data) {
      // Remplit chaque champ de la page avec les infos reçues
        document.querySelector('.details-lieu').textContent = data.nom_departement || '';
        document.querySelector('.details-marque-panneau').textContent = data.marque_panneau || '';
        document.querySelector('.details-nb-panneau').textContent = data.nb_panneaux || '';
        document.querySelector('.details-mois').textContent = data.mois_install || '';
        document.querySelector('.details-modele-panneau').textContent = data.panneau_modele || '';
        document.querySelector('.details-nb-onduleur').textContent = data.nb_onduleur || '';
        document.querySelector('.details-an').textContent = data.annee_install || '';
        document.querySelector('.details-marque-onduleur').textContent = data.marque_onduleur || '';
        document.querySelector('.details-pu').textContent = data.puissance_crete || '';
        document.querySelector('.details-su').textContent = data.surface || '';
        document.querySelector('.details-modele-onduleur').textContent = data.modele_onduleur || '';
    }
  });
}