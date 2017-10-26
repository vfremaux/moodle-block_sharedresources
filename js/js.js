/**
 *
 */
// jshint unused:false, undef:false

function importuncheckall() {

    var fe = document.forms['importfilesasresources'].elements;
    for (var i = 0; i < fe.length; i++) {
        if (fe[i].name && fe[i].name.match(/^file/)) {
            fe[i].checked = false;
        }
    }

}

function importcheckall() {

    var fe = document.forms['importfilesasresources'].elements;
    for (var i = 0; i < fe.length; i++) {
        if (fe[i].name && fe[i].name.match(/^file/)) {
            fe[i].checked = true;
        }
    }

}