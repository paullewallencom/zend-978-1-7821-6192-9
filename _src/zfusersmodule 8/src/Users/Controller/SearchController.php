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

use Users\Model\ImageUpload;
use Users\Model\ImageUploadTable;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Index;

class SearchController extends AbstractActionController
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
	
	public function getIndexLocation()
	{
		// Fetch Configuration from Module Config
		$config  = $this->getServiceLocator()->get('config');
		if ($config instanceof Traversable) {
			$config = ArrayUtils::iteratorToArray($config);
		}
		if (!empty($config['module_config']['search_index'])) {
			return $config['module_config']['search_index'];
		} else {
			return FALSE;
		}
	}
	
	public function getFileUploadLocation()
	{
		// Fetch Configuration from Module Config
		$config  = $this->getServiceLocator()->get('config');
		if ($config instanceof Traversable) {
			$config = ArrayUtils::iteratorToArray($config);
		}
		if (!empty($config['module_config']['upload_location'])) {
			return $config['module_config']['upload_location'];
		} else {
			return FALSE;
		}
	}	
	
    public function indexAction()
    {
		$request = $this->getRequest();
		if ($request->isPost()) {
			$queryText = $request->getPost()->get('query');
			$searchIndexLocation = $this->getIndexLocation();
			$index = Lucene\Lucene::open($searchIndexLocation);
			$searchResults = $index->find($queryText);
		}
		
		// prepare search form
		$form  = new \Zend\Form\Form();
		$form->add(array(
			'name' => 'query',
			'attributes' => array(
				'type'  => 'text',
				'id' => 'queryText',
				'required' => 'required'
			),
			'options' => array(
				'label' => 'Search String',
			),
		));
		$form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Search', 
            	'style' => "margin-bottom: 8px; height: 27px;"
            ),
		));
			
		$viewModel  = new ViewModel(array('form' => $form, 'searchResults' => $searchResults));
		return $viewModel;
    }

   
    public function generateIndexAction()
    {
		$searchIndexLocation = $this->getIndexLocation();
    	$index = Lucene\Lucene::create($searchIndexLocation);
    	
    	$userTable = $this->getServiceLocator()->get('UserTable');
    	$uploadTable = $this->getServiceLocator()->get('UploadTable');
    	$allUploads = $uploadTable->fetchAll();  
    	foreach($allUploads as $fileUpload) {
    		//
    		$uploadOwner = $userTable->getUser($fileUpload->user_id);
    		
	   		// id field
    		$fileUploadId= Document\Field::unIndexed('upload_id', $fileUpload->id);
    		// label field
    		$label = Document\Field::Text('label', $fileUpload->label);
    		// owner field    		
    		$owner = Document\Field::Text('owner', $uploadOwner->name);
    		

    		if (substr_compare($fileUpload->filename, ".xlsx", strlen($fileUpload->filename)-strlen(".xlsx"), strlen(".xlsx")) === 0) {
    			// index excel sheet
    			$uploadPath    = $this->getFileUploadLocation();
    			$indexDoc = Lucene\Document\Xlsx::loadXlsxFile($uploadPath ."/" . $fileUpload->filename);
    		} else if (substr_compare($fileUpload->filename, ".docx", strlen($fileUpload->filename)-strlen(".docx"), strlen(".docx")) === 0) {
    			// index word doc
    			$uploadPath    = $this->getFileUploadLocation();
    			$indexDoc = Lucene\Document\Docx::loadDocxFile($uploadPath ."/" . $fileUpload->filename);
    		} else {
	    		$indexDoc = new Lucene\Document();
    		}
    		$indexDoc->addField($label);
    		$indexDoc->addField($owner);
    		$indexDoc->addField($fileUploadId);
    		$index->addDocument($indexDoc);
    	}
    	$index->commit();
    }
    
}