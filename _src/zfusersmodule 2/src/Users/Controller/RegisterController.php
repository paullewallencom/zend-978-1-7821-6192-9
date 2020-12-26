<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;

use Users\Model\User;
use Users\Model\UserTable;

class RegisterController extends AbstractActionController
{
	
    public function indexAction()
    {
		$form = new RegisterForm();
		$viewModel  = new ViewModel(array('form' => $form)); 
		return $viewModel; 
    }

    public function processAction()
    {
        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute(NULL , array( 
                        'controller' => 'register', 
                        'action' =>  'index' 
                    ));
        }

        $post = $this->request->getPost();

        $form = new RegisterForm();
		$inputFilter = new RegisterFilter();
		$form->setInputFilter($inputFilter);

        $form->setData($post);
        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form'  => $form,
            ));
            $model->setTemplate('users/register/index');
            return $model;
        }

        // Create user
        $this->createUser($form->getData());

        return $this->redirect()->toRoute(NULL , array( 
                        'controller' => 'register', 
                        'action' =>  'confirm' 
                    ));
    }

    public function confirmAction()
    {
		$viewModel  = new ViewModel(); 
		return $viewModel; 
    }

    protected function createUser(array $data)
    {

		$sm = $this->getServiceLocator();
		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

		$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new \Users\Model\User);
		$tableGateway       = new \Zend\Db\TableGateway\TableGateway('user' /* table name  */, $dbAdapter, null, $resultSetPrototype);

		$user = new User();
		$user->exchangeArray($data);

		$userTable = new UserTable($tableGateway);
		$userTable->saveUser($user);

		return true;
    }
}
