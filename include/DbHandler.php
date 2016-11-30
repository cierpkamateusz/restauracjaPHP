<?php
 

class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . './DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    /**
     * Creating new user
     * @param String $imie User full name
     * @param String $email User login email id
     * @param String $haslo User login password
     */
    public function createUser($name, $email, $password) {
    	require_once 'PassHash.php';
    	$response = array();
    
    	// First check if user already existed in db
    	if (!$this->isUserExists($email)) {
    		// Generating password hash
    		$encryptedPassword = PassHash::hash($password);
    
    		// Generating API key
    		$api_key = $this->generateApiKey();
    
    		// insert query
    		$stmt = $this->conn->prepare("INSERT INTO uzytkownicy(imie, email, encryptedPassword, apiKey) values(?, ?, ?, ?)");
    		$stmt->bind_param("ssss", $name, $email, $encryptedPassword,$api_key);
    
    		$result = $stmt->execute();
    
    		$stmt->close();
    
    		// Check for successful insertion
    		if ($result) {
    			// User successfully inserted
    			return USER_CREATED_SUCCESSFULLY;
    		} else {
    			// Failed to create user
    			return USER_CREATE_FAILED;
    		}
    	} else {
    		// User with same email already existed in the db
    		return USER_ALREADY_EXISTED;
    	}
    
    	return $response;
    }
    
    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
    	// fetching user by email
    	$stmt = $this->conn->prepare("SELECT encryptedPassword FROM uzytkownicy WHERE email = ?");
    
    	$stmt->bind_param("s", $email);
    
    	$stmt->execute();
    
    	$stmt->bind_result($encryptedPassword);
    
    	$stmt->store_result();
    
    	if ($stmt->num_rows() > 0) {
    		// Found user with the email
    		// Now verify the password
    
    		$stmt->fetch();
    
    		$stmt->close();
    
    		if (PassHash::check_password($encryptedPassword, $password)) {
    			// User password is correct
    			return TRUE;
    		} else {
    			// user password is incorrect
    			return FALSE;
    		}
    	} else {
    		$stmt->close();
    
    		// user not existed with the email
    		return FALSE;
    	}
    }
    
    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
    	$stmt = $this->conn->prepare("SELECT idUzytkownicy from uzytkownicy WHERE email = ?");
    	$stmt->bind_param("s", $email);
    	$stmt->execute();
    	$stmt->store_result();
    	$num_rows = $stmt->num_rows;
    	$stmt->close();
    	return $num_rows > 0;
    }
    
    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
    	$stmt = $this->conn->prepare("SELECT idUzytkownicy, imie, email, apiKey FROM uzytkownicy WHERE email = ?");
    	$stmt->bind_param("s", $email);
    	if ($stmt->execute()) {
    		$user = $stmt->get_result()->fetch_assoc();
    		$stmt->close();
    		return $user;
    	} else {
    		return NULL;
    	}
    }
    
    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
    	$stmt = $this->conn->prepare("SELECT apiKey FROM uzytkownicy WHERE id = ?");
    	$stmt->bind_param("i", $user_id);
    	if ($stmt->execute()) {
    		$api_key = $stmt->get_result()->fetch_assoc();
    		$stmt->close();
    		return $api_key;
    	} else {
    		return NULL;
    	}
    }
    
    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($apiKey) {
    	$stmt = $this->conn->prepare("SELECT idUzytkownicy FROM uzytkownicy WHERE apiKey = ?");
    	$stmt->bind_param("s", $apiKey);
    	if ($stmt->execute()) {
    		$user_id = $stmt->get_result()->fetch_assoc();
    		$stmt->close();
    		return $user_id;
    	} else {
    		return NULL;
    	}
    }
    
    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $apiKey user api key
     * @return boolean
     */
    public function isValidApiKey($apiKey) {
    	$stmt = $this->conn->prepare("SELECT idUzytkownicy from uzytkownicy WHERE apiKey = ?");
    	$stmt->bind_param("s", $apiKey);
    	$stmt->execute();
    	$stmt->store_result();
    	$num_rows = $stmt->num_rows;
    	$stmt->close();
    	return $num_rows > 0;
    }
    
    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
    	return md5(uniqid(rand(), true));
    }
    
    /**
     * Fetching potrawy
     */
    public function getPotrawy() {
    	$stmt = $this->conn->prepare("
    			SELECT * FROM potrawy");
    	$stmt->execute();
    	$potrawy = $stmt->get_result();
    	$stmt->close();
    	return $potrawy;
    }
    /**
     * Fetching produkty
     */
    public function getProdukty() {
    	$stmt = $this->conn->prepare("
    			SELECT * FROM produkty");
    	$stmt->execute();
    	$produkty = $stmt->get_result();
    	$stmt->close();
    	return $produkty;
    }
    /**
     * Updating ilosc produktow
     * @param idProdukty
     */
    public function updateProdukt($idProdukt, $ilosc) {
    	$stmt = $this->conn->prepare("UPDATE produkty set ilosc = ? WHERE idProdukty = ?");
    	$stmt->bind_param("ii", $ilosc, $idProdukt);
    	$stmt->execute();
    	$num_affected_rows = $stmt->affected_rows;
    	$stmt->close();
    	return $num_affected_rows > 0;
    }
    /**
     * Fetching pracownicy
     */
    public function getPracownicy() {
    	$stmt = $this->conn->prepare("
    			SELECT idPracownicy, imie, nazwisko, numerTelefonu, pesel, stanowisko FROM pracownicy");
    	$stmt->execute();
    	$pracownicy = $stmt->get_result();
    	$stmt->close();
    	return $pracownicy;
    }
    /**
     * Fetching pracownik
     */
    public function getPracownik($idPracownicy) {
    	$stmt = $this->conn->prepare("
    			SELECT * FROM pracownicy WHERE idPracownicy = ?");
    	$stmt->bind_param("i", $idPracownicy);
    	if ($stmt->execute()) {
            $pracownik = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $pracownik;
        } else {
            return NULL;
        }
    }
    /**
     * Fetching dostawcy
     */
    public function getDostawcy() {
    	$stmt = $this->conn->prepare("
    			SELECT * FROM dostawcy");
    	$stmt->execute();
    	$dostawcy = $stmt->get_result();
    	$stmt->close();
    	return $dostawcy;
    }
    /**
     * Fetching dostawca
     */
    public function getDostawca($idDostawca) {
    	$stmt = $this->conn->prepare("
    			SELECT * FROM dostawcy WHERE idDostawcy = ?");
    	$stmt->bind_param("i", idDostawcy);
    	if ($stmt->execute()) {
    		$dostawca = $stmt->get_result()->fetch_assoc();
    		$stmt->close();
    		return $dostawca;
    	} else {
    		return NULL;
    	}
    }
    /**
     * creating zamowienia_produktow
     */
    public function createNewZamowienieProduktow($idDostawcy, $data) {
    	$stmt = $this->conn->prepare("
    			INSERT INTO zamowienia_produktow(data, idDostawcy) VALUES(?,?)");
    	$stmt->bind_param("si", $data, $idDostawcy);
    	$result = $stmt->execute();
    	$stmt->close();
    
    	if ($result) {
    		// plant created successfully
    		return $this->conn->insert_id;
    	}
    	else {
    		// plant failed to create
    		return NULL;
    	}
    }
    /**
     * creating zamowienie_potraw
     */
    public function createNewZamowienie($idPracownicy, $data) {
    	$stmt = $this->conn->prepare("
    			INSERT INTO zamowienia_potraw(data, idPracownicy) VALUES(?,?)");
    	$stmt->bind_param("si", $data, $idPracownicy);
    	$result = $stmt->execute();
    	$stmt->close();
    
    	if ($result) {
    		// plant created successfully
    		return $this->conn->insert_id;
    	}
    	else {
    		// plant failed to create
    		return NULL;
    	}
    }
    /**
     * adding potrawa to zamowienie
     */
    public function addPotrawyDoZamowienia($idZamowienia, $idPotrawy, $ilosc) {
    	$stmt = $this->conn->prepare("
    			INSERT INTO potrawy_w_zamowieniach(idZamowienia, idPotrawy, ilosc) VALUES(?,?,?)");
    	$stmt->bind_param("iii", $idZamowienia, $idPotrawy, $ilosc);
    	$result = $stmt->execute();
    	$stmt->close();
    
    	if ($result) {
    		// plant created successfully
    		return TRUE;
    	}
    	else {
    		// plant failed to create
    		return NULL;
    	}
    }
    /**
     * adding potrawa to zamowienie
     */
    public function addProduktyDoZamowienia($idZamowienia, $idProdukty, $ilosc) {
    	$stmt = $this->conn->prepare("
    			INSERT INTO produkty_w_zamowieniach(idZamowienia_produktow, idProdukty, ilosc) VALUES(?,?,?)");
    	$stmt->bind_param("iii", $idZamowienia, $idProdukty, $ilosc);
    	$result = $stmt->execute();
    	$stmt->close();
    
    	if ($result) {
    		// plant created successfully
    		return TRUE;
    	}
    	else {
    		// plant failed to create
    		return NULL;
    	}
    }
    /**
     * Fetching zamowienie
     */
    public function getZamowienie($idZamowienia) {
    	$stmt = $this->conn->prepare("
    			select zp.idZamowienia, zp.data, p.nazwa, pwz.ilosc, p.cena, pr.imie, pr.nazwisko, if(zp.status=1,'gotowe','niegotowe') as status
				from zamowienia_potraw zp
				join pracownicy pr
				on zp.idPracownicy=pr.idPracownicy
				join potrawy_w_zamowieniach pwz
				on zp.idZamowienia=pwz.idZamowienia
				join potrawy p
				on pwz.idPotrawy=p.idPotrawy
				where zp.idZamowienia=?");
    	$stmt->bind_param("i", $idZamowienia);
    	$stmt->execute();
    	$zamowienie = $stmt->get_result();
    	$stmt->close();
    	return $zamowienie;
    }
    /**
     * Fetching zamowienia
     */
    public function getZamowienia() {
    	$stmt = $this->conn->prepare("
    			select zp.idZamowienia, zp.data, p.nazwa, pwz.ilosc, p.cena, pr.imie, pr.nazwisko, if(zp.status=1,'gotowe','niegotowe') as status
				from zamowienia_potraw zp
				join pracownicy pr
				on zp.idPracownicy=pr.idPracownicy
				join potrawy_w_zamowieniach pwz
				on zp.idZamowienia=pwz.idZamowienia
				join potrawy p
				on pwz.idPotrawy=p.idPotrawy
				");
    	
    	$stmt->execute();
    	$zamowienie = $stmt->get_result();
    	$stmt->close();
    	return $zamowienie;
    }
    /**
     * Updating zamowienie status
     * @param idZamowienie
     */
    public function updateZamowienie($idZamowienie,$status) {
    	$stmt = $this->conn->prepare("UPDATE zamowienia_potraw set status = ? WHERE idZamowienia = ?");
    	$stmt->bind_param("ii", $status, $idZamowienie);
    	$stmt->execute();
    	$num_affected_rows = $stmt->affected_rows;
    	$stmt->close();
    	return $num_affected_rows > 0;
    }
    
}
 
?>