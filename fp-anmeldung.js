
/**
 * script für die Anmeldungsmaske des FPraktikums
 * August 2016
 *
 * TODO: check input, farbige Markierung der freien Plätze, ajax für partnerwahl
 *       styling
 */
console.log('script is loaded');

function institutWahl(studiengang) {
  var target = document.getElementById('institutWahl');
  switch (studiengang) {
  	case 'bachelor':
  	  target.innerHTML = "1. Semesterhälfte:<br>\
  			      <input type='radio' name='institut1' value='IAP'>IAP - Frei: <span id='IAP1-frei'></span><br>\
  			      <input type='radio' name='institut1' value='PI'>PI - Frei: <span id='PI1-frei'></span><br>\
  			      2.Semesterhälfte:<br>\
  			      <input type='radio' name='institut2' value='IAP'>IAP - Frei: <span id='IAP2-frei'></span><br>\
  			      <input type='radio' name='institut2' value='PI'>PI - Frei: <span id='PI2-frei'></span><br>";
  	  break;
  	case 'master':
  	  target.innerHTML = "1.Semesterhälfte:<br>\
  			      <input type='radio' name='institut1' value='IAP'>IAP - Frei: <span id='IAP1-frei'></span><br>\
  			      <input type='radio' name='institut1' value='PI'>PI - Frei: <span id='PI1-frei'></span><br>\
  			      <input type='radio' name='institut1' value='ITP'>ITP - Frei: <span id='ITP1-frei'></span><br>\
  			      2.Semesterhälfte:<br>\
  			      <input type='radio' name='institut2' value='IAP'>IAP - Frei: <span id='IAP2-frei'></span><br>\
  			      <input type='radio' name='institut2' value='PI'>PI - Frei: <span id='PI2-frei'></span><br>\
  			      <input type='radio' name='institut2' value='ITP'>ITP - Frei: <span id='ITP2-frei'></span><br>";
      break;
  }
  freePlaces();
}

function partnerWahl(element) {
  var target = document.getElementById('partnerWahl');
  if (element.checked) {
    target.innerHTML = "HRZ-Account: <input type='text' name='partner-hrz' placeholder='s1234567'>\
                        Nachname: <input type='text' name='partner-name'><br>";
  } else {
    target.innerHTML = "";
  }
}

function freePlaces() {
  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function() {
    try {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        var response = JSON.parse(httpRequest.responseText);

        for (let name in response) {
          var element = document.getElementById(name+'-frei');
          if (element) {
            element.innerHTML = response[name];
          }
        }
      };
    }
    catch(e) {
      alert('Es ist ein Fehler beim Abrufen der freien Plätze aufgetreten: ' + e.description);
    }
  }

  httpRequest.open('GET', './Customizing/global/include/fpraktikum/fp-dbRequest.php?task=freePlaces');
  httpRequest.send();


}
