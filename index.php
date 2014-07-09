<html>
  <head>
    <title> Application for carrier updates </title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>

    <script src="ui_fns.js"></script>
    <link rel="stylesheet" type="text/css" href="applicationMain.css">
  </head>
  
  <body>
    <div id="main">

      <div id="searchBar">
	<form name="carrierId">
	  Please insert Carrier Tracking ID: <input type="text" name="idBar" id="idBar">	  
	</form>
	<a href="#"><div id="submitID" class="button" onclick="getInfo($('#idBar').val())"> Search DB </div></a>
      </div>
      
      <div id="middleFrame">
	<div class="title">
	  <h3 class="title"> Owner ID:  </h3>
	</div>
	<div id="mainInfo">
	
	</div> 
      </div>
      

    </div>

  </body>

</html>
