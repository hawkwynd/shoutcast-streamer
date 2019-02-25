<!DOCTYPE html>
<html>
<head>
    <title>Current Listeners</title>
    <link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Current Listeners</h1>
<div class="listeners-container">
<?php
/**
 * Date: 2/7/19
 * Time: 4:49 PM
 * hawkwynd.com - scottfleming
 */
$url  = "http://54.158.47.252:8000/admin.cgi?sid=1&mode=viewxml&page=3&ipcount=1&pass=scootre1";
$out  = array();
$sxml = simplexml_load_file($url);

echo "<h3>".count($sxml->LISTENERS->LISTENER). " total listeners.</h3>";

echo "<ul>";
foreach( $sxml->LISTENERS->LISTENER as $listener){
    $ip      =  $listener->HOSTNAME;
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    echo "<li>" . $ip . ' : '  . $details->city . ', ' . $details->region; // -> "Mountain View"
    echo "</li>";
}
echo "</ul>";
?>
</div>
</body>
</html>
