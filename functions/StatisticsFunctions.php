<?php

function manageStatistics($userSession){
    $email = $userSession->getEmail();
    updateStatistics($email);

}

function updateStatistics($email) {
    if (empty($email) || ! isset($email)) {
      return ;
    }
    // Open the CSV file in write mode
    $file = fopen(__DIR__ . "/../statistics/statistics.csv", 'r+');
    
    // Lock the file for writing
    flock($file, LOCK_EX);

    // Read the contents of the file into an array
    $rows = [];
    while (($row = fgetcsv($file)) !== false) {
        $rows[] = $row;
    }

    // Check if the email already exists in the array
    $found = false;
    foreach ($rows as &$row) {
        if ($row[0] === $email) {
            // Update the row if it exists
            $row = [$email, date('Y-m-d H:i:s'), ++$row[2]];
            $found = true;
            break;
        }
    }

    // Add a new row if the email does not exist
    if (!$found) {
        $rows[] = [$email, date('Y-m-d H:i:s'), 0];
    }

    // Rewind the file pointer to the beginning of the file
    rewind($file);

    // Write the updated contents to the file
    foreach ($rows as $row) {
        fputcsv($file, $row);
    }

    // Unlock the file
    flock($file, LOCK_UN);
    
    // Close the file
    fclose($file);
}



function getStats(){
    $usages = array();
    if (($handle = fopen(__DIR__ . "/../statistics/statistics.csv", "r")) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            $usage = new Usage();
            $usage->setEmail($data[0]);
            $usage->setLastAccessedTime($data[1]);
            $usage->setUsageCount($data[2]);
            $usages[] = $usage;
        }
        fclose($handle);
    }
    return $usages;
}


?>