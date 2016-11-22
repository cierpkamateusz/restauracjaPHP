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
    			SELECT * FROM pracownicy");
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
    			select zp.idZamowienia, zp.data, p.nazwa, pwz.ilosc, p.cena, pr.imie, pr.nazwisko, zp.status
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
    			select zp.idZamowienia, zp.data, p.nazwa, pwz.ilosc, p.cena, pr.imie, pr.nazwisko, zp.status
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