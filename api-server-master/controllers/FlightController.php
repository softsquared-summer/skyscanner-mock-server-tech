<?php
require 'function.php';
require 'flightFunction.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
const API_KEY = "yYI4BptcIG2hMFuopXy8iUQ%2B2rpXlcdG%2FLSnBPLCDtv%2BOCoT%2FpNYdGl9wpwasN7NcYwNCNtWHajjTZ6e4a8DGg%3D%3D";

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
        case "dailyOneFlightsList":
            http_response_code(200);

            $deAirPortCode = $_GET["deAirPortCode"];
            $arAirPortCode = $_GET["arAirPortCode"];
            $deDate = $_GET["deDate"];
            $seatCode = $_GET["seatCode"];


            $res->result = getDailyOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode);


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "일일 편도 항공편 리스트 조회 성공";
            echo json_encode($res);
            break;

        /*
         * API No. 3
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "oneFlightsList":
            http_response_code(200);

            $deAirPortCode = $_GET["deAirPortCode"];
            $arAirPortCode = $_GET["arAirPortCode"];
            $deDate = $_GET["deDate"];
            $seatCode = $_GET["seatCode"];
            $sortBy = $_GET["sortBy"];

            $res->result = getOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode,$sortBy);


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "편도 항공편 리스트 조회 성공";
            echo json_encode($res);
            break;


        /*
         * API No. 3
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "dailyRoundFlightsList":
            http_response_code(200);

            $deAirPortCode = $_GET["deAirPortCode"];
            $arAirPortCode = $_GET["arAirPortCode"];
            $deDate = $_GET["deDate"];
            $arDate = $_GET["arDate"];
            $seatCode = $_GET["seatCode"];


            $res->result = getDailyRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode);


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "일일 편도 항공편 리스트 조회 성공";
            echo json_encode($res);
            break;

        /*
         * API No. 3
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "roundFlightsList":
            http_response_code(200);

            $deAirPortCode = $_GET["deAirPortCode"];
            $arAirPortCode = $_GET["arAirPortCode"];
            $deDate = $_GET["deDate"];
            $arDate = $_GET["arDate"];
            $seatCode = $_GET["seatCode"];
            $sortBy = $_GET["sortBy"];

            $res->result = getRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode,$sortBy);


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "편도 항공편 리스트 조회 성공";
            echo json_encode($res);
            break;



        /*
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "synchronization":
            http_response_code(201);
            $airPortsList = getAirPortsList();
            $total = count($airPortsList);
            $date = "2020-02-12";

            //나중에 날짜도 추가해야함

            for($i=0;$i<$total;$i++){
                $airPortCode = $airPortsList[$i]["airPortCode"];
                $flightsList = getFlightsListAPI(API_KEY,$airPortCode);
                addFlightsList($flightsList,$date);
            }

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "항공권 정보 동기화 성공";
            echo json_encode($res);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}