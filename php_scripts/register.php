<?php

    require_once "./db_config.php";
    session_start();

    $login = $_POST['login'];
    $password = $_POST['password'];
    $password_r = $_POST['password_again'];
    $mail = $_POST['mail'];

    //validation
    $validation_ok = true;



    //paswword hash
    $password_h = password_hash($password, PASSWORD_DEFAULT);

    //connect to db
    if($validation_ok){
        try{
            $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
            if($db_connect->connect_errno != 0) throw new Exception("Database connection error");

            //check out login is unique
            $sql = "SELECT id FROM użytkownicy WHERE Nickname='$login'";
            $result = $db_connect->query($sql);
            if($result == false){
                $db_connect->close();
                throw new Exception("Select query error");
            }

            if($result->num_rows == 0){
                $result->close();
                //insert new user
                $sql = "INSERT INTO użytkownicy VALUES(NULL, '$login', '$mail', '$password_h', 2)";
                if($db_connect->query($sql)){
                    $_SESSION['register_ok'] = true;
                }else{
                    $db_connect->close();
                    throw new Exception("Insert query error");
                }
            }else{
                $_SESSION['login_error'] = "Istnieje już użytkownik o podanej nazwie";
            }

            $db_connect->close();
            header("Location: ../pages/register_page.php");
        }catch(Exception $e){
            echo $e;
            exit();
        }
    }
?>