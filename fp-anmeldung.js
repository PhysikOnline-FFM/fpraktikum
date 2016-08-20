
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
    target.innerHTML = "<div onblur='checkPartner()'>\
                        HRZ-Account: <input onblur='checkPartner()' id='partner-hrz' type='text' name='partner-hrz' placeholder='s1234567'>\
                        Nachname: <input id='partner-name' type='text' name='partner-name' placeholder='Müller'><br>\
                        <span id='partner-correct'></span>\
                        </div><br>";
  } else {
    target.innerHTML = "";
  }
}

function checkPartner() {
  var hrz = document.getElementById('partner-hrz').value;
  var name = document.getElementById('partner-name').value;

  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function() {
    try {
      if (httpRequest.readyState === XMLHttpRequest.DONE) {
        var response = JSON.parse(httpRequest.responseText);
        console.log(response);

        document.getElementById('partner-correct').innerHTML =
          (response == 'Gefunden') ? 'Gefunden!' : 'Nicht gefunden!';
      };
    }
    catch(e) {
      alert('Es ist ein Fehler beim Abrufen des Partners aufgetreten: ' + e.description);
    }
  }

  httpRequest.open('GET', './Customizing/global/include/fpraktikum/fp-dbRequest.php?task=partner&hrz='+hrz+'&name='+name);
  httpRequest.send();
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
