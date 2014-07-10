//Global variable containing the names of all of the possible owners
var ownerList = {};
var typeList = {};
var termList = {};

//Global variable for the current carrier id
var curTrack;

$(document).ready( function () {
    refreshGlobalLists();

});


function refreshGlobalLists() {
    $.ajax({
	type: "POST",
	url: "server.php",
	datatype: "json",
	data: { mode: "lists" }

    })
	.success(function( data ) {
	    var jsonData = JSON.parse(data);
	    for (var i in jsonData.owner) {
		
		ownerList[jsonData[i].id] = jsonData[i].name;
	    }
	    

	});
}


/*Takes a Carrier tracking ID as an argument and makes an AJAX request for all textiles on that
  carrier - then populates the appropriate <div> with html as a side-effect 
*/
function getInfo(trackId) {
    $.ajax({
	type: "POST",
	url: "server.php",
	datatype: "json",
	data: { carr_id: trackId,
		mode: "all" }

    })
	.success(function( jsonData ) {
	    curTrack = trackId;
	    populateInfo(JSON.parse(jsonData));
	});
}


/*Generates the html code for a drop down menu with the possible choices for owners
  of textile instances.
  Arguments:
  id - the id for the list
  selected - which option should be selected by default

  return: html string with the drop down
  
*/
function generateOwnerList(id, selected) {
    txt = "";
    txt += "<select id = 'owner"+id+"'>";
    for (k in ownerList) {
	if (arguments.length==2 && k==selected) {
	   txt += "<option selected='selected' value='" + k + "'>" + ownerList[k] + "</option>";
	}
	else {
	    txt += "<option value='" + k + "'>" + ownerList[k] + "</option>";
	}
    }
    txt += "</select>";
    return txt;
}

/* Generates html code for table of provenance information
   Arguments:
   curProv - providence element in our JSON tree for the current textile
   inst_id - instance ID for the current textile instance
   iteration - the number of times we've run this function (Probably unnecessary and to be removed)

   return:
   html code string with provenance table
*/
function generateProvenance(curProv, inst_id, iteration) {
    var txt = "", tmp;
    txt += "<h1>Provenance</h1>";
    txt += "<form name='prov"+ inst_id +"'><table id='provTable" + inst_id + "' border=1>";
    txt += "<tr><th class='delete'>Delete</th><th class='owner'>Owner</th><th>Type</th><th>Start</th><th>End</th><th>Term. CD</th></tr>";
    
    
	//For each owner in the provenance list, add a new row in the table
    var tmp;
    for (var k in curProv) {
	tmp = k; //To prevent reference to k being passed to functions
	
	txt += "<tr name='"+ curProv[k].provId +"'>";
	txt += "<td class='delete' name='delete'><input type='checkbox'></td>"; 
	txt += "<td name='owner'>"+generateOwnerList(tmp, curProv[k].owner)+"</td>";
	txt += "<td name='type'><input type='text' value='" + curProv[k].type +"'></td>";
	txt += "<td name='start'><input id='datepickerStart"+ inst_id +"-" +  k
	    + "' type='text' value='" + curProv[k].start +"'></td>";
	txt += "<td name='start'><input id='datepickerEnd"+ inst_id +"-" +  k
	    + "' type='text' value='" + curProv[k].end +"'></td>";
	txt += "<td name='term'><input type='text' value='" + curProv[k].term + "'></td>";
	txt += "</tr>";
	txt += "<tr><td colspan=5>Note: " + curProv[k].note +"</td></tr>";
	
    }
    
    
    
    //We always add a blank row so that we can input a new row
    txt+="<tr name='new'>";
    txt+="<td name='delete'></td><td name='owner'>"+generateOwnerList(iteration)+"</td>";
    txt+="<td name='type'><input type='text' ></td>";
    txt+="<td name='start'><input type='text' id='datepickerStart"+iteration+"'></td>";
    txt+="<td name='end'><input type='text' id='datepickerEnd"+iteration+"'>";
    txt+="</td><td name='term'><input type='text'></td>";
    txt+="</tr>";
    
    
    txt+= "</table></form>";
    return txt;
}


/* Generates html code for table of tag information
   Arguments:
   tagData - tags element in our JSON tree for the current textile
   inst_id - instance ID for the current textile instance

   return:
   html code string with tag input form
*/ 
function generateTags(tagData, inst_id) {
    //Tags - these should be editable
    var txt = "", tagLength=0, k;
    txt += "<h1>Tags</h1>";
    
    //NOTE - the following is a bug fix. The div was not expanding as the number of tags increased. This code manually expands the div.
    //This is probably a bug in the implementation of columns.
    for (k in tagData) {
	tagLength++;
    }

    
    var numTags = tagLength;

    if (numTags>12)
	numTags = parseInt((numTags / 4)/4);
    else
	numTags = 1;

    if (numTags!=1)
	alert(numTags);
    
    //We give 200px extra each time we add 4 tags to a column
    txt += "<div class='infoPointTagList' style='height:"+numTags*250+"px;'>"; 
    txt += "<form name='tags" + inst_id + "'><table>";
    
    //For each tag, add a delete checkbox as well as an input box for editing which has the database entry as the default value
    for (k in tagData) {
	txt += "<tr><td><input name='"+ k + "' type='checkbox'></td><td>";
	txt += "<input name='" + k + "' type='text' value='"+tagData[k]+"'>";
	txt += "</td></tr> ";
    }
    txt += "<tr><td>N:</td> <td><input name='new' type='text'></td></tr>";

    txt += "</table></form>"

    return txt;

    
}


