<style type="text/css">
    .seleccion::selection {
        background: yellow;
        border: 1px solid #39c !important;
    }

    .seleccion::-moz-selection {
        background: yellow;
        border: 1px solid #39c !important;
    }
    
    /* Estilos base para tabla responsiva */
    .compact-table {
        font-size: 0.85em !important;
        margin-bottom: 10px !important;
        width: 100%;
        table-layout: auto;
    }
    
    .compact-table th,
    .compact-table td {
        padding: 6px 8px !important;
        vertical-align: middle !important;
        word-wrap: break-word;
    }
    
    .compact-table .form-control {
        padding: 4px 8px !important;
        font-size: 0.85em !important;
        height: 32px !important;
        min-width: 0;
    }
    
    .compact-table .btn {
        padding: 4px 8px !important;
        font-size: 0.85em !important;
        height: 32px !important;
        min-width: 32px;
    }
    
    .compact-header {
        background: #17a2b8 !important;
        color: white !important;
        font-size: 0.9em !important;
        text-align: center !important;
    }
    
    /* Contenedor responsivo para formulario */
    .form-container {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
        width: 100%;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    
    .form-label {
        font-size: 0.75em;
        font-weight: 500;
        margin-bottom: 2px;
        color: #666;
    }
    
    /* Distribución responsiva de campos */
    .field-desc { flex: 1; min-width: 120px; }
    .field-price { width: 70px; min-width: 60px; }
    .field-discount { width: 70px; min-width: 60px; }
    .field-quantity { width: 70px; min-width: 60px; }
    .field-button { width: 40px; min-width: 40px; }
    
    /* Estilos para móviles */
    @media (max-width: 768px) {
        .compact-table {
            font-size: 0.8em !important;
        }
        
        /* Ocultar headers tradicionales en móviles */
        .desktop-header {
            display: none;
        }
        
        /* Layout de cards para móviles */
        .mobile-card {
            display: block !important;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 12px;
            padding: 12px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .mobile-card td {
            display: block !important;
            border: none !important;
            padding: 4px 0 !important;
        }
        
        .product-name {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .stock-badge {
            margin-bottom: 12px;
        }
        
        /* Formulario en móviles - layout vertical */
        .form-container {
            flex-direction: column;
            gap: 8px;
        }
        
        .form-row {
            display: flex;
            gap: 8px;
            width: 100%;
        }
        
        .field-desc {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .field-price,
        .field-discount,
        .field-quantity {
            flex: 1;
            min-width: 0;
        }
        
        .field-button {
            width: 50px;
            align-self: flex-end;
        }
    }
    
    @media (max-width: 480px) {
        .compact-table {
            font-size: 0.75em !important;
        }
        
        .form-container {
            gap: 6px;
        }
        
        .form-row {
            gap: 6px;
        }
        
        .field-desc input {
            font-size: 0.8em !important;
            padding: 6px !important;
        }
        
        .field-price,
        .field-discount,
        .field-quantity {
            width: 60px;
            flex: none;
        }
    }
    
    /* Estilos para tablets */
    @media (min-width: 769px) and (max-width: 1024px) {
        .field-desc { min-width: 160px; }
        .field-price,
        .field-discount,
        .field-quantity { width: 65px; }
    }
    
    /* Utilidades responsivas */
    .text-responsive {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .text-responsive {
            white-space: normal;
            word-wrap: break-word;
        }
    }
    
    /* Alert responsivo */
    .alert-responsive {
        padding: 8px 12px;
        font-size: 0.85em;
        margin: 5px 0;
    }
    
    @media (max-width: 768px) {
        .alert-responsive {
            padding: 10px;
            font-size: 0.9em;
            text-align: center;
        }
    }
</style>

<?php
$is_desc = UserData::getById($_SESSION["user_id"])->is_desc;
$empresa_sunat = EmpresaData::getDatos();
$default_igv = (!empty($empresa_sunat) && !empty($empresa_sunat->sunat_igv_tipo_defecto)) ? $empresa_sunat->sunat_igv_tipo_defecto : '20';
if (isset($_GET["product"]) && $_GET["product"] != ""):
    $products = ProductData::getLike(htmlspecialchars($_GET["product"], ENT_NOQUOTES, "UTF-8"));
    if (count($products) > 0) {
        ?>
        <!-- Vista Desktop -->
        <div class="d-none d-md-block">
            <table class="table table-bordered table-hover compact-table">
                <thead class="compact-header">
                    <tr>
                        <th colspan="4" style="padding: 8px;">
                            RESULTADOS (<?php echo count($products); ?>)
                        </th>
                    </tr>
                    <tr class="desktop-header">
                        <th style="width: 40%;">PRODUCTO / LABORATORIO</th>
                        <th style="width: 8%;">STOCK</th>
                        <th style="width: 52%;">
                            <div style="display: flex; gap: 4px; font-size: 0.8em;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">DESCRIPCIÓN</label>
                                <label style="width: 88px; margin: 0; font-weight: 500; text-align: center;">IMPUESTO</label>
                                <label style="width: 65px; margin: 0; font-weight: 500; text-align: center;">PRECIO</label>
                                <label style="width: 60px; margin: 0; font-weight: 500; text-align: center;">DESC.</label>
                                <label style="width: 55px; margin: 0; font-weight: 500; text-align: center;">CANT.</label>
                                <label style="width: 35px; margin: 0;">&nbsp;</label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $products_in_cero = 0;
                foreach ($products as $product):
                    $q = $product->stock;

                    if ($q > 0 or $product->is_stock == 0): ?>
                        <tr class="<?php if ($q <= $product->inventary_min) { echo "table-warning"; } ?>">
                            <td>
                                <div class="font-weight-bold text-primary text-responsive"><?php echo $product->name; ?></div>
                                <?php if(isset($product->laboratorio) && trim($product->laboratorio) != ""): ?>
                                    <small class="badge badge-light border text-muted"><?php echo $product->laboratorio; ?></small>
                                    <?php
                                        // Verificar si la fecha es nula o vacía
                                        if (empty($product->fecha_venc)) {
                                            $fecha_venc = strtotime('2099-01-31 00:00:00');
                                            $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                                            $badge_class = 'secondary';
                                            $mensaje = 'Sin fecha de vencimiento';
                                            $fecha_formateada = '31/01/2099';
                                        } else {
                                            $fecha_venc = strtotime($product->fecha_venc);
                                            $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                                            $fecha_formateada = date('d/m/Y', $fecha_venc);
                                            
                                            // Determinar clase del badge y mensaje según días restantes
                                            if ($dias_restantes <= 0) {
                                                $badge_class = 'danger';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Vencido!, hace '.abs($dias_restantes).' dias';
                                            }elseif ($dias_restantes <= 90) {
                                                $badge_class = 'danger';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                                            } elseif ($dias_restantes <= 120) {
                                                $badge_class = 'warning';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                                            } else {
                                                $badge_class = 'success';
                                                $mensaje = 'Vence: ' . $fecha_formateada;
                                            }
                                        }
                                        ?>

                                        <small class="badge badge-<?php echo $badge_class; ?> border text-<?php echo ($badge_class == 'warning' ? 'dark' : 'white'); ?>">
                                            <?php echo $mensaje; ?>
                                        </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo ($product->is_stock == 0) ? 'badge-success' : 'badge-info'; ?>">
                                    <?php if ($product->is_stock == 0) {
                                        echo "∞";
                                    } else {
                                        echo $q;
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" action="./?action=addtocart_ajax" class="form-add-to-cart">
                                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                                    <input type="hidden" name="idpaquete" value="X">
                                    <input type="hidden" name="f_price_in" value="<?php echo $product->price_in; ?>">
                                    
                                    <div class="form-container">
                                        <div class="form-group field-desc">
                                            <input type="text" class="form-control seleccion" name="descripcion" placeholder="Descripción...">
                                        </div>

                                        <div class="form-group" style="width: 88px; min-width: 80px;">
                                            <select class="form-control" name="igv_tipo" style="font-size: 0.78em; padding: 2px 4px; height: 32px;" title="Tipo de Impuesto">
                                                <option value="20" <?= $default_igv == '20' ? 'selected' : '' ?>>Exonerado</option>
                                                <option value="10" <?= $default_igv == '10' ? 'selected' : '' ?>>Gravado (18%)</option>
                                                <option value="30" <?= $default_igv == '30' ? 'selected' : '' ?>>Inafecto</option>
                                                <option value="11" <?= $default_igv == '11' ? 'selected' : '' ?>>Gratuito</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group field-price">
                                            <input type="number" step="any" class="form-control" required name="precio_unitario"
                                                   placeholder="Precio" value="<?php echo $product->price_out ?>" min="0">
                                        </div>
                                        
                                        <div class="form-group field-discount">
                                            <?php if ($is_desc == 1): ?>
                                                <input type="number" step="any" class="form-control" required name="descuento"
                                                       placeholder="Desc." value="0.00" min="0">
                                            <?php else: ?>
                                                <input type="hidden" name="descuento" value="0">
                                                <input type="text" class="form-control" value="0.00" readonly 
                                                       style="background-color: #f8f9fa;">
                                            <?php endif ?>
                                        </div>
                                        
                                        <div class="form-group field-quantity">
                                            <?php $permiso = PermisoData::get_permiso_x_key('decimales'); ?>
                                            <input type="number" class="form-control" required name="q" placeholder="Cant."
                                                   value="1" <?php echo ($permiso->Pee_Valor == 1) ? 'step="any"' : ''; ?>>
                                        </div>
                                        
                                        <div class="form-group field-button">
                                            <button type="submit" class="btn btn-primary btn-sm" title="Agregar">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php else:
                        $products_in_cero++;
                    endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Vista Mobile -->
        <div class="d-md-none">
            <div class="compact-header" style="padding: 12px; border-radius: 8px 8px 0 0; margin-bottom: 0;">
                <strong>RESULTADOS (<?php echo count($products); ?>)</strong>
            </div>
            <?php
            $products_in_cero = 0;
            foreach ($products as $product):
                $q = $product->stock;

                if ($q > 0 or $product->is_stock == 0): ?>
                    <div class="mobile-card <?php if ($q <= $product->inventary_min) { echo "border-warning"; } ?>">
                        <div class="product-name text-responsive" style="margin-bottom: 4px;">
                            <div class="font-weight-bold text-primary"><?php echo $product->name; ?></div>
                            <?php if(isset($product->laboratorio) && trim($product->laboratorio) != ""): ?>
                                <small class="badge badge-light border text-muted" style="font-size: 0.7em;"><?php echo $product->laboratorio; ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="stock-badge">
                            <span class="badge <?php echo ($product->is_stock == 0) ? 'badge-success' : 'badge-info'; ?>">
                                Stock: <?php if ($product->is_stock == 0) {
                                    echo "∞";
                                } else {
                                    echo $q;
                                } ?>
                            </span>
                        </div>
                        
                        <form method="post" action="./?action=addtocart_ajax" class="form-add-to-cart">
                            <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                            <input type="hidden" name="idpaquete" value="X">
                            <input type="hidden" name="f_price_in" value="<?php echo $product->price_in; ?>">
                            
                            <div class="form-container">
                                <div class="form-group field-desc">
                                    <label class="form-label">Descripción</label>
                                    <input type="text" class="form-control seleccion" name="descripcion" placeholder="Descripción del producto...">
                                </div>

                                <div class="form-group" style="width: 100%; margin-bottom: 6px;">
                                    <label class="form-label">Impuesto (Afectación IGV)</label>
                                    <select class="form-control form-control-sm" name="igv_tipo" style="font-size: 0.85em;">
                                        <option value="20" <?= $default_igv == '20' ? 'selected' : '' ?>>Exonerado (0%)</option>
                                        <option value="10" <?= $default_igv == '10' ? 'selected' : '' ?>>Gravado (18% IGV)</option>
                                        <option value="30" <?= $default_igv == '30' ? 'selected' : '' ?>>Inafecto (0%)</option>
                                        <option value="11" <?= $default_igv == '11' ? 'selected' : '' ?>>Gratuito (Transferencia Gratuita)</option>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group field-price">
                                        <label class="form-label">Precio</label>
                                        <input type="number" step="any" class="form-control" required name="precio_unitario"
                                               value="<?php echo $product->price_out ?>" min="0">
                                    </div>
                                    
                                    <div class="form-group field-discount">
                                        <label class="form-label">Desc.</label>
                                        <?php if ($is_desc == 1): ?>
                                            <input type="number" step="any" class="form-control" required name="descuento"
                                                   value="0.00" min="0">
                                        <?php else: ?>
                                            <input type="hidden" name="descuento" value="0">
                                            <input type="text" class="form-control" value="0.00" readonly 
                                                   style="background-color: #f8f9fa;">
                                        <?php endif ?>
                                    </div>
                                    
                                    <div class="form-group field-quantity">
                                        <label class="form-label">Cant.</label>
                                        <?php $permiso = PermisoData::get_permiso_x_key('decimales'); ?>
                                        <input type="number" class="form-control" required name="q"
                                               value="1" <?php echo ($permiso->Pee_Valor == 1) ? 'step="any"' : ''; ?>>
                                    </div>
                                    
                                    <div class="form-group field-button">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary" title="Agregar">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else:
                    $products_in_cero++;
                endif; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($products_in_cero > 0) { ?>
            <div class="alert alert-warning alert-responsive">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?php echo $products_in_cero; ?> productos</strong> sin stock omitidos.
                <a href="./?view=inventary" class="alert-link">Ver inventario</a>
            </div>
        <?php } ?>

    <?php } else { ?>
        <div class="alert alert-info alert-responsive">
            <i class="fas fa-search"></i> No se encontraron productos que coincidan con "<strong><?php echo htmlspecialchars($_GET["product"]); ?></strong>"
        </div>
    <?php } ?>

<?php endif; ?>