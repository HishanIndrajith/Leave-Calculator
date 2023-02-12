<?php

require_once __DIR__ .
    "/libraries/google-api-php-client--PHP7.0/vendor/autoload.php";
include_once "functions/CalculationFunctions.php";
include_once "functions/ClientFunctions.php";
include_once "functions/HtmlFunctions.php";
include_once "functions/BrowserFunctions.php";
include_once "functions/ConfigurationFunctions.php";
include_once "functions/StatisticsFunctions.php";
include_once "models/Leave.php";
include_once "models/UserSession.php";
include_once "models/Configuration.php";
include_once "models/Usage.php";

$redirect_uri = "https://leavecalculator.000webhostapp.com/";

// create a new user session
$userSession = new UserSession();

$userSession->setRedirectUri($redirect_uri);

// set the google calandar api client to session
$userSession->setClient(initializeCalandarAPIClient($userSession));

// Get configuartions and update user session
getConfiguration($userSession);

// Get requesting data and update user session
getYear($userSession);
getUserNameAndEmail($userSession);

// manage statistics
manageStatistics($userSession);

// Manage excluded emails if any configurred
manageExclusions($userSession);

// show others maintainance page except Admin
manageMaintenanceMode($userSession);

// Get the leave array and update the user session
getLeaveList($userSession);

// Calculate the summary and update user session
calculateLeaveSummary($userSession);

// Show the web page with results
createAndShowOutputWebPage($userSession);

?>