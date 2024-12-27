<html>
	<head>
		<title>Test php</title>
	</head>
	<body>
		<p>Hello</p>
		<p style="font-style: italic;">Maximum de 16 charactères alphanumériques, espaces ou tirets.</p>
		<form action="index.php" method="post">
			<label for="user_name">Nom</label>
			<input type="text" name="user_name"/>

			<label for="user_password">Mot de passe</label>
			<input type="text" name="user_password"/>

			<label for="user_data">Données</label>
			<input type="text" name="user_data"/>

			<input type="submit" value="Envoyer"/>
		</form>
		<?php

			function is_valid_entry($entry,$maxLength) {
				$entryLength = strlen($entry);
				if ($entryLength == 0 || $entryLength > $maxLength) {
					return false;
				}

				if (preg_match("/^[\w\s\-]+$/",$entry) == 0) {
					return false;
				}
				return true;
			}

			// Nom d'utilisateur et mots de passe fournis ?
			if (!isset($_POST['user_name']) || !isset($_POST['user_password'])) {
				echo "<p>Mot de passe ou nom d'utilisateur manquant</p>";
				exit();
			}

			$userNameSubmitted = $_POST['user_name'];
			if (!is_valid_entry($userNameSubmitted,16)) {
				echo "<p>Nom d'utilisateur invalide";
				exit();
			}

			$userPasswordSubmitted = $_POST['user_password'];
			if (!is_valid_entry($userPasswordSubmitted,16)) {
				echo "<p>Nom d'utilisateur invalide</p>";
				exit();
			}

			$loadedJson = file_get_contents("names.txt");
			$userItemsArray = ($loadedJson == false) ? [] : json_decode($loadedJson,true);
			$userItem = [];
			
			// Utilisateur existant, on récupère l'item
			if (!empty($userItemsArray) && array_key_exists($userNameSubmitted,$userItemsArray)) {
				$userItem = $userItemsArray[$userNameSubmitted];
				if ($userItem["user_password"] != $userPasswordSubmitted) {
					echo "<p>Mauvais mot de passe fourni</p>";
					exit();
				}
			// Utilisateur non existant, on crée l'item
			} else {
				$userItem = ["user_password" => $userPasswordSubmitted];
			}

			// Données existantes => affiche
			if (array_key_exists("user_data",$userItem))  {
				echo "<p>Salut " . $userNameSubmitted . ", tes données: " . $userItem["user_data"];
			} 

			// Données fournies => remplace
			if (isset($_POST['user_data'])) {
				$userSubmittedData = $_POST['user_data'];
				if (is_valid_entry($userSubmittedData,16)) {
					$userItem["user_data"] = $userSubmittedData;
					echo "<p>Les données suivantes ont été enregistrées: " . $userSubmittedData;
				} else {
					echo "<p>Les données fournies n'ont pas le bon format</p>";
					exit();
				}
			}

			$userItemsArray[$userNameSubmitted] = $userItem;
			$jsonDataToSave = json_encode($userItemsArray);
			file_put_contents("names.txt",$jsonDataToSave);
			echo "<p>Données enregistrées</p>";
		?>
	</body>
</html>
