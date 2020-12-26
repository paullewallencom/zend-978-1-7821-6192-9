<?php
namespace Users\Controller;

use Users\Form\MultiImageUploadForm;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Element;
use Zend\Form\Form;

class Html5TestController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        return $view;
    }
    
    public function formAction()
    {
    	// prepare form
		$form  = new Form();
		
		$dateTime = new Element\DateTime('element-date-time');
		$dateTime
		->setLabel('Date/Time Element')
		->setAttributes(array(
			'min'  => '2000-01-01T00:00:00Z',
			'max'  => '2020-01-01T00:00:00Z',
			'step' => '1', 
		));
		$form->add($dateTime);

		$dateTime = new Element\DateTimeLocal('element-date-time-local');
		$dateTime
		->setLabel('Date/Time Local Element')
		->setAttributes(array(
			'min'  => '2000-01-01T00:00:00Z',
			'max'  => '2020-01-01T00:00:00Z',
			'step' => '1',
		));
		$form->add($dateTime);
		
		$time = new Element\Time('element-time');
		$time->setLabel('Time Element');
		$form->add($time);
		
		$date = new Element\Date('element-date');
		$date
		->setLabel('Date Element')
		->setAttributes(array(
			'min'  => '2000-01-01',
			'max'  => '2020-01-01',
			'step' => '1',
		));
		$form->add($date);
		
		$week = new Element\Week('element-week');
		$week->setLabel('Week Element');
		$form->add($week);

		$month = new Element\Month('element-month');
		$month->setLabel('Month Element');
		$form->add($month);
		
		$email = new Element\Email('element-email');
		$email->setLabel('Email Element');
		$form->add($email);
		
		$url = new Element\Url('element-url');
		$url->setLabel('URL Element');
		$form->add($url);	
		
		$number = new Element\Number('element-number');
		$number->setLabel('Number Element');
		$form->add($number);		

		$range = new Element\Range('element-range');
		$range->setLabel('Range Element');
		$form->add($range);
		
		$color = new Element\Color('element-color');
		$color->setLabel('Color Element');
		$form->add($color);
		
		$submit = new Element\Submit('element-submit');
		$submit->setValue('Submit');
		$form->add($submit);
						
    	$viewModel  = new ViewModel(array('form' => $form));
    	return $viewModel;
    }
    
    public function multiUploadAction()
    {
    	// prepare form
    	$form = $this->getServiceLocator()->get('MultiImageUploadForm');
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$post = array_merge_recursive(
    			$request->getPost()->toArray(),
    			$request->getFiles()->toArray()
    		);
    		
    		$form->setData($post);
    		if ($form->isValid()) {
    			$data = $form->getData();
    			// Form is valid, save the form!
    			return $this->redirect()->toRoute('users/html5-test', array('action' => 'processMultiUpload'));
    		}
    	}
    	$viewModel  = new ViewModel(array('form' => $form));
    	return $viewModel;
    }
    
    public function processMultiUploadAction()
    {
    	$viewModel  = new ViewModel();
    	return $viewModel;
    }
}
