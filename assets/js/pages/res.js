
$(function () {
    //Mensaje
    var message_status = $("#status");
    $("td[contenteditable=true]").blur(function () {
        var rownumber = $(this).attr("id");
        var value = $(this).text();
        $.post('?action=updateres', rownumber + "=" + value, function (data) {
            if (data != '') {
                message_status.show();
                message_status.html(data);
                //hide the message
                setTimeout(function () { message_status.html("REGISTRO EDITADO CORRECTAMENTE"); }, 2000);
                setInterval("location.reload()", 3000);
            }
            else {
                message_status().html = data;
            }
        });
    });
});