<?php
$mysqli = new mysqli($db_host, $db_user, $db_passwd, $db_name, $db_port);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    return;
}


?>