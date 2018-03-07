<?php

require 'rb.php';
R::setup('sqlite:red.db');

// START CUSTOM FUNCTIONS
//imagecopymerge_alpha() by Sina Salek
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){ 
    // creating a cut resource 
    $cut = imagecreatetruecolor($src_w, $src_h); 
    // copying relevant section from background to the cut resource 
    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
    // copying relevant section from watermark to the cut resource 
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
    // insert cut resource to destination image 
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
} 
//insert_box_text gets the bounding box dimensions for the passed text and
//calculates the optimal location to place the text by subtracting the 
//height and width by the image's box's values and adding it to a specified pixel
function insert_box_text($string, $image, $color, $xc, $yc, $head, $temp) {
    if ($temp == 1) {
        $tb = imagettfbbox(8,0,"./resources/consolab.ttf",$string);
        $x = $xc + ceil((61 - ($tb[2] - $tb[0])) / 2);
        if ($head == true)
            $y = $yc + ceil((14 - ($tb[7] - $tb[1])) / 2);
        else
            $y = $yc + ceil((18 - ($tb[7] - $tb[1])) / 2);
        imagettftext($image,8,0,$x,$y,$color,"./resources/consolab.ttf",$string);
    } else if ($temp == 2) {
        if ($head == true) {
            $tb = imagettfbbox(8,0,"./resources/Dosis-Medium.ttf",$string);
            $x = $xc + ceil((67 - ($tb[2] - $tb[0])) / 2);
            $y = $yc + ceil((14 - ($tb[7] - $tb[1])) / 2);
            imagettftext($image,8,0,$x,$y,$color,"./resources/Dosis-Medium.ttf",$string);
       }
        else {
            $tb = imagettfbbox(10,0,"./resources/Dosis-Medium.ttf",$string);
            $x = $xc + ceil((67 - ($tb[2] - $tb[0])) / 2);
            $y = $yc + ceil((28 - ($tb[7] - $tb[1])) / 2);
            imagettftext($image,10,0,$x,$y,$color,"./resources/Dosis-Medium.ttf",$string);
        }
    }
}

