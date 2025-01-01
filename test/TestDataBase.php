<?php

require "class/DataBase.php";

// DataBaseUserInfo
$user = new DataBaseUserInfo();
assert(empty($user->name));
assert(empty($user->password));
assert(empty($user->data));

$user = DataBaseUserInfo::create("nico","azerty","donnees");
assert($user->name === "nico");
assert($user->password === "azerty");
assert($user->data === "donnees");

$userB = new DataBaseUserInfo();
$userB->copyFromOther($user);
assert($userB->name === "nico");
assert($userB->password === "azerty");
assert($userB->data === "donnees");

$user->data = "data";
$userB->updateDataFromOther($user);
assert($userB->name === "nico");
assert($userB->password === "azerty");
assert($userB->data === "data");
$object = new stdClass();
$object->name = "patrick";
$object->password = "pass";
$object->data = "data";
$userC = DataBaseUserInfo::fromObject($object);
assert($userC->name === "patrick");
assert($userC->password === "pass");
assert($userC->data === "data");

// DataBase

if (file_exists("users_test.dat")) unlink("users_test.dat");
if (file_exists("cookies_test.dat")) unlink("cookies_test.dat");

$db = new DataBase("users_test.dat","cookies_test.dat");

// Aucune donnée
assert($db->userExists("") === false);
assert($db->userExists("dummy") === false);
$user = $db->getUserInfo("dummy");
assert($user->name === "");
assert($user->password === "");
assert($user->data === "");

assert($db->cookieExists("") === false);
assert($db->cookieExists("oijfiozj") === false);
assert(empty($db->getUserNameFromCookie("ijzfoiz")));

// 1 utilisateur
$user = DataBaseUserInfo::create("nico","password","4zf5zfe7");
$db->putUserInfo($user);
assert($db->userExists("nico") === true);
$user = $db->getUserInfo("nico");
assert($user->name === "nico");
assert($user->password === "password");
assert($user->data === "4zf5zfe7");

$db->putCookie("ax3b", "nico");
assert($db->getUserNameFromCookie("ax3b") === "nico");

// Sauvegarde & chargement

assert($db->saveToFiles() === true);

$newdb = new DataBase("users_test.dat","cookies_test.dat");
assert($newdb->userExists("nico") === false);
$user = $newdb->getUserInfo("nico");
assert($user->name === "");
assert($user->password === "");
assert($user->data === "");
assert($newdb->loadFromFiles() === true);
assert($newdb->userExists("nico") === true);
$user = $newdb->getUserInfo("nico");
assert($user->name === "nico");
assert($user->password === "password");
assert($user->data === "4zf5zfe7");

assert($newdb->getUserNameFromCookie("ax3b") === "nico");


echo "Test de DataBase.php réussi\n";

?>