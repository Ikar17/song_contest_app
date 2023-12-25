<?php

function get_all_editions($db_connect){
    $sql = "SELECT * FROM edycje";

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

function get_edition($db_connect, $edition_number){
    $sql = "SELECT Id, Nr_edycji, DATE_FORMAT(Zgloszenia, '%Y-%m-%d %H:%i') as Zgloszenia,
    DATE_FORMAT(Glosowanie, '%Y-%m-%d %H:%i') as Glosowanie, DATE_FORMAT(Wyniki, '%Y-%m-%d %H:%i') as Wyniki, Status
    FROM edycje WHERE Nr_edycji='$edition_number'";

    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows == 0) return null;

    $row = $response->fetch_assoc();

    $response->close();
    return $row;
}

function show_editions($db_connect){

    $editions = get_all_editions($db_connect);
    $options = "";
    for($i=0; $i<count($editions); $i++){
        $edition = $editions[$i];
        $edition_number = $edition['edition_number'];
        $options = $options."<option value=$edition_number >".$edition_number."</option>";
    }

    return $options;
}


function show_editable_edition($db_connect, $edition_number){

    $edition = get_edition($db_connect, $edition_number);
    if($edition == null){
        return "Edition not found";
    }

    $participant_deadline = $edition['Zgloszenia'];
    $voting_deadline = $edition['Glosowanie'];
    $result_deadline = $edition['Wyniki'];
    $status = $edition['Status'];

    if($status == 0){
        $options = "
            <option value=0> Nieaktywna </option>
            <option value=1> Aktywna </option>
        ";
    }else{
        $options = "
            <option value=1> Aktywna </option>
            <option value=0> Nieaktywna </option>
        ";
    }

    $_SESSION['update_edition_number'] = $edition_number;

    return "
            <h2 class='edit_edition_headline'> Aktualizacja edycji nr: $edition_number </h2>
            <br>
            <form method='POST' action='../php_scripts/update_edition.php'>
                <table>
                    <tr>
                        <th>Status edycji:</th>
                        <td>
                            <select id='status' name='status'>
                                '$options'
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Start zgłoszeń:</th>
                        <td><input type='datetime-local' name='participant_deadline' id='participant_deadline' value='$participant_deadline'></td>
                    </tr>
                    <tr>
                        <th>Start głosowania <small>(równoznaczne z zakończeniem przyjmowania zgłoszeń):</small></th>
                        <td><input type='datetime-local' name='voting_deadline' id='voting_deadline' value='$voting_deadline'></td>
                    </tr>
                    <tr>
                        <th>Data udostępnienia wyników <small>(równoznaczne z zakończeniem głosowania):</small></th>
                        <td><input type='datetime-local' name='result_deadline' id='result_deadline' value='$result_deadline'></td>
                    </tr>
                    <tr>
                        <td><input type='submit' value='Zaktualizuj edycję'></td>
                    </tr>
                </table>
            </form>";

}
?>