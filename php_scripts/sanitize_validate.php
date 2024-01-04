<?php

function sanitize_string($text){
    if(is_string($text) == false) return null;
    $text = trim($text);
    $text = htmlspecialchars($text);
    return $text;
}

function sanitize_email($email){
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = htmlspecialchars($email);
    return $email;
}

function sanitize_url($url){
    $url = filter_var($url, FILTER_SANITIZE_URL);
    $url = htmlspecialchars($url);
    return $url;
}

function is_validate_date($date){
    if(strtotime($date) == false) return false;
    return true;
}

function is_validate_email($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_validate_url($url){
    return filter_var($url, FILTER_VALIDATE_URL);
}

function is_validate_edition($edition){
    return is_numeric($edition);
}

?>