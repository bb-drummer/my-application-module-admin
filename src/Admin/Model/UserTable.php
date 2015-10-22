<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\User;

class UserTable
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

    public function getUser($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('user_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveUser(User $user)
    {
        $data = array(
            'display_name'	=> $user->display_name,
            'username'		=> $user->username,
            'email'			=> $user->email,
            'street'		=> $user->street,
            'city'			=> $user->city,
            'phone'			=> $user->phone,
            'password'		=> $user->password,
            'state'			=> $user->state,
            'aclrole'		=> $user->aclrole,
        );

        $id = (int)$user->user_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, array('user_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteUser($id)
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