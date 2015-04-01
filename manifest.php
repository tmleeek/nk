<?php
echo phpinfo();

/*
require_once('app/Mage.php');
Mage::app();
	$cstatus = $_POST['chkStatus'];
	$supplierId = $_POST['supplier_id'];
	if($_REQUEST['s'] != '4'){
	$cstatus = 3;	
	$aststus = 'readypickup';
	$id = "('" . implode( "','", $_POST['aOrderid'] ) . "');" ;
	foreach($_POST['aOrderid'] as $aorder){
			$order = Mage::getModel('sales/order')->loadByIncrementId($aorder);
			$state = $aststus;
			$status = $aststus;
			$isCustomerNotified = true;
			$order->setState($state, $status);
			$order->save();	
				
	}
	$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
	$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
	$query = "UPDATE $table SET status='".$cstatus."' WHERE order_number IN $id";
	$connect->query($query);
}	
	switch($cstatus){
		case '1':
			$orderStatus = 'Pending';
			break;
		case '3':
			$orderStatus = 'Ready for Pickup';
			break;
		case '4':
			$orderStatus = 'Handed to Courier';
			break;
		case '5':
			$orderStatus = 'Delivered';
			break;
		case '6':
			$orderStatus = 'Cancel';
			break;
		case '7':
			$orderStatus = 'Hold';
			break;
		case '8':
			$orderStatus = 'Returns';
			break;	
	}
	$table = Mage::getSingleton('core/resource')->getTableName('supplier_manifest');
	$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
	$query = "insert into $table(courier_id,supplier_id) values('2','$supplierId')";
	$connect->query($query);
	$manifest_id = $connect->lastInsertId();
	
	$filename = "manifest_".$manifest_id."_".date('dmY').".pdf";
	
	$query = "UPDATE $table SET file_name='".$filename."' WHERE manifest_id=$manifest_id";
	$connect->query($query);
	
	$arrorderCourier = array();
	
	foreach($_POST['aOrderid'] as $key => $aorder){
		
		if($_POST['courier'][$key] == '2'){
			$arrorderCouriers[2][] = $aorder;
		}
		else if($_POST['courier'][$key] == '3'){
			$arrorderCouriers[3][] = $aorder;
		}
		
	}
	foreach($arrorderCouriers as $c => $arrorderCourier){
					
	$allItems = Mage::getModel('supplier/order')->getCartItemsBySupplierManifest($supplierId,$arrorderCourier);
	
    $html='<div style="border:1px solid">';
    
	$html.='<img style="margin-left:20px" src="http://www.netakart.com/skin/frontend/blacknwhite/default/images/logo.png" alt=""/>';
	$html.='<hr/><div style="margin-left:20px">Hand Over Sheet Date : '.date("d M Y H:i:s").'</div><hr/>';
	
	$supplier = Mage::getModel('supplier/supplier')->load($supplierId)->getData();
	$html.='<table style="width:100%">
    	<tr>
        	<td style="float:left;width:30%">
            <b>Vendor Address</b><br/>
            <div style="padding:10px;margin-top:5px;">';
			
			if($supplier['address1'] == $supplier['address2']){
				$supplier['address2'] ="";
				}
				$html.= $supplier['company'].'<br/>';
				$html.= $supplier['address1'].'<br/>';
				if($supplier['address2']!=''){
					$html.= $supplier['address2'].'<br/>';	
				}
				$html.= $supplier['city'].'<br/>';
				$html.= $supplier['postalcode'].'<br/>';
				
           	$html.= '</div> 	    
            </td>
            <td style="width:30%">&nbsp;</td>
            <td  style="width:30%;float:right">
            <b style="float:right">Logistic Partner</b><br/>
            <div style="text-align:right;padding:10px;margin-top:5px;">';
			
			if($supplier['address1'] == $supplier['address2']){
				$supplier['address2'] ="";
				}
			if($c == '2'){
				$html.= 'Delhivery<br/>';
				}
			if($c == '3'){
				$html.= 'DTDC<br/>';
				}	
				
				$html.= '<br/>';
				if($supplier['address2']!=''){
					$html.= '<br/>';	
				}

				$html.= '<br/>
				<b style="float:right">Order Status</b>
				<br/>'.
				$orderStatus
				.'<br/>
				';
			$html.= '</div>
            </td>
        </tr>
    </table>
 
	 <table style="width:100%;margin-top:20px">
    	<tr>
        	<th style="border-top:1px solid">Sr.No.</th>
            <th style="border-top:1px solid">Order ID</th>
            <th style="border-top:1px solid">SKU</th>
            <th style="border-top:1px solid">Product Details</th>
            <th style="border-top:1px solid">Qty</th>
            <th style="border-top:1px solid">AWB No.</th>
            <th style="border-top:1px solid">Logistic Partner</th>
	    </tr>';

		   $i=1;	 
			foreach($allItems as $order_id => $items){
				
				
				
				$order1 = Mage::getModel('sales/order')->loadByIncrementId($order_id);
				$shipmentCollection = $order1->getShipmentsCollection();
				foreach($shipmentCollection as $shipment){
        			$shipmentIncrementId = $shipment->getIncrementId();
         			$shipment=Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);
           			 
					
					foreach ($shipment->getAllTracks() as $track) {
						$track_number =  $track['track_number'];
	           		}
			 	}
				$html.= '<tr><td style="text-align:center;border-top:1px solid">'.$i++.'</td><td style="text-align:center;border-top:1px solid">'.$order_id.'</td><td style="text-align:center;border-top:1px solid">';
				$getSku = array();
				$getName = array();
				$getQtyOrdered = array();
				foreach($items as $item){
					
					$getSku[]= $item->getSku();	
					$getName[] = $item->getName();
					$getQtyOrdered[] = $item->getQtyOrdered();
				}
				$html.= implode(",",$getSku);
				$html.= '</td><td style="text-align:center;border-top:1px solid">';
				$html.= implode(",",$getName);
				$html.= '</td><td style="text-align:center;border-top:1px solid">';
				$html.= implode(",",$getQtyOrdered);
				$html.= '</td><td style="text-align:center;border-top:1px solid">'.$track_number.'</td>';
				$html.= '<td style="text-align:center;border-top:1px solid">';
				if($c == '2'){
				$html.= 'Delhivery';
				}
				if($c == '3'){
				$html.= 'DTDC';
				}
				$html.='</td></tr>';
		
			}
			

        echo $html;
      	$html.= '</table></div>';
		require("MPDF56/mpdf.php");
		$mpdf=new mPDF('en-x','A4','','',10,10,15,15,5,5,'L');
		$mpdf->WriteHTML($html);
		$mpdf->Output($filename,'D');
		
	}	*/
		
	  	//$html = '<p>Hello</p>';
		include('pdf/fpdf.php');
		//$mpdf=new mPDF('en-x','A4','','',10,10,15,15,5,5,'L');
		$pdf=new FPDF(); 
		$pdf->SetFont('Arial','',14);
		$pdf->Write(5,'Visit ');
		// Then put a blue underlined link
		$pdf->SetTextColor(0,0,255);
		$pdf->SetFont('','U');
		$pdf->Write(5,'www.fpdf.org','http://www.fpdf.org');
		exit;
		
	?>