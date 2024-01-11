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
    <link rel="stylesheet" href="../css/rankings.css?v=<?php echo time(); ?>" />
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
            <h1>RANKING UŻYTKOWNIKÓW</h1>   
        </div>      
    </section>

    <main>
        <div class="container container_ranking">
            <?php echo show_users_ranking($db_connect); ?>
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