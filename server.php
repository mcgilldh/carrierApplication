<?php
include 'dbconfig.php';
include 'dbconnect.php';


$vars = $_POST; 

$mainArray = array();


//If we ask for a carr_id that means we want to see all the textiles on the carrier
//this will be echoed as a JSON
if (!empty($vars['carr_id'])) {

    //Get the textile_id of all relevant textiles
    $ids = array();
    $getCarrIdQuery = "SELECT * FROM textile_carr WHERE vt_tracking='".$vars['carr_id']."'";
    $carrIdResponse = mysqli_query($mysqli, $getCarrIdQuery);
    $carrId = mysqli_fetch_assoc($carrIdResponse)['textile_carr_id'];
    
    $getIdsQueryString = "SELECT * FROM textile_instance WHERE textile_instance.textile_carr_id='".$carrId."'";

    $queryID = mysqli_query($mysqli, $getIdsQueryString); 

    //Keep track of vt_tracking, textile_inst_id and textile_id
    while ($row = mysqli_fetch_assoc($queryID)) {
        array_push($ids, array($row['textile_inst_id'], $row['textile_id'], $row['vt_tracking']));
    }

    //Create an associative array of all textile info
    for ($i=0; $i<count($ids); $i++) {
        $tracking = $ids[$i][2];

        $textile_inst_id = $ids[$i][0];
        $textile_id = $ids[$i][1];

        //Where is the image stored
        $getImgUrlString = "SELECT * FROM img_detail INNER JOIN img_hdr ON img_detail.img_hdr_id = img_hdr.img_hdr_id WHERE img_hdr.textile_inst_id='".$textile_inst_id."'";
        //echo $getImgUrlString;
        $queryImg = mysqli_query($mysqli, $getImgUrlString);
        $imgUrl = mysqli_fetch_assoc($queryImg)['img_path'];
        //echo $imgUrl;


        //Get the current owner
        $getOwnerString = "SELECT * FROM textile_instance WHERE textile_inst_id='".$textile_inst_id."'";
        $queryOwner = mysqli_query($mysqli, $getOwnerString);
        $curOwner = mysqli_fetch_assoc($queryOwner)['own_id'];
        
        //Get the description
        $getDescrString = "SELECT * FROM textile WHERE textile.textile_id=".$ids[$i][1];
                
        $queryDescr = mysqli_query($mysqli, $getDescrString);
        $descr = mysqli_fetch_assoc($queryDescr)['textile_descr'];


        //Get the table of provenance information
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
                "provId" => $row['provenance_id'],

            );

            array_push($provList, $newProvRow);
        }


        //Get all tags associated with textile
        $getTagString = "SELECT * FROM textile_meta_tag WHERE textile_meta_tag.textile_id=".$ids[$i][1];
        $queryTag = mysqli_query($mysqli, $getTagString);
        $tagList = array();
        while ($row = mysqli_fetch_assoc($queryTag)) {
            $tagList[$row['textile_meta_tag_id']] = $row['textile_meta_tag'];
        }






        //Create a new entry for this textile including all relevant information
        $newEntry = array(
            "description" => $descr,
            "provenance" => $provList,
            "tags" => $tagList,
            "tracking" => $tracking,
            "textile_id" => $textile_id,
            "textile_inst_id" => $textile_inst_id,
            "imgUrl" => $imgUrl,
            "owner" => $curOwner
        );

        array_push($mainArray, $newEntry);
        

    }

    
}

//This is the case where we are trying to get the lists of types, owners and terms available
else if ($vars['mode']=='lists') {

    //Get all possible owners
    $getOwnerString = "SELECT own_nm, own_id FROM textile_owner";
    $queryOwner = mysqli_query($mysqli, $getOwnerString);
    $ownerEntry = array();
    while ($row = mysqli_fetch_assoc($queryOwner)) {
        array_push($ownerEntry, array('name' => $row['own_nm'], 'id' => $row['own_id']));
    }



    $typeEntry = array();
    $termEntry = array();

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
    }

    //Add each of these to the final array that will be printed
    $mainArray['owner'] = $ownerEntry;
    $mainArray['type'] = $typeEntry;
    $mainArray['term'] = $termEntry;
    
}

