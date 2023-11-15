<?php
    session_start();

    if(isset($_SESSION['logged']) && $_SESSION['logged'] == true){
        header('Location: ./home_page.php');
        exit();
    }
?>

<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../css/start_page.css" />
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
                    <a href="./login_page.php">Zaloguj się</a>
                </button>
                <button class="primary_button">
                    <a href="./register_page.php">Zarejestruj się</a>
                </button>
            </div>
        </div>
    </nav>
    <main>
        <div class="container main_content main_content--form">
            <div class="main_desc main_desc--form">
                <h2>Rejestracja</h2>
                <form action="../php_scripts/register.php" method="post">
                    <label for="user_name">Nazwa użytkownika:</label><br>
                    <input type="text" id="user_name" name="login"><br>

                    <?php
                    if(isset($_SESSION['login_error'])){
                        echo "<span style='color: red'>".$_SESSION['login_error']."</span>";
                        unset($_SESSION['login_error']);
                    }
                    ?>

                    <label for="user_mail">Email:</label><br>
                    <input type="email" id="user_mail" name="mail"><br>

                    <label for="user_pass">Hasło:</label><br>
                    <input type="password" id="user_pass" name="password"><br>

                    <label for="user_pass_again">Powtórz hasło:</label><br>
                    <input type="password" id="user_pass_again" name="password_again"><br>

                    <input type="submit" value="Zarejestruj się" class="primary_button">
                </form>

                <?php
                    if(isset($_SESSION['register_ok'])){
                        echo "<span style='color: green'>Rejestracja przebiegła pomyślnie</span>";
                        unset($_SESSION['register_ok']);
                    }
                ?>

                <h4>Masz już konto?&nbsp
                    <u><a href="./login_page.php">Zaloguj się </a></u>
                </h4>
            </div>
        </div>
    </main>
</body>
</html>