<?php
namespace Users\Model;

class StoreProduct
{
    public $id;
    public $name;
    public $desc;
    public $cost;

	function exchangeArray($data)
	{
		$this->id		= (isset($data['id'])) ? $data['id'] : null;
		$this->name		= (isset($data['name'])) ? $data['name'] : null;
		$this->desc		= (isset($data['desc'])) ? $data['desc'] : null;		
		$this->cost	= (isset($data['cost'])) ? $data['cost'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
