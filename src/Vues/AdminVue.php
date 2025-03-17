<?php

namespace App\Vues;

use App\Core\VueBase;

class AdminVue extends VueBase
{
    public function renderUtilisateurs($utilisateurs)
    {
        ob_start();
        ?>
        <div class="container mt-4">
            <h2 class="mb-4">Gestion des utilisateurs</h2>
            
            <?php $this->afficherMessages(); ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Pseudo</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $utilisateur): ?>
                        <tr>
                            <td><?= $utilisateur['id_utilisateur'] ?></td>
                            <td><?= htmlspecialchars($utilisateur['pseudo']) ?></td>
                            <td><?= htmlspecialchars($utilisateur['email']) ?></td>
                            <td><?= $utilisateur['role'] ?? 'utilisateur' ?></td>
                            <td>
                                <?php if ($utilisateur['role'] === 'administrateur'): ?>
                                    <a href="<?= Reponses::url('admin/retirer_admin/' . $utilisateur['id_utilisateur']) ?>"
                                       class="btn btn-warning btn-sm"
                                       onclick="return confirm('Êtes-vous sûr de retirer les droits admin ?')">
                                        Retirer admin
                                    </a>
                                <?php else: ?>
                                    <a href="<?= Reponses::url('admin/promouvoir_admin/' . $utilisateur['id_utilisateur']) ?>"
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Êtes-vous sûr de promouvoir cet utilisateur ?')">
                                        Promouvoir admin
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}