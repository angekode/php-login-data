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

	/*
		ClientRequest : les données formulaire et cookie envoyées par le client
		SessionInfo : les données qui servent à configurer les vues
		DataBase : les données à sauvegarder sur le serveur

		1) ClientRequest => on récupère des données qu'on stocke dans SessionInfo
		2) SessionInfo => Interprétation des données: 
			2a. Connexion si l'utilisateur a le bon cookie
			2b. Trie en fonction de la demande du client (login, envoie de données, demande de données)
		3) SessionInfo => Affichage des vues html paramétrées par SessionInfo

	*/


	// 1) ClientRequest => on récupère des données qu'on stocke dans SessionInfo
	// -------------------------------------------------------------------------

	// SessionInfo: contient toutes les données utiles à toutes les pages php
	class SessionInfo {
		public bool $isConnected = false;
		public DataBaseUserInfo $userInfo;
		public array $messages = [];
	}
	$sessionInfo = new SessionInfo();
	$sessionInfo->userInfo = new DataBaseUserInfo();

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

	// 2) SessionInfo => Interprétation des données: 
	// -------------------------------------------------------------------------
	// 2a. Connexion si l'utilisateur a le bon cookie
	// -------------------------------------------------------------------------
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

	// 2b. Trie en fonction de la demande du client (login, envoie de données, demande de données)
	// -------------------------------------------------------------------------
	// Login
	if ($clientRequest->fieldExistsAndValid("header_button_login", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("header_input_name", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("header_input_password", RequestFieldType::Post)
	) {
		$userNameSubmitted = $clientRequest->getField("header_input_name", RequestFieldType::Post);
		$userPasswordSubmitted = $clientRequest->getField("header_input_password", RequestFieldType::Post);

		// Utilisateur existant
		if ($dataBase->userExists($userNameSubmitted->value)) {
			$serverSideUserInfo = $dataBase->getUserInfo($userNameSubmitted->value);
			if (password_verify($userPasswordSubmitted->value, $serverSideUserInfo->password)) {
				$sessionInfo->userInfo = $serverSideUserInfo;
				$sessionInfo->isConnected = true;
			}

		// Nouvel utilisateur
		} else {
			// nouvel utilisateur dans la base
			$newUserInfo = DataBaseUserInfo::create($userNameSubmitted->value, password_hash($userPasswordSubmitted->value,PASSWORD_DEFAULT),"");
			$dataBase->putUserInfo($newUserInfo);
			$sessionInfo->userInfo = $newUserInfo;
			$sessionInfo->isConnected = true;
		}

		// Création d'un cookie pour garder la connexion aux prochaines chargements 
		if ($sessionInfo->isConnected) {
			$token = $dataBase->generate_user_token();
			$dataBase->putCookie($token, $userNameSubmitted->value);
			setcookie("user_token",$token);
			$dataBase->saveToFiles();
		}

	// Envoie de données demandé par le client
	} else if (
		$clientRequest->fieldExistsAndValid("main_button_validation", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("main_input_data", RequestFieldType::Post)
	) {
		$userDataSubmitted = $clientRequest->getField("main_input_data", RequestFieldType::Post);

		// Utilisateur dejà connecté => on enregistre seulement "data" dans SessionInfo
		if ($sessionInfo->isConnected) {
			$sessionInfo->userInfo->data = $userDataSubmitted->value;
			
		// Utilisateur non connecté, les champs nom d'utilisateur et mot de passe doivent être remplis
		} else if (
			$clientRequest->fieldExistsAndValid("main_input_name", RequestFieldType::Post)
			&& $clientRequest->fieldExistsAndValid("main_input_password", RequestFieldType::Post)
		) {

			$userNameSubmitted = $clientRequest->getField("main_input_name", RequestFieldType::Post);
			$userPasswordSubmitted = $clientRequest->getField("main_input_password", RequestFieldType::Post);

			// on a déjà eu cet utilisateur, on vérifie le mot du passe, mais on ne le connecte pas
			if ($dataBase->userExists($userNameSubmitted->value)) {
				$serverSideUserInfo = $dataBase->getUserInfo($userNameSubmitted->value);
				if (password_verify($userPasswordSubmitted->value, $serverSideUserInfo->password)) {
					$userInfo = $serverSideUserInfo;
					$userInfo->data = $userDataSubmitted->value;
					$dataBase->putUserInfo($userInfo);
					$sessionInfo->userInfo = $userInfo;
				}
			// on n'a jamais eu cet utilisateur on l'enregistre
			} else {
				$sessionInfo->userInfo = DataBaseUserInfo::create($userNameSubmitted->value, password_hash($userPasswordSubmitted->value, PASSWORD_DEFAULT), $userDataSubmitted->value);	
			}
		}
		// Utilisateur et data enregistrés dans session, mais là on enregistre sur le disque
		$dataBase->putUserInfo($sessionInfo->userInfo);
		$dataBase->saveToFiles();

	// Demande de données de la part du client (champ main_input_data vide)
	} else if ($clientRequest->fieldExistsAndValid("main_button_validation", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("main_input_name", RequestFieldType::Post)
		&& $clientRequest->fieldExistsAndValid("main_input_password", RequestFieldType::Post)
	) {

		$userNameSubmitted = $clientRequest->getField("main_input_name", RequestFieldType::Post);
		$userPasswordSubmitted = $clientRequest->getField("main_input_password", RequestFieldType::Post);

		if ($dataBase->userExists($userNameSubmitted->value)) {
			$serverSideUserInfo = $dataBase->getUserInfo($userNameSubmitted->value);
			if (password_verify($userPasswordSubmitted->value, $serverSideUserInfo->password)) {
				$sessionInfo->userInfo = $serverSideUserInfo;
			}
		}

	// 1er chargement de page
	} else {

	}

	// 3) SessionInfo => Affichage des vues html paramétrées par SessionInfo
	// -------------------------------------------------------------------------
	require "view/header.php"; // nécessite: $isConnected, $userName
	require "view/main.php"; // nécessite: $isConnected, $userData
	require "view/footer.php"; // nécessite $messages

?>

	</body>
</html>