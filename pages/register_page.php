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
                    <a href="login.html">Zaloguj się</a>
                </button>
                <button class="primary_button">
                    <a href="register.html">Zarejestruj się</a>
                </button>
            </div>
        </div>
    </nav>
    <main>
        <div class="container main_content main_content--form">
            <div class="main_desc main_desc--form">
                <h2>Rejestracja</h2>
                <form>
                    <label for="user_name">Nazwa użytkownika:</label><br>
                    <input type="text" id="user_name" name="user_name"><br>

                    <label for="user_mail">Email:</label><br>
                    <input type="email" id="user_mail" name="user_mail"><br>

                    <label for="user_pass">Hasło:</label><br>
                    <input type="password" id="user_pass" name="user_pass"><br>

                    <label for="user_pass_again">Powtórz hasło:</label><br>
                    <input type="password" id="user_pass_again" name="user_pass_again"><br>

                    <input type="submit" value="Zarejestruj się" class="primary_button">
                </form>
                <h4>Masz już konto?&nbsp
                    <u><a href="login.html">Zaloguj się </a></u>
                </h4>
            </div>
        </div>
    </main>
</body>
</html>