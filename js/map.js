// Script pour la carte interactive Leaflet : affichage des installations sur la carte
'use strict';

// Configuration des icônes Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: '../images/marker-icon-2x.png',
  iconUrl: '../images/marker-icon.png',
  shadowUrl: '../images/marker-shadow.png'
});

// Initialisation de la carte centrée sur la France
var map = L.map('map').setView([48.8584, 2.2945], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Remplit la liste des années pour filtrer la carte
fetch('../php/request.php?type=annees')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-annee-map').innerHTML = '<option value="" disabled selected hidden>Choisir une année</option>' + html;
  });

// Remplit la liste des départements pour filtrer la carte
fetch('../php/request.php?type=dep')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-departement-map').innerHTML = '<option value="" disabled selected hidden>Choisir un département</option>' + html;
  });

// Groupe de marqueurs pour pouvoir les effacer facilement
let markersLayer = L.layerGroup().addTo(map);

// Fonction pour charger et afficher les marqueurs selon les filtres choisis
function loadMarkers() {
    const annee = document.getElementById('select-annee-map').value;
    const departement = document.getElementById('select-departement-map').value;

    markersLayer.clearLayers();

    const params = new URLSearchParams();
    if (annee) params.append('annee', annee);
    if (departement) params.append('departement', departement);

    fetch('../php/request.php?type=batiments_coords&' + params.toString())
        .then(response => response.json())
       .then(data => {
          console.log(data);
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
                      bounds.push([batiment.lat, batiment.lon]);
                  }
              });
              // Centre la carte sur tous les marqueurs affichés
              if (bounds.length > 0) {
                  map.fitBounds(bounds, {padding: [40, 40]});
              }
          } else {
                alert("Aucun client trouvé pour ces critères.");
            }
        });
}

// Lance la recherche de marqueurs quand on clique sur le bouton
document.getElementById('btn-map').addEventListener('click', loadMarkers);
