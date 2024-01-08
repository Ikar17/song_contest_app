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

    unset($_SESSION['update_edition_number']);

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

    //walidacja, sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte głosowanie - odrzucić zmiany
    $sql = "SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?  AND NOT Nr_edycji = ?
            UNION 
            SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?  AND NOT Nr_edycji = ?
            UNION
            SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?  AND NOT Nr_edycji = ?";
    
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("ssississi", $voting_deadline, $voting_deadline, $edition_number, $result_deadline, $result_deadline, $edition_number, $participant_deadline, $participant_deadline, $edition_number);
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();
    
    if($response == false){
        $_SESSION['update_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['update_edition_error'] = "Nie wprowadzono zmian. Data zgłoszeń lub głosowania koliduje z datą głosowania w edycji nr: ".$row['Nr_edycji'];
        $response->close();
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    
    //walidacja, sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte zgloszenia - odrzucić
    $sql = "SELECT * FROM edycje WHERE Zgloszenia <= ? AND Glosowanie >= ? AND NOT Nr_edycji = ?
            UNION 
            SELECT * FROM edycje WHERE Zgloszenia <= ? AND Glosowanie >= ? AND NOT Nr_edycji = ?";
    
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("ssissi", $participant_deadline, $participant_deadline, $edition_number, $voting_deadline, $voting_deadline, $edition_number);
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false){
        $_SESSION['update_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['update_edition_error'] = "Nie wprowadzono zmian. Data zgłoszeń koliduje z datą zgłoszeń w edycji nr: ".$row['Nr_edycji'];
        $response->close();
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    //walidacja, sprawdzenie czy któraś z edycji jest już aktywna (w danym momencie może być tylko jedna) - odrzucić zmiany
    if($edition_status == 1){

        $sql = "SELECT * FROM edycje WHERE Status='1' AND NOT Nr_edycji = ?";
        $stmt = $db_connect->prepare($sql);
        $stmt->bind_param("i", $edition_number);
        $stmt->execute();
        $response = $stmt->get_result();       
        $stmt->close();

        if($response == false){
            $_SESSION['update_edition_error'] = "Błąd bazy danych";
            $db_connect->close();
            header("Location: ../pages/admin_panel.php");
            exit();
        }
        if($response->num_rows != 0){
            $row = $response->fetch_assoc();
            $_SESSION['update_edition_error'] = "Nie wprowadzono zmian. Aktywna jest już edycja nr: ".$row['Nr_edycji'];
            $response->close();
            $db_connect->close();
            header("Location: ../pages/admin_panel.php");
            
        }
    }

    //aktualizacja edycji
    $sql = "UPDATE edycje 
            SET Zgloszenia = ?, Glosowanie = ?, Wyniki=?, Status=?
            WHERE Nr_edycji = ?";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("sssii", $participant_deadline, $voting_deadline, $result_deadline, $edition_status, $edition_number);
    $stmt->execute();       
    $stmt->close();

    $db_connect->close();

    header("Location: ../pages/admin_panel.php");

?>