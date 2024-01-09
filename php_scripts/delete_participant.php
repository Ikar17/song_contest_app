<?php
    require_once "./db_config.php";

    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database";
    };


    //pobieranie id użytkownika
    $nickname = $_SESSION['login']['nickname'];
    $stmt = $db_connect->prepare("SELECT Id FROM użytkownicy WHERE Nickname = ?");
    $stmt->bind_param("s",$nickname);
    $stmt->execute();       
    $result = $stmt->get_result();
    $stmt->close();

    if($result == false || $result->num_rows == 0){
        header('Location: ../pages/active_edition.php');
        exit();
    }

    $row = $result->fetch_assoc();
    $user_id = $row['Id'];
    $result->close();

    //pobieranie id edycji
    $edition = $_SESSION['edition'];
    $sql = "SELECT Id FROM edycje WHERE Nr_Edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i",$edition);
    $stmt->execute();       
    $result = $stmt->get_result();
    $stmt->close();

    if($result == false || $result->num_rows == 0){
        header('Location: ../pages/active_edition.php');
        exit();
    }

    $row = $result->fetch_assoc();
    $edition_id = $row['Id'];
    $result->close();
    
    //usuwanie piosenki
    $sql = "DELETE FROM piosenki WHERE Id_uzytkownika = ? AND Id_edycji = ?"; 
    $stmt = $db_connect->prepare($sql);
    if($stmt){
        $stmt->bind_param("ii", $user_id, $edition_id);
        $stmt->execute();    
        $stmt->close();
    }

    $db_connect->close();

    header('Location: ../pages/active_edition.php');

?>