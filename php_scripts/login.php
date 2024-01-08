<?php

    if(!isset($_POST['login']) || !isset($_POST['password'])){
        header('Location: ../pages/login_page.php');
        exit();
    }

    require_once "db_config.php";
    require_once "sanitize_validate.php";

    session_start();

    $connect =  @new mysqli($host, $db_user, $db_password, $db_name);

    if($connect->connect_errno == 0){
        $login = $_POST['login'];
        $password = $_POST['password'];

        $login = sanitize_string($login);
    
        $sql = "SELECT Nickname, Haslo, role.Nazwa as Rola FROM użytkownicy 
                JOIN role ON role.Id = użytkownicy.id_rola WHERE Nickname = ?";
        
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("s",$login);
        $stmt->execute();       
        $result = $stmt->get_result();
        $stmt->close();

        if($result){

            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                if(password_verify($password, $row['Haslo'])){
                    $_SESSION['login'] = [
                        "nickname" => $row['Nickname'],
                        "role" => $row['Rola']
                    ];
                    header('Location: ../pages/home_page.php');
                }else{
                    $_SESSION['login_error'] = '<span style="color:red">Niepoprawne dane logowania</span>';
                    header('Location: ../pages/login_page.php');
                }
            }else{
                $_SESSION['login_error'] = '<span style="color:red">Niepoprawne dane logowania</span>';
                header('Location: ../pages/login_page.php');
            }

            $result->free_result();

        }else{
            echo "Błąd składni polecenia";
        }

        $connect->close();

    }else{
        echo "Błąd połączenia z bazą danych";
    }

?>