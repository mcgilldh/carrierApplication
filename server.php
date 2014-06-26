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
            "term" => ""
        );
        array_push($provList, $newOwner);
        
    }
            

    
    $newEntry = array(
        "description" => "This is a textile. It is the greatest textile ever created. I love it with all my heart. I love it so much I want to eat it and then clothe myself only in it for the rest of my life. It is truly the most lovely piece of fabric every imagined by mankind. I mean humankind! I apologize for my gender insensitivty. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
        "provenance" => $provList,
        "tags" => $tagList
    ); 
    array_push($mainArray,  $newEntry); 
}
	
echo json_encode($mainArray);
	
?>