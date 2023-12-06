<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    require_once "../php_scripts/db_config.php";
    require_once "../php_scripts/api.php";
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
    <link rel="stylesheet" href="../css/navigation.css" />
    <link rel="stylesheet" href="../css/general.css" />
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
            <div class="nav_profile">
                <img src="../assets/user.png" alt="avatar" />
                <button class="nav_profile_button">
                    <a href="../php_scripts/logout.php">
                        <?php
                            if(isset($_SESSION['login'])){
                                echo $_SESSION['login']['nickname'];
                            }
                        ?>
                    </a>
                    <img src="../assets/down.png"/>
                </button>
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

                for($i=1; $i <= $result->num_rows; $i++){
                    //pobieranie informacji o edycji
                    $row = $result->fetch_assoc();
                    $nr_edycji = $row['Nr_edycji'];
                    $data_zgloszen = $row['Zgloszenia'];
                    $data_glosowania = $row['Glosowanie'];
                    $data_wynikow = $row['Wyniki'];
                    $_SESSION['edition'] = $nr_edycji;

                ?>
                    <div class="edition">
                        <div class="title"><h1>Edycja nr <?php echo $nr_edycji ?></h1></div>

                        <div class="line"></div>

                        <div class="subtitle"><h2>Terminarz</h2></div>
                        <section class="schedule">
                            <p>Termin zgłoszeń: <?php echo $data_zgloszen?> </p>
                            <p>Termin głosowania: <?php echo $data_glosowania?></p>
                            <p>Termin wyników: <?php echo $data_wynikow?></p>
                        </section>

                        <div class="line"></div>

                        <div class="subtitle"><h2>Zgłoszenia</h2></div>
                        <section class="participation_form"> 
                            <?php 
                            if(does_user_participate($db_connect, $_SESSION['login']['nickname'],$nr_edycji)){
                                echo "<h3>Zgłosiłeś już swoją propozycję</h3>";
                            }else{
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
                            }
                            ?>
                            
                        </section>

                        <section class="participants">
                            <?php
                                $participants = get_all_participants($db_connect,$nr_edycji);
                                for($i = 0; $i < count($participants); $i++){
                                    $participant = $participants[$i];
                                    $nickname = $participant['nickname'];
                                    $singer = $participant['singer'];
                                    $title = $participant['title'];
                                    $link = $participant['link'];

                                    echo<<< ENDL
                                    <div class="participant">
                                        <p>$nickname</p>
                                        <p>$singer</p>
                                        <p>$title</p>
                                        <a href="$link">Posłuchaj</a>
                                    </div>
                                    ENDL;
                                }
                            ?>

                        </section>

                        <div class="line"></div>

                        <div class="subtitle"><h2>Głosowanie</h2></div>
                        <section class="voting">
                            <?php echo show_voting($db_connect, $_SESSION['login']['nickname'], $nr_edycji); ?>
                        </section>

                        <div class="line"></div>

                        <div class="subtitle"><h2>Wyniki</h2></div>
                        <section class="results">
                            <?php echo show_results($db_connect,$nr_edycji); ?>
                        </section>
                    </div>
                <?php 
                    }
                    $result->close();
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