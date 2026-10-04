<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fa fa-history"></i> Historial de Combinaciones</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Productos - Servicios</a></li>
                    <li class="breadcrumb-item active">Historial Combinaciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-body">
                <table class="table table-bordered datatable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Producto Principal (Destino)</th>
                            <th>Producto Eliminado (Origen)</th>
                            <th>Código Barras Origen</th>
                            <th>Stock Movido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $db = Database::getCon();
                        // Verificar si la tabla existe antes de consultar
                        $checkTable = $db->query("SHOW TABLES LIKE 'merged_product_history'");
                        if($checkTable->num_rows > 0):
                            $sql = "SELECT m.*, u.name as user_name FROM merged_product_history m LEFT JOIN user u ON m.user_id = u.id ORDER BY m.created_at DESC";
                            $res = $db->query($sql);
                            while($r = $res->fetch_array()):
                            ?>
                            <tr>
                                <td><?php echo $r['created_at']; ?></td>
                                <td><?php echo $r['user_name']; ?></td>
                                <td><?php echo $r['primary_product_name']; ?> <small class="text-muted">(ID: <?php echo $r['primary_product_id']; ?>)</small></td>
                                <td><?php echo $r['duplicate_product_name']; ?> <small class="text-muted">(ID: <?php echo $r['duplicate_product_id']; ?>)</small></td>
                                <td><code><?php echo $r['duplicate_barcode']; ?></code></td>
                                <td><span class="badge badge-success">+ <?php echo number_format($r['merged_stock'], 2); ?></span></td>
                            </tr>
                            <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay registros de combinaciones aún.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
