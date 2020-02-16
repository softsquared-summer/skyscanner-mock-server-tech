<?php


function getFlightsListAPI($key,$startAirCode){

    $url = "http://openapi.airport.co.kr/service/rest/FlightStatusList/getFlightStatusList?ServiceKey={$key}&schLineType=D&schIOType=O&schAirCode={$startAirCode}&numOfRows=100000";
    $ch = cURL_init();

    cURL_setopt($ch, CURLOPT_URL, $url);
    cURL_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = cURL_exec($ch);
    cURL_close($ch);

    $object = simplexml_load_string($response);
    $json = json_encode($object->body->items);
    $array = json_decode($json,TRUE);

    return $array;
}
