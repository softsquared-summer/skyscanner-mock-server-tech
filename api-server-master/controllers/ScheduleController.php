<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "scheduleList":

            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            http_response_code(200);
            $userId = $jwtAuth["id"];
            $result = scheduleList($userId);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 300;
                $res->message = "여행일정 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "여행일정 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "schedule":

            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            http_response_code(200);
            $userId = $jwtAuth["id"];
            $roomId = $vars["roomId"];
            $result = schedule($userId,$roomId);

            if(count($result) == 0){
                $res->isSuccess = TRUE;
                $res->code = 300;
                $res->message = "여행일정 정보가 없습니다";
                echo json_encode($res);
                break;
            }

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "여행일정 세부조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "scheduleAdd":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            http_response_code(200);

            $userId = $jwtAuth["id"];
            $roomId = $req->roomId;
            $deFlightId = $req->deFlightId;
            $reFlightId = $req->reFlightId;
            $seatCode = $req->seatCode;
            $adultCount = $req->adultCount;
            $infantCount = $req->infantCount;
            $childCount = $req->childCount;

            if($deFlightId==null){
                $res->isSuccess = FALSE;
                $res->code = 500;
                $res->message = "필수적인 파라미터를 모두 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!$seatCode){
                $seatCode = 0;
            }
            if(!$adultCount){
                $adultCount = 1;
            }
            if(!$infantCount){
                $infantCount = 0;
            }
            if(!$childCount){
                $childCount = 0;
            }

            $isAdded = scheduleItemAuth($userId,$deFlightId,$reFlightId);

            if($isAdded != 0){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "이미등록된 항공권입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $result = scheduleAdd($userId,$roomId,$deFlightId,$reFlightId,$seatCode,$adultCount,$infantCount,$childCount);

            if($result){
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "여행일정 등록 성공";
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "여행일정 등록 실패";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "scheduleUpdate":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            http_response_code(200);

            $userId = $jwtAuth["id"];
            $roomId = $req->roomId;

            //validation gogo

            if(!scheduleRoomAuth($userId,$roomId)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지않은 방번호 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $title = $req->title;

            scheduleUpdate($userId,$roomId,$title);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "여행일정 제목수정 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "scheduleDelete":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            http_response_code(200);

            $userId = $jwtAuth["id"];
            $deFlightId = $req->deFlightId;
            $reFlightId = $req->reFlightId;

            //validation gogo

            if(!scheduleItemAuth($userId,$deFlightId,$reFlightId)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지 않은 항공권번호 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            scheduleDelete($userId,$deFlightId,$reFlightId);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "여행일정 개별 삭제 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "scheduleDeleteAll":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            http_response_code(200);

            $userId = $jwtAuth["id"];
            $roomId = $vars["roomId"];

            //validation gogo

            if(!scheduleRoomAuth($userId,$roomId)){
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "유효하지않은 방번호 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            scheduleDeleteAll($userId,$roomId);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "여행일정 전체삭제 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
