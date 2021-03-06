<?php class Clay_Courier_Model_Order extends Mage_Core_Model_Abstract {

	public function getShipments($order)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE order_id=".$order;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }
	
	public function processingOrders($courierId, $from, $to, $status){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('supplier_users');
		$query = "SELECT COUNT(sdi.order_id) as totalOrders,su.surname,su.email1 as email,su.contact,su.phone FROM ".$table." sdi INNER JOIN ".$table2." su ON sdi.supplier_id = su.id WHERE courier_id=".$courierId." and status IN (".implode(",",$status).") and date between '".$from."' and '".$to."' group by supplier_id";
		
	
		$result = $connect->query( $query );
       return $result->fetchAll();
	}
	
	public function picklistOrders($courierId, $from, $to, $status){
		$qryFrom = '';
		$qryTo = '';
		if($from !=''){
		$qryFrom = " and date >= '".$from."'";
		}
		if($to !=''){
		$qryTo = " and date <='".$to."'";
		}
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('supplier_users');
		$table3 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$query = "SELECT 
			SUM(sdi.price) as orderprice,
			SUM(sdi.qty) as orderqty, 
			sdi.order_number as order_number, 
			sdi.order_id as order_id, 
			sdi.awbno as awb , 
			sdi.product_name as product_name, 
			sdi.price as price, 
			sdi.sku as sku, 
			su.surname as vendor_name,
			su.address1 as address,
			su.postalcode as vpincode,
			sfo.status as pstatus,
			sfo.updated_at as updated_time,
			sfo.created_at as created_time 
			FROM ".$table." sdi INNER JOIN ".$table2." su ON sdi.supplier_id = su.id INNER JOIN ".$table3." sfo ON sdi.order_number = sfo.increment_id WHERE courier_id=".$courierId." and sdi.status IN (".implode(",",$status).") $qryFrom $qryTo GROUP BY order_number ORDER BY order_number DESC";
		
	
		
	
		$result = $connect->query( $query );
       return $result->fetchAll();
	}
	public function getOrderIdByShippingId($shippingid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$shippingid;
		$result = $connect->query( $query );
       return $result->fetch();
	}
	
	public function getShipment($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$parentid;
        $result = $connect->query( $query );
        return $result->fetch();
    }
	
	public function getShipmentItems($parentid,$productIds)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_item');
		$query = "SELECT * FROM $table WHERE parent_id=$parentid AND product_id IN ($productIds)";
		$result = $connect->query($query);
        return $result->fetchAll();
    }
		
	public function getTrackings($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
		$query = "SELECT * FROM ".$table." WHERE parent_id=".$parentid;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }

	public function checkOrderAuth($courierId,$orderId){
		$items = $this->getCartItemsBySupplier($courierId,$orderId);
		foreach($items as $item){
			if($item->getProductId()){
				return true;
			} else {
				return false;
			}
		}
	}

	public function getCartItemsBySupplier($courierId,$orderId){
		$order = Mage::getModel('sales/order')->load($orderId);	
		$items = $order->getAllItems();
		$status = array(1,5);	
		
		$collection = Mage::getModel('courier/dropshipitems')->getCollection();
		$collection->addFieldToSelect('product_id');
		$collection->addFieldToFilter('courier_id',$courierId);
		$collection->addFieldToFilter('order_id',$orderId);
		$collection->addFieldToFilter('status', array('in' => $status));
		
		$product_list = $collection->getData();
		$products = array();
			
		foreach($product_list as $product){
			$products[] = $product['product_id'];
		}
		
		foreach ($items as $itemid => $item) {				
			if(!in_array($item->getProductId(),$products)){
				unset($items[$itemid]);
			} 
		}  
		return $items;
	}

}