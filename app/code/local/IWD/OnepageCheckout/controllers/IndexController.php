<?php
class IWD_OnepageCheckout_IndexController extends Mage_Checkout_Controller_Action
{
	private $_current_layout = null;

    protected $_sectionUpdateFunctions = array(
    	'review'          => '_getReviewHtml',
    	'shipping-method' => '_getShippingMethodsHtml',
        'payment-method'  => '_getPaymentMethodsHtml',
    );
	
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();
        return $this;
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    protected function _expireAjax()
    {
        if (!$this->getOnepagecheckout()->getQuote()->hasItems()
            || $this->getOnepagecheckout()->getQuote()->getHasError()
            || $this->getOnepagecheckout()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    protected function _getUpdatedLayout()
    {$this->_initLayoutMessages('checkout/session');
        if ($this->_current_layout === null)
        {
            $layout = $this->getLayout();
            $update = $layout->getUpdate();            
            $update->load('onepagecheckout_index_updatecheckout');
            
            $layout->generateXml();
            $layout->generateBlocks();
            $this->_current_layout = $layout;
        }
        
        return $this->_current_layout;
    }
        
    protected function _getShippingMethodsHtml()
    {
    	$layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.shipping.method')->toHtml();
    }

    protected function _getPaymentMethodsHtml()
    {
    	$layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.payment.method')->toHtml();
    }

	protected function _getCouponDiscountHtml()
    {
    	$layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.cart.coupon')->toHtml();
    }
    
    protected function _getReviewHtml()
    {
    	$layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.review')->toHtml();
    }

    protected function _isOnepagecheckoutBlock($ajax = false)
    {
    	if(Mage::helper('onepagecheckout')->isOPCBlock())
    	{
    		Mage::getSingleton('checkout/session')->addError(Mage::helper('onepagecheckout')->__('The one page checkout is blocked.'));
    		if(!$ajax)
            	$this->_redirect('checkout/cart');
            else
            {
				$result['redirect'] = Mage::getUrl('checkout/cart');
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }

            return false;
    	}
    	return true;
    }
    
    public function getOnepagecheckout()
    {
        return Mage::getSingleton('onepagecheckout/type_geo');
    }

    public function indexAction()
    {			
        if (!Mage::helper('onepagecheckout')->isOnepageCheckoutEnabled())
        {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('onepagecheckout')->__('The one page checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_isOnepagecheckoutBlock();
        
        $quote = $this->getOnepagecheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));

        $this->getOnepagecheckout()->initDefaultData()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $title	= Mage::getStoreConfig('onepagecheckout/general/title');
        $this->getLayout()->getBlock('head')->setTitle($title);
        $this->renderLayout();
    }
        
    public function successAction()
    {
    	$error	= Mage::helper('onepagecheckout')->checkParams($this->getRequest());
    	if($error)
    	{
			$this->_redirect('checkout/cart');
			return;
    	}
    	
    	$this->_isOnepagecheckoutBlock();
    	
        $session = $this->getOnepagecheckout()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');

        // mark that order will be saved by OPC module        
        $session->setProcessedOPC('opc');
        
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }

    public function failureAction()
    {
        $lastQuoteId = $this->getOnepagecheckout()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepagecheckout()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function getAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $addressId = $this->getRequest()->getParam('address', false);
        if ($addressId) {
            $address = $this->getOnepagecheckout()->getAddress($addressId);

            if (Mage::getSingleton('customer/session')->getCustomer()->getId() == $address->getCustomerId()) {
                $this->getResponse()->setHeader('Content-type', 'application/x-json');
                $this->getResponse()->setBody($address->toJson());
            } else {
                $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            }
        }
    }
    
    public function updateCheckoutAction()
    {
        if ($this->_expireAjax() || !$this->getRequest()->isPost()) {
            return;
        }
        
        $blockopc	= $this->getRequest()->getPost('blockopc', false);
        Mage::helper('onepagecheckout')->checkBlock($blockopc);
        
        if(!$this->_isOnepagecheckoutBlock(true))
        	return;
        	
		/*********** DISCOUNT CODES **********/
 
        $quote 				= $this->getOnepagecheckout()->getQuote();
        $couponData 		= $this->getRequest()->getPost('coupon', array());
        $processCoupon 		= $this->getRequest()->getPost('process_coupon', false);
       
        $couponChanged 		= false;
	    if ($couponData && $processCoupon) {
	            if (!empty($couponData['remove'])) {
	                $couponData['code'] = '';
	                 
	            }
	            $oldCouponCode = $quote->getCouponCode();
	            if ($oldCouponCode != $couponData['code']) {
	                try {
	                    $quote->setCouponCode(
	                        strlen($couponData['code']) ? $couponData['code'] : ''
	                    );
	                    $this->getRequest()->setPost('payment-method', true);
	                    $this->getRequest()->setPost('shipping-method', true);
	                    if ($couponData['code']) {
	                        $couponChanged = true;
	                    } else {
	                    	$couponChanged = true;
	                        Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('onepagecheckout')->__('Coupon code was canceled.'));
	                    }
	                } catch (Mage_Core_Exception $e) {
	                	$couponChanged = true;
	                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
	                } catch (Exception $e) {
	                	$couponChanged = true;
	                    Mage::getSingleton('checkout/session')->addError(Mage::helper('onepagecheckout')->__('Cannot apply the coupon code.'));
	                }
	                
	            }
	        }
        
        /***********************************/ 
	        
        $bill_data = $this->getRequest()->getPost('billing', array());
        $bill_data = $this->_filterPostData($bill_data);
        $bill_addr_id = $this->getRequest()->getPost('billing_address_id', false);
        $result = array();
        $ship_updated = false;
        
        if ($this->_checkChangedAddress($bill_data, 'Billing', $bill_addr_id) || $this->getRequest()->getPost('payment-method', false))
        {
            if (isset($bill_data['email']))
            {
                $bill_data['email'] = trim($bill_data['email']);
            }
            
            $bill_result = $this->getOnepagecheckout()->saveBilling($bill_data, $bill_addr_id, false);

            if (!isset($bill_result['error']))
            {
                $pmnt_data = $this->getRequest()->getPost('payment', array());
                $this->getOnepagecheckout()->usePayment(isset($pmnt_data['method']) ? $pmnt_data['method'] : null);

                $result['update_section']['payment-method'] = $this->_getPaymentMethodsHtml();

                if (isset($bill_data['use_for_shipping']) && $bill_data['use_for_shipping'] == 1 && !$this->getOnepagecheckout()->getQuote()->isVirtual())
				{
                    $result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
                    $result['duplicateBillingInfo'] = 'true';
                    
                    $ship_updated = true;
                }
            }
            else
            {
                $result['error_messages'] = $bill_result['message'];
            }
        }

        $ship_data = $this->getRequest()->getPost('shipping', array());
        $ship_addr_id = $this->getRequest()->getPost('shipping_address_id', false);
        $ship_method	= $this->getRequest()->getPost('shipping_method', false);

        if (!$ship_updated && !$this->getOnepagecheckout()->getQuote()->isVirtual())
        {
            if ($this->_checkChangedAddress($ship_data, 'Shipping', $ship_addr_id) || $ship_method) 
            {
                $ship_result = $this->getOnepagecheckout()->saveShipping($ship_data, $ship_addr_id, false);

                if (!isset($ship_result['error']))
                {
                    $result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
                }
            }
            
// fix
            if(!isset($result['update_section']['shipping-method']) && $this->getRequest()->getPost('shipping-method', false))
            {
            	$result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
            }
            
        }

        $check_shipping_diff	= false;

        // check how many shipping methods exist
        $rates = Mage::getModel('sales/quote_address_rate')->getCollection()->setAddressFilter($this->getOnepagecheckout()->getQuote()->getShippingAddress()->getId())->toArray();
        if(count($rates['items'])==1)
        {
        	if($rates['items'][0]['code']!=$ship_method)
        	{
        		$check_shipping_diff	= true;

        		$result['reload_totals'] = 'true';
        	}
        }
        else        
			$check_shipping_diff	= true;

// get prev shipping method
		if($check_shipping_diff){
			$shipping = $this->getOnepagecheckout()->getQuote()->getShippingAddress();
			$shippingMethod_before = $shipping->getShippingMethod();
		}

        $this->getOnepagecheckout()->useShipping($ship_method);

        $this->getOnepagecheckout()->getQuote()->collectTotals()->save();

		if($check_shipping_diff){        
			$shipping = $this->getOnepagecheckout()->getQuote()->getShippingAddress();
			$shippingMethod_after = $shipping->getShippingMethod();
        
	        if($shippingMethod_before != $shippingMethod_after)
	        {
	        	$result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
	        	$result['reload_totals'] = 'true';
	        }
	        else
	        	unset($result['reload_totals']);
        }
///////////////

        $result['update_section']['review'] = $this->_getReviewHtml();

        
        /*********** DISCOUNT CODES **********/
    	if ($couponChanged) {
            if ($couponData['code'] == $quote->getCouponCode()) {
                Mage::getSingleton('checkout/session')->addSuccess(
                    Mage::helper('onepagecheckout')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponData['code']))
                );
            } else {
                Mage::getSingleton('checkout/session')->addError(
                    Mage::helper('onepagecheckout')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponData['code']))
                );
            }
            $method = str_replace(' ', '', ucwords(str_replace('-', ' ', 'coupon-discount')));          
            $result['update_section']['coupon-discount'] = $this->{'_get' . $method . 'Html'}();
           
        }
        /************************************/
        
        
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function forgotpasswordAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $email = $this->getRequest()->getPost('email');
        $result = array('success' => false);
        
        if (!$email)
        {
			$result['error'] = Mage::helper('onepagecheckout')->__('Please enter your email.');
        }
        else
        {
			if (!Zend_Validate::is($email, 'EmailAddress'))
			{
                $session->setForgottenEmail($email);
                $result['error'] = Mage::helper('onepagecheckout')->__('Invalid email address.');
            }
            else
            {
                $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
                if(!$customer->getId())
                {
                	$session->setForgottenEmail($email);
                    $result['error'] = Mage::helper('onepagecheckout')->__('This email address was not found in our records.');
                }
                else
                {
                    try
                    {
						$new_pass = $customer->generatePassword();
                        $customer->changePassword($new_pass, false);
                        $customer->sendPasswordReminderEmail();
                        $result['success'] = true;
                        $result['message'] = Mage::helper('onepagecheckout')->__('A new password has been sent.');
                    }
                    catch (Exception $e)
                    {
                        $result['error'] = $e->getMessage();
                    }
                }
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function loginAction()
    {
    	Mage::helper('onepagecheckout')->checkParams($this->getRequest());
    	
        $session = Mage::getSingleton('customer/session');
        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $result = array('success' => false);


        if ($this->getRequest()->isPost())
        {
            $login_data = $this->getRequest()->getPost('login');
			
			if($login_data['checkout_method']== 'guest'){
				if(empty($login_data['username'])){
					$result['error'] = Mage::helper('onepagecheckout')->__('Login and password are required.');
				} else {
				
					//$this->createPostAction();
					//$this->getRequest()->createPostAction();
					require_once 'Mage/Customer/controllers/AccountController.php';
					
					if ($session->isLoggedIn()) {
						$result['redirect'] = Mage::getUrl('*/*/index', array('_secure'=>true));
						return;
					}
					$session->setEscapeMessages(true); // prevent XSS injection in user input
					if (!$customer = Mage::registry('current_customer')) {
                    $customer = Mage::getModel('customer/customer')->setId(null);
                	}
					$customerForm = Mage::getModel('customer/form');
                $customerForm->setFormCode('customer_account_create')
                    ->setEntity($customer);

                $customerData = $customerForm->extractData($this->getRequest());
				
                /**
                 * Initialize customer group id
                 */
                
				$customer->getGroupId();
				$password = $this->getRequest()->getPost('password');
				if(!Mage::getStoreConfig('fastregistration/general/show_password')){
                    $password = Mage::helper('core')->getRandomString(8,
                        Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS
                        . Mage_Core_Helper_Data::CHARS_PASSWORD_UPPERS
                        . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS
                        . Mage_Core_Helper_Data::CHARS_PASSWORD_SPECIALS);
                }
                try {
                    $customerErrors = $customerForm->validateData($customerData);
                    //if ($customerErrors !== true) {
                      //  $errors = array_merge($customerErrors, $errors);
                   // } else {
                        $customerForm->compactData($customerData);
						$customer->setEmail($login_data['username']);
                        $customer->setPassword($password);
                        $customer->setConfirmation($password);
                    //}

                    $validationResult = count($errors) == 0;

                    if (true === $validationResult) {
                        $customer->save();

                        Mage::dispatchEvent('customer_register_success',
                            array('account_controller' => $this, 'customer' => $customer)
                        );

                        if ($customer->isConfirmationRequired()) {
                            $customer->sendNewAccountEmail(
                                'confirmation',
                                $session->getBeforeAuthUrl(),
                                Mage::app()->getStore()->getId()
                            );
                            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                            //$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
							$result['redirect'] = Mage::getUrl('*/*/index', array('_secure'=>true));
                            //return;
                        } else {
                            //$session->setCustomerAsLoggedIn($customer);
                            //$url = $this->_welcomeCustomer($customer);
                            //$this->_redirectSuccess($url);
							
							$session->login($login_data['username'], $password);
                   			$result['success'] = true;
                    		$result['redirect'] = Mage::getUrl('*/*/index', array('_secure'=>true));
					
					
							//$result['redirect'] = Mage::getUrl('*/*/index', array('_secure'=>true));
                            //return;
                        }
                    } else {
                        $session->setCustomerFormData($this->getRequest()->getPost());
                        if (is_array($errors)) {
                            foreach ($errors as $errorMessage) {
                                $session->addError($errorMessage);
                            }
                        } else {
                            $session->addError($this->__('Invalid customer data'));
                        }
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                        $url = Mage::getUrl('customer/account/forgotpassword');
                        $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                        $session->setEscapeMessages(false);
                    } else {
                        $message = $e->getMessage();
                    }
                    //$session->addError($message);
                } catch (Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost())
                        ->addException($e, $this->__('Cannot save the customer.'));
                }
					$result['error'] = $message;
					//$result['error']= 'fsfsfsf';
					
            }
			 
					
            
			}
			else if (empty($login_data['username']) || empty($login_data['password'])) {
            	$result['error'] = Mage::helper('onepagecheckout')->__('Login and password are required.');
            }
            else
            {
				try
				{
                    $session->login($login_data['username'], $login_data['password']);
                    $result['success'] = true;
                    $result['redirect'] = Mage::getUrl('*/*/index', array('_secure'=>true));
                }
                catch (Mage_Core_Exception $e)
                {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('onepagecheckout')->__('Email is not confirmed. <a href="%s">Resend confirmation email.</a>', Mage::helper('customer')->getEmailConfirmationUrl($login_data['username']));
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $result['error'] = $message;
                    $session->setUsername($login_data['username']);
                }
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }



		public function createPostAction()
   	{
       		return $result['error'] = "test";
			
			//$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

       // }else{
           // parent::createPostAction();
       // }
    }




    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if(!$this->_isOnepagecheckoutBlock(true))
        	return;
        
        $result = array();
        try {
            $bill_data = $this->_filterPostData($this->getRequest()->getPost('billing', array()));
            
//			$result = $this->getOnepagecheckout()->saveBilling($bill_data,$this->getRequest()->getPost('billing_address_id', false));
			$result = $this->getOnepagecheckout()->saveBilling($bill_data,$this->getRequest()->getPost('billing_address_id', false),true,true);
            if ($result)
            {
            	$result['error_messages'] = $result['message'];
            	$result['error'] = true;
                $result['success'] = false;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            if ((!$bill_data['use_for_shipping'] || !isset($bill_data['use_for_shipping'])) && !$this->getOnepagecheckout()->getQuote()->isVirtual())
            {
//				$result = $this->getOnepagecheckout()->saveShipping($this->_filterPostData($this->getRequest()->getPost('shipping', array())),$this->getRequest()->getPost('shipping_address_id', false));
				$result = $this->getOnepagecheckout()->saveShipping($this->_filterPostData($this->getRequest()->getPost('shipping', array())),$this->getRequest()->getPost('shipping_address_id', false), true, true);
                if ($result)
                {
                	$result['error_messages'] = $result['message'];
                	$result['error'] = true;
                    $result['success'] = false;
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $agreements = Mage::helper('onepagecheckout')->getAgreeIds();
            if($agreements)
            {
				$post_agree = array_keys($this->getRequest()->getPost('agreement', array()));
				$is_different = array_diff($agreements, $post_agree);
                if ($is_different)
                {
                	$result['error_messages'] = Mage::helper('onepagecheckout')->__('Please agree to all the terms and conditions.');
                	$result['error'] = true;
                    $result['success'] = false;
                    
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            
            $result = $this->_saveOrderPurchase();

            if($result && !isset($result['redirect']))
            {
                $result['error_messages'] = $result['error'];
            }

            if(!isset($result['error']))
            {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepagecheckout()->getQuote()));
                $this->_subscribeNews();
            }

            Mage::getSingleton('customer/session')->setOrderCustomerComment($this->getRequest()->getPost('order-comment'));

            if (!isset($result['redirect']) && !isset($result['error']))
            {
            	$pmnt_data = $this->getRequest()->getPost('payment', false);
                if ($pmnt_data)
                    $this->getOnepagecheckout()->getQuote()->getPayment()->importData($pmnt_data);

                $this->getOnepagecheckout()->saveOrder();
                $redirectUrl = $this->getOnepagecheckout()->getCheckout()->getRedirectUrl();

                $result['success'] = true;
                $result['error']   = false;
                $result['order_created'] = true;
            }
        }
        catch (Mage_Core_Exception $e)
        {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepagecheckout()->getQuote(), $e->getMessage());

            $result['error_messages'] = $e->getMessage();
            $result['error'] = true;
            $result['success'] = false;

            $goto_section = $this->getOnepagecheckout()->getCheckout()->getGotoSection();
            if ($goto_section)
            {
            	$this->getOnepagecheckout()->getCheckout()->setGotoSection(null);
                $result['goto_section'] = $goto_section;
            }

            $update_section = $this->getOnepagecheckout()->getCheckout()->getUpdateSection();
            if ($update_section)
            {
                if (isset($this->_sectionUpdateFunctions[$update_section]))
                {
                    $layout = $this->_getUpdatedLayout();

                    $updateSectionFunction = $this->_sectionUpdateFunctions[$update_section];
                    $result['update_section'] = array(
                        'name' => $update_section,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepagecheckout()->getCheckout()->setUpdateSection(null);
            }

            $this->getOnepagecheckout()->getQuote()->save();
        } 
        catch (Exception $e)
        {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepagecheckout()->getQuote(), $e->getMessage());
            $result['error_messages'] = Mage::helper('onepagecheckout')->__('There was an error processing your order. Please contact support or try again later.');
            $result['error']    = true;
            $result['success']  = false;
            
            $this->getOnepagecheckout()->getQuote()->save();
        }

        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _saveOrderPurchase()
    {
    	$result = array();
    	
        try 
        {
            $pmnt_data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepagecheckout()->savePayment($pmnt_data);

            $redirectUrl = $this->getOnepagecheckout()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl)
            {
                $result['redirect'] = $redirectUrl;
            }
        }
        catch (Mage_Payment_Exception $e)
        {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        }
        catch (Mage_Core_Exception $e)
        {
            $result['error'] = $e->getMessage();
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            $result['error'] = Mage::helper('onepagecheckout')->__('Unable to set Payment Method.');
        }
        return $result;
    }

    protected function _subscribeNews()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('newsletter'))
        {
            $customerSession = Mage::getSingleton('customer/session');

            if($customerSession->isLoggedIn())
            	$email = $customerSession->getCustomer()->getEmail();
            else
            {
            	$bill_data = $this->getRequest()->getPost('billing');
            	$email = $bill_data['email'];
            }

            try {
                if (!$customerSession->isLoggedIn() && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1)
                    Mage::throwException(Mage::helper('onepagecheckout')->__('Sorry, subscription for guests is not allowed. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));

                $ownerId = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email)->getId();
                
                if ($ownerId !== null && $ownerId != $customerSession->getId())
                    Mage::throwException(Mage::helper('onepagecheckout')->__('Sorry, you are trying to subscribe email assigned to another user.'));

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
            }
            catch (Mage_Core_Exception $e) {
            }
            catch (Exception $e) {
            }
        }
    }

    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('dob'));
        return $data;
    }
    
    protected function _checkChangedAddress($data, $addr_type = 'Billing', $addr_id = false)
    {
    	$method	= "get{$addr_type}Address";
        $address = $this->getOnepagecheckout()->getQuote()->{$method}();

        if(!$addr_id)
        {
        	if(($address->getRegionId()	!= $data['region_id']) || ($address->getPostcode() != $data['postcode']) || ($address->getCountryId() != $data['country_id']))
        		return true;
        	else
        		return false;
        }
        else{
        	if($addr_id != $address->getCustomerAddressId())
        		return true;
        	else
        		return false;
        }
    }
}
