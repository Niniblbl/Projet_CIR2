/*L.marker([51.5, -0.09]).addTo(map)
    .bindPopup('A pretty CSS popup.<br> Easily customizable.')
    .openPopup();*/

'use strict';
// Initialisation de la carte Leaflet
var map = L.map('map').setView([48.8584, 2.2945], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Charge les années dans le select
fetch('../back/request.php?type=annees')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-annee-map').innerHTML = html;
  });

// Charge les départements au hasard dans le select
fetch('../back/request.php?type=dep')
  .then(response => response.text())
  .then(html => {
    document.getElementById('select-departement-map').innerHTML = html;
  });