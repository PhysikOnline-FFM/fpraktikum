
/**
 * script für die Anmeldungsmaske des FPraktikums
 * August 2016
 *
 * TODO: check input, farbige Markierung der freien Plätze, automatische Definiation der Institutwahl
 *       styling
 */
console.log('script is loaded');

var semester = 'WS16/17';

/**
 * display free instituts and corresponding places left
 */
function showInstitut(studiengang) {
  
  var target = document.getElementById('instituts');  

  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function() {
    try {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        var response = JSON.parse(httpRequest.responseText);

        var text_haelfte1 = "";
        var text_haelfte2 = "";

        var data = response[studiengang];

        // build form
        for (var institut in data) {
          if (data[institut]['0']) {
            text_haelfte1 += "<input onchange=disableInstitutWahl('"+institut+"2') type='radio' name='institute1' value='"+institut+"' id='"+institut+"1'>\
              "+institut+" - Frei: "+data[institut]['0']+"</span><br>";
          }
          if (data[institut]['1']) {
            text_haelfte2 += "<input <input onchange=disableInstitutWahl('"+institut+"1') type='radio' name='institute2' value='"+institut+"' id='"+institut+"2'>\
              "+institut+" - Frei: "+data[institut]['1']+"</span><br>";
          }          
        }

        target.innerHTML = "<br>1. Semesterhälfte:<br>" + text_haelfte1 + 
					"2.Semesterhälfte:<br>"+text_haelfte2;
      }
    }
    catch(e) {
      alert('Es ist ein Fehler beim Abrufen der freien Plätze aufgetreten: ' + e);
    }
  }

  httpRequest.open('GET', './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=freePlaces&semester='+semester);
  httpRequest.send();

  
  // switch (studiengang) {
  // 	case 'bachelor':
  // 	  target.innerHTML = "1. Semesterhälfte:<br>\
  //       <input type='radio' name='institut1' value='IAP'>IAP - Frei: <span id='IAP1-frei'></span><br>\
  //       <input type='radio' name='institut1' value='PI'>PI - Frei: <span id='PI1-frei'></span><br>\
  //       2.Semesterhälfte:<br>\
  //       <input type='radio' name='institut2' value='IAP'>IAP - Frei: <span id='IAP2-frei'></span><br>\
  //       <input type='radio' name='institut2' value='PI'>PI - Frei: <span id='PI2-frei'></span><br>";
  // 	  break;
  // 	case 'master':
  // 	  target.innerHTML = "1.Semesterhälfte:<br>\
  //       <input type='radio' name='institut1' value='IAP'>IAP - Frei: <span id='IAP1-frei'></span><br>\
  //       <input type='radio' name='institut1' value='PI'>PI - Frei: <span id='PI1-frei'></span><br>\
  //       <input type='radio' name='institut1' value='ITP'>ITP - Frei: <span id='ITP1-frei'></span><br>\
  //       2.Semesterhälfte:<br>\
  //       <input type='radio' name='institut2' value='IAP'>IAP - Frei: <span id='IAP2-frei'></span><br>\
  //       <input type='radio' name='institut2' value='PI'>PI - Frei: <span id='PI2-frei'></span><br>\
  //       <input type='radio' name='institut2' value='ITP'>ITP - Frei: <span id='ITP2-frei'></span><br>";
  //     break;
  //}
  
}

/**
 * if the cehckbox is checked include the partner-form
 */
function choosePartner(element) {
  var target = document.getElementById('choosePartner');
  if (element.checked) {
    target.innerHTML = "<div>\
      HRZ-Account: <input onblur='checkPartner()' id='partner-hrz' type='text' name='partner-hrz' placeholder='s1234567'>\
      Nachname: <input onblur='checkPartner()' id='partner-name' type='text' name='partner-name'><br>\
      <span id='partner-correct'></span>\
      </div><br>";
  } else {
    target.innerHTML = "";
  }
}

/**
 * ajax-call to determine if there is a registered user with this combination of hrz and last-name
 */
function checkPartner() {
  var hrz = document.getElementById('partner-hrz').value;
  var name = document.getElementById('partner-name').value;

  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function() {
    try {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        var response = JSON.parse(httpRequest.responseText);

        var note = "";

        switch (response[0]) {
          case "registered":
            note = "Diese Person ist bereits registriert.";
            break;
          case "partner-accept":
          case "partner-accepted":
            note = "Diese Person ist bereits als Partner hinzugefügt worden.";
            break;
          case false:
            note = "Gefunden";
            break;
          default:
            note = "Nicht Gefunden";
        }
        
        document.getElementById('partner-correct').innerHTML = note;
      };
    }
    catch(e) {
      alert('Es ist ein Fehler beim Abrufen des Partners aufgetreten: ' + e);
    }
  }

  httpRequest.open('GET', './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=partner&hrz='+hrz+'&name='+name+'&semester='+semester);
  httpRequest.send();
}

/**
 * check if one institute has been chosen and disable the option to choose the same institut again
 */
function disableInstitutWahl(institut) {
	console.log('test');
	console.log(institut);

	switch(institut) {
	case "PI1":
		document.getElementById("PI1").disabled = true;
		document.getElementById("IAP1").disabled = false;
        	break;
    	case "PI2":
		document.getElementById("PI2").disabled = true;
		document.getElementById("IAP2").disabled = false;
        	break;
    	case "IAP1":
		document.getElementById("IAP1").disabled = true;
		document.getElementById("PI1").disabled = false;
        	break;
    	case "IAP2":
		document.getElementById("IAP2").disabled = true;
		document.getElementById("PI2").disabled = false;
        	break;
   	default:
        	console.log('default');
	}
}

