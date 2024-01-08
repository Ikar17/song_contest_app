<?php

    require_once "./db_config.php";
    require_once "./sanitize_validate.php";
    session_start();

    $login = $_POST['login'];
    $password = $_POST['password'];
    $password_again = $_POST['password_again'];
    $mail = $_POST['mail'];

    $login = sanitize_string($login);
    $password = sanitize_string($password);
    $password_again = sanitize_string($password_again);
    $mail = sanitize_email($mail);

    //validation
    $validation_ok = false;
    if(is_validate_email($mail)){
        $validation_ok = true;
    }

    if($password != $password_again){
        $_SESSION['password_error'] = "Podane hasła są różne";
        $validation_ok = false;
    }


    //paswword hash
    $password_h = password_hash($password, PASSWORD_DEFAULT);

    //connect to db
    if($validation_ok){
        try{
            $db_connect = new mysqli($host, $db_user, $db_password, $db_name);
            if($db_connect->connect_errno != 0) throw new Exception("Database connection error");

            //check out login is unique
            $sql = "SELECT id FROM użytkownicy WHERE Nickname= ? ";

            $stmt = $db_connect->prepare($sql);
            $stmt->bind_param("s",$login);
            $stmt->execute();       
            $result = $stmt->get_result();
            $stmt->close();

            if($result == false){
                $db_connect->close();
                throw new Exception("Select query error");
            }

            if($result->num_rows == 0){
                $result->close();
                //insert new user
                $user_role_id = 2;
                $sql = "INSERT INTO użytkownicy VALUES(NULL, ?, ?, ?, ?)";
                $stmt = $db_connect->prepare($sql);
                $stmt->bind_param("sssi",$login, $mail, $password_h, $user_role_id);
                $stmt->execute();       
                $result = $stmt->get_result();
                $stmt->close();

                $_SESSION['register_ok'] = true;

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