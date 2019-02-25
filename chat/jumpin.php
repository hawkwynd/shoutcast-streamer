<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('dbcon.php');

global $success;

    if (isAjax()) {
    	$data       = array();
    	$username   = cleanInput($_POST['userid']);
    	
    	if (checkVar($username)) {

    		$getUsers = "SELECT * FROM chat_users WHERE username = '$username'";
    					 
    		if (!hasData($getUsers)) {
    		  $data['result'] = "<div class='message success'>Great! You found a username not in use</div>";
    		  $data['inuse'] = "notinuse";
    		} else {
    		  $data['result'] = "<div class='message warning'>That username is already in use. (Usernames take 2 minutes without use to expire, so please wait.)</div>";
    		  $data['inuse'] = "inuse";
    		}
    		
    		echo json_encode($data);
    			
    	}
    	
    } else {
    
        $username = cleanInput($_POST['userid']);
        
    	if (checkVar($username)) {
    		$getUsers = "SELECT * FROM chat_users WHERE username = '$username'";
    		if (!hasData($getUsers)) {
    			$now = time();
    		    $postUsers = "INSERT INTO chat_users (id,username,status,time_mod) VALUES
                                                      (NULL , '$username', '1', '$now')";
    		    mysqli_query($success, $postUsers);
    		    			
    			$_SESSION['userid'] = $username;
    		  	    header('Location: ./chatrooms.php');

            } else {
    			
    		  header('Location: ./?error=1');
    			
    		}
    
    	}
    
    }

    header('Location: /chat');
?>