<?php


function createAndShowOutputWebPage($userSession){
    // <head> details
    includeHead();
    
    //Include Modal if configured only
    includeModal($userSession);
    
    // Show statistics if requested
    showStatisticsIfRequested($userSession);
    
    // Include page Header
    includePageHeader($userSession);
        
    // Include results in returnning web page
    includeResultTable($userSession);
        
    // Include a form to resubmit name and year in the web page
    includeResubmitForm($userSession);
    
    //Include footer
    includeFooter($userSession);
    
}

function includeHead(){
    $head = file_get_contents(__DIR__ . "/../resources/html/head.html");
    echo $head;
}


function includeModal($userSession){
    // return boolean was modal added ?
    $modalPageInfo = $userSession->getConfiguration()->getModalToInclude();
    if($modalPageInfo == "NONE"){
        return false;
    }else{
        $modal = file_get_contents(__DIR__ . "/../resources/html/modal.html");
        $modal_lines = explode("\n", $modalPageInfo);
        // first line is title
        $modalTitle = $modal_lines[0];
        // second line is filename
        $modalFileName = $modal_lines[1] . ".html";
        $modalUrl = "/configurations/modalPages/" . $modalFileName;
        
        // Below lines are needed in every where to eco the html extracted from resources with variables. So copy and paste in HTML functions where needed.
        $str = addslashes($modal);
        eval("\$str = \"$str\";");
        echo $str;
        
        return true;
    }
}

function showStats($userSession){
    $redirect_uri = $userSession->getRedirectUri();
    $statistics = file_get_contents(__DIR__ . "/../resources/html/statistics.html");
    $contentExpression = file_get_contents(__DIR__ . "/../resources/html/statistics_repititiveRow.html");
    
    $stats = getStats();
    
    $repititiveRows = getRepititiveContent($stats, $contentExpression, "", "");
    
    // Below lines are needed in every where to eco the html extracted from resources with variables. So copy and paste in HTML functions where needed.
    $str = addslashes($statistics);
    eval("\$str = \"$str\";");
    echo $str;
    exit();
}

function includePageHeader($userSession){
    $email = $userSession->getEmail();
    $adminEmail = $userSession->getConfiguration()->getAdminEmail();
    $redirect_uri = $userSession->getRedirectUri();
    $pageHeader = file_get_contents(__DIR__ . "/../resources/html/pageHeader.html");
    $statLinkStyle = $email==$adminEmail? "" : "none";
    
    // Below lines are needed in every where to eco the html extracted from resources with variables. So copy and paste in HTML functions where needed.
    $str = addslashes($pageHeader);
    eval("\$str = \"$str\";");
    echo $str;
}

function includeFooter($userSession){
    $footer = file_get_contents(__DIR__ . "/../resources/html/footer.html");
    echo $footer;
}

function includeResultTable($userSession){
    $name =  $userSession->getName();
    $email = $userSession->getEmail();
    $year = $userSession->getYear();
    $leaveArray = $userSession->getLeaveArray();
    $totalAllowed = $userSession->getConfiguration()->getTotalAllowed();
    $total = $userSession->getTotal();
    $remainning = $userSession->getRemaining();
    $redirect_uri = $userSession->getRedirectUri();
    
    
    $resultDiv = file_get_contents(__DIR__ . "/../resources/html/resultDiv.html");
    $contentExpression = file_get_contents(__DIR__ . "/../resources/html/resultDiv_repititiveRow.html");
    
    $condionalExpression1 = '{$iterator->get_isConsideredForCalculation()}';
    $conditionifTrue1 = '';
    $conditionifFalse1 = ' (Not Considered)';
    $condition1 = array($condionalExpression1, $conditionifTrue1, $conditionifFalse1);
    
    $repititiveRows = getRepititiveContent($leaveArray, $contentExpression, $condition1, "");
    
    // Below lines are needed in every where to eco the html extracted from resources with variables. So copy and paste in HTML functions where needed.
    $str = addslashes($resultDiv);
    eval("\$str = \"$str\";");
    echo $str;
}

function includeResubmitForm($userSession){
    $year =  $userSession->getYear();
    $redirect_uri =  $userSession->getRedirectUri();
    $name =  $userSession->getName();
    
    $supported_years = $userSession->getConfiguration()->getSupportedYears();
    $resubmitDiv = file_get_contents(__DIR__ . "/../resources/html/resubmitDiv.html");
    $contentExpression = file_get_contents(__DIR__ . "/../resources/html/resubmitDiv_repititiveRow.html");
    
    $condionalExpression1 = $year;
    $conditionifTrue1 = 'selected=true';
    $conditionifFalse1 = '';
    $equalityCheck = array($condionalExpression1, $conditionifTrue1, $conditionifFalse1);
    
    $repititiveRows = getRepititiveContent($supported_years, $contentExpression, "", $equalityCheck);
    
    // Below lines are needed in every where to eco the html extracted from resources with variables. So copy and paste in HTML functions where needed.
    $str = addslashes($resubmitDiv);
    eval("\$str = \"$str\";");
    echo $str;
}

function getRepititiveContent($iterable, $contentExpression, $condition1, $equalityCheck){
    // return a evaluated repititve expression combined
    $condionalExpression = isset($condition1) & $condition1 != "" ? addslashes($condition1[0]) : "";
    $contentExpression = addslashes($contentExpression);
    $content = "";
    foreach ($iterable as $iterator) {
        
        if (isset($condition1) & $condition1 != "") {
            $condionalExpressionToEval = $condionalExpression;
            eval("\$condionalExpressionToEval = \"$condionalExpressionToEval\";");
            $conditionalOutput1 = $condionalExpressionToEval ? $condition1[1] : $condition1[2];
        }
        if (isset($equalityCheck) & $equalityCheck !="") {
            $conditionalOutput1 = $iterator == $equalityCheck[0] ? $equalityCheck[1] : $equalityCheck[2];
            
        }
        
        $contentExpressionToEval = $contentExpression;
        eval("\$contentExpressionToEval = \"$contentExpressionToEval\";");
        $content = $content . $contentExpressionToEval;
    }
    return $content;
}


?>