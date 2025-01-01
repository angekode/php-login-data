<?php

class DataBaseUserInfo {
    public string $name = "";
    public string $password = "";
    public string $data = "";

    public static function create(string $name, string $password, string $data) : DataBaseUserInfo {
        echo "hi1";
        $newObject = new self();
        echo "hi1";
        $newObject->name = $name;
        echo "hi1";
        $newObject->password = $password;
        echo "hi1";
        $newObject->data = $data;
        echo "hi1";
        return $newObject;
    }

    public function updateDataFromOther(DataBaseUserInfo $other) : bool {
        if ($this->name != $other->name) {
            return false;
        }
        $this->data = $other->data;
        return true;
    }

    public function copyFromOther(DataBaseUserInfo $other) {
        $this->name = $other->name;
        $this->password = $other->password;
        $this->data = $other->data;
    }

    public static function fromObject(stdClass $object) : DataBaseUserInfo {
        $newObject = new self();
        $properties = get_object_vars($newObject);
        foreach($properties as $key => $value) {
            if (!property_exists($object, $key)) {
                return new self();
            }
            $newObject->$key = $object->$key;
        }
        return $newObject;
    }
}

class DataBaseCookieInfo {
    public string $name = "";
    public string $value = "";

    function __construct(string $name, string $value) {
        $this->$name = $name;
        $this->$value = $value;
    }
}

class DataBase {

    private string $usersFileName = "";
    private string $cookiesFileName = "";
    private array $usersInfoArray = [];
    private array $cookiesArray = [];
    private bool $usersFileLoaded = false;
    private bool $cookiesFileLoaded = false;

    function __construct($usersFilename, $cookiesFilename) {
        $this->usersFileName = $usersFilename;
        $this->cookiesFileName = $cookiesFilename;
    }

    public function loadFromFiles() : bool {
        if (!$this->usersFileLoaded) {
            $this->usersInfoArray = $this->load_user_info_array($this->usersFileName);

            if (empty($this->usersInfoArray)) {
                $this->usersFileLoaded = false;
            } else {
                $this->usersFileLoaded = true;
            }
        }
        if (!$this->cookiesFileLoaded) {
            $this->cookiesArray = $this->load_cookies_array($this->cookiesFileName);
            if (empty($this->cookiesArray)) {
                $this->cookiesFileLoaded = false;
            } else {
                $this->cookiesFileLoaded = true;
            }
        }

        return $this->usersFileLoaded && $this->cookiesFileLoaded;
    }

    public function saveToFiles() : bool {
        $this->save_user_info_array($this->usersInfoArray, $this->usersFileName);
        $this->save_cookies_array($this->cookiesArray, $this->cookiesFileName);
        return true;
    }

    // DataBaseUserInfo

    public function userExists(string $userName) : bool {

        foreach($this->usersInfoArray as $userInfo) {
            if ($userInfo->name === $userName) {
                return true;
            }
        }
        return false;
    }

    public function getUserInfo(string $userName) : DataBaseUserInfo {

        if ($this->userExists($userName)) {
            foreach($this->usersInfoArray as $userInfo) {
                if ($userInfo->name === $userName) {
                    return $userInfo;
                }
            }
        } 
        return new DataBaseUserInfo("", "", "");
    }

    public function putUserInfo(DataBaseUserInfo $userInfoToAdd) : bool {
        if ($this->userExists($userInfoToAdd->name)) {
            $oldUserInfo = $this->usersInfo[$userInfoToAdd->name];
            $oldUserInfo->updateFromOther($userInfoToAdd);
            return true;

        } else {
            $userInfo = new DataBaseUserInfo();
            $userInfo->copyFromOther($userInfoToAdd);
            array_push($this->usersInfoArray, $userInfo);
            return true;
        }
        return true;
    }

    private function load_user_info_array(string $fileName) : array {

        if (!file_exists($fileName)) {
            return [];
        }
        $loadedJson = file_get_contents($fileName);
        if ($loadedJson === false) {
            return [];
        }
        $loadedArray = json_decode($loadedJson, false);
        if ($loadedArray === null) {
            return [];
        }
        $newUsersArray = [];
        foreach($loadedArray as $object) {
            array_push($newUsersArray, DataBaseUserInfo::fromObject($object));
        }
    
        return $newUsersArray;
    }

    private function save_user_info_array(array $array, string $filename) : bool {
        $jsonDataToSave = json_encode($array);
        if (file_put_contents($filename, $jsonDataToSave) === false) {
            return false;
        } else {
            return true;
        }
    }


    // DataBaseCookieInfo

    public function cookieExists(string $token) : bool {

        return array_key_exists($token, $this->cookiesArray);
    }

    public function putCookie(string $token, string $userName) {

        $this->cookiesArray[$token] = $userName;
    }

    public function getUserNameFromCookie(string $token) : string {

        if ($this->cookieExists($token)) {
            return $this->cookiesArray[$token];
        } else {
            return "";
        }
    }

    private function load_cookies_array(string $fileName) : array {
        if (!file_exists($fileName)) {
            return [];
        }
        $loadedJson = file_get_contents($fileName);
        if ($loadedJson === false) {
            return [];
        }
        $userInfoArray = json_decode($loadedJson, true);
        if ($userInfoArray === null) {
            return [];
        }
    
        return $userInfoArray;
    }

    private function save_cookies_array(array $cookiesArray, string $fileName) : bool {
        $jsonString = json_encode($cookiesArray);
        if (file_put_contents($fileName, $jsonString) === false) {
            return false;
        } else {
            return true;
        }
    }

    public function generate_user_token() : string {
        return bin2hex(random_bytes(16));
    }

}

?>