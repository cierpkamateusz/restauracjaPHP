<?php
 
require_once '../include/DbHandler.php';

require '.././libs/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();

/**
 * Listing potrawy
 * method GET
 * url /potrawy
 */
$app->get('/potrawy', function() {

	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getPotrawy();

	$response["error"] = false;
	$response["potrawy"] = array();

	// looping through result and preparing plants array
	while ($produkt = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idPotrawy"] = $produkt["idPotrawy"];
		$tmp["nazwa"] = $produkt["nazwa"];
		$tmp["cena"] = $produkt["cena"];
		array_push($response["potrawy"], $tmp);
	}

	echoRespnse(200, $response);
});
/**
 * Listing produkty
 * method GET
 * url /produkty
 */
$app->get('/produkty', function() {

	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getProdukty();

	$response["error"] = false;
	$response["produkty"] = array();

	// looping through result and preparing plants array
	while ($produkt = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idProdukty"] = $produkt["idProdukty"];
		$tmp["nazwa"] = $produkt["nazwa"];
		$tmp["ilosc"] = $produkt["ilosc"];
		array_push($response["produkty"], $tmp);
	}

	echoRespnse(200, $response);
});
	/**
	 * Updating produkty
	 * method PUT
	 * url /produkty/:id
	 * params - ilosc
	 */
	$app->put('/produkty/:id', function($idProdukt) use ($app) {
		verifyRequiredParams(array('ilosc'));
		$ilosc = $app->request->post('ilosc');
		
		
		
		$db = new DbHandler();
		$response = array();
		$result = $db->updateProdukt($idProdukt, $ilosc);
	
		if ($result) {
			// task updated successfully
			$response["error"] = false;
			$response["message"] = "produkt updated successfully $ilosc";
		} else {
			// task failed to update
			$response["error"] = true;
			$response["message"] = "produkt failed to update. Please try again! $ilosc";
		}
		echoRespnse(200, $response);
	});
/**
 * Listing pracownicy
 * method GET
 * url /pracownicy
 */
$app->get('/pracownicy', function() {
	
	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getPracownicy();

	$response["error"] = false;
	$response["pracownicy"] = array();

	// looping through result and preparing plants array
	while ($pracownik = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idPracownicy"] = $pracownik["idPracownicy"];
		$tmp["imie"] = $pracownik["imie"];
		$tmp["nazwisko"] = $pracownik["nazwisko"];
		$tmp["numerTelefonu"] = $pracownik["numerTelefonu"];
		$tmp["pesel"] = $pracownik["pesel"];
		$tmp["stanowisko"] = $pracownik["stanowisko"];
		array_push($response["pracownicy"], $tmp);
	}

	echoRespnse(200, $response);
});
/**
 * Listing single pracownik
 * method GET
 * url /pracownicy/:id
 */
$app->get('/pracownicy/:id', function($idPracownicy) {

	$response = array();
	$db = new DbHandler();

	// fetch task
	$result = $db->getPracownik($idPracownicy);

	if ($result != NULL) {
		$response["error"] = false;
		$response["idPracownicy"] = $result["idPracownicy"];
		$response["imie"] = $result["imie"];
		$response["nazwisko"] = $result["nazwisko"];
		$response["numerTelefonu"] = $result["numerTelefonu"];
		$response["pesel"] = $result["pesel"];
		$response["stanowisko"] = $result["stanowisko"];
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "The requested resource doesn't exists";
		echoRespnse(404, $response);
	}
});
/**
 * Listing dostawcy
 * method GET
 * url /dostawcy
 */
$app->get('/dostawcy', function() {

	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getDostawcy();

	$response["error"] = false;
	$response["dostawcy"] = array();

	// looping through result and preparing plants array
	while ($dostawca = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idDostawcy"] = $dostawca["idDostawcy"];
		$tmp["nazwa"] = $dostawca["nazwa"];
		$tmp["telefon"] = $dostawca["telefon"];
		$tmp["adres"] = $dostawca["adres"];
		$tmp["NIP"] = $dostawca["NIP"];
		$tmp["REGON"] = $dostawca["REGON"];
		array_push($response["dostawcy"], $tmp);
	}

	echoRespnse(200, $response);
});
/**
 * Listing single dostawca
 * method GET
 * url /dostawcy/:id
 */
$app->get('/dostawcy/:id', function($idDostawcy) {

	$response = array();
	$db = new DbHandler();

	// fetch task
	$result = $db->getDostawca($idDostawcy);

	if ($result != NULL) {
		$response["error"] = false;
		$response["idDostawcy"] = $result["idDostawcy"];
		$response["nazwa"] = $result["nazwa"];
		$response["telefon"] = $result["telefon"];
		$response["adres"] = $result["adres"];
		$response["NIP"] = $result["NIP"];
		$response["REGON"] = $result["REGON"];
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "The requested resource doesn't exists";
		echoRespnse(404, $response);
	}
});
 /**
 * Adding zamowienie_produktow
 * method POST
 * url /zamowienia
 * params data, idPracownicy, listaPotraw np. [{"idProdukty": 1,"ilosc": 2},{"idProdukty": 2,"ilosc": 3}]
 */