//If we are modifying the database
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

    
    //For each tag value that was entered on the website, we will either modify the existing entry
    //or add a new entry
    foreach($modifyTagList as $tagID => $tagValue) {
        //Modifying an old entry
        if ($tagID!="" && $tagID!="new") {
            $updateString = "UPDATE textile_meta_tag SET textile_meta_tag='".$tagValue
                . "' WHERE textile_meta_tag_id='".$tagID."'";
            mysqli_query($mysqli, $updateString);
        }
        //Add a new tag entry
        else if ($tagID=="new") {
            $insertString = "INSERT INTO textile_meta_tag (textile_id, textile_meta_tag, status)"
                . " VALUES ('".$textile_id."', '".$tagValue."', 'active')";

            //Ensure the new tag will not be blank before inserting it
            if ($tagValue!="") {
                mysqli_query($mysqli, $insertString);
            }
        }
    }

    //Now, we delete the tags that the user checked off for deletion
    foreach($deleteTagList as $tagID) {
        $deleteString = "DELETE FROM textile_meta_tag WHERE textile_meta_tag_id='".$tagID."'";
        mysqli_query($mysqli, $deleteString);
    }

    //Now we update all of the provenance information that the user inputted
    foreach($provContents as $provID => $row) {
        //If we are updating a previously entered row of providence info
        if ($provID!="" && $provID!="new") {
            $updateString = "UPDATE provenance SET own_id='".$row['owner']."', own_type_cd='".$row['type']."', "
                ."own_st_dt='".$row['start']."', own_end_dt='".$row['end']."', "
                ."own_term_cd='".$row['term']."' WHERE provenance_id='".$provID."'";
            mysqli_query($mysqli, $updateString);

            //If we are supposed to delete this row, we will still update it but will mark it for deletion in the
            //next stage. This is inefficient but since this will be for internal use I have kept it.
            if ($row['delete'] == "true") {
                array_push($deleteProvRowList, $provID);
            }
        }

        //If we are entering a new row of providence info
        else if ($provID=="new") {

            $insertString = "INSERT INTO provenance (own_id, own_type_cd, own_st_dt, own_end_dt,"
                ."own_term_cd, textile_inst_id) VALUES ('".$row['owner']."', '".$row['type']."', '"
                .$row['start']."', '".$row['end']."', '".$row['term']."', '".$inst_id."')";

            //Ensure this is not a blank row
            if ($row['start']!="" && $row['end']!="") {
                mysqli_query($mysqli, $insertString);
            }
        }

        
    }


    //Update the description
    $updateDescriptionString = "UPDATE textile SET textile_descr='".$descr."' WHERE textile_id='"
        .$textile_id."'";
    mysqli_query($mysqli, $updateDescriptionString);


    //Delete the provenance rows that have been selected for deletion
    foreach ($deleteProvRowList as $toDelete) {
        $deleteString = "DELETE FROM provenance WHERE provenance_id='".$toDelete."'";
        mysqli_query($mysqli, $deleteString);
    }


    //Now we deal with the case that the user wants to make a new owner/type/term
    $addList = $vars['addList'];
    $addCategory = $vars['addListType'];

    if ($addCategory=="owner") {
        $catString = "INSERT INTO textile_owner (own_nm) VALUES ('".$addList."')";
    }
    else if ($addCategory=="type") {
        $catString = "INSERT INTO codec (cd_attr_nm, cd_valu) VALUES ('own_type_cd', '".$addList."')";
    }
    else if ($addCategory=="term") {
        $catString = "INSERT INTO codec (cd_attr_nm, cd_valu) VALUES ('own_term_cd', '".$addList."')";
    }

    if ($addList!="") {
        echo $catString;
        mysqli_query($mysqli, $catString);
    }


    //The case that the user wants to delete owner/type/term
    $killOwns = json_decode($vars['ownDel'], true);
    $killTypes = json_decode($vars['typeDel'], true);
    $killTerms = json_decode($vars['termDel'], true);


    foreach ($killOwns as $killId) {
        $deleteString = "DELETE FROM textile_owner WHERE own_id='".$killId."'";
        mysqli_query($mysqli, $deleteString);
        echo $deleteString;

    }
    foreach ($killTypes as $killId) {
        $deleteString = "DELETE FROM codec WHERE cd_id='".$killId."'";
        mysqli_query($mysqli, $deleteString);
    }
    foreach ($killTerms as $killId) {
        $deleteString = "DELETE FROM codec WHERE cd_id='".$killId."'";
        mysqli_query($mysqli, $deleteString);
    }
}

//Encode whatever we put together as JSON and echo it back to our JS script
echo json_encode($mainArray);
	
?>