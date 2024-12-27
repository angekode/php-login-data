<html>
	<head>
		<title>Test php</title>
	</head>
	<body>
		<p>Hello</p>
		<form action="index.php">
			<label for="user_name">Nom</label>
			<input type="text" name="user_name"/>

			<label for="user_password">Mot de passe</label>
			<input type="text" name="user_password"/>

			<label for="user_data">Données</label>
			<input type="text" name="user_data"/>

			<input type="submit" value="Envoyer"/>
		</form>
		<?php

			// Nom d'utilisateur et mots de passe fournis ?
			if (!isset($_GET['user_name']) or !isset($_GET['user_password'])) {
				echo "<p>Mot de passe ou nom d'utilisateur manquant</p>";
				exit();
			}

			$userNameSubmitted = $_GET['user_name'];
			$userPasswordSubmitted = $_GET['user_password'];
			$loadedJson = file_get_contents("names.txt");
			$userItemsArray = json_decode($loadedJson,true);
			
			$userItem = [];
			// Utilisateur existant, on récupère l'item
			if ($userItemsArray != null and array_key_exists($userNameSubmitted,$userItemsArray)) {
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
			if (isset($_GET['user_data'])) {
				$userSubmittedData = $_GET['user_data'];
				if (strcmp($userSubmittedData,"") != 0) {
					$userItem["user_data"] = $userSubmittedData;
					echo "<p>Les données suivantes ont été enregistrées: " . $userSubmittedData;
				}
			}

			$userItemsArray[$userNameSubmitted] = $userItem;
			$jsonDataToSave = json_encode($userItemsArray);
			file_put_contents("names.txt",$jsonDataToSave);
			echo "<p>Données enregistrées</p>";
		?>
	</body>
</html>
