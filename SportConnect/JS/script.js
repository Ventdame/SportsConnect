/**
 * Initialise les événements une fois que le DOM est entièrement chargé.
 */
document.addEventListener("DOMContentLoaded", () => {
    /**
     * Formulaire de filtre pour rechercher des réservations.
     */
    const formulaireFiltre = document.getElementById("filter-form");

    /**
     * Conteneur pour afficher les réservations.
     */
    const listeReservations = document.getElementById("reservation-list");

    if (formulaireFiltre) {
        /**
         * Gère la soumission du formulaire de filtre et effectue une requête AJAX.
         * @param {Event} e - L'événement de soumission du formulaire.
         */
        formulaireFiltre.addEventListener("submit", async (e) => {
            e.preventDefault();

            // Récupérer les valeurs des champs de filtre
            const ville = document.getElementById("filter-ville").value.trim();
            const sport = document.getElementById("filter-sport").value.trim();
            const date = document.getElementById("filter-date").value.trim();

            console.log("Valeurs envoyées :", { ville, sport, date });

            try {
                // Effectue une requête POST vers le serveur
                const reponse = await fetch(`?page=reservation&action=rechercheAjax`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ ville, sport, date }),
                });

                if (reponse.ok) {
                    const texte = await reponse.text();

                    try {
                        // Tente de parser la réponse en JSON
                        const evenements = JSON.parse(texte);
                        console.log("Données reçues :", evenements);
                        afficherReservations(evenements);
                    } catch (erreur) {
                        console.error("Erreur de parsing JSON :", erreur);
                        console.log("Réponse brute du serveur :", texte);
                        listeReservations.innerHTML = "<p>La réponse du serveur n'est pas valide.</p>";
                    }
                } else {
                    const texteErreur = await reponse.text();
                    console.error("Erreur lors de la récupération des événements :", texteErreur);
                    listeReservations.innerHTML = "<p>Une erreur est survenue. Veuillez réessayer.</p>";
                }
            } catch (erreur) {
                // Gère les erreurs réseau
                console.error("Erreur réseau :", erreur);
                listeReservations.innerHTML = "<p>Erreur réseau. Veuillez vérifier votre connexion.</p>";
            }
        });
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
                    <button type="submit" class="btn-reserver">Réserver</button>
                </form>
            `;
            listeReservations.appendChild(reservationDiv);
        });
    }

    /**
     * Gestion de l'affichage des champs de localisation (nouvelle ou existante).
     */
    const radiosTypeLocalisation = document.querySelectorAll('input[name="localisation_type"]');
    const champsNouvelleLocalisation = document.getElementById("nouvelle-localisation-fields");

    if (radiosTypeLocalisation.length > 0 && champsNouvelleLocalisation) {
        /**
         * Affiche ou masque les champs de la nouvelle localisation.
         */
        function basculerChampsLocalisation() {
            const optionSelectionnee = document.querySelector('input[name="localisation_type"]:checked');
            if (optionSelectionnee && optionSelectionnee.value === "nouvelle") {
                champsNouvelleLocalisation.style.display = "block";
            } else {
                champsNouvelleLocalisation.style.display = "none";
            }
        }

        // Ajoute les événements de changement sur les boutons radio
        radiosTypeLocalisation.forEach((radio) => {
            radio.addEventListener("change", basculerChampsLocalisation);
        });

        // Initialise l'affichage des champs lors du chargement
        basculerChampsLocalisation();
    }
});
