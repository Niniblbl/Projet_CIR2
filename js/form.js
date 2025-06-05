'use strict';

window.onload = function() {
  fetch('../back/request.php?type=marque_ondul')
    .then(r => r.text())
    .then(html => document.getElementById('select-marque').innerHTML = html);

  fetch('../back/request.php?type=marque_pan')
    .then(r => r.text())
    .then(html => document.getElementById('select-panneau').innerHTML = html);

  fetch('../back/request.php?type=dep')
    .then(r => r.text())
    .then(html => document.getElementById('select-departement').innerHTML = html);
};