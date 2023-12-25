<?php 
    session_start();
    if(!isset($_SESSION['login'])){
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
    $nickname = $_SESSION['login']['nickname'];
    $sql = "SELECT Id FROM użytkownicy WHERE Nickname = '$nickname'";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0){
        header('Location: ../pages/active_edition.php');
        exit();
    }

    $row = $result->fetch_assoc();
    $user_id = $row['Id'];
    $result->close();

    //pobieranie id edycji
    $edition = $_SESSION['edition'];
    $sql = "SELECT Id FROM edycje WHERE Nr_Edycji = '$edition'";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0){
        header('Location: ../pages/active_edition.php');
        exit();
    }

    $row = $result->fetch_assoc();
    $edition_id = $row['Id'];
    $result->close();
    
    //sprawdzanie czy dana piosenka wystepuje juz w bazie
    $singer = trim($_POST['author']);
    $title = trim($_POST['songTitle']);
    $link = trim($_POST['songUrl']);

    $sql = "SELECT * FROM piosenki WHERE Wykonawca = '$singer' AND Tytul='$title'";
    $response = $db_connect->query($sql);
    if($response && $response->num_rows > 0){
        $response->close();
        $_SESSION["add_participant_error"] = "Dana piosenka już brała udział";
        header('Location: ../pages/active_edition.php');
        exit();
    }
    $response->close();

    //wstawianie nowej piosenki
    $sql = "INSERT INTO piosenki (Wykonawca, TytuL, Link, Id_uzytkownika, Id_edycji) 
            VALUES('$singer', '$title', '$link', '$user_id', '$edition_id')";

    $res = $db_connect->query($sql);

    $db_connect->close();

    header('Location: ../pages/active_edition.php');

?>