<?php
require 'function.php';
require 'flightFunction.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        /*
         * API No. 3
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "aroundFlightsList":
            http_response_code(200);

            $case = $_GET["case"];
            $deAirPortCode = $_GET["deAirPortCode"];
            $deDate = $_GET["deDate"];
            $arDate = $_GET["arDate"];
            $country = $_GET["country"];

            if($deDate == null){
                //default
                $deDate="2020-02-12";
            }

            if($arDate == null){
                //default
                $arDate="2020-02-12";
            }


            if($case == "O"){
                $res->result = getAroundOneFlightsList($country,$deAirPortCode,$deDate);
                $message = "편도 최저가 조회 성공";
            }
            else if($case == "R"){
                $res->result = getAroundRoundFlightsList($country,$deAirPortCode,$deDate,$arDate);
                $message = "왕복 최저가 조회 성공";
            }


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = $message;
            echo json_encode($res);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}