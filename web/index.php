<?php

use Dotenv\Parser\Entry;

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


  private function checkLoginFormatDataNotEmpty()
  {
    if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
      return true;
    } elseif (empty($_POST['user_name'])) {
      $this->feedback = "Username field was empty.";
    } elseif (empty($_POST['user_password'])) {
      $this->feedback = "Password field was empty.";
    }
    return false;
  }


  private function checkPasswordCorrectnessAndLogin()
  {
    $sql = 'SELECT user_name, user_email,  user_password_hash
    FROM users
    WHERE username = :user_name OR
    user_email = :user_name
    LIMIT 1';

    $query = $this->db_connection->prepare($sql);
    $query->bindValue(':user_name', $_POST['user_name']);
    $query->execute();

    $result_row = $query->fetchObject();
    if ($result_row) {
      if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
        $_SESSION['user_name'] = $result_row->user_name;
        $_SESSION['user_email'] = $result_row->user_email;
        $_SESSION['user_is_logged_in'] = true;
        $this->user_is_logged_in = true;
        return true;
      } else {
        $this->feedback = "Wrong password.";
      }
    } else {
      $this->feedback = "This user does not exist.";
    }
    return false;
  }


  private function checkRegistrationData()
  {
    if (!isset($_POST["register"])) {
      return false;
    }

    if (
      !empty($_POST['user_name'])
      && strlen($_POST['user_name']) <= 64
      && strlen($_POST['user_name']) >= 2
      && preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
      && !empty($_POST['user_email'])
      && strlen($_POST['user_email']) <= 64
      && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
      && !empty($_POST['user_password_new'])
      && strlen($_POST['user_password_new']) >= 6
      && !empty($_POST['user_password_repeat'])
      && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
    ) {
      return true;
    } elseif (empty($_POST['user_name'])) {
      $this->feedback = "Empty Username";
    } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
      $this->feedback = "Empty Password";
    } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
      $this->feedback = "Password and password repeat are not the same";
    } elseif (strlen($_POST['user_password_new']) < 6) {
      $this->feedback = "Password has a minimum length of 6 characters";
    } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
      $this->feedback = "Username cannot be shorter than 2 or longer than 64 characters";
    } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
      $this->feedback = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
    } elseif (empty($_POST['user_email'])) {
      $this->feedback = "Email cannot be empty";
    } elseif (strlen($_POST['user_email']) > 64) {
      $this->feedback = "Email cannot be longer than 64 characters";
    } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
      $this->feedback = "Your email address is not in a valid email format";
    } else {
      $this->feedback = "An unknown error occurred.";
    }

    // default return
    return false;
  }


  private function createNewUser()
  {
    $user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
    $user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
    $user_password = $_POST['user_password_new'];

    $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

    $sql = 'SELECT * FROM users WHERE user_name = :user_name OR user_email = :user_email';
    $query = $this->db_connection->prepare($sql);
    $query->bindValue(':user_name', $user_name);
    $query->bindValue(':user_email', $user_email);
    $query->execute();

    $result_row = $query->fetchObject();
    if ($result_row) {
      $this->feedback = "Sorry, that username / email is already taken. Please choose another one.";
    } else {
      $sql = 'INSERT INTO users (user_name, user_password_hash, user_email)
      VALUES (:user_name, :user_password_hash, :user_email)';
      $query = $this->db_connection->prepare($sql);
      $query->bindValue(':user_name', $user_name);
      $query->bindValue(':user_password_hash', $user_password_hash);
      $query->bindValue(':user_email', $user_email);
      $registration_success_state = $query->execute();

      if ($registration_success_state) {
        $this->feedback = "Your account has been created successfully. You can now log in.";
        return true;
      } else {
        $this->feedback = "Sorry, your registration failed. Please go back and try again.";
      }
    }
    return false;
  }


  public function getUserLoginStatus()
  {
    return $this->user_is_logged_in;
  }

  private function showPageLoggedIn()
  {
    if ($this->feedback) {
      echo $this->feedback . "<br/><br/>";
    }

    echo 'Hello ' . $_SESSION['user_name'] . ', you are logged in.<br/><br/>';
    echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">Log out</a>';
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
