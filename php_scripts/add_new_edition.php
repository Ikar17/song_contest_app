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

    //walidacja - sprawdzanie typu danych
    require_once "./sanitize_validate.php";
    if(!is_validate_date($participant_deadline) || !is_validate_date($voting_deadline) || !is_validate_date($result_deadline)){
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    //walidacja - puste pola
    if($participant_deadline == null || $voting_deadline==null ||  $result_deadline==null){
        $_SESSION['add_edition_error'] = "Nie wprowadzono wszystkich niezbędnych danych";
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    //walidacja - kolejność: zgłoszenia, głosowanie, wyniki
    if($participant_deadline >= $voting_deadline || $voting_deadline >= $result_deadline){
        $_SESSION['add_edition_error'] = "Niepoprawny terminarz. Kolejność terminów: zgłoszenia, głosowanie, wyniki";
        header("Location: ../pages/admin_panel.php");
        exit();
    }

    require_once "./db_config.php";
    $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
    if($db_connect->connect_errno != 0){
        echo "Error with database 1";
        exit();
    };

    //walidacja, sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte głosowanie - odrzucić
    $sql = "SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?
            UNION
            SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?
            UNION
            SELECT * FROM edycje WHERE Glosowanie <= ? AND Wyniki >= ?";

    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("ssssss",$voting_deadline, $voting_deadline, $result_deadline, $result_deadline, $participant_deadline, $participant_deadline);
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false){
        $_SESSION['add_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['add_edition_error'] = "Nie dodano edycji. Data zgłoszeń lub głosowania koliduje z datą głosowania w edycji nr: ".$row['Nr_edycji'];
        $response->close();
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    
    //walidacja, todo sprawdzenie czy któraś z edycji ma już w podanym terminie otwarte zgloszenia - odrzucić
    $sql = "SELECT * FROM edycje WHERE Zgloszenia <= ? AND Glosowanie >= ?
            UNION
            SELECT * FROM edycje WHERE Zgloszenia <= ? AND Glosowanie >= ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("ssss",$participant_deadline, $participant_deadline, $voting_deadline, $voting_deadline);
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false){
        $_SESSION['add_edition_error'] = "Błąd bazy danych";
        $db_connect->close();
        header("Location: ../pages/admin_panel.php");
        exit();
    }
    if($response->num_rows != 0){
        $row = $response->fetch_assoc();
        $_SESSION['add_edition_error'] = "Nie dodano edycji. Data zgłoszeń koliduje z datą zgłoszeń w edycji nr: ".$row['Nr_edycji'];
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
    $stmt = $db_connect->prepare("INSERT INTO edycje (Nr_edycji, Zgloszenia, Glosowanie, Wyniki) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $new_edition_number, $participant_deadline, $voting_deadline, $result_deadline);
    $stmt->execute();

    $stmt->close();
    $db_connect->close();

    header("Location: ../pages/admin_panel.php");
?>