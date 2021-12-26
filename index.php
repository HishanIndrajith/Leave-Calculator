<!DOCTYPE html>
<html>
<head>
	<style>
		table {
		  font-family: arial, sans-serif;
		  border-collapse: collapse;
		  width: 60%;
		  margin-left: auto;
		  margin-right: auto;
		}

		td, th {
		  border: 1px solid #dddddd;
		  text-align: left;
		  padding: 8px;
		}

		tr:nth-child(even) {
		  background-color: #dddddd;
		}
		tr:last-child {
		  background-color: #C7B198;
		}
		h2{
			text-align: center;
		}
		h4{
			margin-left: 20%;
		}

		input[type=text], select {
		  width: 100%;
		  padding: 12px 20px;
		  margin: 8px 0;
		  display: inline-block;
		  border: 1px solid #ccc;
		  border-radius: 4px;
		  box-sizing: border-box;
		}

		input[type=submit] {
		  width: 100%;
		  background-color: #4CAF50;
		  color: white;
		  padding: 14px 20px;
		  margin: 8px 0;
		  border: none;
		  border-radius: 4px;
		  cursor: pointer;
		}

		input[type=submit]:hover {
		  background-color: #45a049;
		}

		#form {
		  border-radius: 5px;
		  background-color: #f2f2f2;
		  padding: 20px;
		  width: calc(60% - 40px);
		  margin-left: auto;
		  margin-right: auto;
		}
	</style>
</head>
<body>

<?php

require_once __DIR__ . '/google-api-php-client--PHP7.0/vendor/autoload.php';
include_once "templates/base.php";

startSession();

/*************************************************
 * Ensure you've downloaded your oauth credentials
 ************************************************/
if (!$oauth_credentials = getOAuthCredentialsFile()) {
  echo missingOAuth2CredentialsWarning();
  return;
}

$redirect_uri = 'http://localhost/LeaveCalculator';
$logout_uri =$redirect_uri . '?logout';

$client = new Google\Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/calendar.events.readonly");
$client->addScope("https://www.googleapis.com/auth/calendar.readonly");
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope("https://www.googleapis.com/auth/userinfo.profile");

// add "?logout" to the URL to remove a token from the session
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['upload_token']);
  header('Location: ' . $redirect_uri);
}

/************************************************
 * If we have a code back from the OAuth 2.0 flow,
 * we need to exchange that with the
 * Google\Client::fetchAccessTokenWithAuthCode()
 * function. We store the resultant access token
 * bundle in the session, and redirect to ourself.
 ************************************************/

if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  echo $token;
  $client->setAccessToken($token);
  // store in the session also
  $_SESSION['upload_token'] = $token;
  // redirect again
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}

?>


<?php 

if (isset($authUrl)){
	header('Location: ' . $authUrl);
}else{
	$year = getYear();
	$totalLeaves = 40;
	list($name, $email) = getUserNameAndEmail($client);
  $calendarList = getCalendarList($client);
  $leaveArray = getLeaveList($client, $name, $calendarList, $year);
  list($total, $remainning) = calculateLeaveSummary($totalLeaves, $leaveArray);
  showOnWebPage($name, $email, $year, $leaveArray, $totalLeaves, $total, $remainning);
  
}

function getLeaveList($client, $name, $calendarList, $year){
	$public_holiday_word = 'SL Public Holiday';
	$word_half_lower_case = 'half';
	// change below array to add leaves that wre excluded
	$words_to_ignore = array('lieu', 'wfh');
	$calendarId = getLeaveCalendarId($calendarList);
	$leaveList = getEventList($name, $client, $calendarId, $year);
	$holidayList = getEventList($public_holiday_word, $client, $calendarId, $year);
	$leaveArray = [];
	foreach ($leaveList as $eve) {
		$startDateStr = $eve->getStart()->getDate();
		$endDateStr = $eve->getEnd()->getDate();
		$startDate=strtotime($startDateStr);
		$endDate=strtotime($endDateStr);
		$gap = ceil(($endDate-$startDate)/60/60/24);
		$leave = new Leave();
		$leave->set_startDate($startDateStr);
		$summary = $eve->getSummary();
		$summary_lower_case = strtolower($summary);
		$leave->set_isConsideredForCalculation(isConsideredForCalculation($summary_lower_case, $words_to_ignore));
		if (strpos($summary_lower_case, $word_half_lower_case)){
			$gap /= 2;
		}
		$leave->set_noOfDays($gap);
		$leave->set_type($summary);
		array_push($leaveArray, $leave);
	}
	foreach ($holidayList as $eve) {
		$startDateStr = $eve->getStart()->getDate();
		$endDateStr = $eve->getEnd()->getDate();
		$startDate=strtotime($startDateStr);
		$endDate=strtotime($endDateStr);
		$gap = ceil(($endDate-$startDate)/60/60/24);
		$leave = new Leave();
		$leave->set_startDate($startDateStr);
		$leave->set_noOfDays($gap);
		$leave->set_type($public_holiday_word);
		$leave->set_isConsideredForCalculation(true);
		array_push($leaveArray, $leave);
	}
	return $leaveArray;
}

function isConsideredForCalculation($word_to_check, $words_to_ignore){
	// both should be in lower case
	foreach ($words_to_ignore as $word_to_ignore) {
		if (strpos($word_to_check, $word_to_ignore)){
			return false;
		}
  }
  return true;
}

function getLeaveCalendarId($calendarList){
	foreach ($calendarList as $calendarListEntry) {
		$summary = $calendarListEntry->getSummary();
		if ($summary == 'SL Leave and Holidays'){
			$id = $calendarListEntry->getId();
			break;
		}
  }
  return $id;
}


