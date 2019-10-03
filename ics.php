<?php

// this file does some _very_ simple ics parsing
// it basically fetches the ics from the API, takes a substring from the first BEGIN:VEVENT to the last END:VEVENT and
// then simply pastes together the content from all the calendars in the template

/**
 * Attempts to fetch the ical data. It does so by using the correct API, if specified, and tries all of them otherwise.
 * @param $id string The id to look for. Can either be the id of the class or prefixed with the correct endpoint, i.e. 'fiw:123456'.
 * @return string The raw response text
 */
function fetchIcs($id) {
    $config = require 'config.php';
    $splitterPos = mb_strpos($id, ':');
    error_log("id $id");

    // if an endpoint is specified, use it
    if ($splitterPos !== false) {
        $faculty = mb_substr($id, 0, $splitterPos);
        $idInt = (int)mb_substr($id, $splitterPos + 1);
        error_log("idint $idInt fac $faculty url ".$config[$faculty]);

        // skip the entry if the faculty is invalid
        if (!isset($config[$faculty]))
            return "";

        return fetchIcsFromApi($idInt, $config[$faculty]);
    }
    // otherwise just try all of them
    else {
        foreach ($config as $faculty => $url) {
            $rv = fetchIcsFromApi((int)$id, $url);
            if ($rv != "") {
                return $rv;
            }
        }

        return '';
    }
}

/**
 * Attempts to fetch the ical-data for a class from a specific url.
 * @param $id int The id to look for.
 * @param $apiUrl string The base url for fetching the data.
 * @return string The raw response text.
 */
function fetchIcsFromApi($id, $apiUrl) {
    $id = (int)$id; // this will prevent non-numeric input
    $url = "$apiUrl/$id/ical";

    $ch = curl_init($url);

    // set the accept header
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: text/calendar'));

    // return response data instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // set a low timeout to avoid overly long loading times
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);

    //execute the POST request
    $result = curl_exec($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpcode != 200)
        return ""; // do nothing on error

    curl_close($ch);

    return $result;
}

/**
 * This method strips the beginning and end data from an ical.
 * @param $ical string
 * @return string The VEVENT data.
 */
function cleanIcal($ical) {
    $noBeginning = mb_substr($ical, mb_strpos($ical, 'BEGIN:VEVENT'));

    $noEnd = mb_substr($noBeginning, 0, mb_strrpos($noBeginning,'END:VEVENT') + 10);
    return $noEnd;
}

header('content-type: text/calendar'); // set the correct content type
header('cache-control: public, max-age=86400'); // allow caching for one day

if(isset($_REQUEST['download'])) {
    // if this parameter is set, use a file attachment
    header('content-disposition: inline; filename="Vorlesungsplan.ics"');
}

$name = "Vorlesungsplan FWHS";
$descr = "Generierter persönlicher Vorlesungsplan für meine Vorlesungen an der FHWS";

$data = [ "BEGIN:VCALENDAR
PRODID:-//SEBASTIAN KAIM//VP 1.0//EN
VERSION:2.0
NAME:$name
X-WR-CALNAME:$name
DESCRIPTION:$descr
X-WR-CALDESC:$descr
CALSCALE:GREGORIAN" ];

if(isset($_REQUEST['classes'])) {
    // get the ids (and filter empty ones)
    $ids = array_filter(explode(',', $_REQUEST['classes']), 'strlen');
    // fetch the raw data
    $classes = array_map(function($id) { return fetchIcs($id); }, $ids);
    $data = array_merge($data, array_map(function ($ic) { return cleanIcal($ic); }, $classes));
}

$data[] = "END:VCALENDAR";


echo implode("\n", $data);
echo "\n";





