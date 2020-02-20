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

function getDailyOneFlightsList($deAirPortCode,$arAirPortCode,$deDate,$seatCode){

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
        $result["timeGapAvg"]=$avg."분";
    }
    else{
        $h=floor($avg/60);
        $m=$avg%60;
        $result["timeGapAvg"]=$h."시간 ".$m."분";
    }



    $query = "SELECT airLineKr,airLineEn,airLineImg, MIN(adultPrice) as price
                FROM
                (
                SELECT f.airLineKr,f.airLineEn,f.airLineImg,p.adultPrice
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ) as t
                GROUP BY airLineKr,airLineEn,airLineImg ORDER BY price ASC, airLineKr ASC;";

    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $airLineList = $st->fetchAll();
    $airLineLegnth = count($airLineList);

    for($i=0; $i<$airLineLegnth; $i++){
        $temp = [];

        $airLineKr = $airLineList[$i]["airLineKr"];
        $airLineEn = $airLineList[$i]["airLineEn"];
        $airLineImg = $airLineList[$i]["airLineImg"];
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
        $temp["airLineImgUrl"] = $airLineImg;
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

    $query = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,f.airLineImg,DATE_FORMAT(f.deDate,'%H:%i') AS deTime,
                DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS price, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
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
        $temp["airLineImgUrl"]=$res[$i]["airLineImg"];
        $temp["type"]="직항";;
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

function getDailyRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode){

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
    $deTotalCount = $res[0]["count"];

    $query = "SELECT count(*) AS count
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $arTotalCount = $res[0]["count"];

    $result["totalTicketCount"] = (int)($deTotalCount+$arTotalCount);

    if($deTotalCount+$arTotalCount == 0){
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

    $deHour=0;
    $deMin=0;
    for($w=0;$w<$deTotalCount;$w++){
        $deHour = $deHour + $res[$w]["hour"];
        $deMin = $deMin + $res[$w]["min"];
    }

    $query = "SELECT DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ORDER BY f.deDate ASC;";
    $st = $pdo->prepare($query);
    $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $arHour=0;
    $arMin=0;
    for($w=0;$w<$arTotalCount;$w++){
        $arHour = $arHour + $res[$w]["hour"];
        $arMin = $arMin + $res[$w]["min"];
    }

    $h = $deHour+$arHour;
    $m = $deMin+$arMin;

    $avg = ($m +($h*60))/($deTotalCount+$arTotalCount);
    $avg = floor($avg);

    if($avg<60){
        $result["timeGapAvg"]=$avg."분";
    }
    else{
        $h=floor($avg/60);
        $m=$avg%60;
        $result["timeGapAvg"]=$h."시간 ".$m."분";
    }



    $query = "SELECT de.airLineKr, de.airLineEn,de.airLineImg, (de.price+ar.price) as totalPrice, de.price AS dePrice, ar.price AS arPrice
                
                FROM
                
                (
                SELECT airLineKr, airLineEn,airLineImg, MIN(adultPrice) as price
                FROM
                (
                SELECT f.airLineKr, f.airLineEn,f.airLineImg ,p.adultPrice
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ) as t
                GROUP BY airLineKr, airLineEn,airLineImg ORDER BY price ASC
                ) AS de
                
                JOIN
                
                (
                SELECT airLineKr, airLineEn,airLineImg, MIN(adultPrice) as price
                FROM
                (
                SELECT f.airLineKr, f.airLineEn,f.airLineImg, p.adultPrice
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?
                ) as t
                GROUP BY airLineKr, airLineEn,airLineImg ORDER BY price ASC
                ) AS ar
                
                ON de.airLineKr = ar.airLineKr
                ORDER BY totalPrice ASC, airLineKr ASC;";

    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode,$arAirPortCode,$deAirPortCode,$arDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $airLineList = $st->fetchAll();
    $airLineLegnth = count($airLineList);

    for($i=0; $i<$airLineLegnth; $i++){
        $temp = [];

        $airLineKr = $airLineList[$i]["airLineKr"];
        $airLineEn = $airLineList[$i]["airLineEn"];
        $airLineImg = $airLineList[$i]["airLineImg"];
        $minPrice = "₩".number_format($airLineList[$i]["totalPrice"]);
        $minDePrice = $airLineList[$i]["dePrice"];
        $minArPrice = $airLineList[$i]["arPrice"];

        $query = "SELECT f.id AS flightId, f.airPlaneCode, p.adultPrice AS priceGap, DATE_FORMAT(f.deDate,'%H:%i') AS deTime, DATE_FORMAT(f.arDate,'%H:%i') AS arTime
                    FROM flights AS f
                    JOIN prices AS p
                    ON f.id = p.flightId
                    WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ? AND f.airLineKr= ?
                    ORDER BY f.deDate ASC;";
        $st = $pdo->prepare($query);
        $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode,$airLineKr]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $deTimeList = $st->fetchAll();

        for($f=0;$f<count($deTimeList);$f++){

            $deTimeList[$f]["flightId"] = (int)$deTimeList[$f]["flightId"];

            if($deTimeList[$f]["priceGap"]-$minDePrice == 0){

                $deTimeList[$f]["priceGap"] = "같은가격대";
            }
            else if($deTimeList[$f]["priceGap"]-$minDePrice < 1000){

                $deTimeList[$f]["priceGap"] = "+".($deTimeList[$f]["priceGap"]-$minDePrice);
            }
            else {

                $deTimeList[$f]["priceGap"] = "+".number_format($deTimeList[$f]["priceGap"]-$minDePrice);
            }

        }

        $query = "SELECT f.id AS flightId, f.airPlaneCode, p.adultPrice AS priceGap, DATE_FORMAT(f.deDate,'%H:%i') AS deTime, DATE_FORMAT(f.arDate,'%H:%i') AS arTime
                    FROM flights AS f
                    JOIN prices AS p
                    ON f.id = p.flightId
                    WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ? AND f.airLineKr= ?
                    ORDER BY f.deDate ASC;";
        $st = $pdo->prepare($query);
        $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$seatCode,$airLineKr]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $arTimeList = $st->fetchAll();

        for($f=0;$f<count($arTimeList);$f++){

            $arTimeList[$f]["flightId"] = (int)$arTimeList[$f]["flightId"];

            if($arTimeList[$f]["priceGap"]-$minArPrice == 0){

                $arTimeList[$f]["priceGap"] = "같은가격대";
            }
            else if($arTimeList[$f]["priceGap"]-$minArPrice < 1000){

                $arTimeList[$f]["priceGap"] = "+".($arTimeList[$f]["priceGap"]-$minArPrice);
            }
            else {

                $arTimeList[$f]["priceGap"] = "+".number_format($arTimeList[$f]["priceGap"]-$minArPrice);
            }

        }

        $temp["airLineKr"] = $airLineKr;
        $temp["airLineEn"] = $airLineEn;
        $temp["airLineImgUrl"] = $airLineImg;
        $temp["minPrice"] = $minPrice;
        $temp["deTicketList"] = $deTimeList;
        $temp["reTicketList"] = $arTimeList;

        $result["airLineList"][]=$temp;
    }

    $st=null;
    $pdo = null;

    return $result;

}


