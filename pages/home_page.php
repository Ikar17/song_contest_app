<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }
?>

<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../css/general.css" />
    <link rel="stylesheet" href="../css/navigation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/home_page.css" />
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
            
        </div>
    </nav>
    <main>
        <div class="container">
            <div class="main_content">
                <header>
                    <div class="note note-down"></div>
                    <div class="note note-up"></div>
                    <h1>strona główna</h1>
                    <div class="note note-up"></div>
                    <div class="note note-down"></div>
                </header>
                <div class="tiles">
                    <div class="tile">
                        <img src="../assets/rules_white.png" />
                        <h3>Regulamin</h3>
                    </div>
                    <a href="./active_edition.php">
                        <div class="tile">
                            <img src="../assets/player_white.png" />
                            <h3>Aktualna edycja</h3>
                        </div>
                    </a>
                    <a href="./rankings_page.php">
                        <div class="tile">
                            <img src="../assets/ranking_white.png" />
                            <h3>Ranking użytkowników</h3>
                        </div>
                    </a>
                    <a href="./song_archive.php">
                        <div class="tile">
                            <img src="../assets/music_archive_white.png" />
                            <h3>Archiwum piosenek</h3>
                        </div>
                    </a>
                    <a href="./edition_archive.php">
                        <div class="tile">
                            <img src="../assets/edition_archive_white.png" />
                            <h3>Archiwum edycji</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </main>
    <footer>

    </footer>
</body>
</html>