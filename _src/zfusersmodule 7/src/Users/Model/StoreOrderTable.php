<?php
namespace Users\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class StoreOrderTable
{
    protected $tableGateway;
    protected $productTableGateway;

    public function __construct(TableGateway $tableGateway, TableGateway $productTableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->productTableGateway = $productTableGateway;
    }

    public function saveOrder(StoreOrder $order)
    {
        $data = array(
            'store_product_id' => $order->store_product_id,
        	'qty' => $order->qty,        	
            'total'  => $order->total,
        	'status'  => $order->status,
        	'first_name' => $order->first_name,
        	'last_name'  => $order->last_name,
        	'email'  => $order->email,
        	'ship_to_street' => $order->ship_to_street,
        	'ship_to_city'  => $order->ship_to_city,
        	'ship_to_state'  => $order->ship_to_state,
        	'ship_to_zip'  => $order->ship_to_zip   	
        );

        $id = (int)$order->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getOrder($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Order ID does not exist');
            }
        }
    }
    
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    public function getOrder($orderId)
    {
    	$orderId  = (int) $orderId;
    	$rowset = $this->tableGateway->select(array('id' => $orderId));
    	$order = $rowset->current();
    	if (!$order) {
    		throw new \Exception("Could not find row $orderId");
    	}
    	
    	$productId = $order->store_product_id;
    	
    	$prodRowset = $this->productTableGateway->select(array('id' => $productId));
    	$product = $prodRowset->current();
    	
    	if (!empty($product)) {
    		$order->setProduct($product);
    	}
    	return $order;
    }
    
    public function deleteOrder($orderId)
    {
    	$this->tableGateway->delete(array('id' => $orderId));
    }
	   
    public function getProduct($orderId)
    {
    	$orderId  = (int) $orderId;
    	$order = $this->getOrder($orderId);
    	$productId = $order->store_product_id;
    	
    	$rowset = $this->productTableGateway->select(array('id' => $productId));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $orderId");
    	}
    	return $row;
    }
}
