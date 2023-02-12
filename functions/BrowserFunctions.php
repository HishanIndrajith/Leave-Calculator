<?php

function unsetToken()
{
    if (!empty($_SESSION["upload_token"])) {
        unset($_SESSION["upload_token"]);
    }
}

function logoutIfRequested($redirect_uri)
{
    if (isset($_REQUEST["logout"])) {
        unset($_SESSION["upload_token"]);
        header("Location: " . $redirect_uri);
    }
}

function showStatisticsIfRequested($userSession){
    if (isset($_REQUEST["stats"]) && $userSession->getEmail()==$userSession->getConfiguration()->getAdminEmail()) {
        showStats($userSession);
    }
}

function manageExclusions($userSession)
{
    $email = $userSession->getEmail(); 
    $emails_excluded = []; 
    if (in_array($email, $emails_excluded)) {
        redirectToErrorPage();
    }
}

function manageMaintenanceMode($userSession){
    $email = $userSession->getEmail();
    $adminEmail = $userSession->getConfiguration()->getAdminEmail();
    $isMaintainanceMode = $userSession->getConfiguration()->getIsMaintenanceMode();
    if ($isMaintainanceMode == "true" && $email != $adminEmail) {
        redirectToMaintainancePage();
    }
}

function redirectToErrorPage(){
	header("Location: https://error.000webhostapp.com");
}

function redirectToMaintainancePage(){
    header("Location: https://leavecalculator.000webhostapp.com/maintainance.html");
}


?>