function getRoundFlightsList($deAirPortCode,$arAirPortCode,$deDate,$arDate,$seatCode,$sortBy){

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

    $deTotalCount = $res[0]["count"];

    $query = "SELECT count(*) AS count
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $arTotalCount = $res[0]["count"];

    $result["totalTicketCount"] = (int)($deTotalCount+$arTotalCount);

    if($sortBy == "price"){
        $result["sortBy"] = "가격";
    }
    else if($sortBy == "deTime"){
        $result["sortBy"] = "출국편 이륙 시간";
    }
    else if($sortBy == "arTime"){
        $result["sortBy"] = "출국편 착륙 시간";
    }
    else if($sortBy == "reDeTime"){
        $result["sortBy"] = "귀국편 이륙 시간";
    }
    else if($sortBy == "reArTime"){
        $result["sortBy"] = "귀국편 착륙 시간";
    }
    else if($sortBy == "timeGap"){
        $result["sortBy"] = "총 비행 시간";
    }

    $query = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,f.airLineImg,DATE_FORMAT(f.deDate,'%H:%i') AS deTime,
                DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS price, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
                DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode =? ";

    $reQuery = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,f.airLineImg,DATE_FORMAT(f.deDate,'%H:%i') AS deTime,
                DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS price, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
                DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.deAirPortCode = ? AND f.arAirPortCode = ? AND DATE(f.deDate) = ? AND p.seatCode =? ";

    if($sortBy == "price"){
        $query = $query."ORDER BY price";
        $reQuery = $reQuery."ORDER BY price";
    }
    else if($sortBy == "deTime"){
        $query = $query."ORDER BY deTime";
        $reQuery = $reQuery."ORDER BY price";
    }
    else if($sortBy == "arTime"){
        $query = $query."ORDER BY arTime";
        $reQuery = $reQuery."ORDER BY price";
    }
    else if($sortBy == "reDeTime"){
        $query = $query."ORDER BY price";
        $reQuery = $reQuery."ORDER BY deTime";
    }
    else if($sortBy == "reArTime"){
        $query = $query."ORDER BY price";
        $reQuery = $reQuery."ORDER BY arTime";
    }
    else if($sortBy == "timeGap"){
        $query = $query."ORDER BY timeGap";
        $reQuery = $reQuery."ORDER BY timeGap";
    }

    $st = $pdo->prepare($query);
    $st->execute([$deAirPortCode,$arAirPortCode,$deDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = $pdo->prepare($reQuery);
    $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$seatCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $reRes = $st->fetchAll();

    $temp = [];
    $reTemp = [];

    if(count($res)== 0 || count($reRes)== 0){
        return "항공편이 존재하지 않습니다";
    }

    if(count($res)>count($reRes)){
        $count = count($reRes);
    }
    else{
        $count = count($res);
    }

    for($i=0;$i<$count;$i++){

        $h = (int)$res[$i]["hour"];
        $m = (int)$res[$i]["min"];

        $temp["flightId"]=(int)$res[$i]["flightId"];
        $temp["airPlaneCode"]=$res[$i]["airPlaneCode"];
        $temp["airLineKr"]=$res[$i]["airLineKr"];
        $temp["airLineEn"]=$res[$i]["airLineEn"];
        $temp["airLineImg"]=$res[$i]["airLineImg"];
        $temp["type"]="직항";;
        $temp["deTime"]=$res[$i]["deTime"];
        $temp["arTime"]=$res[$i]["arTime"];
        $temp["price"]="₩".number_format($res[$i]["price"]);

        if($h>0){
            $temp["timeGap"]=$h."시간 ".$m."분";
        }
        else{
            $temp["timeGap"]=$m."분";
        }

        $h = (int)$reRes[$i]["hour"];
        $m = (int)$reRes[$i]["min"];

        $reTemp["flightId"]=(int)$reRes[$i]["flightId"];
        $reTemp["airPlaneCode"]=$reRes[$i]["airPlaneCode"];
        $reTemp["airLineKr"]=$reRes[$i]["airLineKr"];
        $reTemp["airLineEn"]=$reRes[$i]["airLineEn"];
        $reTemp["airLineImg"]=$reRes[$i]["airLineImg"];
        $reTemp["type"]="직항";;
        $reTemp["deTime"]=$reRes[$i]["deTime"];
        $reTemp["arTime"]=$reRes[$i]["arTime"];
        $reTemp["price"]="₩".number_format($reRes[$i]["price"]);

        if($h>0){
            $reTemp["timeGap"]=$h."시간 ".$m."분";
        }
        else{
            $reTemp["timeGap"]=$m."분";
        }

        $teemp = [];
        $teemp["totalPrice"] = "₩".number_format($res[$i]["price"]+$reRes[$i]["price"]);
        $teemp["deTicket"] = $temp;
        $teemp["reTicket"] = $reTemp;

        $result["ticketList"][]=$teemp;
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
        $airLineImg="";
        if($airLineKr == "대한항공"){
            $airLineImg = "http://kt999.site/airLineImg/korea.png";
        }
        else if($airLineKr == "아시아나항공"){
            $airLineImg = "http://kt999.site/airLineImg/asiana.jpg";
        }
        else if($airLineKr == "이스타항공"){
            $airLineImg = "http://kt999.site/airLineImg/eastar.png";
        }
        else if($airLineKr == "제주항공"){
            $airLineImg = "http://kt999.site/airLineImg/jeju.jpg";
        }
        else if($airLineKr == "에어부산"){
            $airLineImg = "http://kt999.site/airLineImg/airbusan.jpg";
        }
        else if($airLineKr == "에어서울"){
            $airLineImg = "http://kt999.site/airLineImg/airseoul.jpg";
        }
        else if($airLineKr == "티웨이항공"){
            $airLineImg = "http://kt999.site/airLineImg/tway.png";
        }
        else if($airLineKr == "진에어"){
            $airLineImg = "http://kt999.site/airLineImg/jinair.jpg";
        }
        else if($airLineKr == "플라이강원"){
            $airLineImg = "http://kt999.site/airLineImg/flygangwon.jpg";
        }
        else if($airLineKr == "하이에어"){
            $airLineImg = "http://kt999.site/airLineImg/hiair.png";
        }

        $deAirPortCode = $flightsList[$i]["airport"];
        $arAirPortCode = $flightsList[$i]["city"];

        if($flightsList[$i]["rmkKor"] != "결항") {

            $query = "INSERT INTO flights (airPlaneCode,airLineKr,airLineEn,airLineImg,deAirPortCode,arAirPortCode,deDate,arDate) VALUES (?,?,?,?,?,?,?,?);";
            $st = $pdo->prepare($query);
            $st->execute([$airPlaneCode, $airLineKr, $airLineEn,$airLineImg, $deAirPortCode, $arAirPortCode, $deDate, $arDate]);

            $query = "SELECT max(id) as maxId FROM flights;";

            $st = $pdo->prepare($query);
            $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
            $flightId = $res[0]["maxId"];

            for ($j = 0; $j < 4; $j++) {

                $seatName = $seatNameArray[$j];

                $temp = random_int(0, 25);
                $adultPrice = $priceArray[$j][0] + (200 * $temp);
                $infantPrice = $priceArray[$j][1] + (200 * $temp);
                $childPrice = $priceArray[$j][2] + (200 * $temp);

                $query = "INSERT INTO prices (flightId,seatCode,seatName,adultPrice,infantPrice,childPrice) VALUES (?,?,?,?,?,?);";
                $st = $pdo->prepare($query);
                $st->execute([$flightId, $j, $seatName, $adultPrice, $infantPrice, $childPrice]);

            }
        }

    }


    $st = null;
    $pdo = null;

}