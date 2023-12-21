<?php

function does_user_participate($db_connect, $nickname, $edition){

    //pobieranie id edycji
    $sql = "SELECT Id FROM edycje WHERE Nr_edycji ='$edition'";
    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows == 0){
        return false;
    }

    $row = $response->fetch_assoc();
    $edition_id = $row['Id'];
    $response->close();


    $sql = "SELECT * FROM piosenki JOIN użytkownicy ON użytkownicy.Id = piosenki.id_uzytkownika
     WHERE użytkownicy.Nickname = '$nickname' AND Id_edycji = '$edition_id'";

    $result = $db_connect->query($sql);
    if($result == false) return true;
    if($result->num_rows != 0){
        $result->close();
        return true;
    } 

    $result->close();
    return false;
}

function get_all_participants($db_connect, $edition){
    $sql = "SELECT Nickname, Wykonawca, Tytul, Link, piosenki.Id as songId 
    FROM piosenki JOIN użytkownicy ON piosenki.id_uzytkownika = użytkownicy.id 
    JOIN edycje ON piosenki.id_edycji = edycje.Id WHERE edycje.Nr_edycji = '$edition'";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0) return []; 

    $participants = [];
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();

        $participant['songId'] = $row['songId'];
        $participant['nickname'] = $row['Nickname'];
        $participant['singer'] = $row['Wykonawca'];
        $participant['title'] = $row['Tytul'];
        $participant['link'] = $row['Link'];

        $participants[$i] = $participant;
    }


    $result->close();
    return $participants;

}

function get_all_participants_without_one_user($db_connect, $edition, $userNick){
    $sql = "SELECT Nickname, Wykonawca, Tytul, Link, piosenki.Id as songId 
    FROM piosenki JOIN użytkownicy ON piosenki.id_uzytkownika = użytkownicy.id 
    JOIN edycje ON piosenki.id_edycji = edycje.Id WHERE edycje.Nr_edycji = '$edition' AND NOT użytkownicy.Nickname = '$userNick'";

    $result = $db_connect->query($sql);
    if($result == false || $result->num_rows == 0) return []; 

    $participants = [];
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();

        $participant['songId'] = $row['songId'];
        $participant['nickname'] = $row['Nickname'];
        $participant['singer'] = $row['Wykonawca'];
        $participant['title'] = $row['Tytul'];
        $participant['link'] = $row['Link'];

        $participants[$i] = $participant;
    }


    $result->close();
    return $participants;

}

function does_user_voting($db_connect, $nickname, $edition){
    $sql = "SELECT * FROM głosowanie JOIN użytkownicy ON głosowanie.Id_uzytkownika = użytkownicy.Id
            WHERE Id_edycji = '$edition' AND użytkownicy.Nickname = '$nickname'";

    $response = $db_connect->query($sql);
    if($response && $response->num_rows == 0){
        $response->close();
        return false;
    }
    return true;

}

function show_voting($db_connect, $nickname, $edition, $voting_starttime, $results_time){
    //sprawdzenie czy aktualnie jest czas głosowania
    if(date('Y-m-d H:i:s') < $voting_starttime){
        return "<h3>Głosowanie ruszy po zakończeniu przyjmowania zgłoszeń</h3>";
    }

    if(date('Y-m-d H:i:s') >= $results_time){
        return "<h3>Głosowanie zostało zakończone</h3>";
    }


    //sprawdzenie czy uzytkownik zgłosił się
    if(!does_user_participate($db_connect, $nickname, $edition)){
        return "<h3>Musisz najpierw zgłosić swoją propozycję</h3>";
    }


    //sprawdzenie czy użytkownik nie głosował
    if(does_user_voting($db_connect, $nickname, $edition)){
        return "<h3>Już oddałeś głosy</h3>";
    }


    $participants = get_all_participants_without_one_user($db_connect, $edition, $nickname);
    $options = "";
    for($i=0; $i<count($participants); $i++){
        $participant = $participants[$i];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $songId = $participant['songId'];
        $options = $options."<option value=$songId>".$singer." - ".$title."</option>";
    }

    $_SESSION['voting_edition'] = $edition;

    return "
    <form method='POST' action='../php_scripts/voting.php'>
        <label for='first_place'>1 miejsce </label>
        <select id='first_place' name='first_place'>
            $options
        </select>
        <label for='second_place'>2 miejsce </label>
        <select id='second_place' name='second_place' >
            $options
        </select>
        <label for='third_place'>3 miejsce </label>
        <select id='third_place' name='third_place'>
            $options
        </select>
        <input type='submit' value='Zagłosuj'>
    </form>";
}


