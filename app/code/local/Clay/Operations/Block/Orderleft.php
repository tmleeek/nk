<?php
class Clay_Operations_Block_Orderleft extends Clay_Courier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $operationsId = $session->getData('operationsId');
	    if($operationsId && $operationsId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}
	
	public function getCustomStatus(){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_order_status');		
		$sql = "SELECT * FROM ".$table." ORDER BY sort";
		$result = $connect->query( $sql );
		 return $result->fetchAll();
	}	
}
	