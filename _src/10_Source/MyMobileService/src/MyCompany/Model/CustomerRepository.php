<?php
namespace MyCompany\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

/**
 * Stores customers information /data/sqlite.db file.
 */
class CustomerRepository
{

    protected $customerTable;

    public function __construct()
    {
        $this->customerTable = new TableGateway(
            'customers',
            new Adapter(array(
                'driver' => 'Pdo_Sqlite',
                'database' => APPLICATION_PATH . '/data/sqlite.db'
            )
        ));
    }

    public function add($name, $location, $activity = '', $phone = '')
    {
        $inserted = $this->customerTable->insert(array(
            'name' => $name,
            'location' => $location,
            'activity' => $activity,
            'phone' => $phone
        ));
        if($inserted)
            return $this->customerTable->getLastInsertValue();
        return -1;
    }

    public function getAll()
    {
        return $this->customerTable->select()->toArray();
    }

    public function get($id)
    {
        $resultSet = $this->customerTable->select(array('id = ?' => $id));
        $row = $resultSet->current();
        return ($row) ? $row->getArrayCopy() : false;
    }

    public function update($id, $name, $location, $activity = '', $phone = '')
    {
        return (bool) $this->customerTable->update(
            array(
                'name' => $name,
                'location' => $location,
                'activity' => $activity,
                'phone' => $phone
            ),
            array('id = ?' => $id)
        );
    }

    public function delete($id)
    {
        return $this->customerTable->delete(array('id = ?' => $id));
    }

    private function createDb()
    {
        $sqlDrop = 'DROP TABLE IF EXISTS customers;';
        
        $sqlCreate = 'CREATE TABLE customers (' . 'id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ' . 'name TEXT NOT NULL, ' . 'location TEXT NULL, ' . 'activity TEXT NULL, phone TEXT NULL);';
        
        $this->dbAdapter->query($sqlDrop, Adapter::QUERY_MODE_EXECUTE);
        $this->dbAdapter->query($sqlCreate, Adapter::QUERY_MODE_EXECUTE);
        
        $customers = array(
            array(
                'Jane',
                'London',
                'walking',
                '123'
            ),
            array(
                'Marc',
                'San Francisco',
                'coding',
                '456'
            ),
            array(
                'Frank',
                'Zakopane',
                'skiing',
                '789'
            )
        );
        
        foreach ($customers as $customer) {
            $stmt = "INSERT INTO customers (name, location, activity, phone) VALUES ('" . $customer[0] . "', '" . $customer[1] . "', '" . $customer[2] . "', '" . $customer[3] . "');";
            $this->dbAdapter->query($stmt, Adapter::QUERY_MODE_EXECUTE);
        }
    }
    
    public function getSearch($query)
    {
    	$where = new \Zend\Db\Sql\Where();
    	$where->like('name', "%$query%");
    	return $this->customerTable->select($where)->toArray();
    }
}
