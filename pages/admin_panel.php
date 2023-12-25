<?php 
    session_start();

    if(!isset($_SESSION['login']) || $_SESSION['login']['role'] != "Admin"){
        header("Location: ./home_page.php");
        exit();
    }

    require_once "../php_scripts/db_config.php";
    require_once "../php_scripts/admin_api.php";
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
    <link rel="stylesheet" href="../css/admin_panel.css?v=<?php echo time(); ?>" />
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
            <h1>Panel administracyjny</h1>   
        </div>      
    </section>

    <main>
        <section class="container add_edition">
            <div class="headline">
                <img src="../assets/add-button.png" alt="add edition icon"/>
                <h2>Dodaj nową edycję </h2>
            </div>
            
            <form action="../php_scripts/add_new_edition.php" method="POST">
                <table>
                    <tr>
                        <th>Start zgłoszeń</th>
                        <td><input type="datetime-local" name="participant_deadline" id="participant_deadline"></td>
                    </tr>
                    <tr>
                        <th>Start głosowania <small>(równoznaczne z zakończeniem przyjmowania zgłoszeń)</small></th>
                        <td><input type="datetime-local" name="voting_deadline" id="voting_deadline"> </td>
                    </tr>
                    <tr>
                        <th>Data udostępnienia wyników <small>(równoznaczne z zakończeniem głosowania)</small></th>
                        <td><input type="datetime-local" name="result_deadline" id="result_deadline"></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Dodaj nową edycję"></td>
                    </tr>
                </table>
                
            </form>
            <?php
                if(isset($_SESSION['add_edition_error'])){
                    $statement = $_SESSION['add_edition_error'];
                    echo "<div class='error'><p> $statement </p></div>";
                    unset($_SESSION['add_edition_error']);
                }
            ?>
        </section>
        <section class="container update_edition">
            <div class="headline">
                <img src="../assets/edit.png" alt="edit edition icon"/>
                <h2>Zaktualizuj edycję </h2>
            </div>
            <div class="update_edition_search">
                <form method="POST" action="./admin_panel.php">
                    <label for="select_edition">Wybierz edycję: </label>
                    <select id="select_edition" name="select_edition">
                        <?php echo show_editions($db_connect); ?>
                    </select>
                    <input type="submit" value="Potwierdź">
                </form>
            </div>
            
            </br></br>

            <div>
            <?php 
                if(isset($_POST['select_edition'])){
                    echo show_editable_edition($db_connect, $_POST['select_edition']);
                    unset($_POST['select_edition']);
                }
                if(isset($_SESSION['update_edition_error'])){
                    $statement = $_SESSION['update_edition_error'];
                    echo "<div class='error'><p> $statement </p></div>";
                    unset($_SESSION['update_edition_error']);
                }
            ?>
            </div>

        </section>
       
    </main>
    <footer>

    </footer>
</body>
</html>


<?php 

    //zamykanie połączenie z bazą danych
    $db_connect->close();
?>