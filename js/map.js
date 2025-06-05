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
fetch('../back/request.php?type=annees')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-annee-map').innerHTML = '<option value="" disabled selected hidden>Choisir une année</option>' + html;
  });

// Charge les départements au hasard dans le select
fetch('../back/request.php?type=dep')
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

    fetch('../back/request.php?type=batiments_coords&' + params.toString())
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(batiment => {
                    if (batiment.lat && batiment.lon) {
                        L.marker([batiment.lat, batiment.lon])
                            .addTo(markersLayer)
                            .bindPopup('Bâtiment ID: ' + batiment.id + '<br><a href="../html/details.html?id='+ batiment.id + '" target="_blank">Voir les détails</a>' );
                    }
                });
            } else {
                alert("Aucun bâtiment trouvé pour ces critères.");
            }
        });
}

// clique sur le bouton "Envoyer"
document.getElementById('btn-map').addEventListener('click', loadMarkers);
