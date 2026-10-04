<?php

$qReabAct = 0;
$preci_in = 0;
$val      = 0;
$ope  = new OperationData();
$sell = new SellData();
$objP =  new ProductData();
foreach($_POST as $rownumber_name => $value) 
{
        $rownumber = $rownumber_name;
        $val = $value;

        //from the fieldname:rownumber_id we need to get rownumber_id
        $split_data       = explode(':', $rownumber);
        $rownumber_id     = $split_data[1];
        $rownumber_name   = $split_data[0];
        $rownumber_sellid = $split_data[2];
        $qReabOri         = $split_data[3];
        $ope->sell_id     = $rownumber_sellid;
        $ope->product_id  = $rownumber_id;

        if($rownumber_name=="q")
        {
           $qReabAct = $val;     
        }
        else
        {
           $qReabAct = $qReabOri; 
           $preci_in = $val;     
        }
        /********Actualizar tabla operation*********/
        $resp = $ope->updateReab($rownumber_name,$val);

        if ($resp==1) 
        {
         echo "Editando Registro: <img src='assets/img/Loading.gif' height='15px'>";
         /********Actualizar tabla sell*********/
         $sell->updateTotalReab($rownumber_sellid);

         /********Actualizar tabla product*********/
         $objP->id = $ope->product_id; 
         $objP->update_ProductReab($qReabAct,$qReabOri,$preci_in);
        } 
        else 
        {
         printf("Errormessage: %s\n", $resp->error);
        }
}
?>