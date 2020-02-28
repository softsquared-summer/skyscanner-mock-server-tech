<?php


function getAroundOneFlightsList($country,$deAirPortCode,$deDate){

    $pdo = pdoSqlConnect();
    $result = [];

    if(!$country){
        $query = "select DISTINCT country FROM airPorts;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $countryList = $st->fetchAll();

        $countryCount = count($countryList);

        for($i=0;$i<$countryCount;$i++){
            $query = "SELECT f.country,p.adultPrice AS minPrice FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.country from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND DATE(deDate) = ? AND country = ? ORDER BY adultPrice ASC LIMIT 1;";

            $st = $pdo->prepare($query);
            $st->execute([$deAirPortCode,$deDate,$countryList[$i]["country"]]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll()[0];

            if(count($res)==0){
                return $res;
            }

            if($res["country"]=="대한민국"){
                $res["imgUrl"]="http://kt999.site/img/korea.jpg";
            }
            else{
                $res["imgUrl"]=null;
            }

            $res["minPrice"] = (int)$res["minPrice"];

            $result[] = $res;
        }

        foreach ((array) $result as $key => $value) {
            $sort[$key] = $value['minPrice'];
        }
        array_multisort($sort, SORT_ASC, $result);
        return $result;
    }
    else{

        $result = [];

        $query = "SELECT DISTINCT airPortCode FROM airPorts WHERE country = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$country]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $cityList = $st->fetchAll();

        $ciryCount = count($cityList);

        for($i=0;$i<$ciryCount;$i++){

            $temp = [];
            $query = "SELECT f.cityNameKr,f.imgUrl,DATE(f.deDate) AS deDate,p.adultPrice FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.cityNameKr,p.imgUrl from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND arAirPortCode = ? AND DATE(deDate) = ? ORDER BY adultPrice ASC LIMIT 1;";

            $st = $pdo->prepare($query);
            $st->execute([$deAirPortCode,$cityList[$i]["airPortCode"],$deDate,]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();

            if(count($res) != 0){
                $temp["cityNameKr"] = $res[0]["cityNameKr"];
                $temp["imgUrl"] = $res[0]["imgUrl"];
                $temp["deDate"] = $res[0]["deDate"];
                $temp["minPrice"] = (int)$res[0]["adultPrice"];
                $temp["type"]="직항";
                $result[] = $temp;
            }


        }

        foreach ((array) $result as $key => $value) {
            $sort[$key] = $value['minPrice'];
        }
        array_multisort($sort, SORT_ASC, $result);

        return $result;
    }
}

function getAroundRoundFlightsList($country,$deAirPortCode,$deDate,$arDate){

    $pdo = pdoSqlConnect();
    $result = [];

    if(!$country){
        $query = "select DISTINCT country FROM airPorts;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $countryList = $st->fetchAll();

        $countryCount = count($countryList);

        for($i=0;$i<$countryCount;$i++){
            $temp = [];

            $query = "SELECT f.country,f.arAirPortCode,p.adultPrice AS minPrice FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.country from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND DATE(deDate) = ? AND country = ? ORDER BY adultPrice,arAirPortCode ASC LIMIT 1;";

            $st = $pdo->prepare($query);
            $st->execute([$deAirPortCode,$deDate,$countryList[$i]["country"]]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll()[0];
            $deMinPrice = (int)$res["minPrice"];
            $arAirPortCode = $res["arAirPortCode"];
            $query = "SELECT f.country,f.arAirPortCode,p.adultPrice AS minPrice FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.country from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND arAirPortCode = ? AND DATE(deDate) = ? AND country = ? ORDER BY adultPrice ASC LIMIT 1;";

            $st = $pdo->prepare($query);
            $st->execute([$arAirPortCode,$deAirPortCode,$arDate,$countryList[$i]["country"]]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll()[0];

            if(count($res)==0){
                return $res;
            }

            $temp["country"] = $res["country"];
            $temp["minPrice"] = $deMinPrice + (int)$res["minPrice"];

            if($res["country"]=="대한민국"){
                $temp["imgUrl"]="http://kt999.site/img/korea.jpg";
            }
            else{
                $temp["imgUrl"]=null;
            }


            $result[] = $temp;

        }

        foreach ((array) $result as $key => $value) {
            $sort[$key] = $value['minPrice'];
        }
        array_multisort($sort, SORT_ASC, $result);
        return $result;
    }
    else{

        $result = [];

        $query = "SELECT DISTINCT airPortCode FROM airPorts WHERE country = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$country]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $cityList = $st->fetchAll();

        $ciryCount = count($cityList);

        for($i=0;$i<$ciryCount;$i++){

            $temp = [];
            $query = "SELECT f.cityNameKr,f.imgUrl,DATE(f.deDate) AS deDate,p.adultPrice,f.arAirPortCode FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.cityNameKr,p.imgUrl from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND arAirPortCode = ? AND DATE(deDate) = ? ORDER BY adultPrice ASC LIMIT 1;";

            $st = $pdo->prepare($query);
            $st->execute([$deAirPortCode,$cityList[$i]["airPortCode"],$deDate,]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();
            $arAirPortCode = $res[0]["arAirPortCode"];

            if(count($res) != 0){

                $query = "SELECT f.cityNameKr,f.imgUrl,DATE(f.deDate) AS deDate,p.adultPrice,f.arAirPortCode FROM
                        
                        (
                        SELECT f.id, f.airPlaneCode, f.deAirPortCode, f.arAirPortCode, f.deDate, p.cityNameKr,p.imgUrl from
                        
                        flights AS f
                        
                        JOIN
                        
                        airPorts AS p
                        
                        ON f.arAirPortCode = p.airPortCode
                        ) AS f
                        
                        JOIN
                        
                        (
                        SELECT * FROM prices WHERE seatCode = 0
                        ) AS p
                        
                        ON f.id = p.flightId
                        
                        WHERE deAirPortCode = ? AND arAirPortCode = ? AND DATE(deDate) = ? ORDER BY adultPrice ASC LIMIT 1;";

                $st = $pdo->prepare($query);
                $st->execute([$arAirPortCode,$deAirPortCode,$arDate]);
                $st->setFetchMode(PDO::FETCH_ASSOC);
                $reRes = $st->fetchAll();

                if(count($reRes) != 0) {
                    $temp["cityNameKr"] = $res[0]["cityNameKr"];
                    $temp["imgUrl"] = $res[0]["imgUrl"];
                    $temp["deDate"] = $res[0]["deDate"];
                    $temp["arDate"] = $reRes[0]["deDate"];
                    $temp["minPrice"] = (int)$res[0]["adultPrice"] + (int)$reRes[0]["adultPrice"];
                    $temp["type"] = "직항";
                    $result[] = $temp;
                }
            }


        }

        foreach ((array) $result as $key => $value) {
            $sort[$key] = $value['minPrice'];
        }
        array_multisort($sort, SORT_ASC, $result);

        return $result;

    }

}