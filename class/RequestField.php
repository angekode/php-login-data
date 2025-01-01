<?php

enum RequestFieldType {
    case Get;
    case Post;
    case Cookie;
    case Null;
}

enum RequestFieldStatus {
    case NotSet;
    case SetNotValid;
    case SetAndValid;
    case Null;
}

class RequestField {
    public string $name = "";
    public string $value = "";
    public RequestFieldType $type = RequestFieldType::Null;
    public RequestFieldStatus $status = RequestFieldStatus::Null;

    function __construct(string $name, string $value, RequestFieldType $type, RequestFieldStatus $status) {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
        $this->status = $status;
    }

    public static function readFromClient(string $fieldName, RequestFieldType $fieldType) : RequestField {
        $fieldValue = "";

        switch ($fieldType) {
            case RequestFieldType::Get: 
                if (isset($_GET[$fieldName]) === false) {
                    return new RequestField($fieldName, "", $fieldType, RequestFieldStatus::NotSet);
                } else {
                    $fieldValue = $_GET[$fieldName];
                }
                break;

            case RequestFieldType::Post: 
                if (isset($_POST[$fieldName]) === false) {
                    return new RequestField($fieldName, "", $fieldType, RequestFieldStatus::NotSet);
                } else {
                    $fieldValue = $_POST[$fieldName];
                }
                break;
            
            case RequestFieldType::Cookie: 
                if (isset($_COOKIE[$fieldName]) === false) {
                    return new RequestField($fieldName, "", $fieldType, RequestFieldStatus::SetNotValid);
                } else {
                    $fieldValue = $_COOKIE[$fieldName];
                }
                break;
        }

        if (!RequestField::is_valid_entry($fieldValue,($fieldType === RequestFieldType::Cookie ? 32 : 16))) {
            return new RequestField($fieldName, "", $fieldType, RequestFieldStatus::SetNotValid);
        }
        return new RequestField($fieldName, $fieldValue, $fieldType, RequestFieldStatus::SetAndValid);
    }

    private static function is_valid_entry(string $entry, $maxLenght) : bool {
        $entryLength = strlen($entry);
        if ($entryLength === 0 || $entryLength > $maxLenght) {
            return false;
        }

        if (preg_match("/^[\w\s\-\_]+$/",$entry) == 0) {
            return false;
        }
        return true;
    }
}

?>