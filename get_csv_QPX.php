<?php
if(!isset($_GET["origin"]) || !isset($_GET["destination"]) || !isset($_GET["departdate"]) || !isset($_GET["returndate"]) || !isset($_GET["cabin"])) {
  die("Please make sure you enter the proper search parameters. For example, http://localhost:8081/get_csv_QPX.php?origin=LAX&destination=BOS&departdate=2017-09-20&returndate=2017-09-25&cabin=COACH ");
}
require_once("json2csv.class.php");
$url = "https://www.googleapis.com/qpxExpress/v1/trips/search?key=AIzaSyCtDRlVi4DyyEsWVlfIVRyHcJw5RpaHXZ0";

$JSON2CSV = new JSON2CSVutil;

$postData = array(
    "request" => array(
        "passengers" => array(
            "adultCount" => 1
        ),
        "slice" => array(
            array(
                "origin" => $_GET["origin"],
                "destination" => $_GET["destination"],
                "date" => $_GET["departdate"],
                "preferredCabin" => $_GET["cabin"],
                // Allowed values are COACH, PREMIUM_COACH, BUSINESS, and FIRST.
                // "permittedCarrier" => array(
                //   "UA",
                //   "B6"

                // )
            ),
            array(
                "origin" => $_GET["destination"],
                "destination" => $_GET["origin"],
                "date" => $_GET["returndate"],//"2017-09-25"
                "preferredCabin" => $_GET["cabin"],

            )
        )
    )
);
$curlConnection = curl_init();

curl_setopt($curlConnection, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
curl_setopt($curlConnection, CURLOPT_URL, $url);
curl_setopt($curlConnection, CURLOPT_POST, TRUE);
curl_setopt($curlConnection, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($curlConnection, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, FALSE);
// ["prices"=>['200','price2'=>'300','price3'=>'400']];
// data["prices"]['price2'];
$results = curl_exec($curlConnection);
// echo $results;
$phpResults = json_decode($results); // json to php
$numberOfTripOptions = count($phpResults->trips->tripOption);
$dataPoints = array();
for($num = 0; $num < $numberOfTripOptions; $num++) {
  $tripOption = $phpResults->trips->tripOption[$num];
  $dataPoints[$num]['FlightOption'] = $num + 1;
  $dataPoints[$num]['SaleTotal'] = substr($tripOption->saleTotal, 3);
  $dataPoints[$num]['Destination'] = $_GET['destination'];
  $dataPoints[$num]['Origin'] = $_GET['origin'];
  $dataPoints[$num]['DepartDate'] = $_GET['departdate'];
  $dataPoints[$num]['ReturnDate'] = $_GET['returndate'];
  $dataPoints[$num]['Cabin'] = $_GET['cabin'];

  $slices = $phpResults->trips->tripOption[$num]->slice;
  $numberOfSlicesWithinTripOptions = count($slices);

  // $dataPoints[$num]['carrier'] = $tripOption->carrier;
  for($num1 = 0; $num1 < $numberOfSlicesWithinTripOptions-1;$num1++){
    $dataPoints[$num]['DepartDuration'] = $tripOption->slice[$num1]->duration;
    $dataPoints[$num]['ReturnDuration'] = $tripOption->slice[$num1+1]->duration;
  
  }


}
$output = json_encode($dataPoints);

if(isset($output)){
    $JSON2CSV->readJSON($output);
    $JSON2CSV->flattenDL("flightData.CSV", ",");
  }
  
// die(var_dump($dataPoints));
// // echo $tripData; (php) Object of class stdClass could not be converted to string
// // echo $dataToConvertToCsv;
// // json can be interpreted directly
// // data in the array need iterative loop to extract it out

// $tripPricing = $phpResults->trips->tripOption[0]->pricing;
// $taxes = $tripPricing[0]->tax;
// $tripOrigin = $tripPricing[0]->fare[0]->origin;
// // echo $tripOrigin;
// $tripDestination = $tripPricing[0]->fare[0]->destination;
// // echo $tripDestination;
// $tripCarrier = $tripPricing[0]->fare[0]->carrier;
// // echo $tripCarrier;
// $salePrice = '';
// foreach($taxes as $tax) {

//   if($tax->code == 'US') {
//     $salePrice = $tax->salePrice;
//     break;
//   }
// }

// echo $salePrice;
// echo '$_SERVER superglobal';
// var_dump($_SERVER);

// if(isset($dataToConvertToCsv)){
//     $JSON2CSV->readJSON($dataToConvertToCsv);
//     $JSON2CSV->flattenDL("QPX" . ".CSV");
//   }
// echo "The trip from Boston to LAX carries a US Government tax of $" . substr($salePrice, 3) . ".";
?>