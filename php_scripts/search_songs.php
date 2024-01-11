<?php

    $json = file_get_contents("php://input");
    $data = json_decode($json);

    require_once "./sanitize_validate.php";
    $singer = sanitize_string($data->singer);
    $title = sanitize_string($data->title);
    $data = array("results" => []);

    #połączenie z bazą danych 
    require_once "./db_config.php";
    try{
        $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    }catch(Exception $e){
        echo json_encode($data);
        exit();
    }

    #pobieranie danych z bazy
    $singer = $singer."%";
    $title = $title."%";
    $sql = "SELECT * FROM piosenki WHERE Wykonawca LIKE ? AND Tytul LIKE ?";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("ss",$singer, $title );
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false){
        $db_connect->close();
        echo json_encode($data);
        exit();
    }

    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $data["results"][$i] = $row;
    }


    #zamykanie połączenia
    $response->close();
    $db_connect->close();

    $json = json_encode($data);
    echo $json;

?>