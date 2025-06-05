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
            <td contenteditable="true" class="edit" data-field="marque_panneau">${row.marque_panneau || ''}</td>
            <td contenteditable="true" class="edit" data-field="modele_panneau">${row.modele_panneau || ''}</td>
            <td contenteditable="true" class="edit" data-field="nb_panneaux">${row.nb_panneaux || ''}</td>
            <td contenteditable="true" class="edit" data-field="marque_onduleur">${row.marque_onduleur || ''}</td>
            <td contenteditable="true" class="edit" data-field="modele_onduleur">${row.modele_onduleur || ''}</td>
            <td contenteditable="true" class="edit" data-field="nb_onduleur">${row.nb_onduleur || ''}</td>
            <td contenteditable="true" class="edit" data-field="annee_install">${row.annee_install || ''}</td>
            <td contenteditable="true" class="edit" data-field="mois_install">${row.mois_install || ''}</td>
            <td contenteditable="true" class="edit" data-field="puissance_crete">${row.puissance_crete || ''}</td>
            <td contenteditable="true" class="edit" data-field="surface">${row.surface || ''}</td>
            <td contenteditable="true" class="edit" data-field="lat">${row.lat || ''}</td>
            <td contenteditable="true" class="edit" data-field="lon">${row.lon || ''}</td>
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