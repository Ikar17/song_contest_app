<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    require_once "../php_scripts/db_config.php";
    require_once "../php_scripts/user_api.php";
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
    <title>Song Contest</title>
</head>
<body>
    <nav>
        <div class="container nav_content">
            <a href="./home_page.php" class="nav_logo">
                <img src="../assets/logo.png" alt="logo" />
                <h1>Song Contest</h1>
            </a>
            <div class="nav_links">
                <div class="links_to_pages">
                    <a href="./home_page.php">
                        Strona główna
                    </a>
                    <?php
                        if($_SESSION['login']['role'] == "Admin"){
                            echo <<< ENDL
                            <a href="./admin_panel.php">
                                Panel admina
                            </a>
                            ENDL;
                        }
                    ?>
                </div>
                <div class="nav_profile">
                    <img src="../assets/user.png" alt="avatar" />
                    <button class="nav_profile_button">
                        <?php
                            if(isset($_SESSION['login'])){
                                echo $_SESSION['login']['nickname'];
                            }
                        ?>
                        <a href="../php_scripts/logout.php">Wyloguj się</a>
                    </button>
                </div>
            </div>
            
        </div>
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
                            else if(does_user_participate($db_connect, $_SESSION['login']['nickname'],$nr_edycji)){
                                echo "<h3>Zgłosiłeś już swoją propozycję</h3>";
                            }
                            else if(date('Y-m-d H:i:s') < $data_zgloszen->format('Y-m-d H:i:s')){
                                echo "<h3>Zgłoszenia nie są jeszcze otwarte</h3>";
                            }
                            else{
                                echo<<<ENDL
                                <form method="POST" action="../php_scripts/add_participant.php">
                                    <label for="name">Wykonawca piosenki</label>
                                    <input type="text" id="name" name="author">
                                    <label for="songTitle">Tytuł piosenki</label>
                                    <input type="text" id="songTitle" name="songTitle">
                                    <label for="songUrl">Link piosenki</label>
                                    <input type="url" id="songUrl" name="songUrl" >
                                    <input type="submit" value="Dodaj zgłoszenie" >
                                </form>
                                ENDL;

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
                                        </tr>
                                        ENDL;
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

    </footer>
</body>
</html>


<?php 

    //zamykanie połączenie z bazą danych
    $db_connect->close();
?>