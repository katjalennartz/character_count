
//function for counting characters
$(document).ready(function () {
  //variablen setzen
  var length, lengthstr, htmlsetting;

  //wir holen uns über einen ajax request die settings
  $.ajax({
    type: 'GET',
    url: 'xmlhttp.php?action=get_cc_settings',
    data: { get_param: 'value' },
    dataType: 'json',
    success: function (data) {
      //kriegen wir 0, soll keine Mindestzeichenzahl angezeigt werden
      length = data[0].length;
      htmlsetting = data[0].htmlsetting;
      console.log(htmlsetting + "settingist")
      if (data[0].length != 0) {
        //ungleich null, also einmal den string speichern
        lengthstr = "von " + data[0].length + " Zeichen ";
      } else {
        //sonst leer
        lengthstr = "";
      }
    }
  });

  $('#message').keyup(function () {
    //mit html code zählen
    if (htmlsetting == 1) {
      //Zeichen mit Leerzeichen
      var space = $(this).val().length;
      //Wörter zählen
      var words = $(this).val().split(' ').length;
      //Zeichen ohne Leerzeichen
      var nospace = $(this).val().replace(/\s+/g, '').length;
      
    } else { // ohne html code zählen
      var space = $(this).val().replace(/(<([^>]+)>)/ig, "").length;
      
      //Wörter zählen
      var words = $(this).val().replace(/(<([^>]+)>)/ig, "").split(' ').length;
      // var words = $(this).val().split(' ').length;
      //Zeichen ohne Leerzeichen
      var nospace = $(this).val().replace(/\s+/g, '').replace(/(<([^>]+)>)/ig, "").length;
    }

    //Wenn nicht genug zeichen getippt sind, klasse zum stylen hinzufügen
    // console.log ("space ist" + space + "lenth ist"+length )
    if (space < length) {
      $('#cc_chars').addClass("cc_toless");
    } else {
      //bzw. entfernen
      $('#cc_chars').removeClass("cc_toless");
    }
    //same für ohne leerzeichen
    if (nospace < length) {
      $('#cc_nospace').addClass("cc_toless");
    } else {
      $('#cc_nospace').removeClass("cc_toless");
    }

    //und jetzt hängen wir das ganze in unsere div container.
    $('#cc_chars').html(space + " Zeichen " + lengthstr);
    $('#cc_words').html(words + " Wörter ");
    $('#cc_nospace').html(nospace + " Zeichen(ohne Leerzeichen) " + lengthstr);

  });
});
