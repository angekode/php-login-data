<?php

require_once "RequestField.php";

class ClientRequest {

    public array $postFields = [];
    public array $getFields = [];
    public array $cookieFields = [];
    
    public function readFieldsFromClient(array $fieldNames, RequestFieldType $type) : void {
        foreach ($fieldNames as $fieldName) {

            if (!(gettype($fieldName) === "string")) {
                throw new IllegalArgumentException("ClientRequest::readFieldsFromClient(): $fieldNames doit contenir uniquement des string.");
            }

            $field = RequestField::readFromClient($fieldName, $type);

            switch ($type) {
                case RequestFieldType::Get:
                    array_push($this->getFields, $field);
                    break;
                case RequestFieldType::Post:
                    array_push($this->postFields, $field);
                    break;
                case RequestFieldType::Cookie:
                    array_push($this->cookieFields, $field);
                    break;
            }
        }
    }

    public function fieldExistsAndValid(string $fieldName, RequestFieldType $fieldType) : bool {
        $fieldsArray = [];
        switch ($fieldType) {
            case RequestFieldType::Get: 
                $fieldsArray = $this->getFields;
                break;
            case RequestFieldType::Post: 
                $fieldsArray = $this->postFields;
                break;
            case RequestFieldType::Cookie: 
                $fieldsArray = $this->cookieFields;
                break;
        }

        foreach ($fieldsArray as $field) {
            if ($field->name === $fieldName) {
                if ($field->status === RequestFieldStatus::SetAndValid) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    
    public function getField(string $fieldName, RequestFieldType $fieldType) : RequestField {
        $fieldsArray = [];
        switch ($fieldType) {
            case RequestFieldType::Get: 
                $fieldsArray = $this->getFields;
                break;
            case RequestFieldType::Post: 
                $fieldsArray = $this->postFields;
                break;
            case RequestFieldType::Cookie: 
                $fieldsArray = $this->cookieFields;
                break;
        }

        foreach ($fieldsArray as $field) {
            if ($field->name === $fieldName && $field->status === RequestFieldStatus::SetAndValid) {
                return $field;
            }
        }
        return new RequestField($fieldName, "", $fieldType, RequestFieldStatus::NotSet);
    }
}

?>