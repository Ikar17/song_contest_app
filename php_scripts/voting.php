<?php
    session_start();
    if(!isset($_SESSION['login'])){
        header("Location: ../index.php");
        exit();
    }

    if(!isset( $_POST['first_place']) || !isset( $_POST['second_place']) || !isset( $_POST['third_place'])){
        header("Location: ../pages/active_edition.php");
        exit();
    }


    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database";
    };


    $id_song_first_place = $_POST['first_place'];
    $id_song_second_place = $_POST['second_place'];
    $id_song_third_place = $_POST['third_place'];
    $edition = $_SESSION['voting_edition'];
    $nickname = $_SESSION['login'];


    //pobieram id edycji
    $sql = "SELECT Id FROM edycje WHERE Nr_edycji = '$edition'";
    $result = $db_connect->query($sql);
    $row = $result->fetch_assoc();
    $editionId = $row['Id'];
    $result->close();

    //pobieram id uzytkownika
    $sql = "SELECT Id FROM użytkownicy WHERE Nickname = '$nickname'";
    $result = $db_connect->query($sql);
    $row = $result->fetch_assoc();
    $userId = $row['Id'];
    $result->close();


    //wstawiem rekord głosowania
    $sql = "INSERT INTO głosowanie (Id_edycji, Id_uzytkownika, Id_piosenki_1, Id_piosenki_2, Id_piosenki_3)
    VALUES(
        $editionId,
        $userId,
        $id_song_first_place,
        $id_song_second_place,
        $id_song_third_place
    )";

    $db_connect->query($sql);

    unset($_POST['first_place']);
    unset($_POST['second_place']);
    unset($_POST['third_place']);
    unset($_SESSION['voting_edition']);

    $db_connect->close();

    header("Location: ../pages/active_edition.php");
?>