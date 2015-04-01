<?php 
class Clay_Salesreport_IndexController extends Mage_Core_Controller_Front_Action 
{ 
	 public function indexAction() {
        $session = Mage::getSingleton('core/session');
		
        $salesId = $session->getData('salesId');
        Mage::getSingleton('core/session')->setData( 'salesId' , 'logout' );
        if( $salesId && $salesId != "logout" ) {
            $redirectPath = Mage::getUrl() . "salesreport/order";
            $this->_redirectUrl( $redirectPath );
        }
        if( $this->getRequest()->getParam( 'error' ) ){
            Mage::getSingleton('core/session')->addError($this->__('The username or password you entered is incorrect'));
        }
        $this->loadLayout();
        $this->renderLayout();
    }
	
	
	 public function checkSalesInfo( $username, $password ) {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "SELECT id FROM " . Mage::getSingleton('core/resource')->getTableName('sales_user') . "  WHERE (username='$username') and (password='$password')";
		
        $result = $connect->query($query);
        $id = $result->fetch();
        return $id;
    }
	
	public function loginAction() {
				
        $username = $this->getRequest()->getPost('username');
        $password = md5($this->getRequest()->getPost('password'));
        // Check logining user info
				
        $id = $this->checkSalesInfo( $username, $password );
		$homePath = Mage::getUrl() . 'salesreport/order';	
        $errorPath = Mage::getUrl() . 'salesreport/';
		
        if( $id ) {
         
		 
		  Mage::getSingleton('core/session')->setData( 'salesId' , $id['id'] );
		  
            $this->_redirectUrl( $homePath );
        }else {
            Mage::getSingleton('core/session')->addError($this->__('Wrong login or password'));
            $this->_redirectUrl( $errorPath . 'index/index/error/true' );
        }
    }
 
 	public function rand_string( $length ) {
		$chars = "abcdefghijklmnopqrstuvwxyz";
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
			}
			return $str;
		}
		
	public function forgotAction()
	{
		if ($this->getRequest()->isPost())
		{
			$email = $this->getRequest()->getPost('email');
			$collection = Mage::getModel('supplier/supplier')->getCollection();
			foreach ($collection as $supplier)
			{
				if ($supplier->username == $email)
				{
					$pass = $this->rand_string(9);
					
					$senderEmail = Mage::getStoreConfig('supplier/emailoptions/email_sender_email');
					$message = "Here is your new password : " . $pass;
					$headers = 'From: '. $senderEmail . "\r\n";
					mail($email, 'New Supplier Password', $message , $headers);
					Mage::getSingleton('core/session')->setData( 'forgotNotif' , 'Your new password has been sent to your email!' );
					Mage::getSingleton('core/session')->setData( 'forgotError' , NULL);
					$user = Mage::getModel('supplier/supplier')->load($supplier->id);
					$user->password = md5($pass);
					$user->save();
					break;
				}
				else
				{
					 Mage::getSingleton('core/session')->setData( 'forgotError' , $this->__('Wrong Email') . '!');
				}
			}
		}
		$this->loadLayout();
		$this->renderLayout();
	} 
	
	public function logoutAction() {
        $homePath = Mage::getUrl() . 'salesreport';
        Mage::getSingleton('core/session')->setData( 'salesId' , 'logout' );
        $this->_redirectUrl( $homePath );


    }
} 
?>