<?php
   session_start();

   
   if (isset($_SESSION['user'])) {
       echo "<h1>Redirecting to home page...</h1>";
       header('Location: carrier.php');
   }
   else {
   
?>



<html>
  <head>
    <title> Application for carrier updates </title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
    
    <script src="carr_fns.js"></script>
    <link rel="stylesheet" type="text/css" href="applicationMain.css">
  </head>
  
  <body>
    <form name="loginForm" method='post' action="login_server.php">
      Username: <input type="text" name="usrnm"><br>
      Password: <input type="password" name="pswd"><br>
      <input type="submit">
    </form>
  </body>
</html>




<?php
}
?>
