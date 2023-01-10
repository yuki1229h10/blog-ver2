<?php

class Signup extends DBh
{
  protected function setUser($uid, $pwd, $email)
  {
    $stmt = $this->connect()->prepare('SELECT users_uid FROM users WHERE ');

    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
  }



  protected function checkUser($uid, $email)
  {
    $stmt = $this->connect()->prepare('SELECT users_uid FROM users WHERE users_id = ? OR users_email = ?;');

    if (!$stmt->execute(array($uid, $email))) {
      $stmt = null;
      header("location: ../index.php?error=stmtFailed");
      exit();
    }

    $resultCheck;
    if ($stmt->rowCount() > 0) {
      $resultCheck = false;
    } else {
      $resultCheck = true;
    }
    return $resultCheck;
  }
}
