<?php

namespace App\Vues;

use App\Core\VueBase;
use App\Core\Reponses;

class AdminVue extends VueBase
{
    private $utilisateurs = [];
    private $activeTab = 'utilisateurs';

    public function __construct($titre, $utilisateurConnecte = null, $utilisateurs = [], $activeTab = 'utilisateurs', $erreurs = [])
    {
        parent::__construct($titre, [], $erreurs);
        $this->utilisateurs = $utilisateurs;
        $this->activeTab = $activeTab;
        
        if ($utilisateurConnecte !== null) {
            $this->utilisateur = $utilisateurConnecte;
        }
    }

    protected function afficherContenu()
    {
        ?>
        <main class="container">
            <div class="admin-header">
                <h1><i class="fa-solid fa-shield-alt"></i> Panneau d'administration</h1>
                <p>Bienvenue, <strong><?php echo $this->e($this->utilisateur['pseudo']); ?></strong></p>
            </div>
    
            <div class="admin-tabs">
                <div class="tab-header">
                    <a href="?page=admin&action=utilisateurs" class="tab-btn <?php echo $this->activeTab === 'utilisateurs' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users"></i> Utilisateurs
                    </a>
                    <a href="?page=admin&action=evenements" class="tab-btn <?php echo $this->activeTab === 'evenements' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-calendar-days"></i> Événements
                    </a>
                    <a href="?page=admin&action=feedbacks" class="tab-btn <?php echo $this->activeTab === 'feedbacks' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-comments"></i> Feedbacks
                    </a>
                </div>
    
                <div class="tab-content">
                    <?php 
                    if ($this->activeTab === 'utilisateurs') {
                        echo $this->renderUtilisateurs($this->utilisateurs);
                    }
                    elseif ($this->activeTab === 'evenements') {
                        echo "<div class='admin-placeholder'><i class='fa-solid fa-calendar-days fa-3x'></i><p>Gestion des événements - À implémenter</p></div>";
                    }
                    elseif ($this->activeTab === 'feedbacks') {
                        echo "<div class='admin-placeholder'><i class='fa-solid fa-comments fa-3x'></i><p>Gestion des feedbacks - À implémenter</p></div>";
                    }
                    ?>
                </div>
            </div>
        </main>
        <?php
    }
    
    public function renderUtilisateurs($utilisateurs)
    {
        ob_start();
        ?>
        <div class="admin-section">
            <div class="admin-section-header">
                <h2><i class="fa-solid fa-user-cog"></i> Gestion des utilisateurs</h2>
                <p>Total : <?php echo count($utilisateurs); ?> utilisateurs</p>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pseudo</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utilisateurs)): ?>
                            <tr>
                                <td colspan="5" class="empty-table">
                                    <i class="fa-solid fa-user-slash"></i>
                                    <p>Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($utilisateurs as $utilisateur): ?>
                            <tr>
                                <td class="id-cell">#<?= $utilisateur['id_utilisateur'] ?></td>
                                <td class="user-cell">
                                    <span class="user-avatar">
                                        <?= strtoupper(substr($utilisateur['pseudo'], 0, 1)) ?>
                                    </span>
                                    <?= htmlspecialchars($utilisateur['pseudo']) ?>
                                </td>
                                <td><?= htmlspecialchars($utilisateur['email']) ?></td>
                                <td>
                                    <?php if (($utilisateur['role'] ?? '') === 'admin'): ?>
                                        <span class="role-badge admin">
                                            <i class="fa-solid fa-crown"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="role-badge user">
                                            <i class="fa-solid fa-user"></i> Utilisateur
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <?php if (($utilisateur['role'] ?? '') === 'admin'): ?>
                                        <a href="?page=admin&action=retirer_admin&id=<?= $utilisateur['id_utilisateur'] ?>"
                                           class="admin-btn danger"
                                           onclick="return confirm('Êtes-vous sûr de vouloir retirer les droits administrateur de cet utilisateur ?');">
                                            <i class="fa-solid fa-user-minus"></i> Retirer admin
                                        </a>
                                    <?php else: ?>
                                        <a href="?page=admin&action=promouvoir_admin&id=<?= $utilisateur['id_utilisateur'] ?>"
                                           class="admin-btn success"
                                           onclick="return confirm('Êtes-vous sûr de vouloir promouvoir cet utilisateur comme administrateur ?');">
                                            <i class="fa-solid fa-user-plus"></i> Promouvoir admin
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Autres vues admin à implementer...
}