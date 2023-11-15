<?php
    session_start();

    if(isset($_SESSION['logged']) && $_SESSION['logged'] == true){
        header('Location: ./pages/home_page.php');
        exit();
    }

?>
<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="./css/start_page.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet"> 
    <title>Song Contest</title>
</head>
<body>
    <nav>
        <div class="container nav_content">
            <a href="index.php" class="nav_logo">
                <img src="./assets/logo.png" alt="logo" />
                <h1>Song Contest</h1>
            </a>
            <div>
                <button class="primary_button">
                    <a href="./pages/login_page.php">Zaloguj się</a>
                </button>
                <button class="primary_button">
                    <a href="./pages/register_page.php">Zarejestruj się</a>
                </button>
            </div>
        </div>
    </nav>
    <main>
        <div class="container main_content">
            <div class="main_desc">
                <h2>
                    Muzyczny konkurs internetowy, w którym rywalizujesz z innymi o najlepszą piosenkę
                </h2>
                <button class="primary_button">
                    <a href="./pages/register_page.php">Dołącz już teraz</a>
                </button>
            </div>
        </div>
    </main>
</body>
</html>