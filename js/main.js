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

    // Couleurs gourmandes : caramel, chocolat, fraise, pistache, vanille
    const barColors = [
        'rgba(91, 108, 176, 0.85)',   // bleu principal (header)
        'rgba(139, 180, 248, 0.85)',  // bleu clair
        'rgba(0, 159, 154, 0.85)',    // vert d'eau
        'rgba(178, 198, 228, 0.85)',  // bleu pastel
        'rgba(0, 204, 204, 0.85)',    // turquoise doux
        'rgba(244, 250, 255, 0.85)',  // blanc bleuté
        'rgba(100, 181, 246, 0.85)',  // bleu ciel
        'rgba(77, 182, 172, 0.85)',   // vert d'eau foncé
        'rgba(233, 245, 255, 0.85)'   // bleu très pâle
        ];

    const ctx = document.getElementById('graph-installations').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Installations par année',
                data: values,
                backgroundColor: labels.map((_, i) => barColors[i % barColors.length])
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
        const colors = [
        'rgba(91, 108, 176, 0.85)',   // bleu principal (header)
        'rgba(139, 180, 248, 0.85)',  // bleu clair
        'rgba(0, 159, 154, 0.85)',    // vert d'eau
        'rgba(178, 198, 228, 0.85)',  // bleu pastel
        'rgba(0, 204, 204, 0.85)',    // turquoise doux
        'rgba(244, 250, 255, 0.85)',  // blanc bleuté
        'rgba(100, 181, 246, 0.85)',  // bleu ciel
        'rgba(77, 182, 172, 0.85)',   // vert d'eau foncé
        'rgba(233, 245, 255, 0.85)'   // bleu très pâle
        ];


    const ctx = document.getElementById('graph-fromage').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Installations par région',
                data: values,
                backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                borderWidth: 0
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

    // Couleurs soleil pour les régions (mêmes tons que ci-dessus)
    const regionColors = [
        'rgba(91, 108, 176, 0.85)',   // bleu principal (header)
        'rgba(139, 180, 248, 0.85)',  // bleu clair
        'rgba(0, 159, 154, 0.85)',    // vert d'eau
        'rgba(178, 198, 228, 0.85)',  // bleu pastel
        'rgba(0, 204, 204, 0.85)',    // turquoise doux
        'rgba(244, 250, 255, 0.85)',  // blanc bleuté
        'rgba(100, 181, 246, 0.85)',  // bleu ciel
        'rgba(77, 182, 172, 0.85)',   // vert d'eau foncé
        'rgba(233, 245, 255, 0.85)'   // bleu très pâle
        ];

    // Prépare un dataset par région
    const datasets = regions.map((region, idx) => {
        const color = regionColors[idx % regionColors.length];
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