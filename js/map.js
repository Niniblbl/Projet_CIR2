'use strict';
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: '../images/marker-icon-2x.png',
  iconUrl: '../images/marker-icon.png',
  shadowUrl: '../images/marker-shadow.png'
});

// Initialisation de la carte Leaflet
var map = L.map('map').setView([48.8584, 2.2945], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Charge les années dans le select
fetch('../php/request.php?type=annees')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-annee-map').innerHTML = '<option value="" disabled selected hidden>Choisir une année</option>' + html;
  });

// Charge les départements au hasard dans le select
fetch('../php/request.php?type=dep')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-departement-map').innerHTML = '<option value="" disabled selected hidden>Choisir un département</option>' + html;
  });

let markersLayer = L.layerGroup().addTo(map);

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
                      const marker = L.marker([batiment.lat, batiment.lon])
                        .addTo(markersLayer)
                        .bindPopup(`
                          <div class="popup-content">
                            <div class="popup-title">Installation #${batiment.id}</div>
                            <div class="popup-commune"><b>Commune :</b> ${batiment.locality || "Non renseignée"}</div>
                            <a class="popup-link" href="../html/details.html?id=${batiment.id}">Voir les détails</a>
                          </div>
                        `);
                      bounds.push([batiment.lat, batiment.lon]);
                  }
              });
              // Zoom sur tous les marqueurs affichés
              if (bounds.length > 0) {
                  map.fitBounds(bounds, {padding: [40, 40]});
              }
          } else {
                alert("Aucun client trouvé pour ces critères.");
            }
        });
}

// clique sur le bouton "Envoyer"
document.getElementById('btn-map').addEventListener('click', loadMarkers);
