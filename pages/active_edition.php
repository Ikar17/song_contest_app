<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    require_once "../php_scripts/db_config.php";
    require_once "../php_scripts/user_api.php";

    if(isset($_POST['delete'])){
        header('Location: ../php_scripts/delete_participant.php');
        exit();
    }

    try{
        $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
        if($db_connect->connect_errno != 0) throw new Exception("Database connection error");

    }catch(Exception $e){
        echo $e;
        exit();
    }

?>

<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../css/navigation.css?v=<?php echo time(); ?>"/>
    <link rel="stylesheet" href="../css/general.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/activeEdition.css?v=<?php echo time(); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet"> 
    <script src="../js/script.js?v=<?php echo time(); ?>"></script>  
    <title>Song Contest</title>
</head>
<body>
    <nav>
        <?php 
            require_once "../php_scripts/navigation.php";
            echo get_navigation();
        ?>
    </nav>

    <section class="website_title">
        <div>
            <h1>AKTUALNA EDYCJA</h1>   
        </div>      
    </section>

    <main class="editions_container">
        <div class="container">

            <?php

                //pobieranie aktywnych edycji
                $sql = "SELECT * FROM edycje WHERE status=1";
                $result = $db_connect->query($sql);
                if($result == false){
                    $db_connect->close();
                    echo "Błąd serwera";
                    exit();
                }

                if($result->num_rows == 0){
                    echo "Brak aktywnej edycji";
                }else{
                    //pobieranie informacji o edycji
                    $row = $result->fetch_assoc();
                    $nr_edycji = $row['Nr_edycji'];
                    $data_zgloszen = new DateTime($row['Zgloszenia']);
                    $data_glosowania = new DateTime($row['Glosowanie']);
                    $data_wynikow = new DateTime($row['Wyniki']);
                    $_SESSION['edition'] = $nr_edycji;

                ?>
                    <div class="edition">
                        <div class="title"><h1>Edycja nr <?php echo $nr_edycji ?></h1></div>

                        <div class="line"></div>

                        <div class="subtitle">
                            <img src="../assets/calendar.png" alt="calendar icon" />
                            <h2>Terminarz</h2>
                        </div>
                        <section class="schedule">
                            <table>
                                <tr>
                                    <th>Start zgłoszeń:</th>
                                    <td><?php echo $data_zgloszen->format('d-m-Y H:i')?></td>
                                </tr>
                                <tr>
                                    <th>Start głosowania:</th>
                                    <td><?php echo $data_glosowania->format('d-m-Y H:i')?></td>
                                </tr>
                                <tr>
                                    <th>Wyniki:</th>
                                    <td><?php echo $data_wynikow->format('d-m-Y H:i')?></td>
                                </tr>
                            </table>
                        </section>

                        <div class="line"></div>

                        <div class="subtitle">
                            <img src="../assets/join.png" alt="join to contest icon" />
                            <h2>Zgłoszenia</h2>
                        </div>
                        <section class="participation_form"> 
                            <?php 
                            if(date('Y-m-d H:i:s') >= $data_glosowania->format('Y-m-d H:i:s')){
                                echo "<h3>Przyjmowanie zgłoszeń zostało zamknięte</h3>";
                            }
                            else if(!isset($_POST['edit']) && does_user_participate($db_connect, $_SESSION['login']['nickname'],$nr_edycji)){
                                echo "<h3>Zgłosiłeś już swoją propozycję</h3>";
                            }
                            else if(date('Y-m-d H:i:s') < $data_zgloszen->format('Y-m-d H:i:s')){
                                echo "<h3>Zgłoszenia nie są jeszcze otwarte</h3>";
                            }
                            else{
                                if(isset($_POST['edit']) && isset($_POST['singer']) && isset($_POST['title']) && isset($_POST['link']) ){
                                    $singer_edit = $_POST['singer'];
                                    $title_edit = $_POST['title'];
                                    $link_edit = $_POST['link'];
                                    echo<<<ENDL
                                    <form method="POST" action='../php_scripts/update_participant.php'>
                                        <label for="name">Wykonawca piosenki</label>
                                        <input type="text" id="name" name="singer" value='$singer_edit'>
                                        <label for="songTitle">Tytuł piosenki</label>
                                        <input type="text" id="songTitle" name="title" value='$title_edit'>
                                        <label for="songUrl">Link piosenki</label>
                                        <input type="url" id="songUrl" name="link" value='$link_edit'>
                                        <input type="submit" class='button button--large button--primary' value="Zaktualizuj" >
                                    </form>
                                    ENDL; 
                                }else{
                                    echo<<<ENDL
                                    <form method="POST" action="../php_scripts/add_participant.php">
                                        <label for="name">Wykonawca piosenki</label>
                                        <input type="text" id="name" name="author">
                                        <label for="songTitle">Tytuł piosenki</label>
                                        <input type="text" id="songTitle" name="songTitle">
                                        <label for="songUrl">Link piosenki</label>
                                        <input type="url" id="songUrl" name="songUrl" >
                                        <input type="submit" class='button button--large button--primary' value="Dodaj zgłoszenie" >
                                    </form>
                                    ENDL;
                                }

                                if(isset($_SESSION["add_participant_error"])){
                                    $statement = $_SESSION["add_participant_error"];
                                    echo "<span style='color:red'> $statement </span>";
                                    unset($_SESSION["add_participant_error"]);
                                }
                            }
                            ?>
                            
                        </section>

                        <section class="participants">
                            <table>
                                <tr>
                                    <th> L.p </th>
                                    <th>Użytkownik</th>
                                    <th>Wykonawca</th>
                                    <th>Tytuł</th>
                                    <th>Link</th>
                                </tr>
                                <?php
                                    $participants = get_all_participants($db_connect,$nr_edycji);
                                    for($x = 0; $x < count($participants); $x++){
                                        $participant = $participants[$x];
                                        $nickname = $participant['nickname'];
                                        $singer = $participant['singer'];
                                        $title = $participant['title'];
                                        $link = $participant['link'];

                                        $ordinal_number = $x + 1;
                                        
                                        if($nickname == $_SESSION['login']['nickname'] && $data_glosowania->format('d-m-Y H:i') > date('d-m-Y H:i')){
                                            echo<<< ENDL
                                            <tr>
                                            <form method='POST' action='active_edition.php'>
                                                <td>$ordinal_number</td>
                                                <td>$nickname</td>
                                                <td>
                                                    $singer
                                                    <input name='singer' value='$singer' hidden/>
                                                </td>
                                                <td>
                                                    $title
                                                    <input name='title' value='$title' hidden/> 
                                                </td>
                                                <td>
                                                    <a href="$link">
                                                        <img src="../assets/youtube.png"/>
                                                        Posłuchaj
                                                    </a>
                                                    <input name='link' value='$link' hidden/> 
                                                </td>
                                                <td class='song_edit_delete'>
                                                    <input type='submit' class='button button--medium button--primary' name='edit' value='Edytuj' />
                                                    <input type='submit' class='button button--medium button--delete' name='delete' value='Usuń' />
                                                </td>
                                            </form>                                          
                                            ENDL;
                                        }else{
                                            echo<<< ENDL
                                            <tr>
                                                <td>$ordinal_number</td>
                                                <td>$nickname</td>
                                                <td>$singer</td>
                                                <td>$title</td>
                                                <td>
                                                    <a href="$link">
                                                        <img src="../assets/youtube.png"/>
                                                        Posłuchaj
                                                    </a>
                                                </td>
                                        ENDL;
                                        }
                                        echo "</tr>";
                                    }
                                ?>
                            </table>
                        </section>

                        <div class="line"></div>

                        <div class="subtitle">
                            <img src="../assets/vote.png" alt="voting icon" />
                            <h2>Głosowanie</h2>
                        </div>
                        <section class="voting">
                            <?php echo show_voting($db_connect, $_SESSION['login']['nickname'], $nr_edycji, $data_glosowania,$data_wynikow); 
                                  
                                  if(isset($_SESSION['voting_error'])){
                                    $statement = $_SESSION['voting_error'];
                                    echo "<span style='color:red'> $statement </span>";
                                    unset($_SESSION['voting_error']);
                                  }
                            
                            ?>
                        </section>

                        <div class="line"></div>

                        <div class="subtitle">
                            <img src="../assets/ranking_c.png" alt="results icon" />
                            <h2>Wyniki</h2>
                        </div>

                        <section class="results">
                            <?php echo show_results($db_connect,$nr_edycji, $data_wynikow); ?>
                        </section>
                    </div>
                <?php 
                    $result->close();
                    }
                ?>
        </div>
    </main>
    <footer>
        <p><span style="font-size: 130%;">&copy;</span>2024 SongContest </p>
    </footer>
</body>
</html>


<?php 

    //zamykanie połączenie z bazą danych
    $db_connect->close();
?>