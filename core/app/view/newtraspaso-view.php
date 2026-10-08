<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class='fas fa-exchange-alt mr-2'></i> Nuevo Traspaso</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./?view=traspasos">Traspasos</a></li>
                    <li class="breadcrumb-item active">Nuevo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm"><i class="fas fa-search mr-1"></i> BUSCADOR DE PRODUCTOS</h3>
                    </div>
                    <div class="card-body py-3">
                        <form id="searchTraspasoForm" autocomplete="off">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-barcode text-primary"></i></span>
                                        </div>
                                        <input type="text" id="product_code_traspaso" name="product" 
                                               class="form-control border-left-0" 
                                               placeholder="Escriba el nombre o código para buscar..."
                                               autofocus>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Resultados -->
            <div class="col-md-12" id="search-results-traspaso"></div>

            <!-- Contenedor del Carrito -->
            <div class="col-md-12" id="traspaso-cart-container">
                <?php include "core/app/action/traspaso_cart_table-action.php"; ?>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#searchTraspasoForm').submit(function(e) {
        e.preventDefault();
        var p = $("#product_code_traspaso").val();
        if(p == "") { return; }
        $.get("./?action=searchproduct_traspaso", { product: p }, function(data) {
            $("#search-results-traspaso").html(data);
        });
    });
});
</script>
