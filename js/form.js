'use strict';

window.onload = function() {
  fetch('../php/request.php?type=marque_ondul')
    .then(r => r.text())
    .then(html => document.getElementById('select-marque').innerHTML = '<option value="">-- Choisir une marque --</option>' + html);

  fetch('../php/request.php?type=marque_pan')
    .then(r => r.text())
    .then(html => document.getElementById('select-panneau').innerHTML = '<option value="">-- Choisir un panneau --</option>' + html);

  fetch('../php/request.php?type=dep')
    .then(r => r.text())
    .then(html => document.getElementById('select-departement').innerHTML = '<option value="">-- Choisir un département --</option>' + html);
};

document.getElementById('search-form').addEventListener('submit', function(e) {
  e.preventDefault();

const marque = document.getElementById('select-marque').value;
const panneau = document.getElementById('select-panneau').value;
const departement = document.getElementById('select-departement').value;

 fetch(`../php/request.php?type=recherche&marque_ondul=${encodeURIComponent(marque)}&marque_pan=${encodeURIComponent(panneau)}&dep=${encodeURIComponent(departement)}`)
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById('resultats-body');
      tbody.innerHTML = '';
      if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Aucun résultat</td></tr>';
      } else {
        data.forEach(row => {
          tbody.innerHTML += `
            <tr>
              <td>${row.date || ''}</td>
              <td>${row.nb_panneaux || ''}</td>
              <td>${row.surface || ''}</td>
              <td>${row.puissance_crete || ''}</td>
              <td>${row.localisation || ''}</td>
            </tr>
          `;
        });
      }
      document.getElementById('resultats').style.display = 'block';
    });
});

