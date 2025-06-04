'use strict';


async function requestData() {
    try {
        const response = await fetch('../php/request.php?type=stats');
        if (!response.ok) {
            displayErrors(response.status);
            return;
        }
        const data = await response.json();
         if (data.enregistrements !== undefined)
            document.getElementById('stat-enregistrements-count').textContent = data.enregistrements;
        if (data.annee !== undefined)
            document.getElementById('stat-installations-annee-m').textContent = data.annee;
        if (data.mois !== undefined)
            document.getElementById('stat-installations-mois-m').textContent = data.mois;
        if (data.iregion !== undefined)
            document.getElementById('stat-iregion-m').textContent = data.iregion;
        if (data.installateurs !== undefined)
            document.getElementById('stat-installateurs-m').textContent = data.installateurs;
        if (data.marques !== undefined)
            document.getElementById('stat-marques-m').textContent = data.marques;
        if (data.panneaux !== undefined)
            document.getElementById('stat-panneaux-m').textContent = data.panneaux;
    } catch (error) {
        console.error('Erreur lors de la récupération des enregistrements:', error);
    }
}
requestData();

function displayErrors(status){
    console.error(`Error ${status}`);
}