<?php

function initializeCalandarAPIClient($userSession){
    
    $redirect_uri = $userSession->getRedirectUri();
    
    //Start browser session
    startSession();
    
    // Check whether oauth credentials are there
    $oauth_credentials = checkCredentials();
    
    // Get the google api client 
    $client = getGoogleClient($redirect_uri, $oauth_credentials);
    
    // Check "?logout" in the URL to remove a token from the session
    logoutIfRequested($redirect_uri);
    
    // Get the token using code if exists in the url and update session and call redirect url again
    getTokenAndRefresh($client, $redirect_uri);
    
    // If session exsist, set the access token to the client, if not call the authenticate url
    setTokenToClientOrAuthenticateAgain($client);
    
    return $client;
}

function checkCredentials()
{
    if (!($oauth_credentials = getOAuthCredentialsFile())) {
        echo missingOAuth2CredentialsWarning();
        return;
    } else {
        return $oauth_credentials;
    }
}

function getGoogleClient($redirect_uri, $oauth_credentials)
{
    $client = new Google\Client();
    $client->setAuthConfig($oauth_credentials);
    $client->setRedirectUri($redirect_uri);
    $client->addScope(
        "https://www.googleapis.com/auth/calendar.events.readonly"
    );
    $client->addScope("https://www.googleapis.com/auth/userinfo.email");
    $client->addScope("https://www.googleapis.com/auth/userinfo.profile");

    return $client;
}

