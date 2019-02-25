<?php
/**
 * Date: 2/11/19
 * Time: 4:53 PM
 * hawkwynd.com - sfleming
 */
session_start();

//print_r($_SESSION);

unset($_SESSION['userid']);

header('Location: /chat');