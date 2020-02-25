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

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}