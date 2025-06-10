// Script pour la carte interactive Leaflet : affichage des installations sur la carte
'use strict';

// Configuration des icônes Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({ // Supprime l'ancienne méthode de récupération des URL d'icônes
  iconRetinaUrl: '../images/marker-icon-2x.png', 
  iconUrl: '../images/marker-icon.png',  
  shadowUrl: '../images/marker-shadow.png'
});//problèmes de chargement des icônes dans Leaflet, on les remplace par leur bon chemin

// Initialisation de la carte centrée sur la France
var map = L.map('map').setView([48.8584, 2.2945], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map); // Ajoute une couche de tuiles OpenStreetMap à la carte

// Remplit la liste des années pour filtrer la carte
fetch('../php/request.php?type=annees')
  .then(response => response.text()) // Envoie une requête pour récupérer les années d'installation
  .then(html => {
    document.getElementById('select-annee-map').innerHTML = '<option value="" disabled selected hidden>Choisir une année</option>' + html;
  });

// Remplit la liste des départements pour filtrer la carte
fetch('../php/request.php?type=dep')
  .then(response => response.text()) // Envoie une requête pour récupérer les départements
  .then(html => {
    document.getElementById('select-departement-map').innerHTML = '<option value="" disabled selected hidden>Choisir un département</option>' + html;
  });

// Groupe de marqueurs pour pouvoir les effacer facilement
let markersLayer = L.layerGroup().addTo(map);

// Fonction pour charger et afficher les marqueurs selon les filtres choisis
function loadMarkers() {
    const annee = document.getElementById('select-annee-map').value;
    const departement = document.getElementById('select-departement-map').value;

    markersLayer.clearLayers(); // Efface les marqueurs précédents avant d'en ajouter de nouveaux

    const params = new URLSearchParams(); // Crée un objet URLSearchParams pour construire la requête
    if (annee) params.append('annee', annee);
    if (departement) params.append('departement', departement);

    fetch('../php/request.php?type=batiments_coords&' + params.toString()) // Envoie une requête pour récupérer les coordonnées des installations selon les filtres
        .then(response => response.json()) 
       .then(data => {
          console.log(data); // Affiche les données dans la console pour le débogage
          const bounds = [];
          if (Array.isArray(data) && data.length > 0) {
              data.forEach(batiment => {
                  if (batiment.lat && batiment.lon) {
                    // Ajoute un marqueur pour chaque installation
                      const marker = L.marker([batiment.lat, batiment.lon])
                        .addTo(markersLayer)
                        .bindPopup(`
                          <div class="popup-content">
                            <div class="popup-title">Installation #${batiment.id}</div>
                            <div class="popup-commune"><b>Commune :</b> ${batiment.locality || "Non renseignée"}</div>
                            <div class="popup-puissance"><b>Puissance :</b> ${batiment.puissance_crete || "Non renseignée"}</div>
                            <a class="popup-link" href="../html/details.html?id=${batiment.id}">Voir les détails</a>
                          </div>
                        `);
                      bounds.push([batiment.lat, batiment.lon]); // Ajoute les coordonnées du marqueur aux limites de la carte
                  }
              });
              // Centre la carte sur tous les marqueurs affichés
              if (bounds.length > 0) {
                  map.fitBounds(bounds, {padding: [40, 40]});
              }
          } else {
                alert("Aucun client trouvé pour ces critères.");  // Alerte si aucun marqueur n'est trouvé
            }
        });
}

// Lance la recherche de marqueurs quand on clique sur le bouton
document.getElementById('btn-map').addEventListener('click', loadMarkers);
