<?php
/**
 * Authentication Class
 *
 * @category  Core Class
 * @author    M.Noermoehammad
 * @license   MIT
 * @version   1.0
 * @since     Since Release 1.0
 *
 */
class Authentication
{

  /**
   * user's ID
   * 
   * @var integer
   * 
   */
  private $user_id;

  /**
   * user session
   * 
   * @var mixed
   * 
   */
  private $user_session;
 
  /**
   * File Manager
   * 
   * @var string
   * 
   */
  private $fileManager;

  /**
   * User Agent
   * 
   * @var string
   * 
   */
  private $agent;

  /**
   * User's Email
   * 
   * @var string
   * 
   */
  protected $user_email;

  /**
   * Username for login
   * 
   * @var string
   * 
   */
  protected $user_login;

  /**
   * user nicename
   * 
   * @var string
   * 
   */
  protected $user_fullname;

  /**
   * user level
   * 
   * @var string
   * 
   */
  protected $user_level;

  /**
   * Constant COOKIE_EXPIRE
   * 
   * @var const|numeric
   * 
   */
  const COOKIE_EXPIRE =  2592000;  // default 1 month

  /**
   * Constant COOKIE_PATH
   * 
   */
  const COOKIE_PATH = "/";  //Available in whole domain
 
  public function __construct(UserDao $userDao, UserTokenDao $userToken, FormValidator $validator)
  {
    $this->userDao = $userDao;
    $this->userToken = $userToken;
    $this->validator = $validator;
  }
  
  /**
   * Find User by Email
   * @param string $email
   * @return 
   * 
   */
  public function findUserByEmail($email)
  {
    return $this->userDao->getUserByEmail($email);
  }

  public function findTokenByUserEmail($email, $expired)
  {
    return $this->userToken->getTokenByUserEmail($email, $expired);
  }

  public function findUserByLogin($user_login)
  {
    return $this->userDao->getUserByLogin($user_login);
  }