//Generates the HTML which gives all of the information about each textile on the given carrier
//Input: JSON data about a carrier
//Side effect: mainInfo div contains information on each textile
function populateInfo(data) {   
    var txt = "";
    var curProv;
    var id, inst_id;
    //For each textile, create an entry
    for (i in data) {
	id = data[i].textile_id;
	inst_id = data[i].textile_inst_id;

	

	//New entry
	txt += "<div class='info'>";

	//Textile description
	txt += "<div class='description'>";

	//Sidebar containing textile tracking ID and photo
	//Note - Later there may be the ability to zoom in 
	txt += "<div class='side'>";
	txt += "<h2>Textile VT-Tracking ID:</h2>" + data[i].tracking;
	txt += "<img src='"+ data[i].imgUrl +"'>";
	txt += "</div>";

	//Main text of the textile description
	txt += "<h1>Textile Description</h1> ";
	txt += "<textarea id = 'description" + inst_id + "' cols='100' rows='20'>" + data[i].description + "</textarea>";
	txt += "</div>";



	txt += "<div class='provenance'>";
	txt += generateProvenance(data[i].provenance, inst_id, i);

	txt += "</div>";

	txt += "<div class='tagMain'>";
	txt += generateTags(data[i].tags, inst_id);

	txt += "</div><br><br>";



	//button to submit changes for this particular textile
	txt += "<a href='#'><div class='submitChanges' onclick='submitChanges(" + inst_id + ");return false;'>Submit changes</div></a>";

	txt += "</div>";
	txt += "</div>";
	
    }


    $("#mainInfo").html(txt);

    //Activate all of the datepickers
    for (i in data) {
	$(function () { $( "#datepickerStart" + i ).datepicker(); });
	$(function () { $( "#datepickerEnd" + i ).datepicker(); });

	var inst_id = data[i].textile_inst_id;

	var curProv = data[i].provenance; //List of all owners and related information
	
	//For each owner in the provenance list, add a new row in the table
	for (var k in curProv) {
	    $(function () { $( "#datepickerStart" + inst_id + "-" + k ).datepicker(); });
	    $(function () { $( "#datepickerEnd" + inst_id + "-" + k ).datepicker(); });
	    
	}
    }
    
}



function submitChanges(id) {
    var description = $("#description" + id).val();

    var tagObjects = document.forms["tags"+id].getElementsByTagName("input");
    var provTable = document.getElementById("provTable" + id);
    
    var toDeleteTags = [];
    var tagContents = {};

    var curElem, type;
    var curTagId;
    
    for (var i in tagObjects) {
	curElem = tagObjects[i];
	if (typeof curElem.getAttribute !== 'undefined') {
	    type = curElem.getAttribute('type');

	 

	    if (type=='checkbox' && curElem.checked) {
		toDeleteTags.push(curElem.getAttribute('name'));
	    }

	    else if (type=='text') {
		curTagId = curElem.getAttribute('name');
		tagContents[curTagId] = curElem.value;
	    }
	}

    }

    var provVals = {}
    var rowId;
    var colName, newRowData, ownElement, checkedDelete;
    var dateTmp, dateArray, month, day, year;
    for (var i = 0, row; row = provTable.rows[i]; i++) {
	rowId = row.getAttribute('name');
	if (rowId!=null) {
	    newRowData = {};	 
	    for (var j = 0, col; col = row.cells[j]; j++) {
		colName = col.getAttribute('name');

		if (colName == 'delete') {
		    if (col.firstElementChild!=null){
			checkedDelete = col.firstElementChild.checked;
			newRowData['delete'] = checkedDelete;
		    }
		    
		}

		else if (colName =='owner') {
		    ownElement = col.firstElementChild;
		    newRowData['owner'] = ownElement.options[ownElement.selectedIndex].value;

		}
		else if (colName == 'type') {
		    if (col.firstElementChild!=null)
			newRowData['type'] = col.firstElementChild.value;

		}
		else if (colName == 'start') {
		    if (col.firstElementChild!=null) {
			dateTmp = col.firstElementChild.value;

			if (typeof(dateTmp)!="undefined" && dateTmp!="") {
			  
			    newRowData['start'] = dateSwitchFormat(dateTmp);
			}
			else {
			    newRowData['start'] = "";
			}			
			
		    }
		}
		else if (colName == 'end') {
		    if (col.firstElementChild!=null) {
			dateTmp = col.firstElementChild.value;

			if (typeof(dateTmp)!="undefined" && dateTmp!="") {
			    newRowData['end'] = dateSwitchFormat(dateTmp);
			}
			else {
			    newRowData['end'] = "";
			}			
			
		    }		    
		}
		else if (colName == 'term') {
		    if (col.firstElementChild!=null)
			newRowData['term'] = col.firstElementChild.value;
		}
	    }

	    provVals[rowId] = newRowData;
	}

    }

    $.ajax({
	type: "POST",
	url: "server.php",
	datatype: "text",
	data: { inst_id : id,
		delete_tag_list : JSON.stringify(toDeleteTags),
		modify_tag_list : JSON.stringify(tagContents),
		prov_contents : JSON.stringify(provVals),
		descr : description,
		mode: "apply" }
	
    })
	.success(function( jsonData ) {
	    getInfo(curTrack);
	});


}



function dateSwitchFormat(date) {
    var dateArray, year, month, day;
    if (date.indexOf("-")>-1) {
	dateArray = date.split("-");
	year = dateArray[0];
	month = dateArray[1];
	day = dateArray[2];
	return (month + "/" + day + "/" + year);
    }
    else {
	dateArray = date.split("/");
	year = dateArray[2];
	month = dateArray[0];
	day = dateArray[1];
	return (year + "-" + month + "-" + day);
    }
	    
}
