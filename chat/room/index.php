<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    session_start();

    if (isset($_GET['name']) && isset($_SESSION['userid'])): 
      require_once("../dbcon.php");
      $name = cleanInput($_GET['name']);
      $getRooms = "SELECT *
  			           FROM chat_rooms
  		             WHERE name = '$name'";
  		         
      $roomResults = mysqli_query($success, $getRooms);
		
	  	if (mysqli_num_rows($roomResults) < 1) {
  			header("Location: ../chatrooms.php");
  			die();
  		}
        	
      while ($rooms = mysqli_fetch_array($roomResults)) {
          $file =  $rooms['file'];
      }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta
        name='viewport'
        content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'
        />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <title>Welcome to: <?php echo $name; ?></title>
    <link rel="stylesheet" type="text/css" href="../main.css"/>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="chat.js"></script>
    <script type="text/javascript">
    	var chat = new Chat('<?php echo $file; ?>');
    	chat.init();
    	chat.getUsers(<?php echo "'" . $name ."','" .$_SESSION['userid'] . "'"; ?>);
    	var name = '<?php echo $_SESSION['userid'];?>';
    </script>
    <script type="text/javascript" src="settings.js"></script>

</head>

<body>

    <div id="page-wrap"> 
    	<div id="header">
        	<div id="you"><span>Logged in as:</span> <?php echo $_SESSION['userid']?>
                <span id="logout"><a href="/chat/logout.php">Logout</a></span>
            </div>
        </div>
        
    	<div id="section">
            <h2><?php echo $name; ?></h2>
            <div id="chat-wrap">
                <div id="chat-area"></div>
            </div>
            
            <!-- <div id="userlist"></div> -->
                
                <form id="send-message-area" action="">
                    <textarea id="sendie" maxlength='100'></textarea>
                </form>
            
        </div>
        
    </div>
        
</body>

</html>

<?php
    else:
            header('Location: /chat');
    endif; 
?>
