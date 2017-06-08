<?php
if(isset($_SERVER['HTTP_REFERER'])) {
    $MAX_REQUESTS = 2;
    $ref = $_SERVER['HTTP_REFERER'];
    $ref = removeHTTP($ref);
    $whitelist = getwhitelists();
    foreach($whitelist as $whitelistedSite) {
        $whitelistedSite = trim ($whitelistedSite);
        if($whitelistedSite == $ref) {
            return;
        }
    }
    $counters = getsCounted($ref);
    if($counters[$ref] > $MAX_REQUESTS) {
        putInHTACCESS($ref);
    }
}


function removeHTTP($input) {
    $input = trim($input, '/');

    // If scheme not included, prepend it
    if (!preg_match('#^http(s)?://#', $input)) {
        $input = 'http://' . $input;
    }

    $urlParts = parse_url($input);

    // remove www
    $domain = preg_replace('/^www\./', '', $urlParts['host']);

    return $domain;

}

function putInHTACCESS($value)
{
    #region OLD METHOD
    // $myfile = fopen(".htaccess", "r+") or die("Unable to open file!");
    
    // // while(!feof($myfile)) {
    // //     //echo fgets($myfile);
    // // }

    // // $txt = "John Doe\n";
    // // fwrite($myfile, $txt);
    // // $txt = "Jane Doe\n";
    // // fwrite($myfile, $txt);
    // fclose($myfile);
    #endregion

    $file_data = "SetEnvIfNoCase Referer ".$value." bad_referer\n";
    $file_data .= file_get_contents('.htaccess');
    file_put_contents('.htaccess', $file_data);
}

function getwhitelists() {
    // $arr = array();
    // $myfile = fopen("whitelist.txt", "r") or die("Unable to open file!");
    // while(!feof($myfile)) {
    //     $arr[] = fgets($myfile);
    // }
    // fclose($myfile);

    $whitlelisted = [
        'jvzoo.com',
        'google.com'
    ];
    return $whitlelisted;
}

function getsCounted($str) {
    $file = "counter.json";
    $timeDiffinSecs = 300;
    $json = json_decode(file_get_contents($file),TRUE);
    
    if(isset($json['counters']))    //set counters
        $counters = $json['counters'];
    else
        $counters = [];

    if(isset($json['time'])) {
        $diff = time() - strtotime($json['time']);
        if($diff > $timeDiffinSecs) {
            $counters = [];
            $json['time'] = date('Y-m-d H:i:s');
        }
    } else {
        $json['time'] = date('Y-m-d H:i:s');
    }

    $found = false;
    foreach ($counters as $index => $record) {
        if($index == $str) {
            $counters[$index]++;
            $found = true;
        }
    }
    if(!$found) {
        $counters[$str] = 1;
    }

    $json['counters'] = $counters;

    file_put_contents($file, json_encode($json));


    return $counters;
}