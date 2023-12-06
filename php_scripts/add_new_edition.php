<?php 

    if(!isset($_POST['participant_deadline']) || !isset($_POST['voting_deadline'])
       || !isset($_POST['result_deadline']) ){
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    $participant_deadline = $_POST['participant_deadline'];
    $voting_deadline = $_POST['voting_deadline'];
    $result_deadline = $_POST['result_deadline'];

    unset($_POST['participant_deadline']);
    unset($_POST['voting_deadline']);
    unset($_POST['result_deadline']);

    //walidacja 

    if($participant_deadline == null || $voting_deadline==null ||  $result_deadline==null){
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database 1";
        exit();
    };
    

    //pobieram numer ostatniej edycji
    $sql = "SELECT Nr_edycji FROM edycje ORDER BY Nr_edycji DESC LIMIT 1";
    $response = $db_connect->query($sql);
    if($response == false){
        echo "Error with database 2";
        $db_connect->close();
        exit();
    }

    $row = $response->fetch_assoc();
    $last_edition_number = $row["Nr_edycji"];
    $response->close();


    //wstawiam nową edycję
    $new_edition_number = $last_edition_number + 1;
    $sql = "INSERT INTO edycje (Nr_edycji, Zgloszenia, Glosowanie, Wyniki) 
            VALUES('$new_edition_number','$participant_deadline','$voting_deadline','$result_deadline')";

    $db_connect->query($sql);

    $db_connect->close();

    header("Location: ../pages/admin_panel.php");
?>