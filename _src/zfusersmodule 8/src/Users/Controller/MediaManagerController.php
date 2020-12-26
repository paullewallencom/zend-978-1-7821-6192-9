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

use ZendGData\ClientLogin;
use ZendGData\Photos;

class MediaManagerController extends AbstractActionController
{
	protected $storage;
	protected $authservice;
	
	protected $photos;
	
	const GOOGLE_USER_ID = 'zf2.book@gmail.com';
	const GOOGLE_PASSWORD = 'pa$$w0rd';

	
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
    	if ($config instanceof Traversable) {
    		$config = ArrayUtils::iteratorToArray($config);
    	}
    	if (!empty($config['module_config']['image_upload_location'])) {
    		return $config['module_config']['image_upload_location'];
    	} else {
    		return FALSE;
    	}
	}
	
    public function indexAction()
    {
    	$uploadTable = $this->getServiceLocator()->get('ImageUploadTable');
    	$userTable = $this->getServiceLocator()->get('UserTable');
    	$userEmail = $this->getAuthService()->getStorage()->read();
    	$user = $userTable->getUserByEmail($userEmail);
    	$googleAlbums = $this->getGooglePhotos();
    	$googleVideos = $this->getYoutubeVideos();
    	
    	$viewModel  = new ViewModel(
    			array(
    				'myUploads' => $uploadTable->getUploadsByUserId($user->id),
    				'googleAlbums' => $googleAlbums,     				
    				'youtubeVideos' => $googleVideos,
    				)
    		);
    	
    	return $viewModel;
    }

    public function processUploadAction()
    {
    	$userTable = $this->getServiceLocator()->get('UserTable');   	
    	$user_email = $this->getAuthService()->getStorage()->read();    	 
    	$user = $userTable->getUserByEmail($user_email);
		$form = $this->getServiceLocator()->get('ImageUploadForm');
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    	
    		$upload = new ImageUpload();
    		$uploadFile    = $this->params()->fromFiles('imageupload');
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
    				$exchange_data['thumbnail'] = $this->generateThumbnail($uploadFile['name']);
    				$exchange_data['user_id'] = $user->id;
    				
    				$upload->exchangeArray($exchange_data);
    				
    				$uploadTable = $this->getServiceLocator()->get('ImageUploadTable');
    				$uploadTable->saveUpload($upload);
    				
    				return $this->redirect()->toRoute('users/media');    				    				
    			}
    		}
    	}
    	 
    	return array('form' => $form);
    }
    
    public function generateThumbnail($imageFileName) 
    {
    	$path = $this->getFileUploadLocation();
    	$sourceImageFileName = $path . '/' . $imageFileName;
    	$thumbnailFileName = 'tn_' . $imageFileName;
    	
    	$imageThumb    = $this->getServiceLocator()->get('WebinoImageThumb');
    	$thumb          = $imageThumb->create($sourceImageFileName,$options = array());
    	$thumb->resize(75, 75);
    	$thumb->save($path . '/' . $thumbnailFileName);
    	
    	return $thumbnailFileName;
    }
    
    public function uploadAction()
    {
		$form = $this->getServiceLocator()->get('ImageUploadForm');
		$viewModel  = new ViewModel(array('form' => $form)); 
		return $viewModel; 
    }
    
    public function deleteAction()
    {
    	$uploadId = $this->params()->fromRoute('id');
    	$uploadTable = $this->getServiceLocator()
    					->get('ImageUploadTable');
    	$upload = $uploadTable->getUpload($uploadId);
    	$uploadPath    = $this->getFileUploadLocation();
    	// Remove File
   		unlink($uploadPath ."/" . $upload->filename);    	
   		unlink($uploadPath ."/" . $upload->thumbnail);
   		 
    	// Delete Records
    	$uploadTable->deleteUpload($uploadId);
    	
    	return $this->redirect()->toRoute('users/media');
    
    }
    
    public function viewAction()
    {
    	$uploadId = $this->params()->fromRoute('id');
    	$uploadTable = $this->getServiceLocator()
    	->get('ImageUploadTable');
    	$upload = $uploadTable->getUpload($uploadId);
    	
		$viewModel  = new ViewModel(array('upload' => $upload)); 
		return $viewModel; 
    
    }
    
    public function showImageAction()
    {  
    	$uploadId = $this->params()->fromRoute('id');
	    $uploadTable = $this->getServiceLocator()->get('ImageUploadTable');
	    $upload = $uploadTable->getUpload($uploadId);
	     
    	// Fetch Configuration from Module Config
    	$uploadPath    = $this->getFileUploadLocation();
    	if ($this->params()->fromRoute('subaction') == 'thumb') {
    		$filename = $uploadPath ."/" . $upload->thumbnail;
    	} else {
    		$filename = $uploadPath ."/" . $upload->filename;
    		
    	}
    	$file = file_get_contents($filename);
    	 
		// Directly return the Response 
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders(array(
			'Content-Type' => 'application/octet-stream',
			'Content-Disposition' => 'attachment;filename="' .$upload->filename . '"',

		));
		$response->setContent($file);
		
		return $response;	    
    }

    
    public function getGooglePhotos() {
    	
    	$adapter = new \Zend\Http\Client\Adapter\Curl();
    	$adapter->setOptions(array(
    		'curloptions' => array(
    			CURLOPT_SSL_VERIFYPEER => false,
    		)
    	));
    		
    	   
    	$httpClient = new \ZendGData\HttpClient();
    	$httpClient->setAdapter($adapter);

    	$client = \ZendGData\ClientLogin::getHttpClient(
    					self::GOOGLE_USER_ID, 
    					self::GOOGLE_PASSWORD, 
    					\ZendGData\Photos::AUTH_SERVICE_NAME, 
    					$httpClient);
    	
    	$gp = new \ZendGData\Photos($client);
		
    	$gAlbums = array();
    	
    	try {
    		$userFeed = $gp->getUserFeed( self::GOOGLE_USER_ID );
    		foreach ($userFeed as $userEntry) {
    			
    			$albumId = $userEntry->getGphotoId()->getText();
    			$gAlbums[$albumId]['label'] = $userEntry->getTitle()->getText();
    			 
    			$query = $gp->newAlbumQuery();
    			$query->setUser( self::GOOGLE_USER_ID );
    			$query->setAlbumId( $albumId  );
    			    
    			$albumFeed = $gp->getAlbumFeed($query);
    			
    			foreach ($albumFeed as $photoEntry) {
    				
    				$photoId = $photoEntry->getGphotoId()->getText();
    				if ($photoEntry->getMediaGroup()->getContent() != null) {
    					$mediaContentArray = $photoEntry->getMediaGroup()->getContent();
    					$photoUrl = $mediaContentArray[0]->getUrl();
    				}
    				
    				if ($photoEntry->getMediaGroup()->getThumbnail() != null) {
    					$mediaThumbnailArray = $photoEntry->getMediaGroup()->getThumbnail();
    					$thumbUrl = $mediaThumbnailArray[0]->getUrl();
    				}
    				
    				$albumPhoto = array();
    				$albumPhoto['id'] = $photoId;
    				$albumPhoto['photoUrl'] = $photoUrl;
    				$albumPhoto['thumbUrl'] = $thumbUrl;
    				
    				$gAlbums[$albumId]['photos'][] =$albumPhoto;
    				 
    			}
    		}
    	} catch (App\HttpException $e) {
    		echo "Error: " . $e->getMessage() . "<br />\n";
    		if ($e->getResponse() != null) {
    			echo "Body: <br />\n" . $e->getResponse()->getBody() .
    			"<br />\n";
    		}
    		// In new versions of Zend Framework, you also have the option
    		// to print out the request that was made.  As the request
    		// includes Auth credentials, it's not advised to print out
    		// this data unless doing debugging
    		// echo "Request: <br />\n" . $e->getRequest() . "<br />\n";
    	} catch (App\Exception $e) {
    		echo "Error: " . $e->getMessage() . "<br />\n";
    	}
    	
    	return $gAlbums;
    }
    
    public function getYoutubeVideos() 
    {
    	$adapter = new \Zend\Http\Client\Adapter\Curl();
    	$adapter->setOptions(array(
    		'curloptions' => array(
    			CURLOPT_SSL_VERIFYPEER => false,
    		)
    	));
    	
    	
    	$httpClient = new \ZendGData\HttpClient();
    	$httpClient->setAdapter($adapter);
    	
    	$client = \ZendGData\ClientLogin::getHttpClient(
    		self::GOOGLE_USER_ID,
    		self::GOOGLE_PASSWORD,
    		\ZendGData\YouTube::AUTH_SERVICE_NAME,
    		$httpClient);
    	
    	$yt = new \ZendGData\YouTube($client);
    	$yt->setMajorProtocolVersion(2);
    	$query = $yt->newVideoQuery();
    	$query->setOrderBy('relevance');
    	$query->setSafeSearch('none');
    	$query->setVideoQuery('Zend Framework');
    	
    	// Note that we need to pass the version number to the query URL function
    	// to ensure backward compatibility with version 1 of the API.
    	$videoFeed = $yt->getVideoFeed($query->getQueryUrl(2));
    	
    	$yVideos = array();
    	foreach ($videoFeed as $videoEntry) {
    		 $yVideo = array();
    		 $yVideo['videoTitle'] = $videoEntry->getVideoTitle();
    		 $yVideo['videoDescription'] = $videoEntry->getVideoDescription();
    		 $yVideo['watchPage'] = $videoEntry->getVideoWatchPageUrl();
    		 $yVideo['duration'] = $videoEntry->getVideoDuration();
    		 $videoThumbnails = $videoEntry->getVideoThumbnails();
    		  
    		 $yVideo['thumbnailUrl'] = $videoThumbnails[0]['url'];
    		 $yVideos[] = $yVideo;
    	}
    	return $yVideos;
    	 
    }
}    

