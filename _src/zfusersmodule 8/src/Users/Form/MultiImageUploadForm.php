<?php
// filename : module/Users/src/Users/Form/MultiImageUploadForm.php
namespace Users\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter;

class MultiImageUploadForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }
    
    public function addElements() 
    {
    	$imageupload = new Element\File('imageupload');
    	$imageupload->setLabel('Image Upload')
    		->setAttribute('id', 'imageupload')
    		->setAttribute('multiple', true); 	//Enables multiple file uploads
    	$this->add($imageupload);
    	
    	$submit = new Element\Submit('submit');
    	$submit->setValue('Upload Now');
    	$this->add($submit);
    }
    
    public function addInputFilter()
    {
    	$inputFilter = new InputFilter\InputFilter();
    	// File Input
    	$fileInput = new InputFilter\FileInput('imageupload');
    	$fileInput->setRequired(true);
    	$fileInput->getFilterChain()->attachByName(
    		'filerenameupload',
    		array(
    			'target' => './data/images/temp.jpg',
    			'randomize' => true
    		)
    	);
    	$inputFilter->add($fileInput);
    	$this->setInputFilter($inputFilter);
    }
}
