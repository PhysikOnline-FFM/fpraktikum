
/**
 * script für die Anmeldungsmaske des FPraktikums
 * August 2016
 */
console.log('script is loaded');
function institutWahl(studiengang) {
      var target = document.getElementById('institutWahl');
      switch (studiengang) {
	case 'bachelor': 
	  target.innerHTML = "1. Semesterhälfte:<br>\
			      <input type='radio' name='institut' value='IAP'>IAP - Frei: <span id='IAP-frei'></span><br>\
			      <input type='radio' name='institut' value='PI'>PI - Frei: <span id='IAP-frei'></span><br>\
			      1. Semesterhälfte:<br>\
			      <input type='radio' name='institut' value='IAP'>IAP - Frei: <span id='IAP-frei'></span><br>\
			      <input type='radio' name='institut' value='PI'>PI - Frei: <span id='IAP-frei'></span><br>";
	  break;
	case 'master':
	  target.innerHTML = "1.Semesterhälfte:<br>\
			      <input type='radio' name='institut' value='IAP'>IAP - Frei: <span id='IAP-frei'></span><br>\
			      <input type='radio' name='institut' value='PI'>PI - Frei: <span id='PI-frei'></span><br>\
			      <input type='radio' name='institut' value='ITP'>ITP - Frei: <span id='ITP-frei'></span><br>\
			      1.Semesterhälfte:<br>\
			      <input type='radio' name='institut' value='IAP'>IAP - Frei: <span id='IAP-frei'></span><br>\
			      <input type='radio' name='institut' value='PI'>PI - Frei: <span id='PI-frei'></span><br>\
			      <input type='radio' name='institut' value='ITP'>ITP - Frei: <span id='ITP-frei'></span><br>";
      }
}
