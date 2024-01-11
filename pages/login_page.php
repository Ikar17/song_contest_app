<?php
    session_start();

    if(isset($_SESSION['login'])){
        header('Location: ./home_page.php');
        exit();
    }
?>


<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../css/general.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/navigation.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/start_page.css?v=<?php echo time(); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet"> 
    <title>Song Contest</title>
</head>
<body>
    <nav>
        <div class="container nav_content">
            <a href="../index.php" class="nav_logo">
                <img src="../assets/logo.png" alt="logo" />
                <h1>Song Contest</h1>
            </a>
            <div>
                <button class="primary_button">
                    <a href="login_page.php">Zaloguj się</a>
                </button>
                <button class="primary_button">
                    <a href="register_page.php">Zarejestruj się</a>
                </button>
            </div>
        </div>
    </nav>
    <main>
        <div class="container main_content main_content--form">
            <div class="main_desc main_desc--form">
                <h2>Logowanie</h2>
                <form action="../php_scripts/login.php" method="post">
                    <label for="login">Nazwa użytkownika:</label><br>
                    <input type="text" id="login" name="login"><br>
                    <label for="password">Hasło:</label><br>
                    <input type="password" id="password" name="password"><br>
                    <input type="submit" value="Zaloguj się" class="primary_button">
                </form>
                <?php
                    if(isset($_SESSION['login_error'])){
                        echo $_SESSION['login_error'];
                        unset($_SESSION['login_error']);
                    }
                ?>
                <h4>Nie masz konta?&nbsp
                    <u><a href="register_page.php">Zarejestruj się </a></u>
                </h4>
            </div>
        </div>
    </main>
    <footer>
        <p><span style="font-size: 130%;">&copy;</span>2024 SongContest </p>
    </footer>
</body>
</html>