$app->post('/zamowienia_produktow', function() use ($app) {
	// check for required params
	verifyRequiredParams(array('data','idDostawcy','listaProduktow'));

	$response = array();

	$data = $app->request->post('data');
	$idDostawcy = $app->request->post('idDostawcy');
	$list = $app->request->post('listaProduktow');
	$listaProduktow = json_decode($list);


	$db = new DbHandler();


	$idZamowienia = $db->createNewZamowienieProduktow($idDostawcy, $data);

	if ($idZamowienia>0) {

		foreach ($listaProduktow as $produkt) {
			$tmp = array();
			$tmp["idProdukty"] = $produkt->idProdukty;
			$tmp["ilosc"] = $produkt->ilosc;
			$added = $db->addProduktyDoZamowienia($idZamowienia, $tmp["idProdukty"], $tmp["ilosc"]);
			if($added){
				$response["error"] = false;
				$response["message"] = "Zamowienie created successfully ";
			}
			else{
				$response["error"] = false;
				$response["message"] = "Failed to add potrawy do zamowienia $idZamowienia";
			}
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Failed to create zamowienie. Please try again";
	}
	echoRespnse(201, $response);
});

/**
 * Adding zamowienie_posilkow
 * method POST
 * url /zamowienia
 * params data, idPracownicy, listaPotraw czyli np [{"idPotrawy": 1,"ilosc": 2},{"idPotrawy": 2,"ilosc": 3}]
 */
$app->post('/zamowienia', function() use ($app) {
	// check for required params
	verifyRequiredParams(array('data','idPracownicy','listaPotraw'));

	$response = array();
	
	$data = $app->request->post('data');
	$idPracownicy = $app->request->post('idPracownicy');
	$list = $app->request->post('listaPotraw');
	$listaPotraw = json_decode($list);
	
	
	$db = new DbHandler();

	
	$idZamowienia = $db->createNewZamowienie($idPracownicy, $data);
	
	if ($idZamowienia>0) {
		
		foreach ($listaPotraw as $potrawa) {
			$tmp = array();
			$tmp["idPotrawy"] = $potrawa->idPotrawy;
			$tmp["ilosc"] = $potrawa->ilosc;
			$added = $db->addPotrawyDoZamowienia($idZamowienia, $tmp["idPotrawy"], $tmp["ilosc"]);
			if($added){
				$response["error"] = false;
				$response["message"] = "Zamowienie created successfully ";
			}
			else{
				$response["error"] = false;
				$response["message"] = "Failed to add potrawy do zamowienia";
			}
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Failed to create zamowienie. Please try again";
	}
	echoRespnse(201, $response);
});	
/**
 * Listing single zamowienie
 * method GET
 * url /zamowienia/:id
 */
$app->get('/zamowienia/:id', function($idZamowienie) {

	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getZamowienie($idZamowienie);
	$response["error"] = false;
	$response["zamowienie"] = array();

	// looping through result and preparing plants array
	while ($zamowienie = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idZamowienia"] = $zamowienie["idZamowienia"];
		$tmp["data"] = $zamowienie["data"];
		$tmp["nazwa"] = $zamowienie["nazwa"];
		$tmp["ilosc"] = $zamowienie["ilosc"];
		$tmp["cena"] = $zamowienie["cena"];
		$tmp["imie"] = $zamowienie["imie"];
		$tmp["nazwisko"] = $zamowienie["nazwisko"];
		$tmp["status"] = $zamowienie["status"];
		array_push($response["zamowienie"], $tmp);
	}

	echoRespnse(200, $response);
});
/**
 * Listing zamowienia
 * method GET
 * url /zamowienia
 */
$app->get('/zamowienia', function() {

	$response = array();
	$db = new DbHandler();

	// fetching all user tasks
	$result = $db->getZamowienia();
	$response["error"] = false;
	$response["zamowienie"] = array();

	// looping through result and preparing plants array
	while ($zamowienie = $result->fetch_assoc()) {
		$tmp = array();
		$tmp["idZamowienia"] = $zamowienie["idZamowienia"];
		$tmp["data"] = $zamowienie["data"];
		$tmp["nazwa"] = $zamowienie["nazwa"];
		$tmp["ilosc"] = $zamowienie["ilosc"];
		$tmp["cena"] = $zamowienie["cena"];
		$tmp["imie"] = $zamowienie["imie"];
		$tmp["nazwisko"] = $zamowienie["nazwisko"];
		$tmp["status"] = $zamowienie["status"];
		
		array_push($response["zamowienie"], $tmp);
	}

	echoRespnse(200, $response);
});
/**
 * Updating status of existing zamowienie
 * method PUT
 * params bool status (1-aktywne,0-zakonczone)
 * url - /zamowienia/:id
 */
$app->put('/zamowienia/:id', function($idZamowienia) use($app) {

	$status = $app->request->put('status');
	$db = new DbHandler();
	$response = array();


	$result = $db->updateZamowienie($idZamowienia,$status);
	if ($result) {

		$response["error"] = false;
		$response["message"] = "Status updated successfully";
	} else {

		$response["error"] = true;
		$response["message"] = "status failed to update. Please try again!";
	}
	echoRespnse(200, $response);
});
/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}
 

function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
    $app->accessControlAllowOrigin('*');
 
    echo json_encode($response);
}
 
$app->run();
?>