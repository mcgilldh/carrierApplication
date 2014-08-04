<?php

   
   session_start();

   //If we are here to look at a particular carrier, load that charrier
   if (isset($_GET['carrID'])) {
       echo "<script>var CUR_CARR='".$_GET['carrID']."';</script>";
   }

   //If we are not logged in, then redirect to the login page
   if (!isset($_SESSION['user'])) {
       echo "<h1>Redirecting to login page...</h1>";
       header('Location: index.php');
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

    <script>
      //If we have been linked here by a specific carrier, load it up.
      if (typeof CUR_CARR != 'undefined') {
          $(document).ready(function () {
              $("#idBar").val(CUR_CARR);
              getCarrInfo(CUR_CARR);
          });
      }
    </script>
    

    
    <div id="main">
      
      <div id="searchBar">
	
	Please insert Carrier Tracking ID: <input type="text" name="idBar" id="idBar">	  
	
	<a href="#"><div id="submitID" class="button" onclick="getCarrInfo($('#idBar').val()); return false;"> Search DB </div></a>
      </div>
      
      <div id="middleFrame">
	<div class="title">
	  <h3 id="ownerTitle" class="title"> Owner:  </h3>
	</div>
	<div id="mainInfo">


	  <a href="logout.php"><div id="textileButton" class="submitChanges">Log out of:
	      <?php echo $_SESSION['user'];?>

	  </div></a><br>
	  
	  <a href="#"><div id="textileButton" class="submitChanges" onclick="gotoTextiles($('#idBar').val()); return false;">Go to Textiles</div></a>

	  <div id="carrInfo" class="carrInfo">
	    <div id="carrDets" class="carrCol">
	      
	      <table id="carrDetTable" class="carrTable">
		<tr>
		  <td>Carrier Reference #:</td>
		  <td><input id="refField" type="text"></td>
		</tr>
		<tr>
		  <td>Carrier Name:</td>
		  <td><input id="nameField" type="text"></td>
		</tr>
		<tr>
		  <td>Style Reference:</td>
		  <td><input id="styleField" type="text"></td>
		</tr>
		<tr>
		  <td>Issue Date Start:</td>
		  <td><input id="startField" type="text"></td>
		</tr>
		<tr>
		  <td>Issue Date End:</td>
		  <td><input id="endField" type="text"></td>
		</tr>
		<tr>
		  <td>Textile Count:</td>
		  <td><input id="countField" type="text"></td>
		</tr>
	      </table>
	      
	    </div>
	    
	    <div id="carrDescrs" class="carrCol">
	      
	      <table>
		<tr>
		  <td>Description</td>
		  <td><textarea id="descrField" class="carrDescr" rows="4" cols="50"> </textarea></td>
		</tr>
		<tr>
		  <td>Notes</td>
		  <td><textarea id="notesField" class="carrDescr" rows="4" cols="50"> </textarea></td>
		</tr>
		<tr>
		  <td>Condition Notes</td>
		  <td><textarea id="condNotesField" class="carrDescr" rows="4" cols="50"> </textarea></td>
		</tr>
	      </table>
	      
	    </div>

	    <div id="carrOpts" class="carrCol">
	      <table>
		<tr>
		  <td>OADB</td>
		  <td><input id="oadbBox" type="checkbox"></td>
		</tr>
		<tr>
		  <td>VT Com</td>
		  <td><input id="vtcommBox" type="checkbox"></td>
		</tr>
		<tr>
		  <td>Condition</td>
		  <td id="condTd"></td>
		</tr>
	      </table>
	      <br>
	      <a href="#"><div id="submitCarrDets" class="submitChanges" onclick="submitCarrDets(); return false;"> Submit Changes </div></a>
	    </div>

	    
	  </div>
	  
	  <div id="provenance" class="carrInfo">
	    <h3>Provenance</h3>
	  </div>
	  <a href="#"><div id="submitProv" class="submitChanges" onclick="submitProv(); return false;"> Submit Changes </div></a>

	</div> 
      </div>
      
      
    </div>
    
  </body>
  
</html>


<?php
   }
?>
