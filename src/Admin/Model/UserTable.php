<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package		[MyApplication]
 * @package		BB's Zend Framework 2 Components
 * @package		AdminModule
 * @author		Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link		http://gitlab.dragon-projects.de:81/groups/zf2
 * @license		http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright	copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\User;
use Zend\Db\Sql\Select;

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

	public function getUserByEmailOrUsername ( $id )
	{
		if ( empty($id) ) {
			return false;
		}
		$resultSet = $this->tableGateway->select(function (Select $select) use ($id) {
			$select->where(
				array(
					"username = '".$id."'",
					"email = '".$id."'"
				),
				'OR'
			);
		});
		$user = $resultSet->current();
		if (!$user) {
			throw new \Exception("Could not find user with email or username '$id'");
		}
		return $user;
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