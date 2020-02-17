<?php


function getAirPortsList(){

    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM airPorts;";


    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return $res;
    
}

function getDailyFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode){

    $result = array();

    $pdo = pdoSqlConnect();

    $query = "SELECT count(*) AS count
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $totalCount = $res[0]["count"];

    $result["totalTicketCount"] = (int)$totalCount;

    if($totalCount == 0){
        return "티켓정보가 없습니다";
    }

    $query = "SELECT DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ORDER BY f.deDate ASC;";
    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $h=0;
    $m=0;
    for($w=0;$w<$totalCount;$w++){
        $h = $h + $res[$w]["hour"];
        $m = $m + $res[$w]["min"];
    }


    $avg = ($m +($h*60))/$totalCount;
    $avg = floor($avg);

    if($avg<60){
        $result["timeGapAvgMin"]=$avg."분";
    }
    else{
        $h=floor($avg/60);
        $m=$avg%60;
        $result["timeGapAvgMin"]=$h."시간 ".$m."분";
    }



    $query = "SELECT airLineKr,airLineEn, MIN(adultPrice) as price
                FROM
                (
                SELECT f.airLineKr,f.airLineEn,p.adultPrice
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ) as t
                GROUP BY airLineKr,airLineEn ORDER BY price ASC;";

    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $airLineList = $st->fetchAll();
    $airLineLegnth = count($airLineList);

    for($i=0; $i<$airLineLegnth; $i++){
        $temp = [];

        $airLineKr = $airLineList[$i]["airLineKr"];
        $airLineEn = $airLineList[$i]["airLineEn"];
        $minPrice = "₩".number_format($airLineList[$i]["price"]);

        $query = "SELECT f.id AS flightId, f.airPlaneCode, p.adultPrice AS priceGap, DATE_FORMAT(f.deDate,'%H:%i') AS deTime, DATE_FORMAT(f.arDate,'%H:%i') AS arTime
                    FROM flights AS f
                    JOIN prices AS p
                    ON f.id = p.flightId
                    WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ? AND f.airLineKr= ?
                    ORDER BY f.deDate ASC;";
        $st = $pdo->prepare($query);
        $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode,$airLineKr]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $timeList = $st->fetchAll();

        for($f=0;$f<count($timeList);$f++){

            $timeList[$f]["flightId"] = (int)$timeList[$f]["flightId"];

            if($timeList[$f]["priceGap"]-$airLineList[$i]["price"] == 0){

                $timeList[$f]["priceGap"] = "같은가격대";
            }
            else if($timeList[$f]["priceGap"]-$airLineList[$i]["price"] < 1000){

                $timeList[$f]["priceGap"] = "+".($timeList[$f]["priceGap"]-$airLineList[$i]["price"]);
            }
            else {

                $timeList[$f]["priceGap"] = "+".number_format($timeList[$f]["priceGap"]-$airLineList[$i]["price"]);
            }

        }

        $temp["airLineKr"] = $airLineKr;
        $temp["airLineEn"] = $airLineEn;
        $temp["minPrice"] = $minPrice;
        $temp["ticketList"] = $timeList;

        $result["airLineList"][]=$temp;
    }

    $st=null;
    $pdo = null;

    return $result;

}

function getOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode,$sortBy){

    $result = [];

    $pdo = pdoSqlConnect();

    $query = "SELECT count(*) AS count
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $totalCount = $res[0]["count"];

    $result["totalTicketCount"] = (int)$totalCount;
    if($sortBy == "price"){
        $result["sortBy"] = "가격";
    }
    else if($sortBy == "deTime"){
        $result["sortBy"] = "출국편 이륙 시간";
    }
    else if($sortBy == "arTime"){
        $result["sortBy"] = "출국편 착륙 시간";
    }
    else if($sortBy == "timeGap"){
        $result["sortBy"] = "총 비행 시간";
    }
    $result["type"] = "직항";

    $query = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,DATE_FORMAT(f.deDate,'%H:%i') AS deTime, DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS price, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
                DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode =? ";

    if($sortBy == "price"){
        $query = $query."ORDER BY price";
    }
    else if($sortBy == "deTime"){
        $query = $query."ORDER BY deTime";
    }
    else if($sortBy == "arTime"){
        $query = $query."ORDER BY arTime";
    }
    else if($sortBy == "timeGap"){
        $query = $query."ORDER BY timeGap";
    }

    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $temp = [];

    for($i=0;$i<count($res);$i++){

        $h = (int)$res[$i]["hour"];
        $m = (int)$res[$i]["min"];

        $temp["flightId"]=(int)$res[$i]["flightId"];
        $temp["airPlaneCode"]=$res[$i]["airPlaneCode"];
        $temp["airLineKr"]=$res[$i]["airLineKr"];
        $temp["airLineEn"]=$res[$i]["airLineEn"];
        $temp["deTime"]=$res[$i]["deTime"];
        $temp["arTime"]=$res[$i]["arTime"];
        $temp["price"]="₩".number_format($res[$i]["price"]);

        if($h>0){
            $temp["timeGap"]=$h."시간 ".$m."분";
        }
        else{
            $temp["timeGap"]=$m."분";
        }

        $result["ticketList"][] = $temp;
    }



    $st = null;
    $pdo = null;

    return $result;

}

function addFlightsList($flightsList,$date){

    $flightsList = $flightsList["item"];

    $priceArray = [[20000,15000,10000],[40000,35000,30000],[60000,55000,50000],[80000,75000,70000]];
    $seatNameArray = ["일반석","프리미엄 일반석","비즈니스석","일등석"];

    $pdo = pdoSqlConnect();

    $total = count($flightsList);



    for($i=0;$i<$total;$i++){

        $tempTime = $flightsList[$i]["std"];
        $deH = substr($tempTime,0,2);
        $deM = substr($tempTime,2,2);
        $deDate = $date." ".$deH.":".$deM.":00";

        $tempTime = $flightsList[$i]["etd"];
        $arrH = substr($tempTime,0,2);
        $arrM = substr($tempTime,2,2);
        $arDate = $date." ".$arrH.":".$arrM.":00";

        $airPlaneCode = $flightsList[$i]["airFln"];
        $airLineKr = $flightsList[$i]["airlineKorean"];
        $airLineEn = $flightsList[$i]["airlineEnglish"];
        $deAirPortCode = $flightsList[$i]["airport"];
        $arAirPortCode = $flightsList[$i]["city"];

        if($flightsList[$i]["rmkKor"] != "결항") {

            $query = "INSERT INTO flights (airPlaneCode,airLineKr,airLineEn,deAirPortCode,arAirPortCode,deDate,arDate) VALUES (?,?,?,?,?,?,?);";
            $st = $pdo->prepare($query);
            $st->execute([$airPlaneCode, $airLineKr, $airLineEn, $deAirPortCode, $arAirPortCode, $deDate, $arDate]);

            $query = "SELECT max(id) as maxId FROM flights;";

            $st = $pdo->prepare($query);
            $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
            $flighId = $res[0]["maxId"];

            for ($j = 0; $j < 4; $j++) {

                $seatName = $seatNameArray[$j];

                $temp = random_int(0, 25);
                $adultPrice = $priceArray[$j][0] + (200 * $temp);
                $infantPrice = $priceArray[$j][1] + (200 * $temp);
                $childPrice = $priceArray[$j][2] + (200 * $temp);

                $query = "INSERT INTO prices (flightId,seatCode,seatName,adultPrice,infantPrice,childPrice) VALUES (?,?,?,?,?,?);";
                $st = $pdo->prepare($query);
                $st->execute([$flighId, $j, $seatName, $adultPrice, $infantPrice, $childPrice]);

            }
        }

    }


    $st = null;
    $pdo = null;

}