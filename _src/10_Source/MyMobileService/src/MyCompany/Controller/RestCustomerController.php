<?php
namespace MyCompany\Controller;

use ZendServerGateway\Controller\AbstractRestfulController;
use MyCompany\Model\CustomerRepository;

class RestCustomerController extends AbstractRestfulController
{

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::getList()
     */
    public function getList ()
    {
        $cr = new CustomerRepository();
        return $cr->getAll();
    }

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::get()
     */
    public function get ($id)
    {
        $cr = new CustomerRepository();
        $customer = $cr->get($id);
        if ($customer === false) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        return $customer;
    }

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create ($data)
    {
        $data = (array) $data;
        $cr = new CustomerRepository();
        $name = $data['name'];
        $location = $data['location'];
        $activity = isset($data['activity']) ? $data['activity'] : "";
        $phone = isset($data['phone']) ? $data['phone'] : "";
        $id = $cr->add($name, $location, $activity, $phone);
        if ($id > - 1) {
            $this->getResponse()->setStatusCode(201);
            return $cr->get($id);
        } else {
            $this->getResponse()->setStatusCode(422);
            $this->getResponse()
                ->getHeaders()
                ->addHeaderLine('Content-type', 'application/error+json');
        }
    }

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::update()
     */
    public function update ($id, $data)
    {
        $data = (array) $data;
        $cr = new CustomerRepository();
        $name = $data['name'];
        $location = $data['location'];
        $activity = isset($data['activity']) ? $data['activity'] : "";
        $phone = isset($data['phone']) ? $data['phone'] : "";
        if ($cr->update($id, $name, $location, $activity, $phone)) {
            return $cr->get($id);
        } else {
            $this->getResponse()->setStatusCode(422);
            $this->getResponse()
                ->getHeaders()
                ->addHeaderLine('Content-type', 'application/error+json');
        }
    }

    /**
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::delete()
     */
    public function delete ($id)
    {
        $cr = new CustomerRepository();
        if ($cr->delete($id)) {
            $this->getResponse()->setStatusCode(204);
        } else {
            $this->getResponse()->setStatusCode(422);
            $this->getResponse()
                ->getHeaders()
                ->addHeaderLine('Content-type', 'application/error+json');
        }
    }
}
