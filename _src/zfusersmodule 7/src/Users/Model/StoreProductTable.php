<?php
namespace Users\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class StoreProductTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveProduct(StoreProduct $product)
    {
        $data = array(
            'name' => $product->name,
        	'desc' => $product->desc,        	
            'cost'  => $product->cost,
        );

        $id = (int)$product->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getProduct($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Product ID does not exist');
            }
        }
    }
    
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    public function getProduct($productId)
    {
    	$productId  = (int) $productId;
    	$rowset = $this->tableGateway->select(array('id' => $productId));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $productId");
    	}
    	return $row;
    }
    
    public function deleteProduct($productId)
    {
    	$this->tableGateway->delete(array('id' => $productId));
    }
	   
}
