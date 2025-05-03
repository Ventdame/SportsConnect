document.addEventListener("DOMContentLoaded", () => {
    // 1. Récupérer le statut PMR depuis l'attribut data-pmr du <body>
    const pmrStatus = document.body.dataset.pmr || "non";

    /**
     * Formulaire de filtre pour rechercher des réservations.
     */
    const formulaireFiltre = document.getElementById("filter-form");

    /**
     * Conteneur pour afficher les réservations.
     */
    const listeReservations = document.getElementById("reservation-list");

    // Vérifie si le formulaire de filtre existe sur la page
    if (!formulaireFiltre) {
        console.log("Aucun formulaire de filtre (id='filter-form') n'est présent sur cette page.");
        return;
    }

    /**
     * Affiche les événements reçus dans la liste des réservations.
     * @param {Array} evenements - Tableau des événements reçus depuis le serveur.
     */
    function afficherReservations(evenements) {
        listeReservations.innerHTML = "";

        if (!Array.isArray(evenements)) {
            console.error("Les données reçues ne sont pas valides :", evenements);
            listeReservations.innerHTML = "<p>Erreur dans les données reçues.</p>";
            return;
        }

        if (evenements.length === 0) {
            listeReservations.innerHTML = "<p>Aucun événement ne correspond à vos critères.</p>";
            return;
        }

        // Parcourt et affiche chaque événement
        evenements.forEach((evenement) => {
            // Créer l'élément HTML pour un événement
            const reservationDiv = document.createElement("div");
            reservationDiv.classList.add("reservation-item");
            reservationDiv.innerHTML = `
                <h3 class="reservation-title">${evenement.evenement || "Nom non défini"}</h3>
                <p class="reservation-detail"><strong>Date :</strong> ${evenement.date || "Date non définie"}</p>
                <p class="reservation-detail"><strong>Description :</strong> ${evenement.description || "Aucune description disponible"}</p>
                <p class="reservation-detail"><strong>Lieu :</strong> ${evenement.localisation || "Lieu non défini"}</p>
                <p class="reservation-detail"><strong>Accessibilité PMR :</strong> ${evenement.pmr_accessible || "Non spécifié"}</p>
                <p class="reservation-detail"><strong>Prix :</strong> ${evenement.prix || "Non spécifié"}</p>
                <form method="POST" action="?page=reservation&action=reserver">
                    <input type="hidden" name="id_evenement" value="${evenement.id_evenement}">
                    <button type="submit" class="btn btn--primary">Réserver</button>
                </form>
            `;
            listeReservations.appendChild(reservationDiv);
        });
    }
});