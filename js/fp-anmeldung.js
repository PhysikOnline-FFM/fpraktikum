/**
 * script für die Anmeldungsmaske des FPraktikums
 * August 2016
 */
$(document).ready(function(){$('#info').popover({trigger:"focus", content:'Studiengang Master mit Schwerpunkt Computational Science'});});;
var semester = 'WS16/17';

/**
 * display free instituts and corresponding places left
 */
function showInstitut ( graduation ) {

    var target = document.getElementById( 'instituts' );

    Request( './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=freePlaces&semester=' + semester
        , function ( response ) {
            var text_haelfte1 = "";
            var text_haelfte2 = "";
            var text_lehramt = "";

            var data = response[graduation];

            var disabled = "";
            var free = 0;

            // build form
            for ( var institut in data ) {
                if ( graduation == 'LA' ) {
                    disabled = (data[institut]['0'] > 0) ? "" : "disabled=''";
                    free = data[institut]['0'];
                    text_lehramt +=
                        "<div class='radio'>" +
                        "<label>" +
                        "<input class='fp_institute' type='radio' name='institute_la' " +
                            "value='" + institut + "' id='" + institut + "' data-free='" + free + "' " + disabled + ">" +
                        "<span style='display:inline-block; min-width:40px'>" + institut + "</span>&nbsp;" +
                        "<em class='hint text-muted small'>(frei: <span class='" + (free > 0 ? 'text-success' : 'text-danger') + "'>" + free + "</span>)</em>" +
                        "</label>" +
                        "</div>";
                } else {
                    if ( data[institut]['0'] != undefined ) {
                        disabled = (data[institut]['0'] > 0) ? "" : "disabled=''";
                        free = data[institut]['0'];
                        text_haelfte1 +=
                            "<div class='radio'>" +
                            "<label>" +
                            "<input class='fp_institute' onchange=disableInstitutWahl() type='radio' name='institute1' " +
                                "value='" + institut + "' id='" + institut + "1' data-free='" + free + "' " + disabled + ">" +
                            "<span style='display:inline-block; min-width:40px'>" + institut + "</span>&nbsp;" +
                            "<em class='hint text-muted small'>(frei: <span class='" + (free > 0 ? 'text-success' : 'text-danger') + "'>" + free + "</span>)</em>" +
                            "</label>" +
                            "</div>";
                    }
                    if ( data[institut]['1'] != undefined ) {
                        disabled = (data[institut]['1'] > 0) ? "" : "disabled=''";
                        free = data[institut]['1'];
                        text_haelfte2 +=
                            "<div class='radio'>" +
                            "<label>" +
                            "<input class='fp_institute' onchange=disableInstitutWahl() type='radio' name='institute2' " +
                                "value='" + institut + "' id='" + institut + "2' data-free='" + free + "' " + disabled + ">" +
                            "<span style='display:inline-block; min-width:40px'>" + institut + "</span>&nbsp;" +
                            "<em class='hint text-muted small'>(frei: <span class='" + (free > 0 ? 'text-success' : 'text-danger') + "'>" + free + "</span>)</em>" +
                            "</label>" +
                            "</div>";
                    }
                }
            }

            if ( graduation == 'LA' ) {
                target.innerHTML =
                    "<div class='form-group'>" +
                    "<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Institut:</label>" +
                    "<div class='col-sm-8 col-md-9 col-lg-4'>" + text_lehramt + "</div>" +
                    "</div>";
                return;
            }

            target.innerHTML =
                "<div class='form-group'>" +
                "<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1.&nbsp;Semesterhälfte</label>" +
                "<div class='col-sm-8 col-md-9 col-lg-4'>" + text_haelfte1 + "</div>" +
                "</div>" +
                "<div class='form-group'>" +
                "<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2.&nbsp;Semesterhälfte</label>" +
                "<div class='col-sm-8 col-md-9 col-lg-4'>" + text_haelfte2 + "</div>" +
                "</div>";

            if ( graduation == "MA" )
            {
                target.innerHTML = "Das ITP können nur Studenten im Studiengang Master \"Physik mit Schwerpunkt Computational Physics\" belegen!" + target.innerHTML;
            }
        } )
}

/**
 * if the checkbox is checked include the partner-form
 */
