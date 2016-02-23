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
use Admin\Model\Applications;
use Zend\Db\Sql\Select;

class ApplicationsTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll($scope = '')
	{
		$resultSet = $this->tableGateway->select(function (Select $select) use ($scope) {
			if (!empty($scope)) {
				$select->where('scope = \''.$scope.'\'')->order('type, name ASC');
			} else {
				$select->order('name ASC');
			}
		});
		return $resultSet;
	}

	public function fetchAllFull($scope = '')
	{
		$resultSet = $this->tableGateway->selectWith(function (Select $select) use ($scope) {
			$select->join(
				'clients', 
				'applications.client_id = clients.clients_id', 
				array(
					'name' => 'clientname',
					'extraname' => 'clientextraname',
				), 
				Select::JOIN_LEFT
			);
			if (!empty($scope)) {
				$select->where('scope = \''.$scope.'\'')->order('type, name ASC');
			} else {
				$select->order('name ASC');
			}
		});
		return $resultSet;
	}

	public function fetchApplication()
	{
		return $this->fetchAll('application');
	}

	public function fetchUser( $id )
	{
		if (!$id) { return array(); }
		$resultSet = $this->tableGateway->select(function (Select $select) use ($id) {
			if (!empty($id)) {
				$select->where(array( '(scope = \'user\') AND (ref_id = \''.((int)$id).'\')' ))->order('type, name ASC');
			} else {
				$select->order('type, name ASC');
			}
		});
		return $resultSet;
	}

	public function getApplications($id)
	{
		$id  = (int) $id;
		$rowset = $this->tableGateway->select(array('applications_id' => $id));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveApplications(Applications $applications)
	{
		$data = array(
			'name'			=> $applications->name,
			'extraname'		=> $applications->extraname,
			'homepage'		=> $applications->homepage,
			'email'			=> $applications->email,
			'contact'		=> $applications->contact,
			'phone'			=> $applications->phone,
			'statistics'	=> $applications->statistics,
		);

		$id = (int)$applications->applications_id;
		if ($id == 0) {
			$this->tableGateway->insert($data);
		} else {
			if ($this->getApplications($id)) {
				$this->tableGateway->update($data, array('applications_id' => $id));
			} else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function deleteApplications($id)
	{
		$this->tableGateway->delete(array('applications_id' => $id));
	}
}