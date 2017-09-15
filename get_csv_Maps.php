<?php

  if(!isset($_GET["address1"]) || !isset($_GET["address2"])) {
  die("Please make sure you enter the proper search parameters. Tip: Enter the zip code to ensure you find the correct address. For example, http://localhost:8081/get_csv_Maps.php?address=225 Santa Monica Blvd, Santa Monica, CA 90401 ");
  }

  require_once('json2csv.class.php');

  $JSON2CSV = new JSON2CSVutil;

  $data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($_GET["address1"]) .'&key=AIzaSyCtDRlVi4DyyEsWVlfIVRyHcJw5RpaHXZ0');
  // echo $data;
  $data2 = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($_GET["address2"]) .'&key=AIzaSyCtDRlVi4DyyEsWVlfIVRyHcJw5RpaHXZ0');

  $phpData = json_decode($data, true);
  $phpData2 = json_decode($data2, true);


  $dataPoints = array();
  // $phpData['result']['records'];

  $numOfFoundLocation = count($phpData['results']);

  for($num=0; $num < $numOfFoundLocation; $num++){
    
    $resultList=$phpData['results'][$num];

    $dataPoints[$num]['OriginAddress']=$resultList['formatted_address'];
    $dataPoints[$num]['Origin_Latitude']=$resultList['geometry']['location']['lat'];
    $dataPoints[$num]['Origin_Longtitude']=$resultList['geometry']['location']['lng'];


  }

  $numOfFoundLocation2 = count($phpData2['results']);

  for($num2=0; $num2 < $numOfFoundLocation2; $num2++){
    
    $resultList2=$phpData2['results'][$num2];

    $dataPoints[$numOfFoundLocation]['DestinationAddress']=$resultList2['formatted_address'];
    $dataPoints[$numOfFoundLocation]['Destination_Latitude']=$resultList2['geometry']['location']['lat'];
    $dataPoints[$numOfFoundLocation]['Destination_Longtitude']=$resultList2['geometry']['location']['lng'];
    $numOfFoundLocation++;
    // all num2 without ++


  }

// die(var_dump($dataPoints));

  $output = json_encode($dataPoints);
  
  if(isset($output)){
    $JSON2CSV->readJSON($output);
    $JSON2CSV->flattenDL("GeoLocation.CSV", ",");
  }

?>
