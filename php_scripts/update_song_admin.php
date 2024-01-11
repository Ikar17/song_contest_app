<?php
    require_once "./db_config.php";
    require_once "./sanitize_validate.php";

    session_start();
    if(!isset($_SESSION['login']) || $_SESSION['login']['role'] != "Admin"){
        header("Location: ../index.php");
        exit();
    }

    
    if(!isset($_POST['singer']) || !isset($_POST['title']) || !isset($_POST['link']) || !isset($_POST['nickname']) ||  !isset($_SESSION['update_edition_number'])){
        header('Location: ../pages/admin_panel.php');
        exit();
    }

    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database";
    };


    //pobieranie id użytkownika
    $nickname = $_POST['nickname'];
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
    $edition = $_SESSION['update_edition_number'];
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


    //aktualizacja piosenki
    $singer = sanitize_string($_POST['singer']);
    $title = sanitize_string($_POST['title']);
    $link = sanitize_string($_POST['link']);

    $sql = "UPDATE piosenki SET Wykonawca = ?, Tytul = ?, Link = ? WHERE Id_uzytkownika = ? AND Id_edycji = ?"; 
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("sssii", $singer, $title, $link, $user_id, $edition_id);
    $stmt->execute();       
    $stmt->close();

    header("Location: ../pages/admin_panel.php");
?>