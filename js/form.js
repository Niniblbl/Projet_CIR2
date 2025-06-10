//Ce script gère le formulaire de recherche et l'affichage des résultats
'use strict';

// Quand la page est chargée, on remplit les listes déroulantes avec les données de la BDD
window.onload = async function() {
  // Remplit la liste des marques d'onduleur
  const marqueRes = await fetch('../php/request.php?type=marque_ondul');
  const marqueHtml = await marqueRes.text();
  document.getElementById('select-marque').innerHTML = '<option value="">-- Choisir une marque --</option>' + marqueHtml;

  // Remplit la liste des panneaux
  const panneauRes = await fetch('../php/request.php?type=marque_pan');
  const panneauHtml = await panneauRes.text();
  document.getElementById('select-panneau').innerHTML = '<option value="">-- Choisir un panneau --</option>' + panneauHtml;

  // Remplit la liste des départements
  const depRes = await fetch('../php/request.php?type=dep');
  const depHtml = await depRes.text();
  document.getElementById('select-departement').innerHTML = '<option value="">-- Choisir un département --</option>' + depHtml;
};

// Quand l'utilisateur soumet le formulaire de recherche
document.getElementById('search-form').addEventListener('submit', async function(e) {
  e.preventDefault();// Empêche le rechargement de la page

  // Récupère les valeurs sélectionnées
  const marque = document.getElementById('select-marque').value;
  const panneau = document.getElementById('select-panneau').value;
  const departement = document.getElementById('select-departement').value;

  // Envoie la requête de recherche au serveur avec les filtres choisis
  const response = await fetch(`../php/request.php?type=recherche&marque_ondul=${encodeURIComponent(marque)}&marque_pan=${encodeURIComponent(panneau)}&dep=${encodeURIComponent(departement)}`);
  const data = await response.json();
  console.log(data);

  // Affiche les résultats dans le tableau
  const tbody = document.getElementById('resultats-body');
  tbody.innerHTML = '';
  if (!data || data.length === 0) {
    // Aucun résultat trouvé
    tbody.innerHTML = '<tr><td colspan="6">Aucun résultat</td></tr>';
  } else {
    // Affiche chaque résultat dans une ligne du tableau
    data.forEach(row => {
      tbody.innerHTML += `
        <tr>
          <td>${row.date || ''}</td>
          <td>${row.nb_panneaux || ''}</td>
          <td>${row.surface || ''}</td>
          <td>${row.puissance_crete || ''}</td>
          <td>${row.localisation || ''}</td>
          <td>
            <a href="details.html?id=${row.id}" title="Voir les détails">
          <img src="../images/lien.png" alt="Détails" style="width:24px;height:24px;vertical-align:middle;"></td>
        </tr>
      `;
    });
  }
  // Affiche la section des résultats
  document.getElementById('resultats').style.display = 'block';
});

