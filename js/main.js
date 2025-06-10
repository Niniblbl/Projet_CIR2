// Script pour la page d'accueil : affichage des stats et des graphiques
'use strict';

// Récupère et affiche les statistiques principales (enregistrements, installateurs, marques, panneaux)
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

// Affiche le graphique des installations par année
async function drawGraphInstallationsParAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_annee');
    const data = await response.json();
    const labels = data.map(row => row.annee_install);
    const values = data.map(row => row.nb);

    const barColors = [
        'rgba(91, 108, 176, 0.85)',   
        'rgba(139, 180, 248, 0.85)',  
        'rgba(0, 159, 154, 0.85)',    
        'rgba(178, 198, 228, 0.85)',  
        'rgba(0, 204, 204, 0.85)',    
        'rgba(244, 250, 255, 0.85)',  
        'rgba(100, 181, 246, 0.85)', 
        'rgba(77, 182, 172, 0.85)',   
        'rgba(233, 245, 255, 0.85)'   
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

// Affiche le graphique en camembert des installations par région
async function drawGraphFromageRegions() {
    const response = await fetch('../php/request.php?type=installations_par_region');
    const data = await response.json();
    const labels = data.map(row => row.nom_region);
    const values = data.map(row => Number(row.nb));
        const colors = [
        'rgba(91, 108, 176, 0.85)',   
        'rgba(139, 180, 248, 0.85)',  
        'rgba(0, 159, 154, 0.85)',    
        'rgba(178, 198, 228, 0.85)',  
        'rgba(0, 204, 204, 0.85)',    
        'rgba(244, 250, 255, 0.85)',  
        'rgba(100, 181, 246, 0.85)',  
        'rgba(77, 182, 172, 0.85)',  
        'rgba(233, 245, 255, 0.85)'   
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

// Affiche le graphique des installations par région et par année
async function drawGraphRegionAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_region_et_annee');
    const data = await response.json();

    // Récupère toutes les années et régions distinctes
    const annees = [...new Set(data.map(row => row.annee_install))];
    const regions = [...new Set(data.map(row => row.nom_region))];

    const regionColors = [
        'rgba(91, 108, 176, 0.85)',  
        'rgba(139, 180, 248, 0.85)', 
        'rgba(0, 159, 154, 0.85)',    
        'rgba(178, 198, 228, 0.85)', 
        'rgba(0, 204, 204, 0.85)',    
        'rgba(244, 250, 255, 0.85)', 
        'rgba(100, 181, 246, 0.85)',  
        'rgba(77, 182, 172, 0.85)',  
        'rgba(233, 245, 255, 0.85)'  
        ];

    // Prépare un dataset par région pour le graphique groupé
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

// Fonction utilitaire pour afficher les erreurs
function displayErrors(status){
    console.error(`Error ${status}`);
}