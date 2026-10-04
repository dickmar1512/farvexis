<style>
    .btn-outline-nc {
        color: #dc3545 !important;
        border: 1.5px solid #dc3545 !important;
        background-color: #fff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        display: inline-block;
        text-decoration: none !important;
    }
    .btn-outline-nc:hover {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
    .btn-outline-nd {
        color: #28a745 !important;
        border: 1.5px solid #28a745 !important;
        background-color: #fff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        display: inline-block;
        text-decoration: none !important;
    }
    .btn-outline-nd:hover {
        background-color: #28a745 !important;
        color: #fff !important;
    }
    .btn-outline-xml {
        color: #17a2b8 !important;
        border: 1.5px solid #17a2b8 !important;
        background-color: #fff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        display: inline-block;
        text-decoration: none !important;
    }
    .btn-outline-xml:hover {
        background-color: #17a2b8 !important;
        color: #fff !important;
    }
    .btn-outline-cdr {
        color: #28a745 !important;
        border: 1.5px solid #28a745 !important;
        background-color: #fff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        display: inline-block;
        text-decoration: none !important;
    }
    .btn-outline-cdr:hover {
        background-color: #28a745 !important;
        color: #fff !important;
    }
    .btn-outline-sunat {
        color: #d97706 !important;
        border: 1.5px solid #d97706 !important;
        background-color: #fff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        padding: 2px 6px !important;
        border-radius: 4px !important;
        display: inline-block;
    }
    .btn-outline-sunat:hover {
        background-color: #d97706 !important;
        color: #fff !important;
    }
</style>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class='fas fa-cart-plus'></i> Registro de ventas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Venta</a></li>
                    <li class="breadcrumb-item active">Registro de ventas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i>
                    Filtros de búsqueda
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <input type="hidden" name="view" value="sellsnew">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha inicial</label>
                                <div class="input-group date" id="fechaini" data-target-input="nearest">
                                    <input type="text" name="sd"  class="form-control datetimepicker-input" data-target="#fechaini"/>
                                    <div class="input-group-append" data-target="#fechaini" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha final</label>
                                <div class="input-group date" id="fechafin" data-target-input="nearest">
                                    <input type="text" name="ed" class="form-control datetimepicker-input" data-target="#fechafin"/>
                                    <div class="input-group-append" data-target="#fechafin" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Usuario</label>
                                <select name="user_id" id="user_id" class="form-control select2bs4" style="width: 100%;">
									<option value="">Cargando usuarios...</option>
								</select>
								<div id="loading" class="mt-2" style="display: none;">
								    <small class="text-muted">Cargando...</small>
								</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" style="margin-top: 32px;">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Resultados
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sellsnew" class="table table-bordered table-striped" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>Ver</th>
                                <th>Comprobante</th>
                                <th>Cliente</th>
                                <th>Importe </th>
							    <th>Medio Pago</th>
                                <th>Fecha Emision</th>
                                <th>Fecha Envio</th>
                                <th>Estado SUNAT</th>
                                <th>Acciones</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán via AJAX -->
                        </tbody>                        
                    </table>
                    <div style="display: flex; justify-content: center;" id="resumenIngresos">
                        <table class="table table-bordered table-hover col-md-4">
                            <tfoot>
                                <thead class="thead-dark">
                                    <tr>
                                        <th colspan="4" style="text-align: center;">Medios de pago</th>
                                    </tr>
                                </thead>
                                <tr>
                                    <th colspan="1" style="text-align:right">Total Plin</th>
                                    <th id="total-plin" style="text-align:right">0.00</th>
                                    <th colspan="1" style="text-align:right">Total Efectivo</th>
                                    <th id="total-efectivo" style="text-align:right">0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="text-align:right">Total Yape:</th>
                                    <th id="total-yape" style="text-align:right">0.00</th>
                                    <th rowspan="3">&nbsp;</th>
								    <th rowspan="3">&nbsp;</th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="text-align:right">Total T.Debito:</th>
                                    <th id="total-tdebito" style="text-align:right">0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="text-align:right">Total T.Credito:</th>
                                    <th id="total-tcredito" style="text-align:right">0.00</th>
                                </tr>
                                <?php //if (UserData::getById($_SESSION["user_id"])->is_admin): ?>
                                <!-- <tr>
                                    <th colspan="1" style="text-align:right">Capital:</th>
                                    <th id="total-capital">0.00</th>
                                    <th colspan="4"></th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="text-align:right">Ganancia:</th>
                                    <th id="total-ganancia">0.00</th>
                                    <th colspan="4"></th>
                                </tr> -->
                                <?php //endif; ?>
                                <tr class="bg-warning">
                                    <th style="text-align: right;">Sub Total</th>
                                    <th style="text-align: right;" id="subtotal-otros">0.00</th>
                                    <th style="text-align: right;">Sub Total</th>
                                    <th style="text-align: right;" id="subtotal-efectivo">0.00</th>
                                </tr>						
                                <tr class="bg-success">
                                    <th colspan="3" style="text-align: right;">Total de ventas </th>
                                    <th style="text-align: right;" id="total-ventas">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>