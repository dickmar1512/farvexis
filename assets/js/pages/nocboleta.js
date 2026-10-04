$("#buscarb").click(function () {
	var numDocb = $("#num_comprobante_modificadob").val();
	var tipob = $("#notacredito_motivo_idb").val();
	var serieb = $("#serie_comprobanteb").val();
	var compb = $("#numero_comprobanteb").val();
	var motivob = $("#motivob").val();

	if (tipob == "01" || tipob == "02" || tipob == "06") {
		// window.location.href = "./?view=onesell2&id="+3;
		$.ajax({
			type: "POST",
			data: {
				"numDoc": numDocb,
				"motivo": motivob,
				"serie": serieb,
				"comp": compb,
				"tipo": tipob
			},
			url: './?action=addnotacreditoboleta',
			dataType: 'json',

			success: function (data) {
				if (data && data.redirect) window.location.href = data.redirect;
			},
		});
	}

	else if (tipob == '03' || tipob == '05' || tipob == '07') {

		$.ajax({
			type: "POST",
			data: {
				"numDoc": numDocb,
				"tipo": tipob
			},
			url: './?action=obtener_datos_boleta_ajax',

			success: function (datab) {
				$("#datosb").html('');
				$("#datosb").html(datab);
			},
		});
	}

	else if (tipob == '04') {
		var dsctob = $("#dsctob").val();

		$.ajax({
			type: "POST",
			data: {
				"numDoc": numDocb,
				"motivo": motivob,
				"serie": serieb,
				"comp": compb,
				"tipo": tipob,
				"dscto": dsctob
			},
			url: './?action=addnotacreditoboleta',
			dataType: 'json',

			success: function (data) {
				if (data && data.redirect) window.location.href = data.redirect;
			},
		});
	}

});

$("#notacredito_motivo_idb").change(function () {
	if ($("#notacredito_motivo_idb").val() == '04') {
		$("#dscto_globalb").attr('style', 'display:block');
	}

	else {
		$("#dscto_globalb").attr('style', 'display:none');
	}
});