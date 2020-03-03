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

            if($seatCode<0 || $seatCode>3){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "좌석코드값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            $result = getDailyOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 300;
                $res->message = "항공권 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;


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

            if($seatCode<0 || $seatCode>3){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "좌석코드값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            if($sortBy != "price" && $sortBy != "timeGap" && $sortBy != "deTime" && $sortBy != "arTime"){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "정렬값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            $result = getOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode,$sortBy);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 400;
                $res->message = "항공권 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;


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

            if($seatCode<0 || $seatCode>3){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "좌석코드값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            if(strtotime($deDate)>strtotime($arDate)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "출국날짜와 귀국날짜가 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            $result = getDailyRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 400;
                $res->message = "항공권 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;


            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "일일 왕복 항공편 리스트 조회 성공";
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

            if($seatCode<0 || $seatCode>3){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "좌석코드값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }
            if($sortBy != "price" && $sortBy != "timeGap" && $sortBy != "deTime" && $sortBy != "arTime" && $sortBy != "reDeTime" && $sortBy != "reArTime"){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "정렬값이 올바르지 않습니다";
                echo json_encode($res);
                break;
            }
            if(strtotime($deDate)>strtotime($arDate)){
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "출국날짜와 귀국날짜가 올바르지 않습니다";
                echo json_encode($res);
                break;
            }

            $result = getRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode,$sortBy);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 500;
                $res->message = "항공권 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "왕복 항공편 리스트 조회 성공";
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

            //나중에 날짜도 추가해야함

//            for($j=26;$j<=31;$j++){
//
//                if($j<10){
//                    $date="2020-03-0".$j;
//                }
//                else{
//                    $date="2020-03-".$j;
//                }
//
//                for($i=0;$i<$total;$i++){
//                    $airPortCode = $airPortsList[$i]["airPortCode"];
//                    $flightsList = getFlightsListAPI(API_KEY,$airPortCode);
//                    addFlightsList($flightsList,$date);
//                }
//
//            }

            $date="2020-02-12";

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
