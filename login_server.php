<?php


$vars = $_POST;

session_start();

if ($vars['usrnm']==$vars['pswd'] && isset($vars['usrnm']) && isset($vars['pswd'])) {
    $_SESSION['user'] = $vars['usrnm'];
    header('Location: carrier.php');
}
else {
    header('Location: index.php');
}



?>