function show_results($db_connect, $edition, $results_time){

    if(date('Y-m-d H:i:s') < $results_time){
        return "<h3>Wyniki zostaną opublikowane po zakończeniu głosowania</h3>";
    }

    //pobranie listy piosenek bioracych udzial w edycji
    $sql = "SELECT DISTINCT piosenki.Id, Wykonawca, Tytul FROM piosenki JOIN edycje ON piosenki.id_edycji = edycje.id WHERE edycje.Nr_edycji ='$edition'";
    $result = $db_connect->query($sql);
    $songs_results_arrray;
    $songs_id_list = [];
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();
        $id = $row['Id'];
        $singer = $row['Wykonawca'];
        $title = $row['Tytul'];

        $songs_id_list[$i] = $id;
        $songs_results_arrray["$id"] = [0,$singer,$title];
    }
    $result->close();

    //brak piosenek
    if(count($songs_id_list) == 0){
        return "<div>Brak piosenek</div>";
    }

    //pobranie wynikow glosowania - pierwsze miejsca
    $sql = "SELECT Id_piosenki_1, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
            WHERE edycje.Nr_edycji = '$edition' GROUP BY Id_piosenki_1";

    $result = $db_connect->query($sql);
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();
        $id = $row['Id_piosenki_1'];
        $how_many = $row['how_many'];
        $scores = $how_many * 5;

        $songs_results_arrray["$id"][0] += $scores;
    }   
    $result->close();
    
    //pobranie wynikow glosowania - drugie miejsce
    $sql = "SELECT Id_piosenki_2, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
            WHERE edycje.Nr_edycji = '$edition' GROUP BY Id_piosenki_2";

    $result = $db_connect->query($sql);
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();
        $id = $row['Id_piosenki_2'];
        $how_many = $row['how_many'];
        $scores = $how_many * 3;

        $songs_results_arrray["$id"][0] += $scores;
    }   
    $result->close(); 

    //pobranie wynikow glosowania - trzecie miejsce
    $sql = "SELECT Id_piosenki_3, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
            WHERE edycje.Nr_edycji = '$edition' GROUP BY Id_piosenki_3";

    $result = $db_connect->query($sql);
    for($i=0; $i<$result->num_rows; $i++){
        $row = $result->fetch_assoc();
        $id = $row['Id_piosenki_3'];
        $how_many = $row['how_many'];
        $scores = $how_many * 1;

        $songs_results_arrray["$id"][0] += $scores;
    }   
    $result->close(); 

    //sortowanie rezultatow
    usort($songs_results_arrray,'compare_results');

    //tworzenie widoku
    $position = 1;
    $html = "";
    foreach($songs_results_arrray as $key => $value){
        $html = $html."<p><strong>".$position." miejsce: </strong>".$value[1]." - ".$value[2].". <strong>Liczba punktów:</strong> ".$value[0]."</p>";
        $position++;
    }

    return "<div>".$html."</div>";

}

function compare_results($first, $second){
    return $first[0] < $second[0];
}

function get_all_users($db_connect){
    $sql = "SELECT * FROM użytkownicy";
    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows==0) return [];

    $users = [];
    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $users[$row['Id']] = $row;
    }

    $response->close();
    return $users;
}


function show_users_ranking($db_connect){
    $users = get_all_users($db_connect);
    if(count($users) == 0) return "";

    //tworzenie tablicy rezultatow
    $results = [];
    foreach ($users as $user) {
        $results[$user['Id']] = array("user" => $user, "points" => 0);
    } 

    //pobieranie wszystkich rezultatow
    $sql = "SELECT * FROM głosowanie";
    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows==0) return "";

    //uzupełnienie tablicy rezultatow
    for($i=0; $i<$response->num_rows; $i++){
        $voting = $response->fetch_assoc();
        //pierwsze miejsce - 5pkt
        $first_place_song_id = $voting['Id_piosenki_1'];
        $user_id = get_user_id_from_song_id($db_connect, $first_place_song_id);
        if($user_id == -1){
            $response->close();
            return "";
        }
        $results[$user_id]['points'] += 5; 

        //drugie miejsce - 3pkt
        $second_place_song_id = $voting['Id_piosenki_2'];
        $user_id = get_user_id_from_song_id($db_connect, $second_place_song_id);
        if($user_id == -1){
            $response->close();
            return "";
        }
        $results[$user_id]['points'] += 3; 

        //trzecie miejsce - 1pkt
        $third_place_song_id = $voting['Id_piosenki_3'];
        $user_id = get_user_id_from_song_id($db_connect, $third_place_song_id);
        if($user_id == -1){
            $response->close();
            return "";
        }
        $results[$user_id]['points'] += 1; 

    }

    $response->close();

    //sortowanie miejsc
    usort($results,'compare_users');


    //tworzenie widoku
    $html = "
        <table>
            <tr>
                <th>Miejsce</th>
                <th>Użytkownik</th>
                <th>Liczba punktów</th>
            </tr>
        ";

    $place = 1;
    foreach($results as $result){
        $user_name = $result['user']['Nickname'];
        $user_points = $result['points'];
        $html = $html."
            <tr class='place_$place'>
                <td>$place</td>
                <td>$user_name</td>
                <td>$user_points</td>
            </tr>
        ";
        $place += 1;
    }

    $html = $html."</table>";

    echo $html;

}

function get_user_id_from_song_id($db_connect, $song_id){
    $sql = "SELECT użytkownicy.Id FROM użytkownicy JOIN piosenki ON piosenki.Id_uzytkownika = użytkownicy.Id WHERE piosenki.Id = '$song_id'";
    $response = $db_connect->query($sql);
    if($response == false || $response->num_rows == 0){
        return -1;
    }
    $row = $response->fetch_assoc();
    $user_id = $row['Id'];
    $response->close();
    return $user_id;
}

function compare_users($first_user, $second_user){
    return $first_user['points'] < $second_user['points'];
}

?>