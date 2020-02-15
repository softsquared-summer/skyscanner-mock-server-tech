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

        $query = "INSERT INTO flights (airPlaneCode,airLineKr,airLineEn,deAirPortCode,arAirPortCode,deDate,arDate) VALUES (?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$airPlaneCode,$airLineKr,$airLineEn,$deAirPortCode,$arAirPortCode,$deDate,$arDate]);

        $query = "SELECT max(id) as maxId FROM flights;";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $flighId =  $res[0]["maxId"];

        for($j=0;$j<4;$j++){

            $seatName = $seatNameArray[$j];

            $temp = random_int(0,25);
            $adultPrice = $priceArray[$j][0]+(200*$temp);
            $infantPrice = $priceArray[$j][1]+(200*$temp);
            $childPrice = $priceArray[$j][2]+(200*$temp);

            $query = "INSERT INTO prices (flightId,seatCode,seatName,adultPrice,infantPrice,childPrice) VALUES (?,?,?,?,?,?);";
            $st = $pdo->prepare($query);
            $st->execute([$flighId, $j, $seatName, $adultPrice, $infantPrice, $childPrice]);

        }

    }


    $st = null;
    $pdo = null;

}