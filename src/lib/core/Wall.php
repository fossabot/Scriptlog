<?php
/**
 * Class Wall extends Dashboard
 * 
 * @category Core Class
 * @author   M.Noermoehammad
 * @license  MIT
 * @version  1.0
 * @since    Since Release 1.0
 * 
 */
class Wall extends Dashboard
{

/**
 * list Items
 * 
 * @method listItems()
 * 
 */
 public function listItems()
 {
   
   if(true === is_non_administrator()) {

     if (isset(Session::getInstance()->scriptlog_session_fullname)) {

        $non_admin = Session::getInstance()->scriptlog_session_fullname;

     }

     if (isset($_COOKIE['scriptlog_cookie_fullname'])) {

        $non_admin = $_COOKIE['scriptlog_cookie_fullname'];

     }

     $this->welcomeUser('Hello '.safe_html($non_admin));

   } else {

     if (isset(Session::getInstance()->scriptlog_session_login)) {

         $administrator = Session::getInstance()->scriptlog_session_login;

     } 

     if (isset($_COOKIE['scriptlog_cookie_login'])) {

        $administrator = $_COOKIE['scriptlog_cookie_login'];

     }

     $this->welcomeAdmin('Hello '.safe_html($administrator));

   }

 }

/**
 * Detail item
 * 
 * @param integer $id
 * 
 */
 public function detailItem($id)
 {

 }

/**
 * Welcome Admin
 * 
 * @param string $pageTitle
 * 
 */
 public function welcomeAdmin($pageTitle)
 {
   $this->setView('home-admin');
   $this->setPageTitle($pageTitle);
   $this->view->set('pageTitle', $this->getPageTitle());
   return $this->view->render();
 }

/**
 * Welcome User
 * 
 * @param string $pageTitle
 * 
 */
 public function welcomeUser($pageTitle)
 {
   $this->setView('home-user');
   $this->setPageTitle($pageTitle);
   $this->view->set('pageTitle', $this->getPageTitle());
   return $this->view->render();
 }

}