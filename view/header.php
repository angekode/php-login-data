<?php if ($sessionInfo->isConnected): ?>
    <p>Utilisateur connecté: <?= $sessionInfo->userInfo->name ?></p>

<?php else: ?>

    <form action="index.php" method="post">
        <label for="header_input_name">Nom</label>
        <input type="text" name="header_input_name"/>

        <label for="user_password">Mot de passe</label>
        <input type="text" name="header_input_password"/>

        <input type="submit" value="Envoyer" name="header_button_login"/>
    </form>

<?php endif; ?>