<?php
    session_start();
    if(!isset($_SESSION['login'])){
        header("Location: ../index.php");
        exit();
    }

    if(!isset($_SESSION['first_place']) || !isset( $_SESSION['second_place']) || !isset( $_SESSION['third_place'])){
        header("Location: ../pages/active_edition.php");
        exit();
    }

    $id_song_first_place = $_SESSION['first_place'];
    $id_song_second_place = $_SESSION['second_place'];
    $id_song_third_place = $_SESSION['third_place'];
    unset($_SESSION['first_place']);
    unset($_SESSION['second_place']);
    unset($_SESSION['third_place']);

    $places = array("first_place" => $id_song_first_place, "second_place" => $id_song_second_place, "third_place" => $id_song_third_place);
    $json = json_encode($places);

    setcookie('voting',$json, time()+3600*24, "/");
    
    header("Location: ../pages/active_edition.php");
?>