<?php
include 'dbconfig.php';
include 'dbconnect.php';


//Verify the equality of a row with its entry in the DB
function sameRow($con, $prov_row, $prov_id) {
    $selectQuery = "select own_id, own_st_dt, own_end_dt, own_term_cd, own_type_cd from provenance where provenance_id='".$prov_id."'";
    $response = mysqli_query($con, $selectQuery) or die("Query fail: ".mysqli_error($con));
    $dbRow = mysqli_fetch_assoc($response);

    //echo var_dump($prov_row);
    //echo var_dump($dbRow);
    //echo $prov_id;


    return ($prov_row['owner']==$dbRow['own_id'] && $prov_row['type']==$dbRow['own_type_cd']
        && $prov_row['start']==$dbRow['own_st_dt'] && $prov_row['end']==$dbRow['own_end_dt']
	&& $prov_row['term']==$dbRow['own_term_cd']);
        
}

session_start();
if (!isset($_SESSION['user'])) {
    echo "Error! Not logged in";
    return;
}


$vars = $_POST;

if (isset($vars['carr_id'])) {

    $provQuery = "Call `carrapp01_get_provenance_unique` ('".$vars['carr_id']."')";
    $provResponse = mysqli_query($mysqli, $provQuery) or die("Query fail: " . mysqli_error($mysqli));

    $provTable = array();
    while ($provRow = mysqli_fetch_assoc($provResponse)) {
    	  $provEntry = array(
	      "provId" => $provRow['provenance_id'],
	      "owner" => $provRow['own_id'],
	      "start" => $provRow['own_st_dt'],
	      "end" => $provRow['own_end_dt'],
	      "status" => $provRow['own_status'],
	      "term" => $provRow['own_term_cd'],
	      "type" => $provRow['own_type_cd']
	  );
	  array_push($provTable, $provEntry);
    }

    //I don't know why this is required. Without it I get "queries out of order" error
    mysqli_close($mysqli);
    include 'dbconnect.php';

    $carrInfoQuery = "Call `carrapp01_get_carr` ('".$vars['carr_id']."')";

    $response = mysqli_query($mysqli, $carrInfoQuery) or die("Query fail: " . mysqli_error($mysqli));
    $carrInfo = mysqli_fetch_assoc($response);


    $newRow = array(
         "ref" => $carrInfo['textile_carr_ref'],
         "name" => $carrInfo['textile_carr_nm'],
         "descr" => $carrInfo['textile_carr_descr'],
         "count" => $carrInfo['textile_cnt'],
         "notes" => $carrInfo['notes'],
         "style" => $carrInfo['manf_style_ref'],
         "condNotes" => $carrInfo['cond_notes'],
         "start" => $carrInfo['issue_dt_start'],
         "end" => $carrInfo['issue_dt_end'],
         "oadb" => $carrInfo['oadb_flag'],
         "vtcomm" => $carrInfo['vtcomm_flag'],
         "tracking" => $carrInfo['vt_tracking'],
         "ownName" => $carrInfo['own_nm'],
         "cond" => $carrInfo['cond_cd'],
     	"provenance" => $provTable
    );

    echo json_encode($newRow);

        
    
}

else if (isset($vars['mode']) && $vars['mode']=='lists') {

     $mainArray = array();

    //Get all possible owners
    $getOwnerString = "SELECT own_nm, own_id FROM textile_owner";
    $queryOwner = mysqli_query($mysqli, $getOwnerString);
    $ownerEntry = array();
    while ($row = mysqli_fetch_assoc($queryOwner)) {
        array_push($ownerEntry, array('name' => $row['own_nm'], 'id' => $row['own_id']));
    }



    $typeEntry = array();
    $termEntry = array();
    $condEntry = array();

    //Get all possible types and terms differentiated by 'cd_attr_name' field
    $getAttributeString = "SELECT * FROM codec";
    $queryAttribute = mysqli_query($mysqli, $getAttributeString);
    while ($row = mysqli_fetch_assoc($queryAttribute)) {
        $attrName = $row['cd_attr_nm'];
        $attrId = $row['cd_id'];
        $attrVal = $row['cd_valu']; //Type??

        if ($attrName=="own_type_cd") {
            array_push($typeEntry, array('name' => $attrName, 'id' => $attrId, 'value' => $attrVal));
        }
        else if ($attrName=="own_term_cd") {
            array_push($termEntry, array('name' => $attrName, 'id' => $attrId, 'value' => $attrVal));
        }
        else if ($attrName=="cond_cd") {
            array_push($condEntry, array('name' => $attrName, 'id' => $attrId, 'value' => $attrVal));
        }
    }

    //Add each of these to the final array that will be printed
    $mainArray['owner'] = $ownerEntry;
    $mainArray['type'] = $typeEntry;
    $mainArray['term'] = $termEntry;
    $mainArray['cond'] = $condEntry;

    echo json_encode($mainArray);
    
}


