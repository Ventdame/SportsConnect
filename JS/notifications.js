document.addEventListener("DOMContentLoaded", function() {
    // Vérifier si l'utilisateur est connecté (en vérifiant la présence de l'élément user-info)
    const userInfoElement = document.querySelector('.user-info');
    if (!userInfoElement) {
        console.log('Utilisateur non connecté, pas de notifications à charger');
        return;
    }

    // Créer l'élément de notification dans la navbar s'il n'existe pas déjà
    let notificationElement = document.querySelector('.notification-icon');
    if (!notificationElement) {
        // Créer l'élément de notification
        notificationElement = document.createElement('li');
        notificationElement.className = 'notification-icon';
        notificationElement.innerHTML = `
            <a href="#" id="notification-toggle">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-count" id="notification-count">0</span>
            </a>
            <div class="notification-dropdown" id="notification-dropdown">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <a href="#" id="mark-all-read">Tout marquer comme lu</a>
                </div>
                <div class="notification-list" id="notification-list">
                    <p class="no-notifications">Aucune notification</p>
                </div>
            </div>
        `;

        // Insérer l'élément de notification avant l'élément user-info
        const navLinks = document.querySelector('.nav-links');
        navLinks.insertBefore(notificationElement, userInfoElement);

        // Ajouter les gestionnaires d'événements
        document.getElementById('notification-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = document.getElementById('notification-dropdown');
            dropdown.classList.toggle('show');
        });

        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notification-dropdown');
            const toggle = document.getElementById('notification-toggle');
            if (dropdown && dropdown.classList.contains('show') && !dropdown.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Marquer toutes les notifications comme lues
        document.getElementById('mark-all-read').addEventListener('click', function(e) {
            e.preventDefault();
            marquerToutesCommeLues();
        });
    }

    // Fonction pour charger les notifications
    function chargerNotifications() {
        fetch('?page=notification&action=obtenirNotificationsAjax')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    afficherNotifications(data.notifications, data.count);
                } else {
                    console.error('Erreur lors du chargement des notifications:', data.error);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des notifications:', error);
            });
    }

    // Fonction pour afficher les notifications
    function afficherNotifications(notifications, count) {
        const notificationList = document.getElementById('notification-list');
        const notificationCount = document.getElementById('notification-count');

        // Mettre à jour le compteur
        notificationCount.textContent = count;
        notificationCount.style.display = count > 0 ? 'inline-block' : 'none';

        // Vider la liste
        notificationList.innerHTML = '';

        // Si aucune notification, afficher un message
        if (notifications.length === 0) {
            notificationList.innerHTML = '<p class="no-notifications">Aucune notification</p>';
            return;
        }

        // Ajouter chaque notification à la liste
        notifications.forEach(notification => {
            const notificationItem = document.createElement('div');
            notificationItem.className = 'notification-item';
            notificationItem.dataset.id = notification.id_notification;

            notificationItem.innerHTML = `
                <div class="notification-content">
                    <p>${notification.contenu}</p>
                    <small>${formatDate(notification.date_notification)}</small>
                </div>
                <button class="mark-read" data-id="${notification.id_notification}">
                    <i class="fa-solid fa-check"></i>
                </button>
            `;

            notificationList.appendChild(notificationItem);

            // Ajouter un gestionnaire d'événements pour marquer comme lu
            notificationItem.querySelector('.mark-read').addEventListener('click', function() {
                const id = this.dataset.id;
                marquerCommeLue(id);
            });
        });
    }

    // Fonction pour marquer une notification comme lue
    function marquerCommeLue(id) {
        fetch('?page=notification&action=marquerCommeLueAjax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_notification: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger les notifications
                chargerNotifications();
            } else {
                console.error('Erreur lors du marquage de la notification comme lue:', data.error);
            }
        })
        .catch(error => {
            console.error('Erreur lors du marquage de la notification comme lue:', error);
        });
    }

    // Fonction pour marquer toutes les notifications comme lues
    function marquerToutesCommeLues() {
        fetch('?page=notification&action=marquerToutesCommeLuesAjax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger les notifications
                chargerNotifications();
            } else {
                console.error('Erreur lors du marquage de toutes les notifications comme lues:', data.error);
            }
        })
        .catch(error => {
            console.error('Erreur lors du marquage de toutes les notifications comme lues:', error);
        });
    }

    // Fonction pour formater la date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        // Moins d'une minute
        if (diff < 60 * 1000) {
            return 'À l\'instant';
        }

        // Moins d'une heure
        if (diff < 60 * 60 * 1000) {
            const minutes = Math.floor(diff / (60 * 1000));
            return `Il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
        }

        // Moins d'un jour
        if (diff < 24 * 60 * 60 * 1000) {
            const hours = Math.floor(diff / (60 * 60 * 1000));
            return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
        }

        // Moins d'une semaine
        if (diff < 7 * 24 * 60 * 60 * 1000) {
            const days = Math.floor(diff / (24 * 60 * 60 * 1000));
            return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
        }

        // Format de date standard
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    // Charger les notifications au chargement de la page
    chargerNotifications();

    // Rafraîchir les notifications toutes les 60 secondes
    setInterval(chargerNotifications, 60000);
});