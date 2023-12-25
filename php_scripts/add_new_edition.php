<?php 

    session_start();

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

    //walidacja - kolejność: zgłoszenia, głosowanie, wyniki
    if($participant_deadline >= $voting_deadline || $voting_deadline >= $result_deadline){
        $_SESSION['add_edition_error'] = "Niepoprawny terminarz. Kolejność terminów: zgłoszenia, głosowanie, wyniki";
        header("Location: ../pages/admin_panel.php");
        exit();
    }


    //walidacja - puste pola
    if($participant_deadline == null || $voting_deadline==null ||  $result_deadline==null){
        $_SESSION['add_edition_error'] = "Nie wprowadzono wszystkich niezbędnych danych";
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database 1";
        exit();
    };

    //walidacja, todo sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte głosowanie - odrzucić
    $sql = "SELECT * FROM edycje WHERE Glosowanie <= '$voting_deadline' AND Wyniki >= '$voting_deadline'";
    $response = $db_connect->query($sql);
    if($response == false){
        $_SESSION['add_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['add_edition_error'] = "Nie dodano edycji. Data głosowania koliduje z datą głosowania w edycji nr: ".$row['Nr_edycji'];
        $response->close();
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    

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