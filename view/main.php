<?php if ($sessionInfo->isConnected): ?>
    <p style="font-style: italic;">Maximum de 16 charactères alphanumériques, espaces ou tirets.</p>
    <form action="index.php" method="post">
        <label for="main_input_data">Données</label>
        <input type="text" name="main_input_data" value="<?=$sessionInfo->userInfo->data?>"/>
        <input type="submit" value="Envoyer" name="main_button_validation"/>
    </form>

<?php else: ?>
    <p style="font-style: italic;">Maximum de 16 charactères alphanumériques, espaces ou tirets.</p>
    <form action="index.php" method="post">
        <label for="main_input_name">Nom</label>
        <input type="text" name="main_input_name"/>

        <label for="main_input_password">Mot de passe</label>
        <input type="text" name="main_input_password"/>

        <input type="text" name="main_input_data" value="<?=$sessionInfo->userInfo->data?>"/>
        <input type="submit" value="Envoyer" name="main_button_validation"/>
    </form>

<?php endif; ?>