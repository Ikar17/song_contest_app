<?php

    session_start();
    if(!isset($_SESSION['login']) || $_SESSION['login']['role'] != "Admin"){
      $response = array(
        "message" => "error"
      );
      $json_response = json_encode($response);
      echo $json_response;
      exit();
    }

    $json = file_get_contents("php://input");
    $data = json_decode($json);
    $id = $data->id;

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        $data = array(
          "message" => "error"
        );
        $json = json_encode($data);
        echo $json;
        exit();
    };

    //sprawdzenie czy piosenka nie brała udziału w głosowaniu
    $sql = "SELECT * FROM głosowanie WHERE Id_piosenki_1 = ? OR Id_piosenki_2 = ? OR Id_piosenki_3 = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("iii",$id,$id,$id); 
    $stmt->execute();
    $results = $stmt->get_result();       
    $stmt->close();

    if($results->num_rows > 0){
      $_SESSION['delete_song_admin_error'] = "Piosenka brała udział w głosowaniu. Nie można usunąć.";
      $data = array(
          "message" => "error"
      );

      $db_connect->close();
      $json = json_encode($data);
      echo $json;
      exit();
    }

    //usuwanie piosenki
    $sql = "DELETE FROM piosenki WHERE Id = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i",$id); 
    $stmt->execute();       
    $stmt->close();

    $db_connect->close();

    $data = array(
        "message" => "ok"
    );

    $json = json_encode($data);

    echo $json;
?>