function scrape_info() {
    global $NumEvents, $wins, $seconds, $top10, $top25, $cutsMade, $cutsMissed;
    global $cutPercent, $money, $seasonStart, $moneyRank, $wgr, $tour, $name;
    global $memSince, $country, $rounds, $platform, $platUser, $playerId;
    
    $curl = curl_init(); //initialize curl used for a single URL
    $DOM = new DOMDocument; //create DOM object to handle page returns
    libxml_use_internal_errors(true); //ignore HTML5 tag errors
    $url = "http://tgctours.com/player/OverView/$playerId"; //set url to player page
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_URL, $url);
    $overviewPage = curl_exec($curl); //execute curl and sage the response
    $DOM->loadHTML($overviewPage); //load the page to the DOM object
    $xpath = new DOMXPath($DOM); //create an XPath to parse the DOM object
    $entries = $xpath->query("//div[@class='box']/h2");
    $moneyRank = trim($entries->item(1)->textContent); //moneyRank is the second H2 box on overview page
    $wgr = preg_replace('/[^0-9.]+/', '', $entries->item(3)->firstChild->nodeValue);//WGR is the 4th H2 box on overview page, remove everything but numbers
    $tour = trim(explode(' ',$xpath->query("//section[@class='content clearfix']/h1")->item(0)->textContent)[1]); //get tour header under player name
    $name = trim($xpath->query("//div[@class='meta']/h1")->item(0)->textContent); //get the player name H1
    $memSince = trim($xpath->query("//div[@class='meta']/p")->item(0)->childNodes->item(2)->nodeValue); //get the memsince under player name
    $country = trim($xpath->query("//section[@id='title']/div")->item(0)->nodeValue); //get the country next to the player name
    
    $tours = array("18", "1", "2", "4", "10", "11", "12", "13", "14", "15"); //create array of tour numbers set on the tour dropdown menu
    foreach ($tours as $tournum) { //for each tour number
        $url = "http://tgctours.com/player/season/$playerId?tourId=$tournum&season="; //set the url to be a tour at the current season
        curl_setopt($curl, CURLOPT_URL, $url);
        $page = curl_exec($curl); //get the page response for a tour
        $DOM->loadHTML($page); //load response into DOM object
        $xpath = new DOMXPath($DOM); //get new XPath object to parse response
        $curTour = explode(' ',$xpath->query("//h1")->item(1)->textContent)[0]; //get which tour page was loaded
        $nodes = $xpath->query("//table[@id='summary']/tbody/tr/td"); //get the first data table on the seasons page
        $cats = array();
        //loop through the first data table and save stats to array for later use, replace "--" with 0's
        for ($i = 0; $i < $nodes->length; $i++) {
            $strTitle = $nodes->item($i)->nodeValue;
            if ($strTitle == "--")
                array_push($cats, 0);
            else {
                array_push($cats, filter_var($strTitle,FILTER_SANITIZE_NUMBER_INT));
            }
        }
        $wins += $cats[1]; //wins is the second column in table
        $seconds += $cats[2]; //second place is third column in table 
        $top10 += $cats[4]; //top 10 is fifth column in table 
        $top25 += $cats[5]; //top 25 is sixth column in table 
        $money += $cats[7]; //money amount is eighth column in table 

        $items = $xpath->query("//table[@id='results']/tbody/tr"); //get the second data table on seasons page
        if ($items->length == 0)
            continue; //if there are no rows player hasn't played on that tour, so skip this page
        foreach ($items as $item) { //for each tournament row in second data table
            if (preg_replace('/\s+/', '', $item->childNodes->item(4)->nodeValue) == 'DidNotPlay')
                continue; //if a row says the player did not play, skip it
            $round = R::dispense('round'); //create a new row in the database table 'round'
            $round['player'] = $playerId;
            $date = date_create_from_format("n/d/Y H:i:s","{$item->childNodes->item(0)->nodeValue} 00:00:00"); //get the date column as datetime object
            $week = 1 + (($seasonStart->diff($date)->days) / 7); //calculate which week the tournament is from the season start date
            $round['date'] = $date; // set the row's date value
            $round['week'] = $week; // set the row's week value
            if($curTour == "European")
                $curTour = "Euro";
            elseif ($curTour == "Web.com")
                $curTour = "Web";
            $round['league'] = $curTour;
            if (preg_replace('/\s+/', '', $item->childNodes->item(4)->nodeValue) == 'WDfromEvent')
                $round['place'] = "WD"; //if the tournament row says the player was withdrawn, keep the row but make the place reflect they were withdrawn
            else
                $round['place'] = $item->childNodes->item(4)->nodeValue; //set the tournament place for the row
            $tourn = trim($item->childNodes->item(2)->nodeValue); //save the tournament name to the row
            $tourn_words = explode(' ',trim($item->childNodes->item(2)->nodeValue));
            foreach($tourn_words as $tourn_word) {
                if (strpos($tourn_word, '"') !== false or strpos($tourn_word, "'") !== false) {
                    $tourn = $tourn_word;
                    $tourn = str_replace(array("'", "\"", "&quot;"), "", $tourn);
                    break;
                }
            }
            $url = $xpath->query("//table[@id='results']/tbody/tr/td/a[contains(text(),'$tourn')]/@href");
            $round['tournID'] = explode('/', $url->item(0)->nodeValue)[3]; //get the tournament ID fot the row using the name
            if (strpos($item->childNodes->item(4)->nodeValue,"WD") !== false || $item->childNodes->item(10)->nodeValue == "--")
                $cutsMissed++; //if the player was withdrawn, or the 3rd round scores show as --, add 1 to the cutsMissed tracker
            else
                $cutsMade++; //else add 1 to the cutsMade tracker
            R::store($round); //save the row in the database
        }
    }
    $query = "SELECT * FROM round WHERE player = $playerId ORDER BY date DESC";
    $rounds = R::getAll($query); //get all tournaments from every tour and order by descending date
    $rounds = R::convertToBeans( 'round', $rounds );
    $rounds = array_values($rounds); //reorder the id numbers of the array with the new date order
    $NumEvents = count($rounds); //count the number of tournaments in the table for the number of events
    $cutPercent = round((($cutsMade / ($cutsMade + $cutsMissed)) * 100), 2); //calculate cutPercent using the two trackers
    //modify how the money is displayed: $999,999 or $1.234M
    if ($money <= 999999)
        $money = number_format($money,0,'.',',');
    else
        $money = number_format(($money / 1000000),3,'.',',')."M";

    $url = "http://tgctours.com/Tournament/Leaderboard/{$rounds[0]->tournID}"; //set the url to the latest tournament's leaderboard
    curl_setopt($curl, CURLOPT_URL, $url);
    $page = curl_exec($curl);
    $DOM->loadHTML($page);
    $xpath = new DOMXPath($DOM);
    $platform = $xpath->query("//td[@class='ta-left']/a[text()='$name']/@title")->item(0)->nodeValue; //grab the platform the player is registered for
    $platUser = preg_replace('/\s+/', '', explode('-',$platform)[1]); //grab the username the player is registered on the platform with
    $platform = preg_replace('/\s+/', '', explode('-',$platform)[0]);
    //format the $platform variable for use later
    if ($platform == "PC")
        $platform = "STEAM:$platUser";
    elseif ($platform == "XB1")
        $platform = "XBOX ONE:$platUser";
    elseif ($platform == "PS4")
        $platform = "PS4:$platUser";
    else
        $platform = "null:$platUser";
    curl_close($curl); //close the curl connection, no longer needed
}

