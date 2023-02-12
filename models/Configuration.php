<?php

class Configuration {
  private $totalAllowed;
  private $emailsExcluded;
  private $calendarId;
  private $publicHolidayLabel;
  private $supportedYears;
  private $isMaintenanceMode;
  private $modalToInclude;
  private $wordsToIgnore;
  private $wordsImplyingHalf;
  private $adminEmail;

  public function getTotalAllowed() {
    return $this->totalAllowed;
  }

  public function setTotalAllowed($totalAllowed) {
    $this->totalAllowed = $totalAllowed;
  }

  public function getEmailsExcluded() {
    return $this->emailsExcluded;
  }

  public function setEmailsExcluded($emailsExcluded) {
    $this->emailsExcluded = $emailsExcluded;
  }

  public function getCalendarId() {
    return $this->calendarId;
  }

  public function setCalendarId($calendarId) {
    $this->calendarId = $calendarId;
  }

  public function getPublicHolidayLabel() {
    return $this->publicHolidayLabel;
  }

  public function setPublicHolidayLabel($publicHolidayLabel) {
    $this->publicHolidayLabel = $publicHolidayLabel;
  }

  public function getSupportedYears() {
    return $this->supportedYears;
  }

  public function setSupportedYears($supportedYears) {
    $this->supportedYears = $supportedYears;
  }

  public function getIsMaintenanceMode() {
    return $this->isMaintenanceMode;
  }

  public function setIsMaintenanceMode($isMaintenanceMode) {
    $this->isMaintenanceMode = $isMaintenanceMode;
  }

  public function getModalToInclude() {
    return $this->modalToInclude;
  }

  public function setModalToInclude($modalToInclude) {
    $this->modalToInclude = $modalToInclude;
  }
  
  public function getWordsToIgnore() {
    return $this->wordsToIgnore;
  }

  public function setWordsToIgnore($wordsToIgnore) {
    $this->wordsToIgnore = $wordsToIgnore;
  }
  
  public function getWordsImplyingHalf() {
    return $this->wordsImplyingHalf;
  }
  
  public function setWordsImplyingHalf($wordsImplyingHalf) {
    $this->wordsImplyingHalf = $wordsImplyingHalf;
  }
  
  public function getAdminEmail() {
    return $this->adminEmail;
  }
  
  public function setAdminEmail($adminEmail) {
    $this->adminEmail = $adminEmail;
  }
  
  public function isEmpty() {
    return empty($this->totalAllowed) || empty($this->emailsExcluded) || empty($this->calendarId) || empty($this->publicHolidayLabel) || empty($this->supportedYears) || empty($this->isMaintenanceMode) || empty($this->modalToInclude)
     || empty($this->wordsToIgnore) || empty($this->wordsImplyingHalf) || empty($this->adminEmail);
  }
  
}

?>
