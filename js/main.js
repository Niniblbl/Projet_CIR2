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

async function drawGraphInstallationsParAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_annee');
    const data = await response.json();
    const labels = data.map(row => row.annee_install);
    const values = data.map(row => row.nb);

    const ctx = document.getElementById('graph-installations').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Installations par année',
                data: values,
                backgroundColor: 'rgba(255, 0, 0, 0.5)'
            }]
        }
    });
}
drawGraphInstallationsParAnnee();

async function drawGraphFromageRegions() {
    const response = await fetch('../php/request.php?type=installations_par_region');
    const data = await response.json();
    const labels = data.map(row => row.nom_region);
    const values = data.map(row => Number(row.nb));

    const ctx = document.getElementById('graph-fromage').getContext('2d');
    new Chart(ctx, {
        type: 'pie', // ou 'doughnut' pour un donut
        data: {
            labels: labels,
            datasets: [{
                label: 'Installations par région',
                data: values,
                backgroundColor: [
                    'rgba(255, 0, 200, 0.5)',
                    'rgba(21, 0, 255, 0.5)',
                    'rgba(0, 255, 204, 0.5)',
                    'rgba(47, 255, 0, 0.5)',
                    'rgba(255, 234, 0, 0.5)',
                    'rgba(255, 0, 0, 0.5)',
                ]
            }]
        }
    });
}
drawGraphFromageRegions();

async function drawGraphRegionAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_region_et_annee');
    const data = await response.json();

    // Récupère toutes les années et régions distinctes
    const annees = [...new Set(data.map(row => row.annee_install))];
    const regions = [...new Set(data.map(row => row.nom_region))];

    // Prépare un dataset par région
    const datasets = regions.map((region, idx) => {
        // Couleur différente pour chaque région
        const color = `hsl(${(idx * 360 / regions.length)}, 70%, 60%)`;
        return {
            label: region,
            data: annees.map(annee => {
                const found = data.find(row => row.nom_region === region && row.annee_install === annee);
                return found ? Number(found.nb) : 0;
            }),
            backgroundColor: color
        };
    });

    const ctx = document.getElementById('graph-region-annee').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: annees,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                x: { title: { display: true, text: 'Année' } },
                y: { title: { display: true, text: 'Nombre d\'installations' }, beginAtZero: true }
            }
        }
    });
}
drawGraphRegionAnnee();

function displayErrors(status){
    console.error(`Error ${status}`);
}