<?php 
    session_start();
    if(!isset($_SESSION['logged']) || !isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    if(!isset($_POST['author']) || !isset($_POST['songTitle']) || !isset($_POST['songUrl']) || !isset($_SESSION['edition'])){
        header('Location: ../pages/active_edition.php');
        exit();
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database";
    };


    //pobieranie id użytkownika
    $nickname = $_SESSION['login'];
    $sql = "SELECT Id FROM użytkownicy WHERE Nickname = '$nickname'";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0){
        unset($_POST['author']);
        unset($_POST['songTitle']);
        unset($_POST['songUrl']);
        header('Location: ../pages/active_edition.php');
        exit();
    }

    $row = $result->fetch_assoc();
    $user_id = $row['Id'];
    $result->close();


    //wstawianie nowej piosenki
    $edition_id = $_SESSION['edition'];
    $singer = $_POST['author'];
    $title = $_POST['songTitle'];
    $link = $_POST['songUrl'];

    $sql = "INSERT INTO piosenki (Wykonawca, TytuL, Link, Id_uzytkownika, Id_edycji) 
            VALUES('$singer', '$title', '$link', '$user_id', '$edition_id')";

    $db_connect->query($sql);

    $db_connect->close();
    unset($_POST['author']);
    unset($_POST['songTitle']);
    unset($_POST['songUrl']);
    header('Location: ../pages/active_edition.php');

?>