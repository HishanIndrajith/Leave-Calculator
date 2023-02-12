<?php

function getConfiguration($userSession){
    
    //new Configuration object
    $configuration = new Configuration();
    try {
        $file = fopen(__DIR__ . "/../configurations/configuration.txt", "r");
        while (!feof($file)) {
          $line = fgets($file);
          $parts = explode("=", $line);
          $attribute = trim($parts[0]);
          $value = trim($parts[1]);
        
          switch ($attribute) {
            case "totalAllowed":
              $configuration->setTotalAllowed((int) $value);
              break;
            case "emailsExcluded":
              $configuration->setEmailsExcluded(explode(",", $value));
              break;
            case "calendarId":
              $configuration->setCalendarId($value);
              break;
            case "publicHolidayLabel":
              $configuration->setPublicHolidayLabel($value);
              break;
            case "supportedYears":
              $configuration->setSupportedYears(explode(",", $value));
              break;
            case "isMaintenanceMode":
              $configuration->setIsMaintenanceMode($value);
              break;
            case "modalToInclude":
              $configuration->setModalToInclude($value);
              break;
            case "wordsToIgnore":
              $configuration->setWordsToIgnore(explode(",", $value));
              break;
            case "wordsImplyingHalf":
              $configuration->setWordsImplyingHalf(explode(",", $value));
              break;
            case "adminEmail":
              $configuration->setAdminEmail($value);
              break;
            default:
              echo "Invalid Configuration";
              exit;
              break;
          }
        }
        if ($configuration->isEmpty()){
            echo "Configurations are incomplete";
            exit;
        }
    } catch (Exception $e) {
      // exception handling code
      echo "Exception caught while reading configuration: " . $e->getMessage();
      exit;
    }
    
    $userSession->setConfiguration($configuration);
}

?>