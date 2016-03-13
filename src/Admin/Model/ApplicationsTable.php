<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <development@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <development@bjoernbartels.earth>
 */

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\Applications;
use Zend\Db\Sql\Select;

/**
 * class Admin\Model\ApplicationsTable
 * 
 * @author  bba
 * @package ApplicationsTable
 */
class ApplicationsTable
{
    protected $tableGateway;

    /**
     * 
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * 
     * @param string $scope
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll($scope = '')
    {
        $resultSet = $this->tableGateway->select(
            function (Select $select) use ($scope) {
                if (!empty($scope)) {
                    $select->where('scope = \''.$scope.'\'')->order('type, name ASC');
                } else {
                    $select->order('name ASC');
                }
            }
        );
        return $resultSet;
    }

    /**
     * 
     * @param string $scope
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function fetchAllFull($scope = '')
    {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->join(
            'clients',
            'applications.client_id = clients.clients_id',
            array(
            'clientname' => 'name',
            ),
            Select::JOIN_LEFT
        );
        $statement = $this->tableGateway->getSql()->prepareStatementForSqlObject($sqlSelect);
        $resultSet = $statement->execute();
        
        return $resultSet;
    }

    public function getApplication($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('application_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveApplication(Applications $applications)
    {
        $data = array(
        'name'        => $applications->name,
        'shortname'    => $applications->shortname,
        'path'        => $applications->path,
        'url'        => $applications->url,
        'email'        => $applications->email,
        'client_id'    => $applications->client_id,
        );

        $id = (int)$applications->application_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getApplications($id)) {
                $this->tableGateway->update($data, array('application_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteApplication($id)
    {
        $this->tableGateway->delete(array('application_id' => $id));
    }
}