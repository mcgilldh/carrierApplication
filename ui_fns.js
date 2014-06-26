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


function populateInfo(data) {

    var txt = "";
    var curProv;
    for (i in data) {
	txt += "<div class='info'>";

	txt += "<div class='description'>";
	txt += "<div class='side'>";
	txt += "<h2>Textile VT-Tracking ID:</h2>" + data[i].tracking;
	txt += "<img src='blah'>";
	txt += "</div>";
	txt += "<h1>Textile Description</h1> ";
	txt += "<p>" + data[i].description+"</p>";
	txt += "</div>";
	    
	txt += "<div class='provenance'>";
	txt += "<h1>Provenance</h1>";
	txt += "<form><table border=1>";
	txt += "<tr><th class='delete'>Delete</th><th>Owner</th><th>Type</th><th>Start</th><th>End</th><th>Term. CD</th></tr>";
	curProv = data[i].provenance;
	for (k in curProv) {
	    txt += "<tr>";
	    txt += "<td class='delete'><input type='checkbox'></td>"; 
	    txt += "<td>" + curProv[k].owner +"</td>";
	    txt += "<td>" + curProv[k].type + "</td>";
	    txt += "<td>" + curProv[k].start +"</td>";
	    txt += "<td>" + curProv[k].end + "</td>";
	    txt += "<td>" + curProv[k].term + "</td>";
	    txt += "</tr>";
	    txt += "<tr><td colspan=5>Note: I am the greatest</td></tr>";
	    
	}
	txt+= "</table></form>";
	txt += "</div>";
	
	txt += "<div class='tagMain'>";
	txt += "<h1>Tags</h1>";
	
	var numTags = data[i].tags.length;
	numTags = (numTags / 4)/4;
	txt += "<div class='infoPointTagList' style='height:"+numTags*200+"px;'><form><table>";

	for (k in data[i].tags) {
		    txt += "<tr><td><input type='checkbox'></td><td>";
	    txt += "<input type='text' value='"+data[i].tags[k]+"'>";
	    txt += "</td></tr> ";
	}
	txt += "</table></form>"
	txt += "</div><br><br>";
	txt += "<a href='#'><div class='submitChanges'>Submit changes</div></a>";
	txt += "</div>";
	txt += "</div>";
	
    }


    $("#mainInfo").html(txt);
    
}
