<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Http\Headers;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;

use Users\Model\User;
use Users\Model\UserTable;
use Users\Model\Upload;

class UploadManagerController extends AbstractActionController
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
	
	public function getFileUploadLocation()
	{
		// Fetch Configuration from Module Config
	    	$config  = $this->getServiceLocator()->get('config');
    		return $config['module_config']['upload_location'];
	}
	
    public function indexAction()
    {
    	$uploadTable = $this->getServiceLocator()->get('UploadTable');
    	$userTable = $this->getServiceLocator()->get('UserTable');
    	$userEmail = $this->getAuthService()->getStorage()->read();
    	$user = $userTable->getUserByEmail($userEmail);
    	$sharedUploads = $uploadTable->getSharedUploadsForUserId($user->id);
    	foreach($sharedUploads as $sharedUpload) {
    		$uploadOwner = $userTable->getUser($sharedUpload->user_id);
    		$sharedUploadInfo = array();
    		$sharedUploadInfo['label'] = $sharedUpload->label;
    		$sharedUploadInfo['owner'] = $uploadOwner->name;
    		$sharedUploadsList[$sharedUpload->id] = $sharedUploadInfo;
    	}
    	
    	$viewModel  = new ViewModel(
    			array(
    				'myUploads' => $uploadTable->getUploadsByUserId($user->id),
    				'sharedUploadsList' => $sharedUploadsList    				
    				)
    		);
    	return $viewModel;
    }

    public function processUploadAction()
    {
    	$userTable = $this->getServiceLocator()->get('UserTable');   	
    	$user_email = $this->getAuthService()->getStorage()->read();    	 
    	$user = $userTable->getUserByEmail($user_email);
		$form = $this->getServiceLocator()->get('UploadForm');
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    	
    		$upload = new Upload();
    		$uploadFile    = $this->params()->fromFiles('fileupload');
    		$form->setData($request->getPost());
    		
    		if ($form->isValid()) {
    			// Fetch Configuration from Module Config
    			$uploadPath    = $this->getFileUploadLocation();
    			// Save Uploaded file    	
      			$adapter = new \Zend\File\Transfer\Adapter\Http();
    			$adapter->setDestination($uploadPath);
    			
    			if ($adapter->receive($uploadFile['name'])) {
    				
    				$exchange_data = array();
    				$exchange_data['label'] = $request->getPost()->get('label');
    				$exchange_data['filename'] = $uploadFile['name'];
    				$exchange_data['user_id'] = $user->id;
    				
    				$upload->exchangeArray($exchange_data);
    				
    				$uploadTable = $this->getServiceLocator()->get('UploadTable');
    				$uploadTable->saveUpload($upload);
    				
    				return $this->redirect()->toRoute('users/upload-manager' , array(
    					'action' =>  'index'
    				));    				    				
    			}
    		}
    	}
    	 
    	return array('form' => $form);
    }
    
    public function uploadAction()
    {
		$form = $this->getServiceLocator()->get('UploadForm');
		$viewModel  = new ViewModel(array('form' => $form)); 
		return $viewModel; 
    }
    
    public function deleteAction()
    {
    	$uploadId = $this->params()->fromRoute('id');
    	$uploadTable = $this->getServiceLocator()
    					->get('UploadTable');
    	$upload = $uploadTable->getUpload($uploadId);
    	$uploadPath    = $this->getFileUploadLocation();
    	// Remove File
   		unlink($uploadPath ."/" . $upload->filename);    	
    	// Delete Records
    	$uploadTable->deleteUpload($uploadId);
    	
    	return $this->redirect()->toRoute('users/upload-manager');
    
    }
    
    public function editAction()
    {

    	$uploadId = $this->params()->fromRoute('id');
    	$uploadTable = $this->getServiceLocator()->get('UploadTable');
    	$userTable = $this->getServiceLocator()->get('UserTable');

    	// Upload Edit Form
    	$upload = $uploadTable->getUpload($uploadId);
    	$form = $this->getServiceLocator()->get('UploadEditForm');
    	$form->bind($upload);
    	
    	// Shared Users List
    	$sharedUsers = array();
    	$sharedUsersResult = $uploadTable->getSharedUsers($uploadId);
    	foreach($sharedUsersResult as $sharedUserRow ) {
    		$user = $userTable->getUser($sharedUserRow->user_id);
    		$sharedUsers[$sharedUserRow->id] =  $user->name;
    	}
		
    	// Add Additional Sharing
    	$uploadShareForm = $this->getServiceLocator()->get('UploadShareForm');
    	$allUsers = $userTable->fetchAll();
    	$usersList = array();
    	foreach($allUsers as $user) {
    		$usersList[$user->id] = $user->name;
    	}
    	
    	$uploadShareForm->get('upload_id')->setValue($uploadId);    	 
    	$uploadShareForm->get('user_id')->setValueOptions($usersList);
    	
    	$viewModel  = new ViewModel( array(	
    								'form' => $form, 
    								'upload_id' => $uploadId,
    								'sharedUsers' => $sharedUsers,
    								'uploadShareForm' => $uploadShareForm,
    							)
    					);
    	return $viewModel;
    }
    
    public function processUploadShareAction()
    {
    	 
    	$userTable = $this->getServiceLocator()
    								->get('UserTable');
    	$uploadTable = $this->getServiceLocator()
    								->get('UploadTable');
    	 
    	$form = $this->getServiceLocator()->get('UploadForm');
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$userId = $request->getPost()->get('user_id');
    		$uploadId = $request->getPost()->get('upload_id');
    		$uploadTable->addSharing($uploadId, $userId);
    		
    		return $this->redirect()
    					->toRoute('users/upload-manager', 
    									array(	'action' =>  'edit', 
    											'id' =>  $uploadId));
    	}
    
    }
    

    public function fileDownloadAction()
    {  
    	$uploadId = $this->params()->fromRoute('id');
	    $uploadTable = $this->getServiceLocator()->get('UploadTable');
	    $upload = $uploadTable->getUpload($uploadId);
	     
    	// Fetch Configuration from Module Config
    	$uploadPath    = $this->getFileUploadLocation();
		$file = file_get_contents($uploadPath ."/" . $upload->filename);
		
		// Directly return the Response 
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders(array(
			'Content-Type' => 'application/octet-stream',
			'Content-Disposition' => 'attachment;filename="' .$upload->filename . '"',

		));
		$response->setContent($file);
		
		return $response;	    
    }

}

