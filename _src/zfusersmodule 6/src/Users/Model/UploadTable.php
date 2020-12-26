<?php
namespace Users\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class UploadTable
{
    protected $tableGateway;
    protected $uploadSharingTableGateway;

    public function __construct(TableGateway $tableGateway, TableGateway $uploadSharingTableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->uploadSharingTableGateway = $uploadSharingTableGateway;
    }

    public function saveUpload(Upload $upload)
    {
        $data = array(
            'filename' => $upload->filename,
            'label'  => $upload->label,
            'user_id'  => $upload->user_id,
        );

        $id = (int)$upload->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUpload($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Upload ID does not exist');
            }
        }
    }
    
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    public function getUpload($uploadId)
    {
    	$uploadId  = (int) $uploadId;
    	$rowset = $this->tableGateway->select(array('id' => $uploadId));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $uploadId");
    	}
    	return $row;
    }
    
    public function deleteUpload($uploadId)
    {
    	$this->tableGateway->delete(array('id' => $uploadId));
    }
	
    /*
     * Uploads for the user
     */
    public function getUploadsByUserId($userId)
    {
    	$userId  = (int) $userId;
    	$rowset = $this->tableGateway->select(array('user_id' => $userId));
    	return $rowset;
    }
    
    /*
     * Uploads shared with the user
    */    
    public function getSharedUploadsForUserId($userId)
    {
    	$userId  = (int) $userId;
    	
    	$rowset = $this->uploadSharingTableGateway->select(function (Select $select) use ($userId){
    		$select->columns(array()) // no columns from main table
    			->where(array('uploads_sharing.user_id'=>$userId))
    			->join('uploads', 'uploads_sharing.upload_id = uploads.id');
    	});
    		    	
		return $rowset;
    }
    
    /*
     * Uploads shared with the user
    */
    public function getSharedUsers($uploadId)
    {
    	$uploadId  = (int) $uploadId;
    	 
    	$rowset = $this->uploadSharingTableGateway->select(array('upload_id' => $uploadId));
    	
		return $rowset;
    }    
    
    /*
     * Uploads shared with the user
    */
    public function addSharing($uploadId, $userId)
    {
    	$data = array(
    		'upload_id' => (int)$uploadId,
    		'user_id'  => (int)$userId,
    	);
    	$this->uploadSharingTableGateway->delete($data);
   		$this->uploadSharingTableGateway->insert($data);
    }
    
    /*
     * Uploads shared with the user
    */
    public function removeSharing($uploadId, $userId)
    {
    	$data = array(
    		'upload_id' => (int)$uploadId,
    		'user_id'  => (int)$userId,
    	);
    	
   		$this->uploadSharingTableGateway->insert($data);
    }
}
