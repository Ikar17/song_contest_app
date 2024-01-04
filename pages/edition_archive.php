<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header('Location: ../index.php');
        exit();
    }

    require_once "../php_scripts/db_config.php";
    require_once "../php_scripts/archive_api.php";
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
    <link rel="stylesheet" href="../css/archive.css?v=<?php echo time(); ?>" />
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
            <h1>ARCHIWUM EDYCJI</h1>   
        </div>      
    </section>

    <main>
        <div class="container">
            <div>
                <form method="POST" action="./edition_archive.php">
                    <label for="select_edition">Wybierz edycję: </label>
                    <select id="select_edition" name="select_edition_archive">
                        <?php echo show_editions_options($db_connect); ?>
                    </select>
                    <input type="submit" value="Potwierdź">
                </form>
            </div>
            <div class="edition_search_results">
                <?php 
                    if(isset($_POST['select_edition_archive'])){
                        echo show_edition($db_connect, $_POST['select_edition_archive']);
                        unset($_POST['select_edition_archive']);
                    }
                ?>                  
            </div>
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