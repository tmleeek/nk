<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Lastmile
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Shipping Label Printing 
 */
class Delhivery_Lastmile_Model_Shippinglabel extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('lastmile/shippinglabel');
    }
    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    protected $_pdf;

	
    /**
     * Generate Shipment Label Content for each Waybill
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
	 
     */
	 
	 public function getSupplierDetails($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_users');
		 $query = "SELECT i.supplier_name,u.surname, u.address1, u.city, u.state, u.country, u.contact, u.company,u.postalcode, u.phone,u.custom1, u.custom2 FROM ".$table." AS i INNER JOIN ".$table1." AS u ON i.supplier_name = u.name WHERE i.awbno !='' and i.order_id=".$parentid;
		
		$result = $connect->query( $query );
		
        return $result->fetchAll();
    }
	
    public function getContent(&$page, $store = null, $waybill, $order, $pos, $post, $courierName = '')
    {
		$image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image       = Zend_Pdf_Image::imageWithPath($image);
                $top         = $pos; //top border of the page
                $widthLimit  = 100; //half of the page width
                $heightLimit = 70; //assuming the image is not a "skyscraper"
                $width       = $image->getPixelWidth();
                $height      = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width  = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);
				//$session = Mage::getSingleton('core/session');
       			//$supplierId = $session->getData('supplierId');
				//$increment_id = $order->getIncrementId();
				//$items = Mage::getModel("supplier/order")->getCartItemsBySupplierInvoice($supplierId,$increment_id);
				// Add Order ID, Date and COD amount
				$this->_setFontRegular($page, 7);
				$this->_setFontBold($page, 12);
				$page->drawText("$courierName", $x1+350, $y1-10); // Courier Name
				
				if($order->getPayment()->getMethodInstance()->getCode()=='cashondelivery'){
				$page->drawText(Mage::helper('sales')->__('CASH ON DELIVERY'), $x1+450, ($y1-10), 'UTF-8');	
				}
				else {
					$page->drawText(Mage::helper('sales')->__('PREPAID'), $x1+460, ($y1-10), 'UTF-8');	
				}
				
				
				
				// Add Barcode and waybill#
				//$fontPath = '/var/www/html/magento/barcode-fonts/FRE3OF9X.TTF';
				$fontPath = Mage::getBaseDir() . '/media/delhivery/font/FRE3OF9X.TTF';
				$page->setFont(Zend_Pdf_Font::fontWithPath($fontPath), 30);
				$barcodeImage = "*".$waybill."*";
				$page->drawText($barcodeImage, $x1+390, $y1-40);				
		        $this->_setFontRegular($page, 9);
				$page->drawText("*", $x1+385, $y1-48);
				$page->drawText("*", $x1+540, $y1-48);
				$page->drawText("AWB# $waybill", $x1+420, $y1-48);
				
				$this->_setFontBold($page, 12);
				$page->drawText("Ship to:", $x1, $y1-30);
				$page->drawLine($x1+ 200, $y1-75, $x1+550, $y1-77);
				$page->drawLine($x1+ 200, $y1-95, $x1+550, $y1-97);
				$this->_setFontBold($page, 9);
				$page->drawText("S.No", $x1 + 200, $y1-85);
				$page->drawText("Item Description", $x1 + 230, $y1-85);
				//$page->drawText("Est Wt.", $x1+300, $y1-215);
				$page->drawText("Qty.", $x1+380, $y1-85);
				$page->drawText("Rate", $x1+420, $y1-85);
				$page->drawText("Tax", $x1+460, $y1-85);
				$page->drawText("Amount.", $x1+500, $y1-85);
				
				
				
				$this->_setFontRegular($page, 9);
				// Add Shipping Address
				$shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));				
				$addressy = $y1-40;
				foreach ($shippingAddress as $value){
					if ($value!=='') {
						$text = array();
						$value = preg_replace('/<br[^>]*>/i', "\n", $value);
						foreach (Mage::helper('core/string')->str_split($value, 40, true, true) as $_value) {
							$text[] = $_value;
						}
						foreach ($text as $part) {
							$page->drawText(strip_tags(ltrim($part)), $x1, $addressy, 'UTF-8');
							$addressy -= 11;
						}
					}
				}
				$addressy -=20;
				$this->_setFontBold($page, 12);
				$page->drawText(Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), $x1, $addressy, 'UTF-8');
				$addressy -=15;
				$page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(), 'medium', false), $x1, $addressy, 'UTF-8');
				$addressy -= 15;
				$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? $order->getGrandTotal() : "00.00";
				$this->_setFontBold($page, 12);
				$page->drawText("$courierName", $x1+350, $y1-10); // Courier Name
				
				if($order->getPayment()->getMethodInstance()->getCode()=='cashondelivery'){
				$page->drawText(Mage::helper('sales')->__('Collect Rs.') . $codamount, $x1, $addressy, 'UTF-8');							
				}
				else {
					$page->drawText(Mage::helper('sales')->__('No Collection'), $x1, $addressy, 'UTF-8');	
				}
				$addressy -=20;
				$this->_setFontBold($page, 12);
				$page->drawText("From:", $x1, $addressy);
				$addressy -= 15;
					
				$this->_setFontRegular($page, 9);
				$supplierAddress = array();
				$supplierD = $this->getSupplierDetails($order->getId());
				//print_r($supplierD);
				//die;
				$value1 = array();
				foreach ($supplierD as $value){
					
					if ($value!=='') {
						$value1 = $value['surname']."\n";
						$value1 .= $value['address1']."\n";
						$value1 .= $value['city'].", ".$value['postalcode']."\n";
						$value1 .= $value['state']."\n";
						$value1 .= $value['country']."\n";
						$value1 .= "T: ".$value['phone']."\n";
						$value1 .= "VAT/TIN: ".$value['custom1']."\n";
						$value1 .= "Service tax #: ".$value['custom2']."\n";
						//$value = preg_replace('/<br[^>]*>/i', "\n", $value1)	;		
						
						foreach (Mage::helper('core/string')->str_split($value1, 40, true, true) as $_value) {
							$page->drawText(strip_tags(trim($_value)), $x1, $addressy, 'UTF-8');	
							$addressy -= 11;
						}
					}
					break;
				}
				
				//$order_details = Mage::getModel('sales/order')->loadByIncrementId($order->getRealOrderId());
				
				$order_details = Mage::getModel('supplier/order')->getCartItemsBySupplierInvoice($post['supplier_id'],$order->getRealOrderId());
				
				//$order_details = Mage::getModel('supplier/order')->getCartItemsBySupplier($post['supplier_id'],$order->getRealOrderId());
				$addressy = $y1-105;
				$i=1;
				
				//foreach ($order_details->getAllItems() as $item) {  
				foreach ($order_details as $item) {
					if($item->getPrice() > 0){
						$price = (($item->getPrice())* ($item->getQtyOrdered()));
					}
					else{
						$prices = Mage::getModel('supplier/order')->getconfigPrice($item->getData('item_id'));	
						$price = $prices[0]['price'];
					}
					
					$subtotal[] += $price; 
					
					$page->drawText(strip_tags(trim($i)), $x1 + 200, $addressy, 'UTF-8');
					//$item->getName() = preg_replace('/<br[^>]*>/i', "\n", $item->getName());	
					$addressy1 = $addressy;
					$page->drawText(strip_tags(trim($item->getName())), $x1 + 230, $addressy, 'UTF-8');
					$addressy -=10;
					$page->drawText('Sku : '.strip_tags(trim($item->getSku())), $x1 + 230, $addressy, 'UTF-8');
					$addressy -=10;
					if ($_options = $item->getProductOptions()){
						
												
							if($_options['attributes_info']){
								
							foreach ($_options['attributes_info'] as $_option) {
								
								if ($_option['value']){
									$_option['value'] = preg_replace('/<br[^>]*>/i', "\n", $_option['value']);
									$page->drawText(strip_tags(trim($_option['value'])), $x1 + 230, $addressy, 'UTF-8');
								}
								$addressy -=10;
							}
								
							} else {
							foreach ($_options as $_option) {
									
								
							if (is_array($_option['value'])){
								$_option[0]['value'] = preg_replace('/<br[^>]*>/i', "\n", $_option[0]['value']);
								$page->drawText(strip_tags(trim($_option[0]['value'])), $x1+280, $addressy, 'UTF-8');
							}
								else {
									$_option[0]['value'] = preg_replace('/<br[^>]*>/i', "\n", $_option[0]['value']);
									$page->drawText(strip_tags(trim($_option[0]['value'])), $x1 + 230, $addressy, 'UTF-8');
								}
								$addressy -=10;
							}
						}
					} else {
						//$_option[0]['value'] = preg_replace('/<br[^>]*>/i', "\n", $_option[0]['value']);
						$page->drawText(strip_tags(trim('No')), $x1 + 230, $addressy, 'UTF-8');
						$addressy -=10;
					}
					
					
					$page->drawText('Weight : '.strip_tags(trim($item->getWeight())), $x1 + 230, $addressy, 'UTF-8');
					$page->drawText(strip_tags(trim($item->getQtyOrdered())), $x1+380, $addressy1, 'UTF-8');
					$page->drawText(strip_tags(trim($price)), $x1+450, $addressy1, 'UTF-8');
					//$page->drawText(strip_tags(trim(Mage::helper("core")->currency($item->getPrice() * $item->getQtyOrdered()))), $x1+450, $addressy, 'UTF-8');
					$addressy = $addressy - 15;
					$i++;
				}
				
				$page->drawLine($x1+ 200, $addressy + 5, $x1+550, $addressy+7);
				//$addressy= $addressy - 15;
				
				//$addressy = $y1-150;
				$page->drawText("Sub Total.", $x1+350, $addressy -= 10);
				$page->drawText(strip_tags(trim(Mage::helper("core")->currency(array_sum($subtotal)))), $x1+450, $addressy, 'UTF-8');
				$page->drawText("Shipping", $x1+350, $addressy -= 10);
				$page->drawText(strip_tags(trim(Mage::helper("core")->currency($order_details->discount_amount))), $x1+450, $addressy, 'UTF-8');
				$page->drawText("Total Amount.", $x1+350, $addressy -= 10);
				$page->drawText(strip_tags(trim(Mage::helper("core")->currency(array_sum($subtotal)))), $x1+450, $addressy, 'UTF-8');
				
				
				//$page->drawText(strip_tags(trim($supplierD['supplier_name'])), $x1, $addressy, 'UTF-8');
				
				$returnPloicy1  = 'Return Policy: At NET.A.KART we try to deliver perfectly every time. But on the off-chance if you need to return the item, please do so keeping the original Brand box/price tag, original packing and';
				
				$returnPloicy2  = 'invoice intact, without which it will be difficult for us to act on your request. Terms and conditions apply.';
				
				$returnPloicy3 = 'The goods sold as part of this shipment are intended for end user consumption/retail sale and not for re-sale';
				$page->drawText(strip_tags(trim($returnPloicy1)), $x1, $y1-750, 'UTF-8');
				$page->drawText(strip_tags(trim($returnPloicy2)), $x1, $y1-760, 'UTF-8');
				$page->drawText(strip_tags(trim($returnPloicy3)), $x1, $y1-770, 'UTF-8');	
				
				
				$value2 = array();
				
				foreach ($supplierD as $value){
					
					if ($value!=='') {
						$value2 = "Return Address:  \n";
						$value2 .= $value['surname']."\n";
						$value2 .= $value['address1']."\n";
						$value2 .= $value['city'].", ".$value['postalcode']."\n";
						$value2 .= $value['state']."\n";
						$value2 .= $value['country']."\n";
						$value2 .= "T: ".$value['phone']."\n";
						$value2 .= "VAT/TIN: ".$value['custom1']."\n";
						$value2 .= "Service tax #: ".$value['custom2']."\n";
						//$value = preg_replace('/<br[^>]*>/i', "\n", $value1)	;		
						
						foreach (Mage::helper('core/string')->str_split($value2, 550, true, true) as $_value) {
							$page->drawText(strip_tags(trim($_value)), $x1, $addressy-640, 'UTF-8');	
							$addressy -= 11;
						}
					}
				}
				
				
				$page->drawLine($x1, $pos-750, $x1+550, $pos-750);							
            }
        }
    }
    /**
     * Set PDF object
     *
     * @param  Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Return PDF document
     *
     * @param  array $shipments
     * @return Zend_Pdf
     */
    public function getPdf()
    {
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
		//$page  = $this->newPage($pdf);
		//$this->getContent($page, $shipment->getStore(), $waybill, $shipment->getOrder());
        return $pdf;
    }	

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 100, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }	
}