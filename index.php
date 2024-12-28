

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

			function save_user_info_array($serverSideUsersInfoArray) {
				$jsonDataToSave = json_encode($serverSideUsersInfoArray);
				if (file_put_contents("usersinfos.json", $jsonDataToSave) === false) {
					return false;
				} else {
					return true;
				}
			}

			function load_user_info_array() {
				$loadedJson = file_get_contents("usersinfos.json");
				if ($loadedJson === false) {
					return [];
				}
				$serverSideUsersInfoArray = json_decode($loadedJson, true);
				if ($serverSideUsersInfoArray === null) {
					return [];
				}

				return $serverSideUsersInfoArray;
			}

			function get_user_info($userName, $serverSideUsersInfoArray) {
				if (empty($serverSideUsersInfoArray) || !array_key_exists($userName, $serverSideUsersInfoArray)) {
					return [];
				}
				return $serverSideUsersInfoArray[$userName];
			}

			function set_user_info($userName, $userInfo, $usersInfoArray) {
				if ($userInfo == []) {
					return false;
				}
				$usersInfoArray[$userName] = $userInfo;
				return true;
			}

			function save_users_tokens_array($tokensArray) {
				$jsonString = json_encode($tokensArray);
				if (file_put_contents("userstokens.json",$jsonString) === false) {
					return false;
				} else {
					return true;
				}
			}

			function load_users_tokens_array() {
				$loadedJson = file_get_contents("userstokens.json");
				if ($loadedJson === false) {
					return false;
				}
				$serverSideTokensArray = json_decode($loadedJson, true);
				if ($serverSideTokensArray === null) {
					return [];
				}

				return $serverSideTokensArray;
			}

			function generate_user_token() {
				return bin2hex(random_bytes(16));
			}


			$messagesToDisplay = [];
			
			try {
				$clientSideToken = "";
				$serverSideTokensArray = [];
				
				if (isset($_COOKIE["user_token"])) {
					$clientSideToken = $_COOKIE["user_token"];
					$serverSideTokensArray = load_users_tokens_array();
					if (!empty($serverSideTokensArray) && array_key_exists($clientSideToken, $serverSideTokensArray)) {
						$serverSideUserName = $serverSideTokensArray[$clientSideToken];
						array_push($messagesToDisplay, "Re Bonjour " . $serverSideUserName);
					} else {
						// token de session non valide
						$clientSideToken = "";
					}
				}

				// Nom d'utilisateur et mots de passe fournis ?
				if (!isset($_POST['user_name']) || !isset($_POST['user_password']) || !isset($_POST['user_data'])) {
					array_push($messagesToDisplay, "Mot de passe, nom d'utilisateur ou données manquantes");
					throw new Exception();
				}
	
				$submittedUserName = $_POST['user_name'];
				if (!is_valid_entry($submittedUserName, 16)) {
					array_push($messagesToDisplay, "Nom d'utilisateur invalide");
					throw new Exception();
				}
	
				$submittedPassword = $_POST['user_password'];
				if (!is_valid_entry($submittedPassword, 16)) {
					array_push($messagesToDisplay, "Mot de passe invalide");
					throw new Exception();
				}
	
				$serverSideUsersInfoArray = load_user_info_array();
				$serverSideUsersInfoArray = get_user_info($submittedUserName, $serverSideUsersInfoArray);
				
				// Utilisateur existant, on vérifie le mot de passe
				if (!empty($serverSideUsersInfoArray) && !password_verify($submittedPassword, $serverSideUsersInfoArray["user_password"])) {
					array_push($messagesToDisplay, "Mauvais mot de passe fourni");
					throw new Exception();
				} 
	
				// Utilisateur non existant, on crée l'item
				if (empty($serverSideUsersInfoArray)) {
					// On stock le hash du mot de passe fourni, attention le hash généré n'est pas le même
					// à chaque appel, donc il faut utiliser password_verify() le prochain coup.
					$serverSideUsersInfoArray = ["user_password" => password_hash($submittedPassword, PASSWORD_DEFAULT)];
				}
				
				// Données existantes => affiche
				if (array_key_exists("user_data",$serverSideUsersInfoArray))  {
					array_push($messagesToDisplay, "<p>Salut " . $submittedUserName . ", tes données: " . $serverSideUsersInfoArray["user_data"]);
				}
	
				// Données fournies => remplace
				$submittedUserData = $_POST['user_data'];
				if (is_valid_entry($submittedUserData, 16)) {
					$serverSideUsersInfoArray["user_data"] = $submittedUserData;
					array_push($messagesToDisplay, "Les données suivantes ont été enregistrées: " . $submittedUserData);
				}
	
				$serverSideUsersInfoArray[$submittedUserName] = $serverSideUsersInfoArray;
				save_user_info_array($serverSideUsersInfoArray);
				array_push($messagesToDisplay, "<p>Données enregistrées");
	
				if (empty($clientSideToken)) {
					$clientSideToken = generate_user_token();
					$serverSideTokensArray = load_users_tokens_array();
					$serverSideTokensArray[$clientSideToken] = $submittedUserName;
					if (save_users_tokens_array($serverSideTokensArray) === false) {
						throw new Exception();
					}
					setcookie("user_token",$clientSideToken,time()+3600);
					array_push($messagesToDisplay, "Nouveau cookie");
				}

			} catch(Exception $e) {
				error_log($e->getMessage());
			}
		?>

		<?php foreach($messagesToDisplay as $message) : ?>
			<p><?= $message ?></p>
		<?php endforeach; ?>
	</body>
</html>
