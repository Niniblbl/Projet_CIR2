// Script pour la page d'accueil : affichage des stats et des graphiques
'use strict';

// Récupère et affiche les statistiques principales (enregistrements, installateurs, marques, panneaux)
async function requestData() {
    try {
        const response = await fetch('../php/request.php?type=stats'); // Envoie une requête pour récupérer les statistiques
        if (!response.ok) {
            displayErrors(response.status); // Affiche une erreur si la requête a échoué
            return;
        }
        const data = await response.json(); // Convertit la réponse en JSON
         if (data.enregistrements !== undefined)
            document.getElementById('stat-enregistrements-count').textContent = data.enregistrements; // Affiche le nombre d'enregistrements
         if (data.installateurs !== undefined)
            document.getElementById('stat-installateurs-m').textContent = data.installateurs; // Affiche le nombre d'installateurs
         if (data.marques !== undefined)
            document.getElementById('stat-marques-m').textContent = data.marques; // Affiche le nombre de marques
        if (data.panneaux !== undefined)
            document.getElementById('stat-panneaux-m').textContent = data.panneaux; // Affiche le nombre de panneaux
    } catch (error) {
        console.error('Erreur lors de la récupération des enregistrements:', error); // Affiche une erreur dans la console si la requête échoue
    }
}
requestData(); // Appelle la fonction pour récupérer et afficher les statistiques

// Affiche le graphique des installations par année
async function drawGraphInstallationsParAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_annee'); // Envoie une requête pour récupérer les installations par année
    const data = await response.json(); // Convertit la réponse en JSON 
    const labels = data.map(row => row.annee_install); // Récupère les années d'installation
    const values = data.map(row => row.nb); // Récupère le nombre d'installations pour chaque année

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

    const ctx = document.getElementById('graph-installations').getContext('2d'); // Récupère le contexte du canvas pour dessiner le graphique
    new Chart(ctx, { // Crée un nouveau graphique avec Chart.js
        type: 'bar', // Type de graphique : barres
        data: {
            labels: labels,
            datasets: [{
                label: 'Installations par année', // Légende du graphique
                data: values, // Données à afficher
                backgroundColor: labels.map((_, i) => barColors[i % barColors.length]) // Couleurs des barres, en utilisant un tableau de couleurs
            }]
        }
    });
}
drawGraphInstallationsParAnnee();

// Affiche le graphique en camembert des installations par région
async function drawGraphFromageRegions() {
    const response = await fetch('../php/request.php?type=installations_par_region'); // Envoie une requête pour récupérer les installations par région
    const data = await response.json(); // Convertit la réponse en JSON
    const labels = data.map(row => row.nom_region); // Récupère les noms des régions
    const values = data.map(row => Number(row.nb)); // Récupère le nombre d'installations pour chaque région, en s'assurant que c'est un nombre
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


    const ctx = document.getElementById('graph-fromage').getContext('2d'); // Récupère le contexte du canvas pour dessiner le graphique
    new Chart(ctx, { // Crée un nouveau graphique avec Chart.js
        type: 'pie', // Type de graphique : camembert
        data: {
            labels: labels, // Noms des régions
            datasets: [{
                label: 'Installations par région', // Légende du graphique
                data: values, // Données à afficher
                backgroundColor: labels.map((_, i) => colors[i % colors.length]), // Couleurs des segments, en utilisant un tableau de couleurs
                borderWidth: 0
            }]
        }
    });
}
drawGraphFromageRegions();

// Affiche le graphique des installations par région et par année
async function drawGraphRegionAnnee() {
    const response = await fetch('../php/request.php?type=installations_par_region_et_annee'); // Envoie une requête pour récupérer les installations par région et par année
    const data = await response.json(); // Convertit la réponse en JSON

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
                const found = data.find(row => row.nom_region === region && row.annee_install === annee); // Cherche les données pour cette région et cette année
                return found ? Number(found.nb) : 0;
            }),
            backgroundColor: color // Utilise une couleur différente pour chaque région
        };
    });

    const ctx = document.getElementById('graph-region-annee').getContext('2d'); // Récupère le contexte du canvas pour dessiner le graphique
    new Chart(ctx, {
        type: 'bar', // Type de graphique : barres
        data: {
            labels: annees, // Années pour l'axe des abscisses
            datasets: datasets // Données pour chaque région
        },
        options: {
            responsive: true, // Rendre le graphique responsive
            plugins: { 
                legend: { display: true } // Afficher la légende
            },
            scales: {
                x: { title: { display: true, text: 'Année' } }, // Titre de l'axe des abscisses
                y: { title: { display: true, text: 'Nombre d\'installations' }, beginAtZero: true } // Titre de l'axe des ordonnées et commencer à zéro
            }
        }
    });
}
drawGraphRegionAnnee(); // Appelle la fonction pour dessiner le graphique des installations par région et par année

// Fonction utilitaire pour afficher les erreurs
function displayErrors(status){
    console.error(`Error ${status}`);
}