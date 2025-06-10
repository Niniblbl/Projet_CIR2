// Script de gestion de l'interface d'administration (affichage, ajout, modification, suppression des installations)
'use strict';
let currentPage = 1;
const perPage = 100;

// Fonction pour charger et afficher le tableau des installations (avec pagination)
function loadTable(page = 1) {
  fetch(`../php/request.php?type=all_installations&limit=${perPage}&offset=${(page-1)*perPage}`)
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById('table-body');
      tbody.innerHTML = '';
      // Affiche chaque installation dans une ligne du tableau
      data.rows.forEach(row => {
        tbody.innerHTML += `
          <tr data-id="${row.id}">
            <td>${row.id || ''}</td>
            <td contenteditable="true" class="edit" data-field="annee_install">${row.annee_install  || ''}</td>
            <td contenteditable="true" class="edit" data-field="mois_install">${row.mois_install || ''}</td>
            <td>${row.region || ''}</td>
            <td>${row.departement || ''}</td>
            <td>${row.ville || ''}</td>
            <td contenteditable="true" class="edit" data-field="installateur">${row.installateur || ''}</td>
            <td>
              <a href="../html/details.html?id=${row.id}" target="_blank">détails</a>
            </td>
            <td>
              <button class="btn-save">💾</button>
              <button class="btn-delete">🗑️</button>
            </td>
          </tr>
        `;
      });

      //Gère la pagination (boutons précédent/suivant)
      const pagination = document.getElementById('pagination');
      const totalPages = Math.ceil(data.total / perPage);
      let html = '';
      // Ajoute les événements sur les boutons de pagination
      if (page > 1) {
        html += `<button id="prev-page">Précédent</button>`;
      }
      if (page < totalPages) {
        html += `<button id="next-page">Suivant</button>`;
      }
      pagination.innerHTML = html;

      // Gestion des boutons
      if (page > 1) {
        document.getElementById('prev-page').onclick = () => {
          currentPage--;
          loadTable(currentPage);
        };
      }
      if (page < totalPages) {
        document.getElementById('next-page').onclick = () => {
          currentPage++;
          loadTable(currentPage);
        };
      }
    });
}
loadTable();

// Gestion de l'ajout d'une installation via le formulaire
document.getElementById('add-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('../php/request.php?type=add_installation', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(() => {
    this.reset();
    loadTable();
  });
});

// Gestion de la modification et suppression d'une installation
document.getElementById('table-body').addEventListener('click', function(e) {
  const tr = e.target.closest('tr');
  const id = tr ? tr.getAttribute('data-id') : null;
  if (e.target.classList.contains('btn-save')) {
    // Récupère les champs édités pour la sauvegarde
    const updates = {};
    tr.querySelectorAll('.edit').forEach(td => {
      updates[td.dataset.field] = td.textContent.trim();
    });
    updates.id = id;
    fetch('../php/request.php?type=update_installation', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(updates)
    })
    .then(r => r.json())
    .then(() => loadTable());
  }
  if (e.target.classList.contains('btn-delete')) {
    if (confirm('Supprimer cette installation ?')) {
      fetch('../php/request.php?type=delete_installation&id=' + encodeURIComponent(id), { method: 'POST' })
        .then(r => r.json())
        .then(() => loadTable());
    }
  }
});