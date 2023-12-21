<?php 

function get_all_inactive_editions($db_connect){
    $sql = "SELECT * FROM edycje WHERE Status=0";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0) return []; 

    $editions = [];
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();

        $edition['id'] = $row['Id'];
        $edition['edition_number'] = $row['Nr_edycji'];
        $edition['participant_deadline'] = $row['Zgloszenia'];
        $edition['voting_deadline'] = $row['Glosowanie'];
        $edition['result_deadline'] = $row['Wyniki'];
        $edition['status'] = $row['Status'];

        $editions[$i] = $edition;
    }


    $result->close();
    return $editions;
}


function show_editions_options($db_connect){

    $editions = get_all_inactive_editions($db_connect);
    $options = "";
    for($i=0; $i<count($editions); $i++){
        $edition = $editions[$i];
        $edition_number = $edition['edition_number'];
        $options = $options."<option value=$edition_number >".$edition_number."</option>";
    }

    return $options;
}

function show_edition($db_connect, $edition_number){
    require_once "../php_scripts/user_api.php";


    $html = "<div><h1>Edycja nr $edition_number </h1></div>";

    #pobieranie informacji o terminarzu
    $sql = "SELECT * FROM edycje WHERE Nr_edycji = '$edition_number'";
    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows == 0){
        return "Błąd bazy danych";
        exit();
    }

    $row = $response->fetch_assoc();
    $response->close();
    $participant_deadline = $row['Zgloszenia'];
    $voting_deadline = $row['Glosowanie'];
    $results_deadline = $row['Wyniki'];

    $html = $html."<div class='subtitle'><h2>Terminarz</h2></div>";

    $html = $html."
        <div>
            <p>Termin zgłoszeń: $participant_deadline </p>
            <p>Termin głosowania: $voting_deadline</p>
            <p>Termin wyników: $results_deadline</p>
        </div>
        ";

    #pobieranie danych o piosenkach biorących udział
    $participants = get_all_participants($db_connect, $edition_number);
    $html = $html."<div class='subtitle'><h2>Zgłoszenia</h2></div>";
    for($x = 0; $x < count($participants); $x++){
        $participant = $participants[$x];
        $nickname = $participant['nickname'];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $link = $participant['link'];

        $html = $html."
        <div class='participant'>
            <p>$nickname</p>
            <p>$singer</p>
            <p>$title</p>
            <a href='$link'>Posłuchaj</a>
        </div>
        ";

    }

    //pobieranie danych o rezultatach
    $html = $html."<div class='subtitle'><h2>Wyniki</h2></div>";
    $voting = show_results($db_connect, $edition_number, $results_deadline);
    $html = $html.$voting;

    return $html;
}

?>