function make_image_1() { //for EmeraldPi template choice
    global $NumEvents, $wins, $seconds, $top10, $top25;
    global $cutPercent, $money, $moneyRank, $wgr, $tour, $name;
    global $memSince, $country, $rounds, $platform, $file;
    
    $template = imagecreatefrompng("resources/template.png"); //load template image
    //get the tour logo based on $tour variable from before
    if ($tour == "World")
        $logo = imagecreatefrompng("resources/World-logo.png");
    elseif ($tour == "PGA")
        $logo = imagecreatefrompng("resources/PGA-logo.png");
    elseif ($tour == "European")
        $logo = imagecreatefrompng("resources/European-logo.png");
    elseif (strpos($tour,"Web") !== false)
        $logo = imagecreatefrompng("resources/Web-logo.png");
    elseif (strpos($tour,"CC") !== false)
        $logo = imagecreatefrompng("resources/CC-logo.png");
    $logoresized = imagecreate(87, 87); //make a blank image to hold tour logo
    imagecopyresized($logoresized, $logo, 0, 0, 0, 0, 87, 87, imagesx($logo),imagesy($logo)); //resize tour logo and save to blank image
    imagecopymerge_alpha($template,$logoresized,387,50,0,0,imagesx($logoresized),imagesy($logoresized),100); //copy the tour logo and place on template image
    $color = imagecolorallocate($template, 255, 255, 255); //set white color to use for text on template image
    //place the player name, country, tour, platform, and member since values on the template image
    imagettftext($template,10.5,0,45,62,$color,"./resources/consolab.ttf",$name);
    imagettftext($template,10.5,0,71,80,$color,"./resources/consolab.ttf",$country);
    imagettftext($template,10.5,0,50,98,$color,"./resources/consolab.ttf",$tour);
    imagettftext($template,10.5,0,110,116,$color,"./resources/consolab.ttf",$memSince);
    imagettftext($template,10.5,0,11,135,$color,"./resources/consolab.ttf",$platform);
    //use insert_box_text to place the stats centered in their respective boxes
    insert_box_text($NumEvents, $template, $color, 0, 175, false, 1);
    insert_box_text($wins, $template, $color, 63, 175, false, 1);
    insert_box_text($seconds, $template, $color, 125, 175, false, 1);
    insert_box_text($top10, $template, $color, 187, 175, false, 1);
    insert_box_text($top25, $template, $color, 249, 175, false, 1);
    insert_box_text("$cutPercent%", $template, $color, 311, 175, false, 1);
    insert_box_text("$$money", $template, $color, 373, 173, false, 1);
    insert_box_text($moneyRank, $template, $color, 435, 175, false, 1);
    insert_box_text($wgr, $template, $color, 0, 232, false, 1);
    //put the rounds and their places on the template image using insert_box_text
    for ($i = 1; $i <= count($rounds) && $i < 8; $i++) {
        insert_box_text("WK {$rounds[$i - 1]->week}", $template, $color, (62 * $i + 1), 203, true, 1);
        insert_box_text($rounds[$i - 1]->league, $template, $color, (62 * $i + 1), 216, true, 1);
        insert_box_text($rounds[$i - 1]->place, $template, $color, (62 * $i + 1), 232, false, 1);
    }
    imagepng($template,$file,6); //save the image to a file
}

