//Takes a Carrier tracking ID as an argument, and returns JSON data about all of the textiles on the carrier
function getInfo(trackId) {
    $.ajax({
	type: "POST",
	url: "server.php",
	datatype: "json",
	data: { track: trackId,
		mode: "all" }

    })
	.success(function( jsonData ) {
	    populateInfo(JSON.parse(jsonData));
	});
}


//Generates the HTML which gives all of the information about each textile on the given carrier
//Input: JSON data about a carrier
//Side effect: mainInfo div contains information on each textile
function populateInfo(data) {

    var txt = "";
    var curProv;
    //For each textile, create an entry
    for (i in data) {
	
	//New entry
	txt += "<div class='info'>";

	//Textile description
	txt += "<div class='description'>";

	//Sidebar containing textile tracking ID and photo
	//Note - Later there may be the ability to zoom in 
	txt += "<div class='side'>";
	txt += "<h2>Textile VT-Tracking ID:</h2>" + data[i].tracking;
	txt += "<img src='TODO'>";
	txt += "</div>";

	//Main text of the textile description
	txt += "<h1>Textile Description</h1> ";
	txt += "<p>" + data[i].description+"</p>";
	txt += "</div>";
	    
	//Provenance list - Appears as a table
	txt += "<div class='provenance'>";
	txt += "<h1>Provenance</h1>";
	txt += "<form><table border=1>";
	txt += "<tr><th class='delete'>Delete</th><th>Owner</th><th>Type</th><th>Start</th><th>End</th><th>Term. CD</th></tr>";
	curProv = data[i].provenance; //List of all owners and related information

	//For each owner in the provenance list, add a new row in the table
	for (k in curProv) {
	    txt += "<tr>";
	    txt += "<td class='delete'><input type='checkbox'></td>"; 
	    txt += "<td>" + curProv[k].owner +"</td>";
	    txt += "<td>" + curProv[k].type + "</td>";
	    txt += "<td>" + curProv[k].start +"</td>";
	    txt += "<td>" + curProv[k].end + "</td>";
	    txt += "<td>" + curProv[k].term + "</td>";
	    txt += "</tr>";
	    txt += "<tr><td colspan=5>Note: " + curProv[k].note +"</td></tr>";
	    
	}
	txt+= "</table></form>";
	txt += "</div>";

	//Tags - these should be editable
	txt += "<div class='tagMain'>";
	txt += "<h1>Tags</h1>";
	
	//NOTE - the following is a bug fix. The div was not expanding as the number of tags increased. This code manually expands the div.
	//This is probably a bug in the implementation of columns.
	var numTags = data[i].tags.length;
	numTags = (numTags / 4)/4;


	txt += "<div class='infoPointTagList' style='height:"+numTags*200+"px;'><form><table>"; //We give 200px extra each time we add 4 tags to a column

	//For each tag, add a delete checkbox as well as an input box for editing which has the database entry as the default value
	for (k in data[i].tags) {
		    txt += "<tr><td><input type='checkbox'></td><td>";
	    txt += "<input type='text' value='"+data[i].tags[k]+"'>";
	    txt += "</td></tr> ";
	}

	txt += "</table></form>"
	txt += "</div><br><br>";

	//button to submit changes for this particular textile
	txt += "<a href='#'><div class='submitChanges'>Submit changes</div></a>";

	txt += "</div>";
	txt += "</div>";
	
    }


    $("#mainInfo").html(txt);
    
}
