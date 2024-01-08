<?php
    session_start();
    if(!isset($_SESSION['login'])){
        header("Location: ../index.php");
        exit();
    }

    if(!isset($_POST['first_place']) || !isset( $_POST['second_place']) || !isset( $_POST['third_place'])){
        header("Location: ../pages/active_edition.php");
        exit();
    }

    if(isset($_POST['save_voting'])){
        $_SESSION['first_place'] = $_POST['first_place'];
        $_SESSION['second_place']  = $_POST['second_place'];
        $_SESSION['third_place']  = $_POST['third_place'];
        header("Location: ./cookies_voting.php");
        exit();
    }

    
    $id_song_first_place = $_POST['first_place'];
    $id_song_second_place = $_POST['second_place'];
    $id_song_third_place = $_POST['third_place'];
    $edition = $_SESSION['voting_edition'];
    $nickname = $_SESSION['login']['nickname'];

    unset($_SESSION['voting_edition']);

    //walidacja - jedna piosenka == jedno miejsce
    if($id_song_first_place == $id_song_second_place || $id_song_second_place == $id_song_third_place || $id_song_first_place == $id_song_third_place ){
        $_SESSION['voting_error'] = "Jednej piosence można przyznać tylko jedno miejsce";
        header("Location: ../pages/active_edition.php");
        exit();
        
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database";
    };


    //pobieram id edycji
    $sql = "SELECT Id FROM edycje WHERE Nr_edycji = ?";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition );
    $stmt->execute(); 
    $result = $stmt->get_result();      
    $stmt->close();

    $row = $result->fetch_assoc();
    $editionId = $row['Id'];
    $result->close();

    //pobieram id uzytkownika
    $sql = "SELECT Id FROM użytkownicy WHERE Nickname = ?";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("s", $nickname );
    $stmt->execute(); 
    $result = $stmt->get_result();      
    $stmt->close();

    $row = $result->fetch_assoc();
    $userId = $row['Id'];
    $result->close();

    //wstawiem rekord głosowania
    $sql = "INSERT INTO głosowanie (Id_edycji, Id_uzytkownika, Id_piosenki_1, Id_piosenki_2, Id_piosenki_3) VALUES(?, ?, ?, ?, ?)";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("iiiii", $editionId, $userId, $id_song_first_place, $id_song_second_place, $id_song_third_place );
    $stmt->execute();       
    $stmt->close();

    $db_connect->close();

    header("Location: ../pages/active_edition.php");
?>