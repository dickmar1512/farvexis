<?php
	if(count($_POST) > 0)
	{
		$orden_id = $_POST["orden_id"];

		$orden = OrdenTrabajoData::getById($orden_id);

		if(isset($_SESSION["cart2"]))
		{
			$cart = $_SESSION["cart2"];

			if(count($cart) > 0)
			{
				$num_succ = 0;
				$process = false;
				$errors = array();
			
				foreach($cart as  $c)
				{
					$detalle_orden = new DetalleOrdenData();
					$detalle_orden->product_id = $c["product_id"];
					$detalle_orden->q = $c["q"];
					$detalle_orden->prec_alt = $c["precio_unitario"];
					$detalle_orden->orden_id = $orden_id;

					$add = $detalle_orden->add();

					$product = ProductData::getById($c["product_id"]);

					//restar stock
					if($orden->tipo_servicio == 1)
					{
						if($product->is_stock == 1)
						{
							$product2 = new ProductData();
							$product2->stock = $c["q"];
							$product2->id = $c["product_id"];

							$product2->restar_stock();
						}						
					}

					unset($_SESSION["cart2"]);
				}
			}
		}

		print "<script>window.location='./?view=oneorden&id=$orden_id';</script>";
	}
?>