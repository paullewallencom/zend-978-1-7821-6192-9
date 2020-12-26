<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StoreController extends AbstractActionController
{
    public function indexAction()
    {
    	$productTable = $this->getServiceLocator()->get('StoreProductsTable');
    	$storeProducts = $productTable->fetchAll();
         
        $viewModel  = new ViewModel(
        	array(
        		'storeProducts' => $storeProducts
        	)
        );
        return $viewModel;
    }
    
    public function productDetailAction()
    {
    	$productId = $this->params()->fromRoute('id');
    	$productTable = $this->getServiceLocator()->get('StoreProductsTable');
    	$product = $productTable->getProduct($productId);
    	
    	//Prepare AddToCart Form
    	$form    = new \Zend\Form\Form();
    	
    	$form->add(array(
    		'name' => 'qty',
    		'attributes' => array(
    			'type'  => 'text',
    			'id' => 'qty',
    			'required' => 'required'
    		),
    		'options' => array(
    			'label' => 'Quantity',
    		),
    	));
    	
    	$form->add(array(
    		'name' => 'submit',
    		'attributes' => array(
    			'type'  => 'submit',
    			'value' => 'Purchase'
    		),
    	));
    	
    	$form->add(array(
    		'name' => 'store_product_id',
    		'attributes' => array(
    			'type'  => 'hidden',
    			'value' => $product->id
    		),
    	));    	
   	
    	$viewModel  = new ViewModel(
        	array(
        		'product' => $product,
        		'form'=> $form
        	)
        );
    	return $viewModel;
    }
    
    public function shoppingCartAction()
    {
    	$request = $this->getRequest();
    	 
    	$productId = $request->getPost()->get('store_product_id');
    	$quantity = $request->getPost()->get('qty');
    	
    	$orderTable = $this->getServiceLocator()->get('StoreOrdersTable');
    	$productTable = $this->getServiceLocator()->get('StoreProductsTable');
    	$product = $productTable->getProduct($productId);
		
    	// Store Order
    	$newOrder = new \Users\Model\StoreOrder($product);
    	$newOrder->setQuantity($quantity);

    	$orderId = $orderTable->saveOrder($newOrder);
    	
    	$order = $orderTable->getOrder($orderId);
    	$viewModel  = new ViewModel(
        	array(
        		'order' => $order,
        		'productId' => $order->getProduct()->id,
        		'productName' => $order->getProduct()->name,
        		'productQty' => $order->qty,
        		'unitCost' => $order->getProduct()->cost,
        		'total'=> $order->total,
        		'orderId'=> $order->id,
        	)
        );		
    	return $viewModel;
    }
    
    
    public function paypalExpressCheckoutAction()
    {
    	$request = $this->getRequest();
    	$orderId = $request->getPost()->get('orderId');
    	
    	$orderTable = $this->getServiceLocator()->get('StoreOrdersTable');
    	$order = $orderTable->getOrder($orderId);
    	 
    	$paypalRequest = $this->getPaypalRequest();
    	
    	$paymentDetails = new \SpeckPaypal\Element\PaymentDetails(array(
    		'amt' => $order->total
    	));
    	$express = new \SpeckPaypal\Request\SetExpressCheckout(array('paymentDetails' => $paymentDetails));
    	
    	$express->setReturnUrl('http://comm-app.local/users/store/paymentConfirm');
    	$express->setCancelUrl('http://comm-app.local/users/store/paymentCancel');
    	   
    	$response = $paypalRequest->send($express);
    	$token = $response->getToken();
    	
    	$paypalSession = new \Zend\Session\Container('paypal');
    	$paypalSession->tokenId = $token; 
    	$paypalSession->orderId = $orderId;
    	
    	$this->redirect()->toUrl('https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token);
    }
    
    public function paymentConfirmAction()
    {
    	$orderTable = $this->getServiceLocator()->get('StoreOrdersTable');

    	//To capture Payer Information from PayPal
    	$paypalSession = new \Zend\Session\Container('paypal');
    	$paypalRequest = $this->getPaypalRequest();
    	
    	$expressCheckoutInfo =  new \SpeckPaypal\Request\GetExpressCheckoutDetails();
    	$expressCheckoutInfo->setToken($paypalSession->tokenId);
    	$response = $paypalRequest->send($expressCheckoutInfo);
    	
    	//To capture express payment
    	$order = $orderTable->getOrder($paypalSession->orderId);
    	$paymentDetails = new \SpeckPaypal\Element\PaymentDetails(array(
    		'amt' => $order->total
    	));
    	
    	$token = $response->getToken();
    	$payerId = $response->getPayerId();
    	
    	$captureExpress = new \SpeckPaypal\Request\DoExpressCheckoutPayment(array(
    		'token'             => $token,
    		'payerId'           => $payerId,
    		'paymentDetails'    => $paymentDetails
    	));
    	$captureResponse = $paypalRequest->send($captureExpress);
    	
    	//To Save Order Information
    	$order->first_name = $response->getFirstName();
    	$order->last_name = $response->getLastName();
    	$order->ship_to_street = $response->getShipToStreet();
    	$order->ship_to_city = $response->getShipToCity();
    	$order->ship_to_state = $response->getShipToState();
    	$order->ship_to_zip = $response->getShipToZip();
    	$order->email = $response->getEmail();
    	$order->store_order_id = $paypalSession->orderId;
    	$order->status = 'completed';
    	
    	$orderTable->saveOrder($order);
    	
    	$paypalSession->orderId = NULL;
    	$paypalSession->tokenId = NULL;
    	
    	$view  = new ViewModel(
    		array(
    			'storeOrder' => $order,
    			'orderProduct' => $order->getProduct(),
    		)
    	);
    	return $view;
    }
    
    public function paymentCancelAction()
    {
    	$paypalSession = new \Zend\Session\Container('paypal');
    	 
    	$storeOrdersTG = $this->getServiceLocator()->get('StoreOrdersTableGateway');
    	$storeOrdersTG->update(array('status' => 'cancelled'), array('id' => $paypalSession->orderId));
    	
    	$paypalSession->orderId = NULL;
    	$paypalSession->tokenId = NULL;
    	 
    	$view = new ViewModel();
    	return $view;
    }

    protected function getPaypalRequest()
    {
    	$config  = $this->getServiceLocator()->get('config');
    	$paypalConfig = new \SpeckPaypal\Element\Config(
    							$config['speck-paypal-api']);
    	 
    	$adapter = new \Zend\Http\Client\Adapter\Curl();
    	$adapter->setOptions(array(
    		'curloptions' => array(
    			CURLOPT_SSL_VERIFYPEER => false,
    		)
    	));
    	 
    	$client = new \Zend\Http\Client;
    	$client->setMethod('POST');
    	$client->setAdapter($adapter);
    	 
    	$paypalRequest = new \SpeckPaypal\Service\Request;
    	$paypalRequest->setClient($client);
    	$paypalRequest->setConfig($paypalConfig);
    	
    	return $paypalRequest;
    }
    
    
}