function choosePartner ( element ) {
    var target = document.getElementById( 'partnerForm' );

    document.getElementById( 'submitRegister' ).disabled = false;
    var err_element = document.getElementById( 'partner-correct' );
    if ( err_element ) {
        document.getElementById( 'partner-correct' ).innerHTML = "";
    }

    if ( element.checked ) {
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
function checkPartner () {
    var hrz = document.getElementById( 'partner-hrz' ).value;
    var name = document.getElementById( 'partner-name' ).value;
    var note_element = document.getElementById( 'partner-correct' );
    var user = document.getElementById( 'user_login' ).value;

    Request( './Customizing/global/include/fpraktikum/fp-ajax-request.php?task=partner&hrz=' + hrz + '&name=' + name + '&semester=' + semester
        , function ( response ) {
            var note = "";

            switch ( response['type'] ) {
                case "registered":
                    note = "<div class='alert alert-info' role='alert'><strong>Nicht möglich!</strong> Diese Person ist bereits registriert.</div>";
                    document.getElementById( 'submitRegister' ).disabled = true;
                    break;
                case "partner-open":
                case "partner-accepted":
                    note = "<div class='alert alert-info' role='alert'><strong>Zu spät!</strong> Diese Person ist bereits als Partner hinzugefügt worden.</div>";
                    document.getElementById( 'submitRegister' ).disabled = true;
                    break;
                case 'new':
                    note = "<div class='alert alert-success' role='alert'><strong>Super!</strong> Person existiert.</div>";
                    document.getElementById( 'submitRegister' ).disabled = false;
                    break;
                default:
                    note = "<div class='alert alert-danger' role='alert'><strong>Vertippt?</strong> Person wurde nicht gefunden.</div>";
                    document.getElementById( 'submitRegister' ).disabled = true;
            }

            if (hrz == user)
            {
                note = "<div class='alert alert-danger' role='alert'><strong>Stop!</strong> Es ist nicht möglich sich selbst als Partner zu wählen.</div>";
                document.getElementById( 'submitRegister' ).disabled = true;
            }

            note_element.innerHTML = note;
        } );
}

function Request ( request, func ) {
    var httpRequest = new XMLHttpRequest();

    httpRequest.onreadystatechange = function () {
        try {
            if ( httpRequest.readyState === XMLHttpRequest.DONE ) {
                var response = JSON.parse( httpRequest.responseText );
                func( response );
            }
        }
        catch ( e ) {
            alert( 'Es ist ein Fehler aufgetreten: ' + e );
        }
    };
    httpRequest.open( 'GET', request );
    httpRequest.send();
}


/**
 * check if one institute has been chosen and disable the option to choose the same institute again
 */
function disableInstitutWahl () {

    var otherOption = document.getElementsByClassName( 'fp_institute' );

    for ( var name in otherOption ) {
        var element = otherOption[name];

        // why is otherOption.length in the array?
        if ( !element.id ) {
            continue;
        }

        if ( element.dataset.free <= 0 )
        {
            continue;
        }

        var otherNumber = element.id.slice( -1 ) % 2 + 1;

        if ( !document.getElementById( element.id.slice( 0, -1 ) + otherNumber ) ) {
            continue;
        }

        element.disabled = document.getElementById( element.id.slice( 0, -1 ) + otherNumber ).checked;
    }
}

/**
 * checks if form is valid
 * TODO: check if there are enough slots left
 * @return {boolean}
 */
function formValidate () {
    var form = document.forms['registration'];
    var error = [];
    var check_places = false;

    // check if user chose a graduation
    if ( !form['graduation'].value ) {
        error.push( 'Bitte wähle einen Studiengang aus.' );
    }
    // check if institutes are checked
    else if ( form['graduation'].value == 'LA' ) {
        if ( ! form['institute_la'].value ) {
            error.push( 'Bitte wähle ein Institut aus.' );
        }
    }
    else {
        if ( !form['institute1'].value || !form['institute2'].value ) {
            error.push( 'Bitte wähle zwei Institute aus.' );
        }
        if ( form['institute1'].value == form['institute2'].value ) {
            //error.push( 'Bitte wähle zwei verschiedene Institute.' );
        }
    }
    check_places = error.length == 0;
    if ( form['check-partner'].checked ) {
        // check if a partner was chosen
        if ( ! form['partner-hrz'].value || ! form['partner-name'].value ) {
            error.push( 'Bitte trage einen Partner ein.' );
        }
        // check if enough places are available
        if ( check_places )
        {
            if ( form['graduation'].value == 'LA' )
            {
                if ( document.getElementById( form['institute_la'].value ).dataset.free < 2 ) {
                    error.push( 'In einem Institut sind nicht ausreichend Plätze vorhanden.' )
                }
            }
            else {
                if ( document.getElementById( form['institute1'].value + "1" ).dataset.free < 2
                    || document.getElementById( form['institute2'].value + "2" ).dataset.free < 2 ) {
                    error.push( 'In einem Institut sind nicht ausreichend Plätze vorhanden.' )
                }
            }
        }
    }

    if ( error[0] ) {
        var errors = "";
        errors += "<div class='alert alert-danger' role='alert'>";
        errors += error.join( "<br>" );
        errors += "</div>";
        document.getElementById( 'fp_errors' ).innerHTML = errors;
        return false;
    } else {
        return true;
    }
}
