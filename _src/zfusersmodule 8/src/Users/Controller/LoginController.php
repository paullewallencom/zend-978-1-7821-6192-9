<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

use Users\Form\LoginForm;
use Users\Form\LoginFilter;

use Users\Model\User;
use Users\Model\UserTable;

class LoginController extends AbstractActionController
{
	protected $storage;
    protected $authservice;
    
    public function getAuthService()
    {
        if (! $this->authservice) {
        	$this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }
        
    public function logoutAction()
    {
        $this->getAuthService()->clearIdentity();
        
        return $this->redirect()->toRoute('users/login');
    }
	
    public function indexAction()
    {
		$form = $this->getServiceLocator()->get('LoginForm');
		$viewModel  = new ViewModel(array('form' => $form)); 
		return $viewModel; 
    }

    public function processAction()
    {
        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute('users/login');
        }

        $post = $this->request->getPost();

		$form = $this->getServiceLocator()->get('LoginForm');

        $form->setData($post);
        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form'  => $form,
            ));
            $model->setTemplate('users/login/index');
            return $model;
        } else {
			//check authentication...
			$this->getAuthService()->getAdapter()
								   ->setIdentity($this->request->getPost('email'))
								   ->setCredential($this->request->getPost('password'));
            $result = $this->getAuthService()->authenticate();
            if ($result->isValid()) {
				$this->getAuthService()->getStorage()->write($this->request->getPost('email'));
				return $this->redirect()->toRoute('users/login', array( 
                        'action' =>  'confirm' 
                    ));			
            } else {
				$model = new ViewModel(array(
					'error' => true,
					'form'  => $form,
				));
				$model->setTemplate('users/login/index');
				return $model;
			}
		}
    }

    public function confirmAction()
    {
    	$this->layout('layout/myaccount');    	
		$user_email = $this->getAuthService()->getStorage()->read();
		$viewModel  = new ViewModel(array(
            'user_email' => $user_email 
        )); 
		return $viewModel; 
    }

}
