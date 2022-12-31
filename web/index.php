<?php

class loginSystem
{
  private $db_type = "mysql";

  private $db_mysql_path = "";

  private $db_connection = null;

  private $user_is_logged_in = false;

  public $feedback = "";


  public function __construct()
  {
    if ($this->performMinimumRequirementsCheck()) {
      $this->runApplication();
    }
  }


  private function
  performMinimumRequirementsCheck()
  {
    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
      echo "Sorry, Simple PHP Login dose not run on a PHP version older than 5.3.7!";
    } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
      require_once("libraries/password_compatibility_library.php");
      return true;
    } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
      return true;
    }
    return false;
  }


  public function runApplication()
  {
    if (isset($_GET["action"]) && $_GET["action"] == "register") {
      $this->doRegistration();
      $this->showPageRegistration();
    } else {
      $this->doStartSession();
      $this->performUserLoginAction();

      if ($this->getUserLoginStatus()) {
        $this->showPageLoggedIn();
      } else {
        $this->showPageLoginForm();
      }
    }
  }


  private function createDatabaseConnection()
  {
    try {
      $this->db_connection = new PDO($this->db_type . ':' . $this->db_mysql_path);
      return true;
    } catch (PDOException $e) {
      $this->feedback = "PDO database connection problem: " . $e->getMessage();
    } catch (Exception $e) {
      $this->feedback = "General problem: " . $e->getMessage();
    }
    return false;
  }


  private function performUserLoginAction()
  {
    if (isset($_GET["action"]) && $_GET["action"] == "logout") {
      $this->doLogout();
    } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
      $this->doLoginWithSessionData();
    } elseif (isset($_POST["login"])) {
      $this->doLoginWithPostData();
    }
  }


  private function doStartSession()
  {
    if (session_status() == PHP_SESSION_NONE) session_start();
  }


  private function doLoginWithSessionData()
  {
    $this->user_is_logged_in = true;
  }

  private function doLoginWithPostData()
  {
    if ($this->checkLoginFormatDataNotEmpty()) {
      if ($this->createDatabaseConnection()) {
        $this->checkPasswordCorrectnessAndLogin();
      }
    }
  }


  private function doLogout()
  {
    $_SESSION = array();
    session_destroy();
    $this->user_is_logged_in = false;
    $this->feedback = "You were just logged out.";
  }


  private function doRegistration()
  {
    if ($this->checkRegistrationData()) {
      if ($this->createDatabaseConnection()) {
        $this->createNewUser();
      }
    }
    return false;
  }
}



// <?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// require_once('../vendor/autoload.php');

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
// $dotenv->load();

// try {
//   $dbDsn = $_ENV['DB_DSN'];
//   $dbUser = $_ENV['DB_USER'];
//   $dbPassword = $_ENV['DB_PASSWORD'];

//   $pdo = new PDO(
//     $dbDsn,
//     $dbUser,
//     $dbPassword,
//     [
//       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//     ]
//   );
// } catch (PDOException $e) {
//   header('Content-Type: text/plain; charset=UTF-8', true, 500);
//   exit($e->getMessage());
// }

// header('Content-Type: text/html; charset=UTF-8');
