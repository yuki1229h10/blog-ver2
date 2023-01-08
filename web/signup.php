<?php

class SignUpController
{
  private $uid;
  private $pwd;
  private $pwdRepeat;
  private $email;

  public function __construct($uid, $pwd, $pwdRepeat, $email)
  {
    $this->$uid = $uid;
    $this->$pwd = $pwd;
    $this->$pwdRepeat = $pwdRepeat;
    $this->$email = $email;
  }

  private function emptyInput()
  {
    $result;
    if (
      empty($this->$uid) ||
      empty($this->$pwd) ||
      empty($this->$pwdRepeat) ||
      empty($this->$email)
    ) {
      $result = false;
    } else {
      $result = true;
    }
    return $result;
  }

  private function invalidUid()
  {
    $result;
    if (!preg_match("/^[a-zA-Z0-9]*$/", $this->uid)) {
      $result = false;
    } else {
      $result = true;
    }
    return $result;
  }

  private function invalidEmail()
  {
    $result;
    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      $result = false;
    } else {
      $result = true;
    }
    return $result;
  }

  private function pwdMatch()
  {
    $result;
    if ($this->pwd !== $this->pwdRepeat) {
      $result = false;
    } else {
      $result = true;
    }
    return $result;
  }
}