  /**
   * Is Email Exists
   * 
   * @param string  $email
   * @return boolean
   */
  public function checkEmailExists($email)
  {

    if ($this->userDao->checkUserEmail($email)) {

       return true;

    }

    return false;
    
  }

/**
 * Checking access level
 * 
 * @return boolean 
 * 
 */
 public function accessLevel()
 {
   
    if (isset($_COOKIE['cookie_user_level'])) {

       return $_COOKIE['cookie_user_level'];
    
    }

    if (isset($_SESSION['user_level'])) {

       return $_SESSION['user_level'];
       
    }
      
    return false;

 }
 
/**
 * Login
 * 
 * @method public login()
 * @param array $values
 * 
 */
 public function login(array $values)
 {
    
     $email = (isset($values['user_email'])) ? $values['user_email'] : null;
     $password = (isset($values['user_pass'])) ? $values['user_pass'] : null;
     $remember_me = ($values['remember']) ? $values['remember'] : null;

     $this->validator->sanitize($email, 'email');
     $this->validator->validate($email, 'email');
     $this->validator->validate($password, 'password'); 

     $account_info = $this->findUserByEmail($email);

     $tokenizer = new Tokenizer();
     
     $this->user_id = $_SESSION['user_id'] = $account_info['ID'];
     $this->user_email = $_SESSION['user_email'] = $account_info['user_email'];
     $this->user_level = $_SESSION['user_level'] = $account_info['user_level'];
     $this->user_login = $_SESSION['user_login'] = $account_info['user_login'];
     $this->user_fullname = $_SESSION['user_fullname'] = $account_info['user_fullname'];
     
     $this->agent = $_SESSION['agent'] = sha1(
                    $_SERVER['HTTP_ACCEPT_CHARSET'].
                    $_SERVER['HTTP_ACCEPT_ENCODING'].
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'].
                    $_SERVER['HTTP_USER_AGENT']);

      if (!empty($remember_me)) {
           
           setcookie("cookie_user_email", $this->user_email, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
           setcookie("cookie_user_login", $this->user_login, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
           setcookie("cookie_user_level", $this->user_level, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
           setcookie("cookie_user_fullname", $this->user_fullname, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
           setcookie("cookie_user_id", $this->user_id, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);

           $random_password = $tokenizer -> createToken(16);
           setcookie("random_pwd", $random_password, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);

           $random_selector = $tokenizer -> createToken(32);
           setcookie("random_selector", $random_selector, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);

           $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
           $hashed_selector = password_hash($random_selector, PASSWORD_DEFAULT);
           $expired_date = date("Y-m-d H:i:s", time() + self::COOKIE_EXPIRE);

           $token_info = $this->findTokenByUserEmail($email, 0);

           if (!empty($token_info['ID'])) {

             $updateExpired = $this->userToken->updateTokenExpired($token_info['ID']);

           }

           $bind = ['user_id' => $account_info['ID'], 'pwd_hash' => $hashed_password, 
                   'selector_hash' => $hashed_selector, 'expired_date' => $expired_date];

           $this->userToken->createUserToken($bind);

      } else {

           $this->removeCookies();

      }

      $this->userDao->updateUserSession(regenerate_session(), abs((int)$account_info['ID']));
       
      direct_page('index.php?load=dashboard', 302);
   
 }
 
/**
  * Logout
  */
public function logout()
{
    regenerate_session();
    
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_login']);
    unset($_SESSION['user_fullname']);
    unset($_SESSION['user_level']);
    unset($_SESSION['agent']);
    
    $_SESSION = array();
    
    $this->removeCookies();

    session_destroy();
    
    $logout = APP_PROTOCOL . '://' . APP_HOSTNAME . dirname($_SERVER['PHP_SELF']) . DS;

    header($_SERVER["SERVER_PROTOCOL"]." 302 Found");
    header("Location: $logout");
    exit();
    
}
  
/**
  * Validate User Account
  * 
  * @param string $email
  * @param string $password
  * @return boolean
  */
public function validateUserAccount($email, $password)
{
    
  $result = $this->userDao->checkUserPassword($email, $password);

  if (false === $result) {

      return false;

  }
  
  return true;
    
}

/**
 * Reset user password
 * updating reset key and send notification to user
 * 
 * @param string $email
 * 
 */
public function resetUserPassword($user_email)
{
   
  $reset_key = md5(uniqid(rand(),true));

  if ($this->userDao->updateResetKey($reset_key, $user_email)) {
      
      # send notification to user email account
      reset_password($user_email, $reset_key);
    
  }

}

/**
 * Update new password
 * Recovering user password
 * 
 * @param string $user_pass
 * @param integer $user_id
 * 
 */
public function updateNewPassword($user_pass, $user_id)
{
  $this->validator->sanitize($user_id, 'int');
  $this->validator->validate($user_id, 'number');
  $this->validator->validate($user_pass, 'password');

  $bind = ['user_pass' => $user_pass, 'user_reset_complete' => 'Yes'];

  if ($this->userDao->recoverNewPassword($bind, $user_id)) {
      recover_password($user_pass);
  }

}

/**
 * Remove cookies
 * removing cookies when logging out
 * from administrator page
 * 
 */
public function removeCookies()
{

  if ((isset($_COOKIE['cookie_user_email'])) && (isset($_COOKIE['random_pwd'])) && (isset($_COOKIE['random_selector']))) {

    setcookie("cookie_user_email", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
    setcookie("cookie_user_id", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
    setcookie("cookie_user_level", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
    setcookie("cookie_user_login", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
    setcookie("cookie_user_fullname", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
    setcookie("random_pwd", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);  
    setcookie("random_selector", "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);

  }

  if (ini_get("session.use_cookies")) {
      
      $params = session_get_cookie_params();
      
      setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    
  );

}

}

/**
 * Activate user account
 * user activation
 * 
 * @param string $keys
 * 
 */
public function activateUserAccount($key)
{
  if ($this->userDao->activateUser($key) === false) {
    
    direct_page();

  } else {

    $actived = APP_PROTOCOL . '://' . APP_HOSTNAME . dirname($_SERVER['PHP_SELF']) . '/login.php?status=actived';
    header("Location: $actived");
    exit();
    
  }

}

/**
 * User access control
 * 
 * @param string $control
 * 
 */
public function userAccessControl($control = null)
{

    switch ($control) {

        case ActionConst::NEWUSER:
        case ActionConst::DELETEUSER:  
            
            if(($this->accessLevel() != 'administrator') && ($this->accessLevel() != 'manager')) {

                return false;
            }

            break;

        case ActionConst::PLUGINS:
           
            if($this->accessLevel() != 'administrator') {

                return false;

            }

            break;

        case ActionConst::THEMES:

           if($this->accessLevel() != 'administrator') {

               return false;

           }

            break;

        case ActionConst::CONFIGURATION:

           if($this->accessLevel() != 'administrator') {

             return false;

           }

          break;

        case ActionConst::MEDIALIB:

           if(($this->accessLevel() != 'administrator') && ($this->accessLevel() != 'manager') && ($this->accessLevel() != 'editor') && ($this->accessLevel() != 'author')) {

              return false;

           }

          break;

        case ActionConst::PAGES:

           if(($this->accessLevel() != 'administrator') && ($this->accessLevel() != 'manager')) {

              return false;

           }

          break;
        
        default:
          
            if($this->accessLevel() != 'administrator' && $this->accessLevel() != 'manager' 
               && $this->accessLevel() != 'editor' && $this->accessLevel() != 'author' 
               && $this->accessLevel() != 'contributor') {

              return false;

            }
             
           break;

    }

    return true;

}

}