function calculateLeaveSummary($totalLeaves, $leaveArray){
	$total = 0;
	foreach ($leaveArray as $leave) {
		if($leave->get_isConsideredForCalculation()){
			$total += $leave->get_noOfDays();
		}
	}
	return array($total, $totalLeaves-$total);
}

function getEventList($query, $client, $calendarId, $year){
	try {
		$timeMax = ($year + 1 ) . '-01-01T00:00:00Z';
		$timeMin = $year . '-01-01T00:00:00Z';
		$optParamsTime = array('timeMax' => $timeMax, 'timeMin' => $timeMin, 'q' => $query);
		$service = getCalendarService($client);
		$events = $service->events->listEvents($calendarId, $optParamsTime);
		$eventsList = $events->getItems();

		while(true) {
		  $pageToken = $events->getNextPageToken();
		  if ($pageToken) {
		    $optParams = array_merge($optParamsTime, array('pageToken' => $pageToken));
		    $events = $service->events->listEvents($calendarId, $optParams);
		    $eventsList = array_merge($eventsList, $events->getItems());
		  } else {
		    break;
		  }
		}
	}catch(Exception $e) {
		unsetToken();
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
	return $eventsList;
}


function getCalendarList($client){
	try {
		$service = getCalendarService($client);
		$calendarList = $service->calendarList->listCalendarList();
		$calendarItmes = $calendarList->getItems();

		while(true) {
		  $pageToken = $calendarList->getNextPageToken();
		  if ($pageToken) {
		    $optParams = array('pageToken' => $pageToken);
		    $calendarList = $service->calendarList->listCalendarList($optParams);
		    $calendarItmes = array_merge($calendarItmes, $calendarList->getItems());
		  } else {
		    break;
		  }
		}
	}catch(Exception $e) {
		unsetToken();
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
	return $calendarList->getItems();
}

function getUserNameAndEmail($client){
	try {
		$peopleService = new Google_Service_PeopleService($client);
		$profile = $peopleService->people->get(
	    		'people/me', array('personFields' => 'names,emailAddresses'));
		$name = '';
		if (isset($_GET['name'])) {
  		$name = $_GET['name'];
  	}else{
  		$names = $profile->getNames();
			if (count($names) > 0) {
			  $name = $names[0]->getGivenName( );
			}
  	}
	  $emails = $profile->getEmailAddresses();
		$email = '';
		if (count($emails) > 0) {
		  $email = $emails[0]->getValue( );
		}
	}
		catch(Exception $e) {
		unsetToken();
		header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
	return array($name, $email);
}

function getCalendarService($client){
	if (isset($calendarService)){
		return $calendarService;
	}else{
		$calendarService = new Google\Service\Calendar($client);
		return $calendarService;
	}
	
}

function getYear(){
	if (isset($_GET['year'])) {
  	return $_GET['year'];
  }else{
		return '' . date("Y");
  }
}

function unsetToken(){
	if (!empty($_SESSION['upload_token'])) {
		unset($_SESSION['upload_token']);
	}
}

function showOnWebPage($name, $email, $year, $leaveArray, $totalLeaves, $total, $remainning){
	echo pageHeader("Leave Calculator", 'Logged in User : ' . $email, $GLOBALS['logout_uri']);
	echo '';
	print "<h2>Leaves Remainning for {$name} in year {$year}: ({$totalLeaves} - {$total} = {$remainning} ) Days</h2>";
	echo '<table>';
	echo '<h4>Details</h4>';
	print 
	"<tr>
    <th>Start Date</th>
    <th>Number of Days</th>
    <th>Type</th>
  </tr>";
  foreach ($leaveArray as $leave) {
  	$msg = $leave->get_isConsideredForCalculation() ? '' : ' (Not Considered)';
  	print 
		"<tr>
	    <td>{$leave->get_startDate()}</td>
	    <td>{$leave->get_noOfDays()}{$msg}</td>
	    <td>{$leave->get_type()}</td>
	  </tr>";
  }
  print 
	"<tr>
	  <th>Total</td>
	  <th>{$total}</td>
	  <th></td>
	</tr>";
	echo '</table>';
	echo '</br>';

}

class Leave {
  // Properties
  public $startDate;
  public $noOfDays;
  public $type;
  public $isConsideredForCalculation;

  // Methods
  function set_startDate($startDate) {
    $this->startDate = $startDate;
  }
  function get_startDate() {
    return $this->startDate;
  }

  function set_noOfDays($noOfDays) {
    $this->noOfDays = $noOfDays;
  }
  function get_noOfDays() {
    return $this->noOfDays;
  }

  function set_type($type) {
    $this->type = $type;
  }
  function get_type() {
    return $this->type;
  }

  function set_isConsideredForCalculation($isConsideredForCalculation) {
    $this->isConsideredForCalculation = $isConsideredForCalculation;
  }
  function get_isConsideredForCalculation() {
    return $this->isConsideredForCalculation;
  }
}

?>

<!-- Form to change name and year -->
<div id="form">
	<h5>After Login if needed to change the name or year change it here and submit</h5>
  <form action="<?php echo $redirect_uri?>">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?php echo $name?>">

    <label for="year">Year</label>
    <select id="year" name="year">
      <option value="2019" <?php echo ($year=='2019' ? 'selected' : '' )?>>2019</option>
      <option value="2020" <?php echo ($year=='2020' ? 'selected' : '' )?>>2020</option>
      <option value="2021" <?php echo ($year=='2021' ? 'selected' : '' )?>>2021</option>
      <option value="2022" <?php echo ($year=='2022' ? 'selected' : '' )?>>2022</option>
    </select>
  
    <input type="submit" value="Submit">
  </form>
</div>

</body>
</html> 


