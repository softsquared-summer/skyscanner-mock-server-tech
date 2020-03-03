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
        case "signUp":

            http_response_code(200);

            $email = $req->email;
            $pw = $req->password;

            if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i",$email) == false)
            {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "이메일형식을 다시 확인해주세요";
            }
            $num = preg_match('/[0-9]/u', $pw);
            $eng = preg_match('/[a-z]/u', $pw);
            $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$pw);

            if(strlen($pw) < 8 || strlen($pw) > 20)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 8자리이상 20자리 이하로 입력해주세요";
            }
            if( $num == 0 || $eng == 0 || $spe == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 8자리이상 20자리 이하로 입력해주세요";
            }
            if(preg_match("/\s/u", $pw) == true)
            {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "비밀번호는 공백없이 입력해주세요";
            }
            $emailAuth = emailAuth($email);

            if($emailAuth){
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "이미 가입된 이메일입니다";
            }

            $result = signUp($email,$pw);

            if($result == 100){
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "회원가입 성공";
            }
            else{
                $res->isSuccess = FALSE;
                $res->code = 300;
                $res->message = "회원가입 실패";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "byeBye":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $userId = $jwtAuth["id"];

            byeBye($userId);

            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "회원탈퇴 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "signIn":

            http_response_code(200);

            $email = $req->email;
            $pw = $req->password;


            if(!isValidUser($email,$pw)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 이메일 혹은 비밀번호입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $jwt = getJWToken($email, $pw, JWT_SECRET_KEY);
            $res->result = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "토큰이 발행되었습니다";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "jwtAuth":

            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$result) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            http_response_code(200);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유효한 토큰입니다";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "emailAuth":

            http_response_code(200);

            $email = $req->email;

            $result = emailAuth($email);

            if($result){
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "해당 이메일이 존재합니다";
            }
            else{
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "해당 이메일이 존재하지 않습니다";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 19.04.25
         */
        case "isSaved":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $jwtAuth = isValidHeader($jwt, JWT_SECRET_KEY);

            if (!$jwtAuth) {
                $res->result = 0;
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "등록된 항공권정보가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            http_response_code(200);
            $userId = $jwtAuth["id"];
            $deFlightId = $_GET["deFlightId"];
            $reFlightId = $_GET["reFlightId"];

            $isSaved = scheduleItemAuth($userId,$deFlightId,$reFlightId);

            if($isSaved != 0){
                $res->result = 1;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "등록된 항공권 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $res->result = 0;
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "등록된 항공권정보가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            /*
             * API No. 1
             * API Name : JWT 생성 테스트 API (로그인)
             * 마지막 수정 날짜 : 19.04.25
             */
            case "googleAuth":


                $googleToken = $_GET["access_token"];

                $googleEmail = getEmailByGoogle($googleToken);

                if(!$googleEmail){
                  $res->isSuccess = FALSE;
                  $res->code = 200;
                  $res->message = "유효하지 않은 토큰입니다";
                  echo json_encode($res, JSON_NUMERIC_CHECK);
                  return;
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "유효한 토큰입니다";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
