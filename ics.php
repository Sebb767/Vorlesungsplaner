<?php

// this file does some _very_ simple ics parsing
// it basically fetches the ics from the API, takes a substring from the first BEGIN:VEVENT to the last END:VEVENT and
// then simply pastes together the content from all the calendars in the template

/**
 * Fetches the ical data from the FIWIS api.
 * @param $id int The id to look for.
 * @return string The raw response text
 */
function fetchIcs($id) {
    $id = (int)$id; // this will prevent non-numeric input
    $url = "https://fiwis.fiw.fhws.de/fiwis2/api/classes/$id/ical";

    $ch = curl_init($url);

    // set the accept header
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: text/calendar'));

    // return response data instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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





