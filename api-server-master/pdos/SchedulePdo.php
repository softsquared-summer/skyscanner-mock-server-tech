<?php



function scheduleList($userId){
    $pdo = pdoSqlConnect();
    $query="SELECT r.id as roomId,r.title,i.deDate,i.reDate,a.imgUrl AS cityImgUrl FROM
                
                scheduleRooms AS r
                
                JOIN
                (
                SELECT i.roomId,f.deDate AS deDate,r.deDate AS reDate, f.arAirPortCode FROM
                scheduleItems AS i
                JOIN
                flights AS f
                ON i.deFlightId=f.id
                LEFT JOIN
                flights AS r
                ON i.reFlightId=r.id
                ORDER BY f.deDate
                ) AS i
                
                ON r.id = i.roomId
                
                JOIN
                
                airPorts AS a
                
                ON i.arAirPortCode = a.airPortCode
                
                WHERE r.userId = ? ORDER BY deDate ASC;";

    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $count=count($res);

    for($i=0;$i<$count-1;$i++){
        for($j=$i+1;$j<$count;$j++){
            if($res[$i]["roomId"]==$res[$j]["roomId"]){
                unset($res[$j]);
            }
        }
    }

    $res = array_values($res);

    return $res;
}

function schedule($userId,$roomId){
    $result = [];

    $pdo = pdoSqlConnect();
    $query="SELECT i.deFlightId,f.deDate,i.reFlightId,i.seatCode,i.adultCount,i.infantCount,i.childCount FROM

            scheduleRooms AS r
            
            JOIN
            
            scheduleItems AS i
            
            ON r.id=i.roomId
            
            JOIN
            
            flights AS f
            
            ON i.deFlightId = f.id
            
            WHERE r.userId = ? AND r.id = ?
            ORDER BY deDate ASC;";

    $st = $pdo->prepare($query);
    $st->execute([$userId,$roomId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $items = $st->fetchAll();

    $count = count($items);

    for($j=0;$j<$count;$j++){
        $temp=[];
        if(!$items[$j]["reFlightId"]){
            $temp["type"]="O";
        }
        else{
            $temp["type"]="R";
        }
        $temp["seatCode"]=$items[$j]["seatCode"];
        $temp["adultCount"]=$items[$j]["adultCount"];
        $temp["infantCount"]=$items[$j]["infantCount"];
        $temp["childCount"]=$items[$j]["childCount"];

        $query = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,f.airLineImg,DATE_FORMAT(f.deDate,'%H:%i') AS deTime,
                DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS adultPrice, p.infantPrice AS infantPrice, p.childPrice AS childPrice, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
                DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.id = ? AND p.seatCode =? ";

        $st = $pdo->prepare($query);
        $st->execute([$items[$j]["deFlightId"],$items[$j]["seatCode"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $tempSub = [];

        for($i=0;$i<count($res);$i++){

            $h = (int)$res[$i]["hour"];
            $m = (int)$res[$i]["min"];
            $time = ($h*60)+$m;

            $tempSub["flightId"]=(int)$res[$i]["flightId"];
            $tempSub["airPlaneCode"]=$res[$i]["airPlaneCode"];
            $tempSub["airLineKr"]=$res[$i]["airLineKr"];
            $tempSub["airLineEn"]=$res[$i]["airLineEn"];
            $tempSub["airLineImgUrl"]=$res[$i]["airLineImg"];
            $tempSub["type"]="직항";;
            $tempSub["deTime"]=$res[$i]["deTime"];
            $tempSub["arTime"]=$res[$i]["arTime"];
            $tempSub["adultPrice"]=(int)$res[$i]["adultPrice"];
            $tempSub["infantPrice"]=(int)$res[$i]["infantPrice"];
            $tempSub["childPrice"]=(int)$res[$i]["childPrice"];
            $tempSub["timeGap"]=$time;

            $temp["deTicket"] = $tempSub;
        }

        if($items[$j]["reFlightId"]){

            $query = "SELECT f.id AS flightId, f.airPlaneCode, f.airLineKr, f.airLineEn,f.airLineImg,DATE_FORMAT(f.deDate,'%H:%i') AS deTime,
                DATE_FORMAT(f.arDate,'%H:%i') AS arTime, p.adultPrice AS adultPrice, p.infantPrice AS infantPrice, p.childPrice AS childPrice, DATE_FORMAT(timediff(f.arDate,f.deDate),'%H:%i') AS timeGap,
                DATE_FORMAT(timediff(f.arDate,f.deDate),'%H') AS hour, DATE_FORMAT(timediff(f.arDate,f.deDate),'%i') AS min
                FROM flights AS f
                JOIN prices AS p
                ON f.id = p.flightId
                WHERE f.id = ? AND p.seatCode =? ";

            $st = $pdo->prepare($query);
            $st->execute([$items[$j]["reFlightId"],$items[$j]["seatCode"]]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();

            $tempSub = [];

            for($i=0;$i<count($res);$i++){

                $h = (int)$res[$i]["hour"];
                $m = (int)$res[$i]["min"];
                $time = ($h*60)+$m;

                $tempSub["flightId"]=(int)$res[$i]["flightId"];
                $tempSub["airPlaneCode"]=$res[$i]["airPlaneCode"];
                $tempSub["airLineKr"]=$res[$i]["airLineKr"];
                $tempSub["airLineEn"]=$res[$i]["airLineEn"];
                $tempSub["airLineImgUrl"]=$res[$i]["airLineImg"];
                $tempSub["type"]="직항";;
                $tempSub["deTime"]=$res[$i]["deTime"];
                $tempSub["arTime"]=$res[$i]["arTime"];
                $tempSub["adultPrice"]=(int)$res[$i]["adultPrice"];
                $tempSub["infantPrice"]=(int)$res[$i]["infantPrice"];
                $tempSub["childPrice"]=(int)$res[$i]["childPrice"];
                $tempSub["timeGap"]=$time;

                $temp["reTicket"] = $tempSub;
            }
        }

        $result[] = $temp;
    }

    return $result;
}

function scheduleAdd($userId,$roomId,$deFlightId,$reFlightId,$seatCode,$adultCount,$infantCount,$childCount){
    $pdo = pdoSqlConnect();


    //new room
    if($roomId == 0){
        $query="SELECT deAirPortCode,arAirPortCode FROM flights WHERE id = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$deFlightId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $deAirPortCode = $res[0]["deAirPortCode"];
        $arAirPortCode = $res[0]["arAirPortCode"];

        $query="SELECT cityNameKr FROM airPorts WHERE airPortCode = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$deAirPortCode]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $deRes = $st->fetchAll();

        $query="SELECT cityNameKr FROM airPorts WHERE airPortCode = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$arAirPortCode]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $arRes = $st->fetchAll();

        $deName = (string)$deRes[0]["cityNameKr"];
        $deName = mb_substr($deName,0,2);
        $arName = $arRes[0]["cityNameKr"];
        $arName = mb_substr($arName,0,2);

        $title=$deName."에서 ".$arName."까지";

        $query = "INSERT INTO scheduleRooms (title,userId) VALUES (?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$title,$userId]);

        $query = "SELECT max(id) AS id FROM scheduleRooms";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $roomId = $res[0]["id"];

        $query = "INSERT INTO scheduleItems (roomId,userId,deFlightId,reFlightId,seatCode,adultCount,infantCount,childCount) VALUES (?,?,?,?,?,?,?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$roomId,$userId,$deFlightId,$reFlightId,$seatCode,$adultCount,$infantCount,$childCount]);

        return $roomId;
    }
    else{
        $query = "INSERT INTO scheduleItems (roomId,userId,deFlightId,reFlightId,seatCode,adultCount,infantCount,childCount) VALUES (?,?,?,?,?,?,?,?);";

        $st = $pdo->prepare($query);
        $st->execute([$roomId,$userId,$deFlightId,$reFlightId,$seatCode,$adultCount,$infantCount,$childCount]);

        return $roomId;
    }
}

function scheduleRoomAuth($userId,$roomId){
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM scheduleRooms WHERE id = ? AND userId = ?;";


    $st = $pdo->prepare($query);
    $st->execute([$roomId, $userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    $count=count($res);

    return $count;
}

function scheduleItemAuth($userId,$deFlightId,$reFlightId){
    $pdo = pdoSqlConnect();

    if($reFlightId){
        $query = "SELECT * FROM scheduleItems WHERE deFlightId = ? AND reFlightId = ? AND userId = ?;";


        $st = $pdo->prepare($query);
        $st->execute([$deFlightId,$reFlightId,$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;
        $pdo = null;

        $count=count($res);

        return $count;
    }

    $query = "SELECT * FROM scheduleItems WHERE deFlightId = ? AND reFlightId is NULL AND userId = ?;";


    $st = $pdo->prepare($query);
    $st->execute([$deFlightId, $userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    $count=count($res);

    return $count;
}

function scheduleUpdate($userId,$roomId,$title){
    $pdo = pdoSqlConnect();

    $query = "UPDATE scheduleRooms SET title = ? WHERE id = ? AND userId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$title,$roomId,$userId]);

    $st = null;
    $pdo = null;
}

function scheduleDelete($userId,$deFlightId,$reFlightId){
    $pdo = pdoSqlConnect();
    if($reFlightId){
        $query = "DELETE from scheduleItems WHERE userId = ? AND deFlightId = ? AND reFlightId = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$userId,$deFlightId,$reFlightId]);

        $st = null;
        $pdo = null;

        return;
    }
    else{
        $query = "DELETE from scheduleItems WHERE userId = ? AND deFlightId = ? AND reFlightId is NULL;";

        $st = $pdo->prepare($query);
        $st->execute([$userId,$deFlightId]);

        $st = null;
        $pdo = null;

        return;
    }
}

function scheduleDeleteAll($userId,$roomId){
    $pdo = pdoSqlConnect();
    $query = "DELETE from scheduleRooms WHERE userId = ? AND id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userId,$roomId]);

    $query = "DELETE from scheduleItems WHERE userId = ? AND roomId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userId,$roomId]);

    $st = null;
    $pdo = null;
}