<?php

require "class/RequestField.php";

$_POST["input_name"] = "input_value";
$_POST["input_name_invalid"] = "input_value_invalid<>";

$r = RequestField::readFromClient("dummy", RequestFieldType::Post);
assert ($r->name == "dummy");
assert ($r->value == "");
assert ($r->type == RequestFieldType::Post);
assert ($r->status == RequestFieldStatus::NotSet);

$r = RequestField::readFromClient("input_name", RequestFieldType::Post);
assert ($r->name == "input_name");
assert ($r->value == "input_value");
assert ($r->type == RequestFieldType::Post);
assert ($r->status == RequestFieldStatus::SetAndValid);

$r = RequestField::readFromClient("input_name", RequestFieldType::Get);
assert ($r->name == "input_name");
assert ($r->value == "");
assert ($r->type == RequestFieldType::Get);
assert ($r->status == RequestFieldStatus::NotSet);

$r = RequestField::readFromClient("input_name_invalid", RequestFieldType::Post);
assert ($r->name == "input_name_invalid");
assert ($r->value == "");
assert ($r->type == RequestFieldType::Post);
assert ($r->status == RequestFieldStatus::SetNotValid);

echo "Test de RequestField réussi\n";

?>