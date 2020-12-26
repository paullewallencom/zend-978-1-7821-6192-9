<?php
namespace MyCompany\Controller;

use ZendServerGateway\Controller\AbstractActionController;
use MyCompany\Model\CustomerRepository;

class RpcController extends AbstractActionController
{

    public function getHelloAction ($name, $surname)
    {
        return array(
            'message' => "Hello $name $surname!"
        );
    }

    public function getCustomersAction ()
    {
        $cr = new CustomerRepository();
        return $cr->getAll();
    }
    
    public function getSearchCustomersAction ($query)
    {
    	$cr = new CustomerRepository();
    	return $cr->getSearch($query);
    }
    
}
