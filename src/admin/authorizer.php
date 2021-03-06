<?php
/**
 * File authorizer.php
 * checking whether session or cookies exists or not
 * 
 * @category checking whether cookies or session exists or not
 * @author   Vincy vincy@gmail.com
 * @see     https://phppot.com/php/secure-remember-me-for-login-using-php-session-and-cookies/
 * @see     https://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
 * @see     https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 */

$timeout = 60 * 30;
$current_date = date("Y-m-d H:i:s", time()); 
$fingerprint  = hash_hmac('sha256', $_SERVER['HTTP_USER_AGENT'], hash('sha256', $ip, true));
$loggedIn = false;

if ((isset(Session::getInstance()->scriptlog_last_active) && Session::getInstance()->scriptlog_last_active < (time()-$timeout)) 
    || (isset(Session::getInstance()->scriptlog_fingerprint)) && Session::getInstance()->scriptlog_fingerprint != $fingerprint) {

    do_logout($authenticator);

}

if (!empty(Session::getInstance()->scriptlog_session_id)) {

    $loggedIn = true;

} elseif ((!empty($_COOKIE['scriptlog_cookie_login'])) && (!empty($_COOKIE['scriptlog_validator'])) && (!empty($_COOKIE['scriptlog_selector']))) {  

    $validator_verified = false;
    $selector_verified = false;
    $expired_verified = false;
    
    // retrieve user token info
    $token_info = $authenticator -> findTokenByLogin($_COOKIE['scriptlog_cookie_login'], 0);

    $expected_validator = crypt($_COOKIE['scriptlog_validator'], $token_info['pwd_hash']);
    $correct_validator = crypt($_COOKIE['scriptlog_validator'], $token_info['pwd_hash']);

    $expected_selector = crypt($_COOKIE['scriptlog_selector'], $token_info['selector_hash']);
    $correct_selector = crypt($_COOKIE['scriptlog_selector'], $token_info['selector_hash']);

    if(!function_exists('hash_equals')) {

         if(timing_safe_equals($expected_validator, $correct_validator) == 0) {

              // validate random validator cookie with database
              if(password_verify($_COOKIE['scriptlog_validator'], $token_info['pwd_hash'])) {

                  $validator_verified = true;

              }

         }

         if(timing_safe_equals($expected_selector, $correct_selector) == 0) {

              // Validate random selector cookie with database
              if(password_verify($_COOKIE['scriptlog_selector'], $token_info['selector_hash'])) {

                  $selector_verified = true;

              }

         }

    } else {

         if(hash_equals($expected_validator, $correct_validator)) {

              if(password_verify($_COOKIE['scriptlog_validator'], $token_info['pwd_hash'])) {

                 $validator_verified = true;

              }

         }

         if(hash_equals($expected_selector, $correct_selector)) {

             if(password_verify($_COOKIE['scriptlog_selector'], $token_info['selector_hash'])) {

                 $selector_verified = true;

             }

         }

    }
    
    // check cookie expiration by date
    if ($token_info['expired_date'] >= $current_date) {
        $expired_verified = true;
    }

    /** 
     * Redirect if all cookies based validation return true
     * Else, mark the token as expired and clear cookies
     */

    if ((!empty($token_info['ID'])) && $validator_verified && $selector_verified && $expired_verified ) {

        $loggedIn = true;

    } else {

         if (!empty($token_info['ID'])) {

             $userToken -> updateTokenExpired($token_info['ID']);

         }

         // clear cookies
         $authenticator -> clearAuthCookies($token_info['user_login']);
         
    } 

}