function loadTable() {
  fetch('../php/request.php?type=all_installations&limit=100')
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById('table-body');
      tbody.innerHTML = '';
      data.forEach(row => {
        tbody.innerHTML += `
          <tr data-id="${row.id}">
            <td>${row.id || ''}</td>
            <td contenteditable="true" class="edit" data-field="locality">${row.locality || ''}</td>
            <td contenteditable="true" class="edit" data-field="marque_panneau">${row.annee_install  || ''}</td>
            <td contenteditable="true" class="edit" data-field="modele_panneau">${row.mois_install || ''}</td>
            <td contenteditable="true" class="edit" data-field="nb_panneaux">${row.region || ''}</td>
            <td contenteditable="true" class="edit" data-field="marque_onduleur">${row.departement || ''}</td>
            <td contenteditable="true" class="edit" data-field="modele_onduleur">${row.ville || ''}</td>
            <td contenteditable="true" class="edit" data-field="nb_onduleur">${row.installateur || ''}</td>
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
    });
}
loadTable();

// Ajout
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

// Modification et suppression
document.getElementById('table-body').addEventListener('click', function(e) {
  const tr = e.target.closest('tr');
  const id = tr ? tr.getAttribute('data-id') : null;
  if (e.target.classList.contains('btn-save')) {
    // Récupère les champs édités
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