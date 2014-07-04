<?php
//include 'dbconfig.php';



/* $post = $_POST; */

$mainArray = array();
for ($i=0; $i<100; $i++) {
    $tagList = array("cats", "dogs", "monsters", "blobs", "black", "white", "red", "yellow", "white", "blue", "kittens", "puppies", "salamanders", "explosions", "pikachu", "japan", "canada", "united states", "cambodia", "argentina");

    $provList = array();
    for ($k=0; $k<15; $k++) {
        $newOwner = array(
            "owner" => "Owner".$k,
            "type" => "type".$k,
            "start" => "5-1-1981",
            "end" => "9-1-1999",
            "term" => "",
            "note" => "Owner".$k." is the greatest!"
        );
        array_push($provList, $newOwner);
        
    }
            

    
    $newEntry = array(
        "description" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
        "provenance" => $provList,
        "tags" => $tagList
    ); 
    array_push($mainArray,  $newEntry); 
}
	
echo json_encode($mainArray);
	
?>