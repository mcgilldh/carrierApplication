var ownerList = {};
var typeList = {};
var termList = {};
var condList = {};

var currentTracking = "";
/*Takes a Carrier tracking ID as an argument and makes an AJAX request for all textiles on that
  carrier - then populates the appropriate <div> with html as a side-effect 
*/
function getCarrInfo(trackId) {
    $.ajax({
	type: "POST",
	url: "carr_server.php",
	datatype: "json",
	data: { carr_id: trackId,
		mode: "all" }

    })
	.success(function( jsonData ) {
	    currentTracking = trackId;
	    refreshGlobalLists();
	    populateInfo(JSON.parse(jsonData));
	});
}



function submitCarrDets() {
    condElement = document.getElementById("cond");
    $.ajax({
	type: "POST",
	url: "carr_server.php",
	datatype: "json",
	data: {
	    mode: "submitCarrDets",
	    ref: $("#refField").val(),
	    name: $("#nameField").val(),
	    style: $("#styleField").val(),
	    start: dateSwitchFormat($("#startField").val()),
	    end: $("#endField").val(),
	    count: $("#countField").val(),
	    descr: $("#descrField").val(),
	    notes: $("#notesField").val(),
	    condNotes: $("#condNotesField").val(),
	    cond: condElement.options[condElement.selectedIndex].value,
	    oadb: $("#oadbBox").prop('checked'),
	    vtcomm: $("#vtcommBox").prop('checked'),
	    trackId: currentTracking
	}
	
    })
	.success(function( jsonData ) {
	    refreshGlobalLists();
	    
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
    var txt = "";
    var ownerTest = ownerList;
    txt += "<select id = 'owner"+id+"'>";
    for (var k in ownerList) {
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


/*Generates the html code for a drop down menu with the possible choices for types
  of textile instances.
  Arguments:
  id - the id for the list
  selected - which option should be selected by default

  return: html string with the drop down
  
*/
function generateTypeList(id, selected) {
    var txt = "";

    txt += "<select id = 'type"+id+"'>";
    for (var k in typeList) {
	if (arguments.length==2 && k==selected) {
	   txt += "<option selected='selected' value='" + k + "'>" + typeList[k] + "</option>";
	}
	else {
	    txt += "<option value='" + k + "'>" + typeList[k] + "</option>";
	}
    }
    txt += "</select>";
    return txt;
}


/*Generates the html code for a drop down menu with the possible choices for owners
  of textile instances.
  Arguments:
  id - the id for the list
  selected - which option should be selected by default
  return: html string with the drop down
  
*/
function generateTermList(id, selected) {
    var txt = "";
    txt += "<select id = 'term"+id+"'>";
    for (var k in termList) {
	if (arguments.length==2 && k==selected) {
	   txt += "<option selected='selected' value='" + k + "'>" + termList[k] + "</option>";
	}
	else {
	    txt += "<option value='" + k + "'>" + termList[k] + "</option>";
	}
    }
    txt += "</select>";
    return txt;
}


/*Generates the html code for a drop down menu with the possible choices for owners
  of textile instances.
  Arguments:
  id - the id for the list
  selected - which option should be selected by default
  return: html string with the drop down
  
*/
function generateCondList(id, selected) {
    var txt = "";
    if (arguments.length==2) {
	txt += "<select id = 'cond"+id+"'>";
    }
    else {
	txt += "<select id = 'cond'>";
    }
    for (var k in condList) {
	if (arguments.length==2 && k==selected) {
	   txt += "<option selected='selected' value='" + k + "'>" + condList[k] + "</option>";
	}
	else {
	    txt += "<option value='" + k + "'>" + condList[k] + "</option>";
	}
    }
    txt += "</select>";
    return txt;
}




function refreshGlobalLists() {
    $.ajax({
	type: "POST",
	url: "carr_server.php",
	datatype: "json",
	async: false,
	data: { mode: "lists" }

    })
	.success(function( data ) {
	    ownerList = {};
	    typeList = {};
	    termList = {};
	    var jsonData = JSON.parse(data);
	    for (var i in jsonData.owner) {
		ownerList[jsonData.owner[i].id] = jsonData.owner[i].name;
	    }
	    for (var i in jsonData.type) {
		typeList[jsonData.type[i].id] = jsonData.type[i].value;
	    }
	    for (var i in jsonData.term) {
		termList[jsonData.term[i].id] = jsonData.term[i].value;
	    }

	    for (var i in jsonData.cond) {
		condList[jsonData.cond[i].id] = jsonData.cond[i].value;
	    }

	    var typeTest = typeList;
	    var termTest = termList;

		 
	});
}



function populateInfo(data) {
    $("#refField").val(data.ref);
    $("#nameField").val(data.name);
    $("#styleField").val(data.style);
    $("#startField").val(dateSwitchFormat(data.start));
    $("#endField").val(dateSwitchFormat(data.end));
    $("#countField").val(data.count);
    $("#descrField").val(data.descr);
    $("#notesField").val(data.notes);

    $("#condTd").html(generateCondList("", data.cond));
    
    $("#condNotesField").val(data.condNotes);
    $("#ownerTitle").html("Owner: " + data.tracking);


    if (data.oadb=="1") $("#oadbBox").prop("checked", true);
    else $("#oadbBox").prop("checked", false);

    if (data.vtcomm=="1") $("#vtcommBox").prop("checked", true);
    else $("#vtcommBox").prop("checked", false);

    
    $("#provenance").html(generateProvenance(data.provenance));


    for (var k in data.provenance) {
	$(function () { $( "#datepickerStart" + k ).datepicker(); });
	$(function () { $( "#datepickerEnd" + k ).datepicker(); });
    }
    $(function () { $( "#datepickerStart").datepicker(); });
    $(function () { $( "#datepickerEnd").datepicker(); });
    
}

/* Generates html code for table of provenance information
   Arguments:
   curProv - providence element in our JSON tree for the current textile
   inst_id - instance ID for the current textile instance
   iteration - the number of times we've run this function (Probably unnecessary and to be removed)

   return:
   html code string with provenance table
*/
function generateProvenance(curProv) {
    var txt = "", tmp;
    txt += "<h1>Provenance</h1>";
    txt += "<form name='prov'><table id='provTable' border=1>";
    txt += "<tr><th class='delete'>Delete</th><th class='owner'>Owner</th><th>Type</th><th>Start</th><th>End</th><th>Term. CD</th></tr>";
    
    
	//For each owner in the provenance list, add a new row in the table
    var tmp;
    for (var k in curProv) {
	tmp = k; //To prevent reference to k being passed to functions
	
	txt += "<tr name='"+ curProv[k].provId +"'>";
	txt += "<td class='delete' name='delete'><input type='checkbox'></td>"; 
	txt += "<td name='owner'>"+generateOwnerList(tmp, curProv[k].owner)+"</td>";
	txt += "<td name='type'>"+generateTypeList(tmp, curProv[k].type)+"</td>";
	txt += "<td name='start'><input id='datepickerStart" +  k
	    + "' type='text' value='" + dateSwitchFormat(curProv[k].start) +"'></td>";
	txt += "<td name='end'><input id='datepickerEnd" +  k
	    + "' type='text' value='" + dateSwitchFormat(curProv[k].end) +"'></td>";
	txt += "<td name='term'>" + generateTermList(tmp, curProv[k].term)  + "</td>";
	txt += "</tr>";
	txt += "<tr><td colspan=5>Note: " + curProv[k].note +"</td></tr>";
	
    }
    
    
    
    //We always add a blank row so that we can input a new row
    txt+="<tr name='new'>";
    txt+="<td name='delete'></td><td name='owner'>"+generateOwnerList()+"</td>";
    txt+="<td name='type'>"+generateTypeList()+"</td>";
    txt+="<td name='start'><input type='text' id='datepickerStart'></td>";
    txt+="<td name='end'><input type='text' id='datepickerEnd'>";
    txt+="</td><td name='term'>"+generateTermList()+"</td>";
    txt+="</tr>";
    txt+="</table><br>";


    return txt;
}


function submitProv() {
    var provTable = document.getElementById("provTable");
    
    var provVals = {} //An associative array of our provenance table
    var rowId;
    var colName, newRowData, ownElement, checkedDelete;
    var dateTmp, dateArray, month, day, year;
    
    //Now we go through the provenance tables
    for (var i = 0, row; row = provTable.rows[i]; i++) {
	rowId = row.getAttribute('name');
	if (rowId!=null) {
	    newRowData = {};	 
	    for (var j = 0, col; col = row.cells[j]; j++) {
		colName = col.getAttribute('name');

		//Should this row be deleted?
		if (colName == 'delete') {
		    if (col.firstElementChild!=null){
			checkedDelete = col.firstElementChild.checked;
			newRowData['delete'] = checkedDelete;
		    }
		    
		}

		//Record all of the entered values - NOTE: Dates are displayed in different format from the databse
		//That is why we run dateSwitchFormate()
		else if (colName =='owner') {
		    ownElement = col.firstElementChild;
		    newRowData['owner'] = ownElement.options[ownElement.selectedIndex].value;

		}
		else if (colName == 'type') {
		    typeElement = col.firstElementChild;
		    if (col.firstElementChild!=null)
			newRowData['type'] = typeElement.options[typeElement.selectedIndex].value;
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
		    termElement = col.firstElementChild;
		    if (col.firstElementChild!=null)
			newRowData['term'] = termElement.options[termElement.selectedIndex].value;
		}
	    }

	    provVals[rowId] = newRowData;
	}

    }
    

    $.ajax({
	type: "POST",
	url: "carr_server.php",
	datatype: "text",
	asynch: false,
	data: { prov_contents : JSON.stringify(provVals),
		mode: "applyProv",
		carrID: currentTracking 
	      }
	
    })
	.success(function( jsonData ) {
	    refreshGlobalLists(); //Get the new list of owners, terms and types
	    
	    getCarrInfo(currentTracking);

	});


    

}
 



//Changes yyyy-mm-dd to mm/dd/yyyy
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



$(document).ready(function () {
    $(function () { $( "#startField").datepicker(); });
    $(function () { $( "#endField").datepicker(); }); 
});





//Goes to textile view
function gotoTextiles(carrierID) {
    window.location.href = "textiles.php?carrID=" + carrierID;
}









$(document).ready( function () {
    refreshGlobalLists();
    $("#condTd").html(generateCondList());
    
    $('#idBar').bind('keypress', function(e) {
	var code = e.keyCode || e.which;
	if(code == 13) { //Enter keycode
	    getCarrInfo($('#idBar').val());
	}
    });
});

