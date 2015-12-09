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
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUserProfile($id)) {
                $this->tableGateway->update($data, array('user_id' => $id));
            } else {
                throw new \Exception('User id does not exist');
            }
        }
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