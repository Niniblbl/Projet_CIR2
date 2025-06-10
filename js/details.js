// Script pour afficher les détails d'une installation sur la page de détails
'use strict'; //mode strict pour éviter les erreurs silencieuses et améliorer la sécurité du code

// Récupère l'id de l'installation depuis l'URL
const params = new URLSearchParams(window.location.search); // Utilise l'API URLSearchParams pour parser les paramètres de l'URL
const id = params.get('id'); // Récupère l'id de l'installation depuis l'URL

// Si un id est présent, on va chercher les infos détaillées
if (id) {
    fetch('../php/request.php?type=batiment_details&id=' + encodeURIComponent(id)) // Envoie une requête au serveur pour récupérer les détails de l'installation
    .then(response => response.json()) // Convertit la réponse en JSON
    .then(data => { // Traite les données reçues
    if (data) {
      // Remplit chaque champ de la page avec les infos reçues
        document.querySelector('.details-lieu').textContent = data.nom_departement || ''; // Affiche le nom du département (null si non renseigné)
        document.querySelector('.details-marque-panneau').textContent = data.marque_panneau || ''; // Affiche la marque du panneau (null si non renseigné)
        document.querySelector('.details-nb-panneau').textContent = data.nb_panneaux || ''; // Affiche le nombre de panneaux (null si non renseigné)
        document.querySelector('.details-mois').textContent = data.mois_install || ''; // Affiche le mois d'installation (null si non renseigné)
        document.querySelector('.details-modele-panneau').textContent = data.panneau_modele || ''; // Affiche le modèle du panneau (null si non renseigné)
        document.querySelector('.details-nb-onduleur').textContent = data.nb_onduleur || ''; // Affiche le nombre d'onduleurs (null si non renseigné)
        document.querySelector('.details-an').textContent = data.annee_install || ''; // Affiche l'année d'installation (null si non renseigné)
        document.querySelector('.details-marque-onduleur').textContent = data.marque_onduleur || ''; // Affiche la marque de l'onduleur (null si non renseigné)
        document.querySelector('.details-pu').textContent = data.puissance_crete || ''; // Affiche la puissance crête (null si non renseigné)
        document.querySelector('.details-su').textContent = data.surface || ''; // Affiche la surface (null si non renseigné)
        document.querySelector('.details-modele-onduleur').textContent = data.modele_onduleur || ''; // Affiche le modèle de l'onduleur (null si non renseigné)
    }
  });
}