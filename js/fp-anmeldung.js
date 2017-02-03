/**
 * script für die Anmeldungsmaske des FPraktikums
 * August 2016
 *
 * TODO: check input, farbige Markierung der freien Plätze, automatische Definiation der Institutwahl
 *       styling
 */
var semester = 'WS16/17';

/**
 * display free instituts and corresponding places left
 */
function showInstitut(studiengang) {

    var target = document.getElementById('instituts');

    Request( './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=freePlaces&semester=' + semester
        , function ( response ) {
            var text_haelfte1 = "";
            var text_haelfte2 = "";

            var data = response[studiengang];

            // build form
            for (var institut in data) {
                if (data[institut]['0']) {
                    text_haelfte1 +=
                        "<div class='radio'>" +
                        "<label>" +
                        "<input class='fp_institute' onchange=disableInstitutWahl() type='radio' name='institute1' value='" + institut + "' id='" + institut + "1'>" +
                        "<span style='display:inline-block; min-width:40px'>" + institut + "</span>&nbsp;" +
                        "<em class='hint text-muted small'>(frei: <span class='" + (data[institut]['0'] > 0 ? 'text-success' : 'text-danger') + "'>" + data[institut]['0'] + "</span>)</em>" +
                        "</label>" +
                        "</div>";
                }
                if (data[institut]['1']) {
                    text_haelfte2 +=
                        "<div class='radio'>" +
                        "<label>" +
                        "<input class='fp_institute' onchange=disableInstitutWahl() type='radio' name='institute2' value='" + institut + "' id='" + institut + "2'>" +
                        "<span style='display:inline-block; min-width:40px'>" + institut + "</span>&nbsp;" +
                        "<em class='hint text-muted small'>(frei: <span class='" + (data[institut]['1'] > 0 ? 'text-success' : 'text-danger') + "'>" + data[institut]['1'] + "</span>)</em>" +
                        "</label>" +
                        "</div>";
                }
            }

            target.innerHTML =
                "<div class='form-group'>" +
                "<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1.&nbsp;Semesterhälfte</label>" +
                "<div class='col-sm-8 col-md-9 col-lg-4'>" + text_haelfte1 + "</div>" +
                "</div>" +
                "<div class='form-group'>" +
                "<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2.&nbsp;Semesterhälfte</label>" +
                "<div class='col-sm-8 col-md-9 col-lg-4'>" + text_haelfte2 + "</div>" +
                "</div>" +
                "<div id='partner-correct'></div>";
        } )
}

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


/**
 * if the cehckbox is checked include the partner-form
 */
function choosePartner(element) {
    var target = document.getElementById('partnerForm');

    document.getElementById('submitRegister').disabled = false;
    if (element.checked) {
        target.innerHTML =
            "<div class='form-group'>" +
            "<label class='col-sm-4 col-md-2 control-label' for='partner-hrz'>HRZ-Account</label>" +
            "<div class='col-sm-8 col-md-3 col-lg-2'><input  id='partner-hrz' onkeyup='checkPartner()' type='text' name='partner-hrz' placeholder='s1234567' class='form-control'></div>" +
            "</div>" +
            "<div class='form-group'>" +
            "<label class='col-sm-4 col-md-2 control-label' for='partner-name'>Nachname</label>" +
            "<div class='col-sm-8 col-md-3 col-lg-2'><input  id='partner-name'  onkeyup='checkPartner()' type='text' name='partner-name' class='form-control'></div>" +
            "</div>" +
            "<div id='partner-correct'></div>";
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

    Request( './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=partner&hrz=' + hrz + '&name=' + name + '&semester=' + semester
        , function ( response ) {
            var note = "";

            switch (response['type']) {
                case "registered":
                    note = "<div class='alert alert-info' role='alert'><strong>Nicht möglich!</strong> Diese Person ist bereits registriert.</div>";
                    document.getElementById('submitRegister').disabled = true;
                    break;
                case "partner-open":
                case "partner-accepted":
                    note = "<div class='alert alert-info' role='alert'><strong>Zu spät!</strong> Diese Person ist bereits als Partner hinzugefügt worden.</div>";
                    document.getElementById('submitRegister').disabled = true;
                    break;
                case 'new':
                    note = "<div class='alert alert-success' role='alert'><strong>Super!</strong> Person existiert.</div>";
                    document.getElementById('submitRegister').disabled = false;
                    break;
                default:
                    note = "<div class='alert alert-warning' role='alert'><strong>Vertippt?</strong> Person wurde nicht gefunden.</div>";
                    document.getElementById('submitRegister').disabled = true;
            }

            document.getElementById('partner-correct').innerHTML = note;
        });
}

function Request( request, func ) {
    var httpRequest = new XMLHttpRequest();

    httpRequest.onreadystatechange = function () {
        try {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                var response = JSON.parse(httpRequest.responseText);
                func( response );
            }
        }
        catch (e) {
            alert('Es ist ein Fehler aufgetreten: ' + e);
        }
    };
    httpRequest.open('GET', request);
    httpRequest.send();
}


/**
 * check if one institute has been chosen and disable the option to choose the same institut again
 */
function disableInstitutWahl() {
    //console.log('test');
    //console.log(institute);

    var otherOption = document.getElementsByClassName('fp_institute');

    for (var name in otherOption) {
        var element = otherOption[name];

        // why is otherOption.length in the array?
        if (!element.id) {
            continue;
        }

        var otherNumber = element.id.slice(-1) % 2 + 1;

        if (!document.getElementById(element.id.slice(0, -1) + otherNumber)) {
            continue;
        }

        element.disabled = document.getElementById(element.id.slice(0, -1) + otherNumber).checked;
    }

    // switch(institut) {
    // case "PI1":
    // 	document.getElementById("PI1").disabled = true;
    // 	document.getElementById("IAP1").disabled = false;
    //        	break;
    //    	case "PI2":
    // 	document.getElementById("PI2").disabled = true;
    // 	document.getElementById("IAP2").disabled = false;
    //        	break;
    //    	case "IAP1":
    // 	document.getElementById("IAP1").disabled = true;
    // 	document.getElementById("PI1").disabled = false;
    //        	break;
    //    	case "IAP2":
    // 	document.getElementById("IAP2").disabled = true;
    // 	document.getElementById("PI2").disabled = false;
    //        	break;
    //   	default:
    //        	console.log('default');
    //}
}

/**
 * checks if form is valid
 * TODO: check if there are enough slots left
 * @return {bool}
 */


function formValidate() {
    console.log( "Test" );
    var form = document.forms['registration'];
    var error = [];

    if (!form['graduation'].value) {
        error.push('Bitte wähle einen Studiengang aus.');
    } else if (!form['institute1'].value || !form['institute2'].value) {
        error.push('Bitte wähle zwei Institute aus.');
    } else if (form['institute1'] == form['institute2']) {
        error.push('Bitte wähle zwei verschiedene Institute.');
    }

//  if(form['check-partner'].checked) {
//    if (document.getElementById('partner-correct').innerHTML != 'Gefunden') {
//      error.push('Dein Partner ist nicht valid.');
//    }
//  }

    if (error[0]) {
        var errors = "";
        errors += "<div class='alert alert-danger' role='alert'>";
        errors += error.join();
        errors += "</div>";
        document.getElementById( 'fp_errors' ).innerHTML = errors;
        return false;
    } else {
        return true;
    }
}
