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

			function is_valid_entry($entry, $maxLength) {
				$entryLength = strlen($entry);
				if ($entryLength == 0 || $entryLength > $maxLength) {
					return false;
				}

				if (preg_match("/^[\w\s\-]+$/",$entry) == 0) {
					return false;
				}
				return true;
			}

			function save_user_info_array($userInfoArray) {
				$jsonDataToSave = json_encode($userInfoArray);
				if (file_put_contents("names.txt", $jsonDataToSave) == false) {
					return false;
				} else {
					return true;
				}
			}

			function load_user_info_array() {
				$loadedJson = file_get_contents("names.txt");
				if ($loadedJson === false) {
					return [];
				}
				$userInfoArray = json_decode($loadedJson, true);
				if ($userInfoArray === null) {
					return [];
				}

				return $userInfoArray;
			}

			function get_user_info($userName, $userInfoArray) {
				if (empty($userInfoArray) || !array_key_exists($userName, $userInfoArray)) {
					return [];
				}
				return $userInfoArray[$userName];
			}

			function set_user_info($userInfo, $userInfoArray) {
				if ($userInfoArray == []) {
					return false;
				}
				$userItemsArray[$userNameSubmitted] = $userInfo;
				return true;
			}

			// Nom d'utilisateur et mots de passe fournis ?
			if (!isset($_POST['user_name']) || !isset($_POST['user_password']) || !isset($_POST['user_data'])) {
				echo "<p>Mot de passe, nom d'utilisateur ou données manquantes</p>";
				exit();
			}

			$userNameSubmitted = $_POST['user_name'];
			if (!is_valid_entry($userNameSubmitted, 16)) {
				echo "<p>Nom d'utilisateur invalide";
				exit();
			}

			$userPasswordSubmitted = $_POST['user_password'];
			if (!is_valid_entry($userPasswordSubmitted, 16)) {
				echo "<p>Mot de passe invalide</p>";
				exit();
			}

			$userInfoArray = load_user_info_array();
			$userInfo = get_user_info($userNameSubmitted, $userInfoArray);
			echo var_dump($userInfo);
			// Utilisateur existant, on vérifie le mot de passe
			if (!empty($userInfo) && !password_verify($userPasswordSubmitted, $userInfo["user_password"])) {
				echo "<p>Mauvais mot de passe fourni</p>";
				exit();
			
			} 

			// Utilisateur non existant, on crée l'item
			if (empty($userInfo)) {
				// On stock le hash du mot de passe fourni, attention le hash généré n'est pas le même
				// à chaque appel, donc il faut utiliser password_verify() le prochain coup.
				$userInfo = ["user_password" => password_hash($userPasswordSubmitted, PASSWORD_DEFAULT)];
			}
			
			// Données existantes => affiche
			if (array_key_exists("user_data",$userInfo))  {
				echo "<p>Salut " . $userNameSubmitted . ", tes données: " . $userInfo["user_data"];
			}

			// Données fournies => remplace
			$userSubmittedData = $_POST['user_data'];
			if (is_valid_entry($userSubmittedData, 16)) {
				$userInfo["user_data"] = $userSubmittedData;
				echo "<p>Les données suivantes ont été enregistrées: " . $userSubmittedData;
			}

			$userInfoArray[$userNameSubmitted] = $userInfo;
			save_user_info_array($userInfoArray);
			echo "<p>Données enregistrées</p>";
		?>
	</body>
</html>
