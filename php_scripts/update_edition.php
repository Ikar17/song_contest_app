<?php 

session_start();

if(!isset($_POST['participant_deadline']) || !isset($_POST['voting_deadline'])
       || !isset($_POST['result_deadline']) || !isset($_SESSION['update_edition_number']) || !isset($_POST['status'])){
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    $participant_deadline = $_POST['participant_deadline'];
    $voting_deadline = $_POST['voting_deadline'];
    $result_deadline = $_POST['result_deadline'];
    $edition_number = $_SESSION['update_edition_number'];
    $edition_status = $_POST['status'];

    unset($_POST['participant_deadline']);
    unset($_POST['voting_deadline']);
    unset($_POST['result_deadline']);
    unset($_SESSION['update_edition_number']);
    unset($_POST['status']);

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
    

    //aktualizacja edycji
    $sql = "UPDATE edycje 
            SET Zgloszenia = '$participant_deadline', Glosowanie = '$voting_deadline', Wyniki='$result_deadline ', Status='$edition_status' 
            WHERE Nr_edycji = '$edition_number'";

    $db_connect->query($sql);

    $db_connect->close();

    header("Location: ../pages/admin_panel.php");

?>