function getTokenAndRefresh($client, $redirect_uri)
{
    /************************************************
     * If we have a code back from the OAuth 2.0 flow,
     * we need to exchange that with the
     * Google\Client::fetchAccessTokenWithAuthCode()
     * function. We store the resultant access token
     * bundle in the session, and redirect to ourself.
     ************************************************/

    if (isset($_GET["code"])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION["upload_token"] = $token;
        // redirect again
        header("Location: " . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }
}

// if session exsist, set the access token to the client, if not call the authenticate url
function setTokenToClientOrAuthenticateAgain($client){
    if (!empty($_SESSION['upload_token'])) {
      $client->setAccessToken($_SESSION['upload_token']);
      if ($client->isAccessTokenExpired()) {
        unset($_SESSION['upload_token']);
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
      }
    } else {
      $authUrl = $client->createAuthUrl();
      header('Location: ' . $authUrl);
    }
}

/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest()
{
    return isset($_SERVER["HTTP_USER_AGENT"]);
}

function startSession()
{
    // Start the session (for storing access tokens and things)
    if (!headers_sent()) {
        session_start();
    }
}

function pageFooter($file = null)
{
    $ret = "";
    if ($file) {
        $ret .= "<h3>Code:</h3>";
        $ret .= "<pre class='code'>";
        $ret .= htmlspecialchars(file_get_contents($file));
        $ret .= "</pre>";
    }
    $ret .= "</html>";

    return $ret;
}

function missingApiKeyWarning()
{
    $ret = "
    <h3 class='warn'>
      Warning: You need to set a Simple API Access key from the
      <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";

    return $ret;
}

function missingClientSecretsWarning()
{
    $ret = "
    <h3 class='warn'>
      Warning: You need to set Client ID, Client Secret and Redirect URI from the
      <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";

    return $ret;
}

function missingServiceAccountDetailsWarning()
{
    $ret = "
    <h3 class='warn'>
      Warning: You need download your Service Account Credentials JSON from the
      <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
      Once downloaded, move them into the root directory of this repository and
      rename them 'service-account-credentials.json'.
    </p>
    <p>
      In your application, you should set the GOOGLE_APPLICATION_CREDENTIALS environment variable
      as the path to this file, but in the context of this example we will do this for you.
    </p>";

    return $ret;
}

function missingOAuth2CredentialsWarning()
{
    $ret = "
    <h3 class='warn'>
      Warning: You need to set the location of your OAuth2 Client Credentials from the
      <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
      Once downloaded, move them into the root directory of this repository and
      rename them 'oauth-credentials.json'.
    </p>";

    return $ret;
}

function invalidCsrfTokenWarning()
{
    $ret = "
    <h3 class='warn'>
      The CSRF token is invalid, your session probably expired. Please refresh the page.
    </h3>";

    return $ret;
}

function checkServiceAccountCredentialsFile()
{
    // service account creds
    $application_creds = __DIR__ . "/../../service-account-credentials.json";

    return file_exists($application_creds) ? $application_creds : false;
}

function getCsrfToken()
{
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(openssl_random_pseudo_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function validateCsrfToken()
{
    return isset($_REQUEST["csrf_token"]) &&
        isset($_SESSION["csrf_token"]) &&
        $_REQUEST["csrf_token"] === $_SESSION["csrf_token"];
}

function getOAuthCredentialsFile()
{
    // oauth2 creds
    $oauth_creds = __DIR__ . "/../credentials/oauth-credentials.json";
    if (file_exists($oauth_creds)) {
        return $oauth_creds;
    }

    return false;
}

function setClientCredentialsFile($apiKey)
{
    $file = __DIR__ . "/../../tests/.apiKey";
    file_put_contents($file, $apiKey);
}

function getApiKey()
{
    $file = __DIR__ . "/../../tests/.apiKey";
    if (file_exists($file)) {
        return file_get_contents($file);
    }
}

function setApiKey($apiKey)
{
    $file = __DIR__ . "/../../tests/.apiKey";
    file_put_contents($file, $apiKey);
}

///////////////////////////////////////////////////////

function getCalendarService($client)
{
    if (isset($calendarService)) {
        return $calendarService;
    } else {
        $calendarService = new Google\Service\Calendar($client);
        return $calendarService;
    }
}

function getUserNameAndEmail($userSession)
{
    $client = $userSession->getClient();
    try {
        $peopleService = new Google_Service_PeopleService($client);
        $profile = $peopleService->people->get("people/me", [
            "personFields" => "names,emailAddresses",
        ]);
        $name = "";
        if (isset($_GET["name"])) {
            $name = $_GET["name"];
        } else {
            $names = $profile->getNames();
            if (count($names) > 0) {
                $name = $names[0]->getGivenName();
            }
        }
        $emails = $profile->getEmailAddresses();
        $email = "";
        if (count($emails) > 0) {
            $email = $emails[0]->getValue();
        }
    } catch (Exception $e) {
        unsetToken();
        header("Location: " . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }
    
    $userSession->setName($name);
    $userSession->setEmail($email);
}

function getEventList($query, $client, $calendarId, $year)
{
    try {
        $timeMax = $year + 1 . "-01-01T00:00:00Z";
        $timeMin = $year . "-01-01T00:00:00Z";
        $optParamsTime = [
            "timeMax" => $timeMax,
            "timeMin" => $timeMin,
            "q" => $query,
        ];
        $service = getCalendarService($client);
        $events = $service->events->listEvents($calendarId, $optParamsTime);
        $eventsList = $events->getItems();

        while (true) {
            $pageToken = $events->getNextPageToken();
            if ($pageToken) {
                $optParams = array_merge($optParamsTime, [
                    "pageToken" => $pageToken,
                ]);
                $events = $service->events->listEvents($calendarId, $optParams);
                $eventsList = array_merge($eventsList, $events->getItems());
            } else {
                break;
            }
        }
    } catch (Exception $e) {
        unsetToken();
        header("Location: " . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }
    return $eventsList;
}

function getLeaveList($userSession)
{
    $client = $userSession->getClient(); 
    $name = $userSession->getName();
    $year = $userSession->getYear();
    $calendarId = $userSession->getConfiguration()->getCalendarId();
    $public_holiday_word = $userSession->getConfiguration()->getPublicHolidayLabel();
    // change below array to add leaves that wre excluded
    $leaveEventList = getEventList($name, $client, $calendarId, $year);
    $holidayEventList = getEventList(
        $public_holiday_word,
        $client,
        $calendarId,
        $year
    );
    $leaveList = getFinalLeaveList($userSession, $leaveEventList, $holidayEventList);
    $userSession->setLeaveArray($leaveList);
}


function countWeekendDays($startDate, $numDays) {
    $count = 0;
    for ($i = 0; $i < $numDays; $i++) {
        $day = date('N', strtotime($startDate . '+' . $i . 'days'));
        if ($day >= 6) {
            $count++;
        }
    }
    return $count;
}

?>