else if (isset($vars['mode']) && $vars['mode']=="submitCarrDets") {
   if ($vars['oadb']=="true") $oadb = "true";
   else $oadb = "false";
   
   if ($vars['vtcomm']=="true") $vtcomm = "true";
   else $vtcomm = "false";

    $updateQuery = "Update `textile_carr`
  SET 
   `textile_carr_ref` = '".$vars['ref']."'
   , `textile_carr_nm` = '".$vars['name']."'
   , `textile_carr_descr` = '".$vars['descr']."'
   , `textile_cnt` = '".$vars['count']."'
   , `notes` = '".$vars['notes']."'
   , `manf_style_ref` = '".$vars['style']."'
   , `cond_cd` = ''
   , `cond_notes` = '".$vars['condNotes']."'
   , `issue_dt_start` = '".$vars['start']."'
   , `issue_dt_end` = '".$vars['stop']."'
   , `cond_cd` = '".$vars['cond']."'
   , `oadb_flag` = ".$oadb."
   , `vtcomm_flag` = ".$vtcomm."
 WHERE `vt_tracking` = '".$vars['trackId']."'
";
   echo $updateQuery;

    mysqli_query($mysqli, $updateQuery) or die("Query fail: " . mysqli_error($mysqli));  
}


else if (isset($vars['mode']) && $vars['mode']=="applyProv") {
   $provContents = json_decode($vars['prov_contents'], true);

   


   $getInstancesQuery = "select textile_inst_id from textile_instance a, textile_carr b where a.textile_carr_id = b.textile_carr_id and b.vt_tracking='".$vars['carrID']."'";
   $instancesResponse = mysqli_query($mysqli, $getInstancesQuery) or die("Query fail: " . mysqli_error($mysqli));

   $instances = array();
   while ($instRow = mysqli_fetch_assoc($instancesResponse)) {
      array_push($instances, $instRow['textile_inst_id']);

   }


   foreach ($provContents as $provID => $row) {

       //Get the owner and the date of the entry we want to update. This will allow us to find
       //all of the instances.
       $selectQuery = "SELECT own_id, own_st_dt FROM provenance WHERE provenance_id='".$provID."'";
       $selectResponse = mysqli_query($mysqli, $selectQuery);
       $provFind = mysqli_fetch_assoc($selectResponse);

       $oldOwner = $provFind['own_id'];
       $oldStart = $provFind['own_st_dt'];

       $selectQuery = "
       SELECT 
            `provenance_id` 

    FROM `provenance` 

  WHERE  own_st_dt='".$oldStart."' AND own_id='".$oldOwner."'";

       
       $selectResponse = mysqli_query($mysqli, $selectQuery);


       if ($provID!="" && $provID!="new") {
           while ($instProvID = mysqli_fetch_assoc($selectResponse)) {
               $updateQuery = "
UPDATE `provenance`
   SET    `own_type_cd` = '".$row['type']."'
, `own_st_dt` = '".$row['start']."'
, `own_end_dt` = '".$row['end']."'
, `own_status` = ''
, `own_term_cd` ='".$row['term']."'
, `status` = 'Active'
, `mod_usr`= '".$_SESSION['user']."'
, `mod_tmsp` = now()
 WHERE `provenance_id` = '".$instProvID['provenance_id']."'";


               if (!sameRow($mysqli, $row, $instProvID['provenance_id']))
                   mysqli_query($mysqli, $updateQuery) or die("Query fail:".mysqli_error($mysqli));
               
               if ($row['delete']) {
                   $deleteQuery = "DELETE FROM provenance WHERE provenance_id='".$instProvID['provenance_id']."'";
                   mysqli_query($mysqli, $deleteQuery) or die("Query fail:".mysqli_error($mysqli));
               }
           }
           

           
           
       }

      
      
      else if ($provID=="new") {


          foreach($instances as $instID) {
              
              $insertQuery = "
    	          INSERT INTO `provenance`
	               (`own_id`
	   	       , `own_type_cd`
	   	       , `own_st_dt`
	   	       , `own_end_dt`
	   	       , `own_status`
	   	       , `own_term_cd`
	   	       , `status`
	   	       , `creatn_usr`
	   	       , `textile_inst_id`
	               ) 
                  VALUES
		      ('".$row['owner']."'
   		      ,'".$row['type']."'
   		      ,'".$row['start']."'
   		      ,'".$row['end']."'
   		      ,''
   		      ,'".$row['term']."'
   		      , 'Active'
   		      , '".$_SESSION['user']."'
   		      , '".$instID."'
    		      ) 
	          ;
              ";
              
              if ($row['start']!="" && $row['end']!="")
                  mysqli_query($mysqli, $insertQuery) or die("Query fail: " . mysqli_error($mysqli));
              
          }    

      }
      
    }

}



?>