'use strict';

window.onload = async function() {
  const marqueRes = await fetch('../php/request.php?type=marque_ondul');
  const marqueHtml = await marqueRes.text();
  document.getElementById('select-marque').innerHTML = '<option value="">-- Choisir une marque --</option>' + marqueHtml;

  const panneauRes = await fetch('../php/request.php?type=marque_pan');
  const panneauHtml = await panneauRes.text();
  document.getElementById('select-panneau').innerHTML = '<option value="">-- Choisir un panneau --</option>' + panneauHtml;

  const depRes = await fetch('../php/request.php?type=dep');
  const depHtml = await depRes.text();
  document.getElementById('select-departement').innerHTML = '<option value="">-- Choisir un département --</option>' + depHtml;
};

document.getElementById('search-form').addEventListener('submit', async function(e) {
  e.preventDefault();

  const marque = document.getElementById('select-marque').value;
  const panneau = document.getElementById('select-panneau').value;
  const departement = document.getElementById('select-departement').value;

  const response = await fetch(`../php/request.php?type=recherche&marque_ondul=${encodeURIComponent(marque)}&marque_pan=${encodeURIComponent(panneau)}&dep=${encodeURIComponent(departement)}`);
  const data = await response.json();
  console.log(data);
  const tbody = document.getElementById('resultats-body');
  tbody.innerHTML = '';
  if (!data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6">Aucun résultat</td></tr>';
  } else {
    data.forEach(row => {
      tbody.innerHTML += `
        <tr>
          <td>${row.date || ''}</td>
          <td>${row.nb_panneaux || ''}</td>
          <td>${row.surface || ''}</td>
          <td>${row.puissance_crete || ''}</td>
          <td>${row.localisation || ''}</td>
          <td></td>
        </tr>
      `;
    });
  }
  document.getElementById('resultats').style.display = 'block';
});

