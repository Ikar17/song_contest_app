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

    //walidacja - kolejność: zgłoszenia, głosowanie, wyniki
    if($participant_deadline >= $voting_deadline || $voting_deadline >= $result_deadline){
        $_SESSION['update_edition_error'] = "Niepoprawny terminarz. Kolejność terminów: zgłoszenia, głosowanie, wyniki";
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    //walidacja - puste pola    

    if($participant_deadline == null || $voting_deadline==null ||  $result_deadline==null){
        $_SESSION['update_edition_error'] = "Nie wprowadzono wszystkich niezbędnych danych";
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database 1";
        exit();
    };

    //walidacja, todo sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte głosowanie - odrzucić zmiany
    $sql = "SELECT * FROM edycje WHERE Glosowanie <= '$voting_deadline' AND Wyniki >= '$voting_deadline' AND NOT Nr_edycji = '$edition_number'";
    $response = $db_connect->query($sql);
    if($response == false){
        $_SESSION['update_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['update_edition_error'] = "Nie wprowadzono zmian. Data głosowania koliduje z datą głosowania w edycji nr: ".$row['Nr_edycji'];
        $response->close();
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    

    //aktualizacja edycji
    $sql = "UPDATE edycje 
            SET Zgloszenia = '$participant_deadline', Glosowanie = '$voting_deadline', Wyniki='$result_deadline ', Status='$edition_status' 
            WHERE Nr_edycji = '$edition_number'";

    $db_connect->query($sql);

    $db_connect->close();

    header("Location: ../pages/admin_panel.php");

?>