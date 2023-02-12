<?php
function getYear($userSession)
{
    $year = "";
    if (isset($_GET["year"])) {
        $year = $_GET["year"];
    } else {
        $year = "" . date("Y");
    }
    $userSession->setYear($year);
}

function getIgnoreWordCount($userSession, $word_to_check){
    $words_to_ignore = $userSession->getConfiguration()->getWordsToIgnore();
    //return how much to ignore
    $count = 0;
    // both should be in lower case
    foreach ($words_to_ignore as $word_to_ignore) {
        if (strpos($word_to_check, $word_to_ignore)) {
            $count += 1;
        }
    }
    if($count > 2){
        $count = 2;
    }
    return $count;
}

function getHalfCount($userSession, $word_to_check){
    $words_implying_half = $userSession->getConfiguration()->getWordsImplyingHalf();
    $coult = 0;
    // both should be in lower case
    $count = 0;
    foreach ($words_implying_half as $word) {
        if (strpos($word_to_check, $word)) {
            $count++;
        }
    }
    if($count > 2){
        $count = 2;
    }
    return $count;
}


function getLeaveWeight($userSession, $word_to_check){
    $ignore_word_count = getIgnoreWordCount($userSession, $word_to_check);
    $half_word_count = getHalfCount($userSession, $word_to_check);
    
    $weight = 1;
    if ($ignore_word_count == 0 && $half_word_count == 0) {
      $weight = 1;
    } elseif ($ignore_word_count == 0 && $half_word_count == 1) {
      $weight = 0.5;
    } elseif ($ignore_word_count == 0 && $half_word_count == 2) {
      $weight = 1;
    } elseif ($ignore_word_count == 1 && $half_word_count == 0) {
      $weight = 0;
    } elseif ($ignore_word_count == 1 && $half_word_count == 1) {
      $weight = 0;
    } elseif ($ignore_word_count == 1 && $half_word_count == 2) {
      $weight = 0.5;
    } elseif ($ignore_word_count == 2 && $half_word_count == 0) {
      $weight = 0;
    } elseif ($ignore_word_count == 2 && $half_word_count == 1) {
      $weight = 0;
    } elseif ($ignore_word_count == 2 && $half_word_count == 2) {
      $weight = 0;
    }
    return $weight;
}

function getFinalLeaveList($userSession, $leaveEventList, $holidayEventList){
    $leaveArray = [];
    foreach ($leaveEventList as $eve) {
        $startDateStr = $eve->getStart()->getDate();
        $endDateStr = $eve->getEnd()->getDate();
        $startDate = strtotime($startDateStr);
        $endDate = strtotime($endDateStr);
        $gap = ceil(($endDate - $startDate) / 60 / 60 / 24);
        
        // Reduce weekend days if included
        $weekendDays = countWeekendDays($startDateStr, $gap);
        if($weekendDays > 0){
            $gap = $gap - $weekendDays;
        }
        
        $leave = new Leave();
        $leave->set_startDate($startDateStr);
        $summary = $eve->getSummary();
        $summary_lower_case = strtolower($summary);
        $leaveWeight = getLeaveWeight($userSession, $summary_lower_case);
        $leave->set_isConsideredForCalculation($leaveWeight == 0 ? false : true);
        // if not considerd as a leave show full days with a message, otherwise show the actural considered leave days
        $leave->set_noOfDays($leaveWeight == 0 ? $gap : $gap*$leaveWeight);
        $leave->set_type($summary);
        array_push($leaveArray, $leave);
    }
    foreach ($holidayEventList as $eve) {
        $startDateStr = $eve->getStart()->getDate();
        $endDateStr = $eve->getEnd()->getDate();
        $startDate = strtotime($startDateStr);
        $endDate = strtotime($endDateStr);
        $gap = ceil(($endDate - $startDate) / 60 / 60 / 24);
        $leave = new Leave();
        $leave->set_startDate($startDateStr);
        $leave->set_noOfDays($gap);
        $leave->set_type($eve->getSummary());
        $leave->set_isConsideredForCalculation(true);
        array_push($leaveArray, $leave);
    }
    return $leaveArray;
}

function calculateLeaveSummary($userSession)
{
    $totalAllowed = $userSession->getConfiguration()->getTotalAllowed();
    $leaveArray = $userSession->getLeaveArray();
    $total = 0;
    
    foreach ($leaveArray as $leave) {
        if ($leave->get_isConsideredForCalculation()) {
            $total += $leave->get_noOfDays();
        }
    }
    
    $userSession->setTotal($total);
    $userSession->setRemaining($totalAllowed - $total);
}

?>
