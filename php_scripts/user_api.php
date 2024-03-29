<?php

function does_user_participate($db_connect, $nickname, $edition){

    //pobieranie id edycji
    $sql = "SELECT Id FROM edycje WHERE Nr_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();
    if($response == false || $response->num_rows == 0){
        return false;
    }

    $row = $response->fetch_assoc();
    $edition_id = $row['Id'];
    $response->close();


    $sql = "SELECT * FROM piosenki JOIN użytkownicy ON użytkownicy.Id = piosenki.id_uzytkownika
     WHERE użytkownicy.Nickname = ? AND Id_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("si", $nickname, $edition_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

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
    JOIN edycje ON piosenki.id_edycji = edycje.Id WHERE edycje.Nr_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

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
    JOIN edycje ON piosenki.id_edycji = edycje.Id WHERE edycje.Nr_edycji =  ? AND NOT użytkownicy.Nickname = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("is", $edition, $userNick);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

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
            WHERE Id_edycji = ? AND użytkownicy.Nickname = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("is", $edition, $nickname);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();          

    if($response && $response->num_rows == 0){
        $response->close();
        return false;
    }
    return true;

}

function show_voting($db_connect, $nickname, $edition, $voting_starttime, $results_time){
    //sprawdzenie czy aktualnie jest czas głosowania
    if(date('Y-m-d H:i:s') < $voting_starttime->format('Y-m-d H:i:s')){
        return "<h3>Głosowanie ruszy po zakończeniu przyjmowania zgłoszeń</h3>";
    }

    if(date('Y-m-d H:i:s') >= $results_time->format('Y-m-d H:i:s')){
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

    //sprawdzenie czy są ustawione ciasteczka
    $save_voting = null;
    if(isset($_COOKIE['voting'])){
        $save_voting = json_decode($_COOKIE['voting']);
    }
    
    $participants = get_all_participants_without_one_user($db_connect, $edition, $nickname);
    $options_first = "";
    for($i=0; $i<count($participants); $i++){
        $participant = $participants[$i];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $songId = $participant['songId'];
        if($save_voting != null && ($save_voting->first_place == $songId)){
            $options_first = $options_first."<option selected='selected' value=$songId >".$singer." - ".$title."</option>";
        }else{
            $options_first = $options_first."<option value=$songId>".$singer." - ".$title."</option>";
        }
    }

    $options_second = "";
    for($i=0; $i<count($participants); $i++){
        $participant = $participants[$i];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $songId = $participant['songId'];
        if($save_voting != null && ($save_voting->second_place == $songId)){
            $options_second = $options_second."<option selected='selected' value=$songId>".$singer." - ".$title."</option>";
        }else{
            $options_second = $options_second."<option value=$songId>".$singer." - ".$title."</option>";
        }
    }

    $options_third = "";
    for($i=0; $i<count($participants); $i++){
        $participant = $participants[$i];
        $singer = $participant['singer'];
        $title = $participant['title'];
        $songId = $participant['songId'];
        if($save_voting != null && ($save_voting->third_place == $songId)){
            $options_third = $options_third."<option selected='selected' value=$songId>".$singer." - ".$title."</option>";
        }else{
            $options_third = $options_third."<option value=$songId>".$singer." - ".$title."</option>";
        }
    }

    $_SESSION['voting_edition'] = $edition;

    return "
    <form method='POST' action='../php_scripts/voting.php'>
        <label for='first_place'>1 miejsce </label>
        <select id='first_place' name='first_place'>
            $options_first
        </select>
        <label for='second_place'>2 miejsce </label>
        <select id='second_place' name='second_place' >
            $options_second
        </select>
        <label for='third_place'>3 miejsce </label>
        <select id='third_place' name='third_place'>
            $options_third
        </select>
        <input type='submit' class='button button--large button--primary' name='save_voting' value='Zapisz'>
        <input type='submit' class='button button--large button--primary' name='send_voting' value='Zagłosuj'>
    </form>";
}


function show_results($db_connect, $edition, $results_time){

    if(date('Y-m-d H:i:s') < $results_time->format('Y-m-d H:i:s')){
        return "<h3>Wyniki zostaną opublikowane po zakończeniu głosowania</h3>";
    }

    //pobranie listy piosenek bioracych udzial w edycji
    $sql = "SELECT DISTINCT piosenki.Id, Wykonawca, Tytul FROM piosenki JOIN edycje ON piosenki.id_edycji = edycje.id WHERE edycje.Nr_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();   

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
            WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_1";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();  

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
            WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_2";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();  

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
            WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_3";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();  

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
        $order = $position;

        if($position == 1) $order = "<img src='../assets/gold_medal.png' alt='first place icon' />";
        else if($position == 2) $order = "<img src='../assets/silver_medal.png' alt='second place icon'  />";
        else if($position == 3) $order = "<img src='../assets/bronze_medal.png' alt='third place icon'  />";

        $html = $html.
                    "<tr>
                        <td>$order</td>
                        <td>".$value[1]." - ".$value[2]."</td>
                        <td>".$value[0]."</td>
                    </tr>";
                    
        $position++;
    }

    return "<table>
                <tr>
                    <th>Miejsce</th>
                    <th>Piosenka</th>
                    <th>Liczba punktów </th>
                </tr>"
                .$html
            ."</table>";

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
    //pobieranie wszystkich użytkowników
    $users = get_all_users($db_connect);
    if(count($users) == 0) return "";

    //tworzenie tablicy rezultatow
    $user_results = [];
    foreach ($users as $user) {
        $user_results[$user['Id']] = array("user" => $user, "points" => 0);
    } 

    //pobieranie wszystkich zakończonych edycji
    $current_datetime = date("Y-m-d H:i:s");
    $sql = "SELECT Nr_edycji FROM edycje WHERE Wyniki <= ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("s", $current_datetime);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();  

    if($response == false) return "Błąd bazy danych";
    if($response->num_rows==0){
        $response->close();
        return "Brak danych";
    }

    //pobieranie rezultatow dla kazdej edycji i tworzenie rezultatow uzytkownikow
    for($i = 0; $i < $response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $edition_number = $row['Nr_edycji'];

        $song_results = get_top_results_of_edition($db_connect, $edition_number);

        $index = 1;
        foreach($song_results as $result){
            $user_id = $result['song']['Id_uzytkownika'];
            if($index == 1) $user_results[$user_id]["points"] += 5;
            else if($index == 2) $user_results[$user_id]["points"] += 3;
            else if($index == 3) $user_results[$user_id]["points"] += 1;

            $index++;
        }
    }

    $response->close();

    //sortowanie miejsc
    usort($user_results,'compare_users');


    //tworzenie widoku
    $html = "
        <table>
            <tr>
                <th>Miejsce</th>
                <th>Użytkownik</th>
                <th>Liczba punktów</th>
            </tr>
        ";

    $order = 1;
    foreach($user_results as $result){
        $user_name = $result['user']['Nickname'];
        $user_points = $result['points'];

        if($order == 1) $place = "<img src='../assets/gold_medal.png' alt='first place icon' />";
        else if($order == 2) $place = "<img src='../assets/silver_medal.png' alt='second place icon'  />";
        else if($order == 3) $place = "<img src='../assets/bronze_medal.png' alt='third place icon'  />";
        else $place = $order;

        $html = $html."
            <tr>
                <td>$place</td>
                <td>$user_name</td>
                <td>$user_points</td>
            </tr>
        ";
        $order += 1;
    }

    $html = $html."</table>";

    return $html;

}

function get_user_id_from_song_id($db_connect, $song_id){
    $sql = "SELECT użytkownicy.Id FROM użytkownicy JOIN piosenki ON piosenki.Id_uzytkownika = użytkownicy.Id WHERE piosenki.Id = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $song_id);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();
    
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

function get_top_results_of_edition($db_connect, $edition){
    //pobieram rezultaty
    $sql = "SELECT * FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id WHERE edycje.Nr_Edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false) return [];
    if($response->num_rows == 0){
        $response->close();
        return [];
    }

    //pobranie listy piosenek bioracych udzial w edycji
    $sql = "SELECT DISTINCT piosenki.* FROM piosenki JOIN edycje ON piosenki.id_edycji = edycje.id WHERE edycje.Nr_edycji = ?";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false) return [];
    if($response->num_rows == 0){
        $response->close();
        return [];
    }

    $songs_results = [];
    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $id = $row['Id'];
        $songs_results["$id"] = array("song" => $row , "result" => 0);
    }
    $response->close();
    
    
    //pobranie wynikow glosowania - pierwsze miejsca
    $sql = "SELECT Id_piosenki_1, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
                WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_1";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false) return [];
    if($response->num_rows == 0){
        $response->close();
        return [];
    }

    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $id = $row['Id_piosenki_1'];
        $how_many = $row['how_many'];
        $scores = $how_many * 5;
    
        $songs_results["$id"]["result"] += $scores;
    }   
    $response->close();
        
    //pobranie wynikow glosowania - drugie miejsce
    $sql = "SELECT Id_piosenki_2, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
                WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_2";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false) return [];
    if($response->num_rows == 0){
        $response->close();
        return [];
    }

    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $id = $row['Id_piosenki_2'];
        $how_many = $row['how_many'];
        $scores = $how_many * 3;
    
        $songs_results["$id"]["result"] += $scores;
    }   
    $response->close(); 
    
    //pobranie wynikow glosowania - trzecie miejsce
    $sql = "SELECT Id_piosenki_3, COUNT(*) as how_many FROM głosowanie JOIN edycje ON głosowanie.Id_edycji = edycje.Id
                WHERE edycje.Nr_edycji = ? GROUP BY Id_piosenki_3";
    $stmt = $db_connect->prepare($sql);
    $stmt->bind_param("i", $edition);
    $stmt->execute();
    $response = $stmt->get_result();
    $stmt->close();

    if($response == false) return [];
    if($response->num_rows == 0){
        $response->close();
        return [];
    }

    for($i=0; $i<$response->num_rows; $i++){
        $row = $response->fetch_assoc();
        $id = $row['Id_piosenki_3'];
        $how_many = $row['how_many'];
        $scores = $how_many * 1;
    
        $songs_results["$id"]["result"] += $scores;
    }   
    $response->close(); 
    
    //sortowanie rezultatow
    usort($songs_results,'compare_songs_results');

    return $songs_results;

}

function compare_songs_results($first, $second){
    return $first['result'] < $second['result'];
}


?>