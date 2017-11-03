<?php

require 'rb.php'; //use the red bean php package
R::setup('sqlite:red.db'); //connect to the SQLite database file
$users = R::findAll('user'); //get all rows in table 'user'
$hostName = $_SERVER['HTTP_HOST']; //get hostname url
$ch = curl_init(); //intiate curl object
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
foreach ($users as $user) { //for each user row in table
    $id = $user->playerID; //set $id to be the player id column for the row
    $templateId = $user->templateID; //set $template to be the template choice column for the row
    //set the curl url to the mkimg page for the user and execute job
    curl_setopt($ch, CURLOPT_URL, "http://$hostName/mkimg.php?id=$id&template=$templateId");
    curl_exec($ch);
}
curl_close($ch); //close curl session after every user updated
R::close(); //close the database connection