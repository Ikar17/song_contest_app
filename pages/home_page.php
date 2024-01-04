<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }
?>

<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../css/general.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/navigation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/home_page.css?v=<?php echo time(); ?>" />
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
                    <a href="./rules_page.php">
                        <div class="tile">
                            <img src="../assets/rules_white.png" />
                            <h3>Regulamin</h3>
                        </div>
                    </a>
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