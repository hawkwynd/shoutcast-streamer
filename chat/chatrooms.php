<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//session_start();

    require_once("dbcon.php");
    global $success;

if (checkVar($_SESSION['userid'])):
        $getRooms = "SELECT * FROM chat_rooms";
        $roomResults = mysqli_query($success, $getRooms);
       header('Location: /chat/room/?name=requests'); // just go right to the requests channel
?>
<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chat Rooms</title>
    <link rel="stylesheet" type="text/css" href="main.css"/>
</head>

<body>

    <div id="page-wrap"> 
    	<div id="header">
        	<div id="you">
                <span>Your handle:</span> <?php echo $_SESSION['userid']?></div>
        </div>
        
    	<div id="section">
            <div id="rooms">
            	<h3>Rooms</h3>
                <ul>
                    <?php 
                        while($rooms = mysqli_fetch_array($roomResults)):
                            $room   = $rooms['name'];
                            $query  = mysqli_query($success, "SELECT * FROM chat_users_rooms WHERE room = '$room' ") or die("Cannot find data". mysqli_error($success));
                            $numOfUsers = mysqli_num_rows($query);
                    ?>
                    <li>
                        <a href="room/?name=<?php echo $rooms['name']?>">
                            <?php echo $rooms['name'] . "<span>Users chatting: <strong>" . $numOfUsers . "</strong></span>" ?></a>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
        
    </div>

</body>

</html>
-->
<?php 

    else: 
	  // header('Location: /chat');
	   
	endif;
	
?>