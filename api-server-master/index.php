<?php

require '../env.php';
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/FlightPdo.php';
require './pdos/AroundPdo.php';
require './pdos/UserPdo.php';
require './pdos/SchedulePdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
//    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
//    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);

    $r->addRoute('GET', '/city', ['IndexController', 'cityList']);


    $r->addRoute('GET', '/daily-one-flight', ['FlightController', 'dailyOneFlightsList']);

    $r->addRoute('GET', '/one-flight', ['FlightController', 'oneFlightsList']);

    $r->addRoute('GET', '/daily-round-flight', ['FlightController', 'dailyRoundFlightsList']);

    $r->addRoute('GET', '/round-flight', ['FlightController', 'roundFlightsList']);


    $r->addRoute('GET', '/flight', ['AroundController', 'aroundFlightsList']);

    $r->addRoute('GET', '/flight/sync', ['FlightController', 'synchronization']);

    $r->addRoute('GET', '/schedule', ['ScheduleController', 'scheduleList']);
    $r->addRoute('GET', '/schedule/{roomId}', ['ScheduleController', 'schedule']);
    $r->addRoute('POST', '/schedule', ['ScheduleController', 'scheduleAdd']);
    $r->addRoute('PATCH', '/schedule', ['ScheduleController', 'scheduleUpdate']);
    $r->addRoute('PATCH', '/schedule/item', ['ScheduleController', 'scheduleDelete']);
    $r->addRoute('DELETE', '/schedule/{roomId}', ['ScheduleController', 'scheduleDeleteAll']);


    $r->addRoute('GET', '/user/flight', ['UserController', 'isSaved']);
    $r->addRoute('POST', '/user', ['UserController', 'signUp']);
    $r->addRoute('DELETE', '/user', ['UserController', 'byeBye']);


    $r->addRoute('GET', '/auth/jwt', ['UserController', 'jwtAuth']);
    $r->addRoute('GET', '/auth/google', ['UserController', 'googleAuth']);
    $r->addRoute('POST', '/auth/email', ['UserController', 'emailAuth']);
    $r->addRoute('POST', '/auth', ['UserController', 'signIn']);




});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'FlightController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/FlightController.php';
                break;
            case 'AroundController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/AroundController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'ScheduleController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ScheduleController.php';
                break;
            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
