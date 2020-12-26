<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StoreAdminController extends AbstractActionController
{
    public function indexAction()
    {
    	$productsTable = $this->getServiceLocator()->get('StoreProductsTable');
    	$storeProducts = $productsTable->fetchAll();
         
        $viewModel  = new ViewModel(
        	array(
        		'storeProducts' => $storeProducts
        	)
        );
        return $viewModel;
    }
    
    public function listOrdersAction()
    {
    	$storeOrdersTable = $this->getServiceLocator()->get('StoreOrdersTable');
    	$storeOrders = $storeOrdersTable->fetchAll();
         
        $viewModel  = new ViewModel(
        	array(
        		'storeOrders' => $storeOrders
        	)
        );
        return $viewModel;
    }
    
    public function viewOrderAction()
    {
    	$orderId = $this->params()->fromRoute('id');
    	$storeOrdersTable = $this->getServiceLocator()->get('StoreOrdersTable');
    	$storeOrder = $storeOrdersTable->getOrder($orderId);
    	
    	$viewModel  = new ViewModel(
    		array(
    			'storeOrder' => $storeOrder,
    			'orderProduct' => $storeOrder->getProduct(),
    		)
    	);
    	return $viewModel;
    }
    
    
    public function deleteProductAction()
    {
    	$productId = $this->params()->fromRoute('id');
    	$storeProductsTG = $this->getServiceLocator()->get('StoreProductsTableGateway');
    	$storeProductsTG->delete(array('id' => $productId));
    	return $this->redirect()->toRoute('users/store-admin');
    }
    
    public function updateOrderStatusAction()
    {
    	$orderId = $this->params()->fromRoute('id');
    	$newOrderStatus = $this->params()->fromRoute('subaction');
    	
    	$storeOrdersTG = $this->getServiceLocator()->get('StoreOrdersTableGateway');
    	$storeOrdersTG->update(array('status' => $newOrderStatus), array('id' => $orderId));
    	
    	return $this->redirect()->toRoute('users/store-admin/', array(action => 'viewOrder', id => $orderId));
    }
}





