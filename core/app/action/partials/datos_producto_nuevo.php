<?php if (isset($dato)&& count($dato)!=0) { ?>
	<input id="<?php echo $input; ?>" type="hidden" value="<?php echo $dato['name']; ?>">
	Debería decir: <?php echo $dato['name']; ?>
<?php } else{ ?>
	<input id="<?php echo $input; ?>" type="hidden" value="">
	No se encontró el producto con ese código
<?php } ?>