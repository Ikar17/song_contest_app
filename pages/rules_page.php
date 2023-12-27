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
    <link rel="stylesheet" href="../css/rules.css?v=<?php echo time(); ?>" />
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
            <h1>REGULAMIN</h1>   
        </div>      
    </section>

    <main>
        <div class="container">
            <p><img src="../assets/number-one.png" alt="number one image"/> Użytkownik w sekcji "Aktywna edycja" może zgłosić jedną piosenkę, która nie wystąpiła w żadnej poprzedniej edycji. </p>
            <p><img src="../assets/two.png" alt="number two image"/> W każdej edycji przewidziane jest głosowanie. W głosowaniu w danej edycji może wziąć udział użytkownik, który zgłosił swoją
                propozycję. Uczestnik nie może głosować na swoją propozycję. Uczestnik wybiera swoje TOP3 edycji, piosenki które uważa za najlepsze w danej edycji.
                Piosenki nie mogą powtarzać się </p>
            <p><img src="../assets/number-3.png" alt="number three image"/> Po zakończeniu głosowania publikowane są wyniki. Wyniki są ustalane jako suma punktów głosowań użytkowników na dane piosenki.
                Użytkownik ustalając swoje TOP3 przyznaje danej piosence punkty: 1 miejsce - 5pkt, 2 - miejsce - 3 pkt, 3 miejsce - 1pkt. </p>
            <p><img src="../assets/number-four.png" alt="number four image"/> W danym momencie może być aktywna tylko jedna edycja. Termin zgłaszania piosenek w jednej edycji nie może być zawarty w terminie zgłaszania
                i głosowania innej edycji. Termin głosowania w jednej edycji nie może również być zawarty w terminie zgłaszania i głosowania innej edycji. </p>
            <p><img src="../assets/number-5.png" alt="number five image"/> Na podstawie wyników edycji tworzony jest ranking najlepszych użytkowników. Osoba która zgłosiła piosenkę w danej edycji i następnie zajęła 1 miejsce
                otrzymuje do rankingu 5 pkt, 2 miejsce - 3 pkt, 3 miejsce - 1pkt. </p>

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