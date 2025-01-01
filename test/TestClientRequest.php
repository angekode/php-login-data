<?php

require "class/ClientRequest.php";

$cr = new ClientRequest();

assert(empty($cr->postFields));
assert(empty($cr->getFields));
assert(empty($cr->cookieFields));

$cr->readFieldsFromClient(["dummy1"], RequestFieldType::Get);
$cr->readFieldsFromClient(["dummy1"], RequestFieldType::Post);
$cr->readFieldsFromClient(["dummy1"], RequestFieldType::Cookie);


$f = $cr->getField("dummy1", RequestFieldType::Post);
assert($f->name === "dummy1");
assert($f->value === "");
assert($f->type === RequestFieldType::Post);
assert($f->status === RequestFieldStatus::NotSet);

$f = $cr->getField("dummy1", RequestFieldType::Get);
assert($f->name === "dummy1");
assert($f->value === "");
assert($f->type === RequestFieldType::Get);
assert($f->status === RequestFieldStatus::NotSet);

$f = $cr->getField("dummy1", RequestFieldType::Cookie);
assert($f->name === "dummy1");
assert($f->value === "");
assert($f->type === RequestFieldType::Cookie);
assert($f->status === RequestFieldStatus::NotSet);

$_GET["getvar"] = "getvalue";
$_POST["postvar"] = "postvalue";
$_COOKIE["cookievar"] = "cookievalue";


$cr->readFieldsFromClient(["getvar"], RequestFieldType::Get);
$cr->readFieldsFromClient(["postvar"], RequestFieldType::Post);
$cr->readFieldsFromClient(["cookievar"], RequestFieldType::Cookie);

$f = $cr->getField("getvar", RequestFieldType::Get);
assert($f->name === "getvar");
assert($f->value === "getvalue");
assert($f->type === RequestFieldType::Get);
assert($f->status === RequestFieldStatus::SetAndValid);

$f = $cr->getField("postvar", RequestFieldType::Post);
assert($f->name === "postvar");
assert($f->value === "postvalue");
assert($f->type === RequestFieldType::Post);
assert($f->status === RequestFieldStatus::SetAndValid);

$f = $cr->getField("cookievar", RequestFieldType::Cookie);
assert($f->name === "cookievar");
assert($f->value === "cookievalue");
assert($f->type === RequestFieldType::Cookie);
assert($f->status === RequestFieldStatus::SetAndValid);

echo "Test de ClientRequest réussi\n";
?>