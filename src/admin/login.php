<?php
/**
 * login.php
 * login functionality 
 * to access control panel or administrator page
 * 
 * @category login.php file
 * @author   M.Noermoehammad
 * @license  MIT
 * @version  1.0
 * @since    Since Release 1.0
 * 
 */
$ip = (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : zend_ip_address();

if (file_exists(__DIR__ . '/../config.php')) {
    
  include __DIR__ . '/../lib/main.php';
  require __DIR__ . '/authorizer.php';
  include __DIR__ . '/login-layout.php';

  $stylePath =  preg_replace("/\/login\.php.*$/i", "", app_url().DS.APP_ADMIN);

} else {

   header("Location: ../install");
   exit();
  
}

if ($loggedIn) {

   direct_page('index.php?load=dashboard', 302);
   
}

if(isset($_POST['LogIn']) && $_POST['LogIn'] == 'Login') {

  safe_human_login($_POST);
  
  list($errors, $failed_login) = processing_human_login($ip, $authenticator, $errors, $_POST);   

}

login_header($stylePath);

?>

<div class="login-logo">
  <a href="#"><img class="d-block mx-auto mb-4" src="<?=$stylePath; ?>/assets/dist/img/icon612x612.png" alt="Log In" width="72" height="72"></a>
</div>
<div class="login-box-body">  

<?php 
  if (isset($errors['errorMessage'])) : 
?>

<div class="alert alert-danger alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <?= $errors['errorMessage']; ?>
</div>

<?php 
    endif; 

  if (isset($_GET['status']) && $_GET['status'] == 'changed') {
  
      echo '<div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>The password has been ' . htmlspecialchars($_GET['status']) . '. Please enter with your new password!</div>';
   
  } elseif (isset($_GET['status']) && $_GET['status'] == 'actived') {
 
      echo '<div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>The account has been ' . htmlspecialchars($_GET['status']) . '. Pleas log in with your email and password!</div>';
 
  }

?>

<form name="formlogin" action="login.php" method="post" onSubmit="return validasi(this)"  role="form">
<div class="form-group has-feedback">
<label>Username or Email Address</label>
<input type="text"  class="form-control" placeholder="username or email" name="login" maxlength="186" value="
<?php if (isset($_COOKIE['scriptlog_cookie_login'])) : echo $_COOKIE['scriptlog_cookie_login'];
elseif (isset($_COOKIE['scriptlog_cookie_email'])) : echo $_COOKIE['scriptlog_cookie_email']; endif; ?>" autocomplete="off" autofocus required>
<span class="glyphicon glyphicon-user form-control-feedback"></span>
</div>
<div class="form-group has-feedback">
<label>Password</label>
<input type="password" class="form-control" placeholder="Password" name="user_pass" maxlength="50" autocomplete="off" value="
<?=(isset($_COOKIE['user_pass'])) ? $_COOKIE['user_pass'] : ""; ?>" required>
<span class="glyphicon glyphicon-lock form-control-feedback"></span>  
</div>
<div class="form-group has-feedback">
<input type="text" name="scriptpot_name" class="form-control scriptpot" autocomplete="off">
</div>
<div class="form-group has-feedback">
<input type="email" name="scriptpot_email" class="form-control scriptpot" autocomplete="off">
</div>

<?php if (isset($failed_login) && $failed_login >= 5) : ?> 

<div class="form-group has-feedback">
<label>Enter captcha code</label>
<input type="text" class="form-control" placeholder="Please type a captcha code here" name="captcha_code">
<span class="glyphicon glyphicon-hand-down form-control-feedback"></span>
<img src="<?=app_url().'/admin/captcha-login.php'; ?>" alt="image_captcha">
</div>

<?php endif; ?>

<div class="row">
  <div class="col-xs-8">
    <div class="checkbox icheck">
      <label>
        <input type="checkbox" name="remember" <?php if (isset($_COOKIE['scriptlog_cookie_login'])) : echo "checked"?> 
        <?php elseif(isset($_COOKIE['scriptlog_cookie_email'])) : echo "checked";?><?php endif; ?>> Remember Me
      </label>
    </div>
</div>          
<div class="col-xs-4">

<?php 
  $block_csrf = generate_form_token('login_form', 32);
?>
    
<input type="hidden" name="csrf" value="<?= $block_csrf; ?>">
  <input type="submit" class="btn btn-primary btn-block btn-flat" name="LogIn" value="Login">
</div>
</div>
</form>
  <a href="reset-password.php" class="text-center">Lost your password?</a>    
</div>
  
<?= login_footer($stylePath); ?>