<?php

class UserSession {
  private $client;
  private $name;
  private $email;
  private $year;
  private $redirect_uri;
  private $total;
  private $remaining;
  private $leaveArray;
  private $configuration;
  
  public function getClient() {
    return $this->client;
  }

  public function setClient($client) {
    $this->client = $client;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
  }

  public function getYear() {
    return $this->year;
  }

  public function setYear($year) {
    $this->year = $year;
  }

  public function getRedirectUri() {
    return $this->redirect_uri;
  }

  public function setRedirectUri($redirect_uri) {
    $this->redirect_uri = $redirect_uri;
  }

  public function getTotal() {
    return $this->total;
  }

  public function setTotal($total) {
    $this->total = $total;
  }

  public function getRemaining() {
    return $this->remaining;
  }

  public function setRemaining($remaining) {
    $this->remaining = $remaining;
  }

  public function getLeaveArray() {
    return $this->leaveArray;
  }

  public function setLeaveArray($leaveArray) {
    $this->leaveArray = $leaveArray;
  }
  
  public function getConfiguration() {
    return $this->configuration;
  }

  public function setConfiguration($configuration) {
    $this->configuration = $configuration;
  }
}


?>
