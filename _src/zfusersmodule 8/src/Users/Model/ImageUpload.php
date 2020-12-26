<?php
namespace Users\Model;

class ImageUpload
{
    public $id;
    public $filename;
    public $thumbnail;
    public $label;
    public $user_id;

	function exchangeArray($data)
	{
		$this->id		= (isset($data['id'])) ? $data['id'] : null;
		$this->filename		= (isset($data['filename'])) ? $data['filename'] : null;
		$this->thumbnail		= (isset($data['thumbnail'])) ? $data['thumbnail'] : null;		
		$this->label	= (isset($data['label'])) ? $data['label'] : null;
		$this->user_id	= (isset($data['user_id'])) ? $data['user_id'] : null;	
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
