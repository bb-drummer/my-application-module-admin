<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\User;
use Zend\Db\Sql\Select;

class UserProfileTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->setTableGateway($tableGateway);
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getUserProfile($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('user_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveUserProfile(UserProfile $user)
    {
    	
        $data = array(
            'user_id'		=> $user->user_id,
        		
            'street'		=> $user->street,
            'city'			=> $user->city,
            'country'		=> $user->country,
            'phone'			=> $user->phone,
            'cell'			=> $user->cell,
        		
            'twitter'		=> $user->twitter,
            'facebook'		=> $user->facebook,
            'skype'			=> $user->skype,
            'icq'			=> $user->icq,
        );

        $id = (int)$user->user_id;
        if ($id == 0) {
        	throw new \Exception('invalid user');
        } else {
        	try {
        		if ($this->getUserProfile($id)) {
	                $this->tableGateway->update($data, array('user_id' => $id));
	            }
        	} catch (Exception $e) {
	            $this->tableGateway->insert($data);
        	}
        }
        return $this;
    }

    public function deleteUserProfile($id)
    {
        $this->tableGateway->delete(array('user_id' => $id));
    }
    
    public function getTableGateway()
    {
    	return $this->tableGateway;
    }
    
    public function setTableGateway(TableGateway $tableGateway)
    {
    	$this->tableGateway = $tableGateway;
    }
    
    
}