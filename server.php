<?php
include 'dbconfig.php';
include 'dbconnect.php';


$vars = $_GET; 

$mainArray = array();


if (!empty($vars['carr_id'])) {
    $ids = array();
    $getIdsQueryString = "SELECT * FROM textile_instance WHERE textile_instance.textile_carr_id=".$vars['carr_id'];

    $queryID = mysqli_query($mysqli, $getIdsQueryString); 
    
    while ($row = mysqli_fetch_assoc($queryID)) {
        array_push($ids, array($row['textile_inst_id'], $row['textile_id'], $row['vt_tracking']));
        
    }

    for ($i=0; $i<count($ids); $i++) {
        $tracking = $ids[$i][2];

        $textile_inst_id = $ids[$i][0];
        $textile_id = $ids[$i][1];

        $getDescrString = "SELECT * FROM textile WHERE textile.textile_id=".$ids[$i][1];
        
        
        $queryDescr = mysqli_query($mysqli, $getDescrString);
        $descr = mysqli_fetch_assoc($queryDescr)['textile_descr'];

        $getProvString = "SELECT * FROM provenance WHERE provenance.textile_inst_id=".$ids[$i][0];
        $queryProv = mysqli_query($mysqli, $getProvString);
        $provList = array();
        while ($row = mysqli_fetch_assoc($queryProv)) {
            $newProvRow = array(
                "owner" => $row['own_id'],
                "type" => $row['own_type_cd'],
                "start" => $row['own_st_dt'],
                "end" => $row['own_end_dt'],
                "term" => $row['own_term_cd'],
                "note" => "",
                "provId" => $row['provenance_id']
            );

            array_push($provList, $newProvRow);
        }


        $getTagString = "SELECT * FROM textile_meta_tag WHERE textile_meta_tag.textile_id=".$ids[$i][1];
        $queryTag = mysqli_query($mysqli, $getTagString);
        $tagList = array();
        while ($row = mysqli_fetch_assoc($queryTag)) {
            $tagList[$row['textile_meta_tag_id']] = $row['textile_meta_tag'];
        }

        $newEntry = array(
            "description" => $descr,
            "provenance" => $provList,
            "tags" => $tagList,
            "tracking" => $tracking,
            "textile_id" => $textile_id,
            "textile_inst_id" => $textile_inst_id
        );

        array_push($mainArray, $newEntry);
        

    }

    
}

else if ($vars['mode']=='owners') {
    $getOwnerString = "SELECT own_nm, own_id FROM textile_owner";
    $queryOwner = mysqli_query($mysqli, $getOwnerString);
    while ($row = mysqli_fetch_assoc($queryOwner)) {
        array_push($mainArray, array('name' => $row['own_nm'], 'id' => $row['own_id']));
    }
}


else if ($vars['mode'] == 'apply') {
    $deleteTagList = json_decode($vars['delete_tag_list'], true);
    $modifyTagList = json_decode($vars['modify_tag_list'], true);
    $provContents = json_decode($vars['prov_contents'], true);
    $inst_id = $vars['inst_id'];
    $descr = $vars['descr'];

    $deleteProvRowList = [];


    //Get the textile_id associated with the current instance
    $findTextileIdString = "SELECT textile_id FROM textile_instance WHERE textile_inst_id='".$inst_id
        ."'";
    $idQuery = mysqli_query($mysqli, $findTextileIdString);
    $textile_id = mysqli_fetch_assoc($idQuery)['textile_id'];

    
    /* echo var_dump($provContents); */
    foreach($modifyTagList as $tagID => $tagValue) {
        if ($tagID!="" && $tagID!="new") {
            $updateString = "UPDATE textile_meta_tag SET textile_meta_tag='".$tagValue
                . "' WHERE textile_meta_tag_id='".$tagID."'";
            mysqli_query($mysqli, $updateString);
        }
        else if ($tagID=="new") {
            $insertString = "INSERT INTO textile_meta_tag (textile_id, textile_meta_tag, status)"
                . " VALUES ('".$textile_id."', '".$tagValue."', 'active')";
            if ($tagValue!="") {
                mysqli_query($mysqli, $insertString);
            }
        }
    }

    foreach($deleteTagList as $tagID) {
        $deleteString = "DELETE FROM textile_meta_tag WHERE textile_meta_tag_id='".$tagID."'";
        echo $deleteString."\n";
        mysqli_query($mysqli, $deleteString);
    }

    foreach($provContents as $provID => $row) {
        if ($provID!="" && $provID!="new") {
            $updateString = "UPDATE provenance SET own_id='".$row['owner']."' own_type_cd='".$row['type']."' "
                ."own_st_dt='".$row['start']."' ownd_end_dt='".$row['end']."' "
                ."own_term_dt='".$row['term']."' WHERE provenance_id='".$provID."'";
            mysqli_query($mysqli, $updateString);

            if ($row['delete'] == "true") {
                array_push($deleteProvRowList, $provID);
            }
        }
        else if ($provID=="new") {

            $insertString = "INSERT INTO provenance (own_id, own_type_cd, own_st_dt, own_end_dt,"
                ."own_term_cd, textile_inst_id) VALUES ('".$row['owner']."', '".$row['type']."', '"
                .$row['start']."', '".$row['end']."', '".$row['term']."', '".$inst_id."')";
            
            if ($row['start']!="" || $row['term']!="" || $row['type']!="" || $row['end']!="") {
                /* echo $insertString; */
                mysqli_query($mysqli, $insertString);
            }
        }

        
    }


    $updateDescriptionString = "UPDATE textile SET textile_descr='".$descr."' WHERE textile_id='"
        .$textile_id."'";
    //echo $updateDescriptionString;
    mysqli_query($mysqli, $updateDescriptionString);


    //Delete the provenance rows that have been selected for deletion
    foreach ($deleteProvRowList as $toDelete) {
        $deleteString = "DELETE FROM provenance WHERE provenance_id='".$toDelete."'";
        mysqli_query($mysqli, $deleteString);
    }
}
	
echo json_encode($mainArray);
	
?>