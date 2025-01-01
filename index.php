<?php declare(strict_types=1); ?>
<html>
	<head>
		<title>Data App</title>
	</head>
	<body>

<?php

	require_once "class/DataBase.php";
	require_once "class/ClientRequest.php";
	require_once "class/RequestField.php";


	// 1) On récupère les données du client (ClientRequest),
	// les données enregistrées (DataBase), et on les stocke 
	// dans un objet (SessionInfo)

	// SessionInfo: contient toutes les données utiles à toutes les pages php
	class SessionInfo {
		public bool $isConnected = false;
		public DataBaseUserInfo $userInfo;
		public array $messages = [];
	}
	$sessionInfo = new SessionInfo();

	// ClientRequest: donne les champs remplis par le client, les boutons actionnés, les cookies
	$clientRequest = new ClientRequest();
	$clientRequest->readFieldsFromClient(
		[
			"header_input_name",
			"header_input_password",
			"header_button_login",
			"main_input_name",
			"main_input_password",
			"main_input_data",
			"main_button_validation",
		],
		RequestFieldType::Post
	);

	$clientRequest->readFieldsFromClient(
		["user_token"],
		RequestFieldType::Cookie
	);

	// DataBase: contient les données enregistrées des précédents appels
	$dataBase = new DataBase("usersinfo.json","cookies.json");
	$dataBase->loadFromFiles();

	// 2) On connecte le client s'il a le bon cookie

	// Est qu'il existe un cookie chez le client qui permet de le reconnaitre 
	// et donc de le connecter automatiquement ?
	if ($clientRequest->fieldExistsAndValid("user_token", RequestFieldType::Cookie)) {
		$cookieField = $clientRequest->getField("user_token", RequestFieldType::Cookie);
		$tokenValue = $cookieField->value;
		if ($dataBase->cookieExists($tokenValue)) {
			$userNameFromToken = $dataBase->getUserNameFromCookie($tokenValue);
			$userInfoSaved = $dataBase->getUserInfo($userNameFromToken);
			// Si le nom enregistré dans la base des cookies est également enregistré 
			// dans la base des users on le connecte
			if ($userInfoSaved->name === $userNameFromToken) {
				$sessionInfo->userInfo = $userInfoSaved;
				$sessionInfo->isConnected = true;
			}
		}
	}

	// 3) Action demandée par le client, on configure les données dans $sessionInfo en fonction

	// Login ?
	if ($clientRequest->fieldExistsAndValid("header_button_login", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("header_input_name", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("header_input_password", RequestFieldType::Post)
	) {
		$userNameSubmitted = $clientRequest->getField("header_input_name", RequestFieldType::Post);
		$userPasswordSubmitted = $clientRequest->getField("header_input_password", RequestFieldType::Post);
		// on a déjà eu cet utilisateur, on vérifie le mot du passe et on le connecte
		if ($dataBase->userExists($userNameSubmitted->value)) {
			echo "1";
			$serverSideUserInfo = $dataBase->getUserInfo($userNameSubmitted->value);
			if (password_verify($userPasswordSubmitted->value, $serverSideUserInfo->password)) {
				$sessionInfo->userInfo = $serverSideUserInfo;
				$sessionInfo->isConnected = true;
			}
		} else {
			echo $userPasswordSubmitted->value;
			$newUserInfo = DataBaseUserInfo::create($userNameSubmitted->value, password_hash($userPasswordSubmitted->value,PASSWORD_DEFAULT),"");
			$dataBase->putUserInfo($newUserInfo);
			$token = $dataBase->generate_user_token();
			$dataBase->putCookie($token, $userNameSubmitted->value);
			$sessionInfo->userInfo = $newUserInfo;
			$sessionInfo->isConnected = true;
			setcookie("user_token",$token);
			$dataBase->saveToFiles();
		}

	// Envoie de données demandé par le client
	} else if ($clientRequest->fieldExistsAndValid("main_button_validation", RequestFieldType::Post)) {

		// L'utilisateur a bien remplie les champs nom et mot de passe ?
		if ($clientRequest->fieldExistsAndValid("main_input_name", RequestFieldType::Post)
			&& $clientRequest->fieldExistsAndValid("main_input_password", RequestFieldType::Post)
			&& $clientRequest->fieldExistsAndValid("main_input_data", RequestFieldType::Post)
		) {
			$userNameSubmitted = $clientRequest->getField("main_input_name", RequestField::Post);
			$userPasswordSubmitted = $clientRequest->getField("main_input_password", RequestField::Post);
			$userDataSubmitted = $clientRequest->getField("main_input_data", RequestField::Post);

			// on a déjà eu cet utilisateur, on vérifie le mot du passe, mais on ne le connecte pas
			if ($dataBase->userExists($userNameSubmitted)) {
				$serverSideUserInfo = $dataBase->getUserInfo($userNameSubmitted);
				if (password_verify($userPasswordSubmitted, $serverSideUserInfo->password)) {
					$sessionInfo->userInfo = $serverSideUserInfo;
				}
			// on n'a jamais eu cet utilisateur on l'enregistre
			} else {
				$userInfo = new DataBaseUserInfo($userNameSubmitted, password_hash($userPasswordSubmitted, DEFAULT_ALGO), $userDataSubmitted);	
				$dataBase->putUserInfo($userInfo);
			}
		}

	// 1er chargement de page
	} else {

	}


	// 4) On affiche les pages en fonction des données contenues dans $sessionInfo
	
	require "view/header.php"; // nécessite: $isConnected, $userName
	require "view/main.php"; // nécessite: $isConnected, $userData
	require "view/footer.php"; // nécessite $messages

?>

	</body>
</html>