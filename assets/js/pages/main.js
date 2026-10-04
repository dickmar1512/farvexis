$(document).ready(function() {
    // Mapeo de vistas a sus scripts correspondientes
    const viewScripts = {
        'home':'home.js',
        'sell':'sell.js',
        'sells':'sells.js',
        'products':'products.js',
        'providers':'providers.js',    
        'clients':'clients.js',
        'res':'res.js',
        're':'re.js',
        'nocfactura':'nocfactura.js',
        'nocboleta':'nocboleta.js',
        'unidades':'unidades.js',
        'categories':'categories.js',
        'box':'box.js',
        'b':'box.js',
        'expenseentry':'expenseentry.js',
        'reports':'reports.js',
        'users':'users.js',
        'onesell':'onesell.js',
    };

    // Función para cargar un script dinámicamente con jQuery
    function loadScript(scriptName) {
        return $.ajax({
            url: BASE_URL + `/assets/js/pages/${scriptName}?v=${Math.random()}`,
            dataType: "script",
            cache: true // Opcional: permite caché para mejorar rendimiento
        });
    }

    // Obtener la vista actual de la URL
    function getCurrentView() {
        if (typeof CURRENT_VIEW !== 'undefined') {
            return CURRENT_VIEW;
        }
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('view') || 'home'; // 'home' como vista por defecto
    }

    // Cargar el script correspondiente a la vista actual
    const currentView = getCurrentView();
    if (viewScripts[currentView]) {
        loadScript(viewScripts[currentView])
            .done(() => console.log(`${viewScripts[currentView]} cargado correctamente`))
            .fail((jqXHR, textStatus, errorThrown) => {
                console.error(`Error al cargar ${viewScripts[currentView]}:`, textStatus, errorThrown);
            });
    }

    // Manejar clics en los enlaces del menú para cargar scripts antes de navegar
    $('a[data-view]').on('click', function(e) {
        e.preventDefault();
        const view = $(this).data('view');
        const targetHref = $(this).attr('href');

        if (viewScripts[view]) {
            loadScript(viewScripts[view])
                .done(() => {
                    window.location.href = targetHref;
                })
                .fail(() => {
                    console.error(`Error al cargar el script para ${view}`);
                    window.location.href = targetHref; // Navega aunque falle la carga del JS
                });
        } else {
            window.location.href = targetHref;
        }
    });
});