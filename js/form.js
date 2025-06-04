window.onload = function() {
  fetch('php/request.php?type=marque_ondul')
    .then(r => r.text())
    .then(html => document.getElementById('marque_ondul').innerHTML += html);

  fetch('php/request.php?type=marque_pan')
    .then(r => r.text())
    .then(html => document.getElementById('marque_pan').innerHTML += html);

  fetch('php/request.php?type=dep')
    .then(r => r.text())
    .then(html => document.getElementById('dep').innerHTML += html);
};