function make_image_2() { //for Pablo template choice
    global $NumEvents, $wins, $seconds, $top10, $top25;
    global $cutPercent, $money, $moneyRank, $wgr, $tour, $name;
    global $memSince, $country, $rounds, $platform, $file;
    
    //get the template based on $tour variable from before
    if ($tour == "World")
        $template = imagecreatefrompng("resources/pablo-templates/world.png");
    elseif ($tour == "PGA")
        $template = imagecreatefrompng("resources/pablo-templates/pga.png");
    elseif ($tour == "European")
        $template = imagecreatefrompng("resources/pablo-templates/euro.png");
    elseif (strpos($tour,"Web") !== false)
        $template = imagecreatefrompng("resources/pablo-templates/web.png");
    elseif (strpos($tour,"CC") !== false)
        $template = imagecreatefrompng("resources/pablo-templates/challenge.png");
    $color = imagecolorallocate($template, 255, 255, 255); //set white color for use for text on template image
    ///place the player name, country, tour, platform, and member since values on the template image
    imagettftext($template,12,0,79,139,$color,"./resources/BankGothic-Regular.ttf",strtoupper($name));
    imagettftext($template,12,0,114,168,$color,"./resources/BankGothic-Regular.ttf",strtoupper($country));
    imagettftext($template,12,0,95,197,$color,"./resources/BankGothic-Regular.ttf",strtoupper($memSince));
    $system = explode(":",$platform)[0];
    if ($system == "STEAM")
        $system = "PC"; //ues PC instead of STEAM for the 'system' identifier on the template
    imagettftext($template,12,0,405,139,$color,"./resources/BankGothic-Regular.ttf",$system);
    imagettftext($template,12,0,348,168,$color,"./resources/BankGothic-Regular.ttf",strtoupper(explode(":",$platform)[1]));
    //use insert_box_text to place the stats centered in their respective boxes
    insert_box_text($NumEvents, $template, $color, 1, 237, false, 2);
    insert_box_text($wins, $template, $color, 69, 237, false, 2);
    insert_box_text($seconds, $template, $color, 136, 237, false, 2);
    insert_box_text($top10, $template, $color, 204, 237, false, 2);
    insert_box_text($top25, $template, $color, 271, 237, false, 2);
    insert_box_text("$cutPercent%", $template, $color, 341, 237, false, 2);
    insert_box_text("$$money", $template, $color, 407, 237, false, 2);
    insert_box_text($moneyRank, $template, $color, 476, 237, false, 2);
    insert_box_text($wgr, $template, $color, 1, 296, false, 2);
    //put the rounds and their places on the template image using insert_box_text
    for ($i = 1; $i <= count($rounds) && $i < 8; $i++) {
        insert_box_text("Week {$rounds[$i - 1]->week}", $template, $color, (67 * $i + 2), 267, true, 2);
        insert_box_text($rounds[$i - 1]->league, $template, $color, (67 * $i + 2), 281, true, 2);
        insert_box_text($rounds[$i - 1]->place, $template, $color, (67 * $i + 1), 296, false, 2);
    }
    imagepng($template,$file,6); //save the image
}
// END CUSTOM FUNCTIONS

$playerId = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_URL); //get the player id set in the URL
if ($playerId == NULL)
    $playerId = 1;

$templateId = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_URL); //get the template id set in the URL
if ($templateId == NULL)
    $templateId = 1;

//set up database query to find if the player is already in the database
$query = sprintf('WHERE player_id = %s', $playerId);
$user = R::find('user',$query); //search database using the query for the user
$user = reset($user); //get only the first element in the database response object, there should only be one anyways
if (empty($user) == false) { //if the response is not empty
    if ($user->templateID != $templateId) { //if the template choice set in the database is not the same one requested
        $user->templateID = $templateId; //update the row with the new template choice
        R::store($user); //save the table row
    } //do nothing else if id and template requests match database
}
else { //if the response is empty the user is not in the database, create a new entry in the database for the user
    $user = R::dispense('user');
    $user['playerID'] = $playerId;
    $user['templateID'] = $templateId;
    R::store($user);
}

// START GLOBAL VARS
$NumEvents = 0;
$wins = 0;
$seconds = 0;
$top10 = 0;
$top25 = 0;
$cutsMade = 0;
$cutsMissed = 0;
$cutPercent = 0.0;
$money = 0;
$seasonStart = date_create_from_format("m/d/Y H:i:s","10/16/2017 00:00:00");
$moneyRank = 0;
$wgr = 0;
$tour = "null";
$name = "null";
$memSince = "null";
$country = "null";
$file = "cards/$playerId.png";
// END GLOBAL VARS

scrape_info();
if($templateId == 1)
    make_image_1();
else if ($templateId == 2)
    make_image_2();
else 
    make_image_1();
//prepare the saved image for content sent to the browser upon completion
$fp = fopen($file, 'rb'); //open the file
header('Content-type: image/png'); //set the HTML header for an image object
header("Content-Length: " . filesize($file)); //set teh HTML header's length for the image size
fpassthru($fp); //send the content to the browser
R::exec("DELETE FROM round WHERE player = $playerId");
//R::wipe('round'); //delete all tournament rows created in the 'round' table during script execution
R::close(); //close the database connection