<?php

function get_navigation(){
 
$html = "
    <div class='container nav_content'>
    <a href='./home_page.php' class='nav_logo'>
        <img src='../assets/logo.png' alt='logo'/>
        <h1>Song Contest</h1>
    </a>
    <ul class='nav_links'>
        <li>
            <a href='./home_page.php'>
                Strona główna
            </a>
        </li> 
    ";

        if($_SESSION['login']['role'] == 'Admin'){
            $html = $html."
                <li>
                    <a href='./admin_panel.php'>
                        Panel admina
                    </a>
                </li>
                ";
        }

$html = $html."
        <li class='nav_profile'>
            <img src='../assets/user.png' alt='avatar'/>
        ";

        if(isset($_SESSION['login'])){
            $html = $html.$_SESSION['login']['nickname'];
        }

$html = $html."
        </li>
        <li>
            <a href='../php_scripts/logout.php'>
                Wyloguj się
            </a>
        </li>
    </ul>

    <button onclick=showSidebar() class='open_sidebar_button'> </button>

    <!-- Wersja mobilna -->

    <ul id='mobile-sidebar' class='nav_links--mobile'>
        <li class='nav_profile'>
            <img src='../assets/user.png' alt='avatar' />

    ";
                
    if(isset($_SESSION['login'])){
        $html = $html.$_SESSION['login']['nickname'];
    }
 
$html = $html."
        </li>
        <li>
            <a href='./home_page.php'>
                Strona główna
            </a>
        </li>
        ";

    if($_SESSION['login']['role'] == "Admin"){
        $html = $html."
                    <li>
                        <a href='./admin_panel.php'>
                            Panel admina
                        </a>
                    </li>
                ";
    }

$html = $html."
        <li>
            <a href='../php_scripts/logout.php'>
                Wyloguj się
            </a>
        </li>
        <li>
            <button onclick=hideSidebar() class='close_sidebar_button'></button>
        </li>
    </ul>

    </div>";

return $html;
}
?>