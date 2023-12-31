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


    $html = "<div class='edition_archive_headline'><h1>Edycja nr $edition_number </h1></div>";

    #pobieranie informacji o terminarzu
    $sql = "SELECT * FROM edycje WHERE Nr_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i",$edition_number);
    $stmt->execute();       
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false || $response->num_rows == 0){
        return "Błąd bazy danych";
        exit();
    }

    $row = $response->fetch_assoc();
    $response->close();
    $participant_deadline = new DateTime($row['Zgloszenia']);
    $voting_deadline = new DateTime($row['Glosowanie']);
    $results_deadline = new DateTime($row['Wyniki']);

    $html = $html."<div class='subtitle'>
                        <img src='../assets/calendar.png' alt='calendar icon' />
                        <h2>Terminarz</h2>
                  </div>";

    $html = $html."
        <table class='calendar'>
            <tr>
                <th>Start zgłoszeń:</th>
                <td>".$participant_deadline->format('d-m-Y H:i')."</td>
            </tr>
            <tr>
                <th>Start głosowania:</th>
                <td>".$voting_deadline->format('d-m-Y H:i')."</td>
            </tr>
            <tr>
                <th>Wyniki:</th>
                <td>".$results_deadline->format('d-m-Y H:i')."</td>
            </tr>
        </table>
        ";

    #pobieranie danych o piosenkach biorących udział
    $participants = get_all_participants($db_connect, $edition_number);
    $html = $html."<div class='subtitle'>
                        <img src='../assets/list.png' alt='list icon' />
                        <h2>Zgłoszenia</h2>
                    </div>";

    $html = $html."<table>
                        <tr>
                            <th> L.p </th>
                            <th>Użytkownik</th>
                            <th>Wykonawca</th>
                            <th>Tytuł</th>
                            <th>Link</th>
                        </tr>";

    for($x = 0; $x < count($participants); $x++){
        $participant = $participants[$x];
        $nickname = $participant['nickname'];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $link = $participant['link'];

        $order = $x + 1;
        $html = $html."
        <tr>
            <td>$order</td>
            <td>$nickname</td>
            <td>$singer</td>
            <td>$title</td>
            <td>
                <a href='$link'>
                    <img src='../assets/youtube.png'/>
                    Posłuchaj
                </a>
            </td>
        </tr>
        ";
    }
    $html = $html."</table>";

    //pobieranie danych o rezultatach
    $html = $html."<div class='subtitle'>
                        <img src='../assets/ranking_c.png' alt='results icon' />
                        <h2>Wyniki</h2>
                   </div>";
    $voting = show_results($db_connect, $edition_number, $results_deadline);
    $html = $html."<div class='voting_results'>".$voting."</div>";

    return $html;
}

?>