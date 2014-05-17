<?php
/*
Alistair Judson's giffgaff api thing
alistair.p.judson@gmail.com
The MIT License (MIT)

Copyright (c) 2014 Alistair Judson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
	require_once('httplib.php');
	//Set the domain
	$domain = "giffgaff.com";
	$httplib = new httplib();
	//Make the url (https: true, to $domain, with the path "/auth/login") and request the page
	$response = $httplib->requestPage($httplib->makeURL(true, $domain, "/auth/login"));

	//Put the body of the document into a variable
	$document = $response["body"];
	
	//Create a DOM from it to enable fetching of embedded data
	$domDocument = new DOMDocument();
	
	//make it error tolerant
	$domDocument->recover = true;
	libxml_use_internal_errors(true);
	$domDocument->loadHTML($document);
	libxml_use_internal_errors(false);

	//get the login security token
	$login_security_token = $domDocument->getElementById("login_security_token")->getAttribute("value");
	
	//get the cookies from the last request
	$cookie = $httplib->getCookieString($response["header"]);
	$nickname = "";
	$password = "";
	if(isset($_REQUEST["nickname"]) || isset($_REQUEST["passoword"]))
	{
		$nickname = $_REQUEST["nickname"];
		$password = $_REQUEST["password"];
	}
	//fill out all of the data needed to log in, including the login security token
	$postData = array(
						"redirect" => "",
						"p_next_page" => "",
						"nickname" => $nickname,
						"password" => $password,
						"submit_button" => "Login",
						"login_security_token" => $login_security_token
					);
	//Make the url (https: true, to $domain, with the path "/auth/login") and request the page with
	//the cookies from the last request and the post data
	$loggedInResponse = $httplib->requestPage($httplib->makeURL(true, $domain, "/auth/login"), $postData, $cookie);
	if(array_key_exists("Set-Cookie", $loggedInResponse["header"]))
	{
		//Get the cookies from this request and use them to authenticate in the future
		$cookie = $httplib->getCookieString($loggedInResponse["header"]);

		//Make the url (https: true, to $domain, with the path "/dashboard") and request the page with
		//the cookies from the last request
		$dashboardResponse = $httplib->requestPage($httplib->makeURL(true, $domain, "/dashboard"), null, $cookie);
		
		//Create a DOM from it to enable fetching of embedded data
		$dashboardDomDocument = new DOMDocument();

		//Make it error tolerant
		$dashboardDomDocument->recover = true;
		libxml_use_internal_errors(true);
		$dashboardDomDocument->loadHTML($dashboardResponse["body"]);
		libxml_use_internal_errors(false);
		
		//Grab the balence, split it on the £ symbol to give just the number
		$balence = 0;
		$actualBalanceElement = @$dashboardDomDocument->getElementById("balance-value")->nodeValue;
		if($actualBalanceElement)
		{
			//$actualBalanceElement = $dashboardDomDocument->getElementById("balance-value")->nodeValue;
			$balanceElement = explode("£", $actualBalanceElement);
			$balence = trim($balanceElement[1]) * 1;
		}
		
		//put it in the accountData array
		$accountData = array("Balance" => $balence);
		

		//Grab the elements that store Goodybag Data
		$xpath = new DOMXPath($dashboardDomDocument);
		$results = $xpath->query("//p[@class='msisdn']");
		$phoneNumber = @$results->item(0)->nodeValue;
		if($phoneNumber)
		{
			$accountData["Phone Number"] = $phoneNumber;
		}
		else
		{
			$accountData["Phone Number"] = "No Number";
		}
		$results = $xpath->query("//span[@class='total']");
		$paybackPoints = @$results->item(0)->nodeValue;
		if($paybackPoints)
		{
			$accountData["Payback"]["Points"] = $paybackPoints * 1;
			$results = $xpath->query("//div[@class='summary big']//span");
			$paybackValue = explode("£", @$results->item(3)->nodeValue);
			$accountData["Payback"]["Value"] = $paybackValue[1] * 1;
		}

		$results = $xpath->query("//p[@class='big center']");

		//Find When the goodybag ends
		$currentGoodybag = array();

		//Put it in the currentGoodybag array and format it
		$endDate = @$results->item(0)->nodeValue;
		if($endDate)
		{
			$currentGoodybag["Dates"]["End"] = str_replace("Until", "", $endDate); 
			
			$classname = "big progressbar-label";
			$results = $xpath->query("//*[@class='" . $classname . "']");
			
			//Put the results in the Current Goodybag Array and format them
			$currentGoodybag["Minutes"]["Amount"] = str_replace(" minutes", "", $results->item(0)->nodeValue) * 1; 
			$currentGoodybag["Texts"]["Amount"] = str_replace(" texts", "", $results->item(1)->nodeValue); 
			$currentGoodybag["Data"]["Amount"] = $results->item(2)->nodeValue;
			
			$results = $xpath->query("//div[@class='bar']");
			
			$currentGoodybag["Minutes"]["Percent Free"] = str_replace("%", "", str_replace("width:", "", $results->item(0)->getAttribute("style"))) * 1;
			$currentGoodybag["Texts"]["Percent Free"]   = str_replace("%", "", str_replace("width:", "", $results->item(1)->getAttribute("style"))) * 1;
			$currentGoodybag["Data"]["Percent Free"]    = str_replace("%", "", str_replace("width:", "", $results->item(2)->getAttribute("style"))) * 1;
			$accountData["CurrentGoodybag"] = $currentGoodybag;

			$results = $xpath->query("//div[@class='goodybag-container']//p[@class='big']");
			$nextDateString = @$results->item(0)->nodeValue;
			if($nextDateString)
			{
				$nextGoodybagDates = explode(" until ", substr(trim($nextDateString),10));
				
				$nextGoodyBag["Dates"]["Start"] = $nextGoodybagDates[0];
				$nextGoodyBag["Dates"]["End"] = $nextGoodybagDates[1];


				$results = $xpath->query("//span[@class='big']");
				$nextGoodyBag["Minutes"]["Amount"] = str_replace(" minutes", "", $results->item(0)->nodeValue) * 1;
				$nextGoodyBag["Minutes"]["Percent Free"] = 100;  
				$nextGoodyBag["Texts"]["Amount"] = str_replace(" texts", "", $results->item(1)->nodeValue);
				$nextGoodyBag["Texts"]["Percent Free"] = 100;  
				$nextGoodyBag["Data"]["Amount"] = $results->item(2)->nodeValue;
				$nextGoodyBag["Data"]["Percent Free"] = 100;  

				//Put the current goodybag array in the account data aray
				$accountData["NextGoodybag"] = $nextGoodyBag;
			}
		}
		//Json encode and display
		if(!isset($_REQUEST["format"]))
		{
			print_r($accountData);
		}
		elseif($_REQUEST["format"] == "json")
		{
			header('Content-Type: application/json');
			echo json_encode($accountData);
		}
		elseif($_REQUEST["format"] == "xml")
		{
			// creating object of SimpleXMLElement
			$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><giffgaff_data></giffgaff_data>");

			// function call to convert array to xml
			$httplib->array_to_xml($accountData, $xml);
			echo $xml->asXML();
		}
		elseif($_REQUEST["format"] == "uwidget") 
		{
			$row1 = array("name"=>"Minutes","value"=>$accountData["CurrentGoodybag"]["Minutes"]["Amount"].":".$accountData["CurrentGoodybag"]["Minutes"]["Percent Free"] . "%");
			$row3 = array("name"=>"Texts","value"=>$accountData["CurrentGoodybag"]["Texts"]["Amount"].":".$accountData["CurrentGoodybag"]["Texts"]["Percent Free"] . "%");
			$row5 = array("name"=>"Data MB","value"=>str_replace(" MB", "", $accountData["CurrentGoodybag"]["Data"]["Amount"]).":".round($accountData["CurrentGoodybag"]["Data"]["Percent Free"], 1) . "%");
			$rows = array($row1, $row3, $row5);
			$data = array(
							"title" => "giffgaff",
							"type" => "list",
							"date" => $accountData["Phone Number"]." stats",
							"data" => $rows
						);
			header('Content-Type: application/json');
			echo json_encode($data);
		}
	}
	else
	{
		echo "Account Error";
	}
?>
