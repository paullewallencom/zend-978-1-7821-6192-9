<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;

use Users\Model\User;
use Users\Model\UserTable;

use Zend\Mail;


class GroupChatController extends AbstractActionController
{
	protected $storage;
	protected $authservice;
	
	protected function getAuthService()
	{
		if (! $this->authservice) {
			$this->authservice = $this->getServiceLocator()->get('AuthService');
		}
	
		return $this->authservice;
	}
	
	protected function getLoggedInUser()
	{
		$userTable = $this->getServiceLocator()->get('UserTable');
		$userEmail = $this->getAuthService()->getStorage()->read();
		$user = $userTable->getUserByEmail($userEmail);
	
		return $user;
	}
	
	protected function sendMessage($messageTest, $fromUserId)
	{
		$chatMessageTG = $this->getServiceLocator()->get('ChatMessagesTableGateway');
		$data = array(
			'user_id' => $fromUserId,
			'message'  => $messageTest,
			'stamp' => NULL
		);
		$chatMessageTG->insert($data);
		
		return true;
	}
	
	public function indexAction()
	{
		$user = $this->getLoggedInUser();	
		$request = $this->getRequest();
		if ($request->isPost()) {
			$messageTest = $request->getPost()->get('message');
			$fromUserId = $user->id;
			$this->sendMessage($messageTest, $fromUserId);
			// to prevent duplicate entries on refresh
			return $this->redirect()->toRoute('users/group-chat');
		}
		
		//Prepare Send Message Form
		$form    = new \Zend\Form\Form();
		
		$form->add(array(
			'name' => 'message',
			'attributes' => array(
				'type'  => 'text',
				'id' => 'messageText',
				'required' => 'required'
			),
			'options' => array(
				'label' => 'Message',
			),
		));
		
		$form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Send'
            ),
		));
		
		$form->add(array(
			'name' => 'refresh',
			'attributes' => array(
				'type'  => 'button',
				'id' => 'btnRefresh',
				'value' => 'Refresh'
			),
		));
		
		$viewModel  = new ViewModel(array('form' => $form, 'userName' => $user->name));
		return $viewModel;
	}

	public function messageListAction()
	{
		$userTable = $this->getServiceLocator()->get('UserTable');
		
		$chatMessageTG = $this->getServiceLocator()->get('ChatMessagesTableGateway');
		$chatMessages = $chatMessageTG->select();
		
		$messageList = array();
		foreach($chatMessages as $chatMessage) {
			$fromUser = $userTable->getUser($chatMessage->user_id);
			$messageData = array();
			$messageData['user'] = $fromUser->name;
			$messageData['time'] = $chatMessage->stamp;
			$messageData['data'] = $chatMessage->message;
			$messageList[] = $messageData;
		}
		
		$viewModel  = new ViewModel(array('messageList' => $messageList));
		$viewModel->setTemplate('users/group-chat/message-list');
		$viewModel->setTerminal(true);		
		return $viewModel;
	}
	
	public function sendOfflineMessageAction()
	{
		$userTable = $this->getServiceLocator()->get('UserTable');
		$allUsers = $userTable->fetchAll();
		$usersList = array();
		foreach($allUsers as $user) {
			$usersList[$user->id] = $user->name . '(' . $user->email . ')';
		}
		
		$user = $this->getLoggedInUser();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$msgSubj = $request->getPost()->get('messageSubject');
			$msgText = $request->getPost()->get('message');
			$toUser = $request->getPost()->get('toUserId');
			$fromUser = $user->id;
			$this->sendOfflineMessage($msgSubj, $msgText, $fromUser, $toUser);
			// to prevent duplicate entries on refresh
			return $this->redirect()->toRoute('users/group-chat', 
									array('action' => 'sendOfflineMessage'));
		}
	
		//Prepare Send Message Form
		$form    = new \Zend\Form\Form();
        $form->setAttribute('method', 'post');
        $form->setAttribute('enctype','multipart/form-data');
		
		$form->add(array(
			'name' => 'toUserId',
			'type'  => 'Zend\Form\Element\Select',
			'attributes' => array(
				'type'  => 'select',
			),
			'options' => array(
				'label' => 'To User',
			),
		));
				
		$form->add(array(
			'name' => 'messageSubject',
			'attributes' => array(
				'type'  => 'text',
				'id' => 'messageSubject',
				'required' => 'required'
			),
			'options' => array(
				'label' => 'Subject',
			),
		));

		$form->add(array(
			'name' => 'message',
			'attributes' => array(
				'type'  => 'textarea',
				'id' => 'message',
				'required' => 'required'
			),
			'options' => array(
				'label' => 'Message',
			),
		));
				
		$form->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Send'
			),
			'options' => array(
				'label' => 'Send',
			),			
		));
	
		$form->get('toUserId')->setValueOptions($usersList);
		$viewModel  = new ViewModel(array('form' => $form, 
									'userName' => $user->name));
		return $viewModel;
	}
	
	protected function sendOfflineMessage($msgSubj, $msgText, $fromUserId, $toUserId)
	{
		$userTable = $this->getServiceLocator()->get('UserTable');
		$fromUser = $userTable->getUser($fromUserId);
		$toUser = $userTable->getUser($toUserId);
		
		$mail = new Mail\Message();
		$mail->setFrom($fromUser->email, $fromUser->name);
		$mail->addTo($toUser->email, $toUser->name);
		$mail->setSubject($msgSubj);
		$mail->setBody($msgText);
		
		$transport = new Mail\Transport\Sendmail();
		$transport->send($mail);
		
		return true;
	}	
	
}
