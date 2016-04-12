<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels <coding@bjoernbartels.earth>
 * @link      https://gitlab.bjoernbartels.earth/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\Clients;
use Zend\Db\Sql\Select;

class ClientsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

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

    public function fetchApplication()
    {
        return $this->fetchAll('application');
    }

    public function fetchUser( $id )
    {
        if (!$id) { return array(); 
        }
        $resultSet = $this->tableGateway->select(
            function (Select $select) use ($id) {
                if (!empty($id)) {
                    $select->where(array( '(scope = \'user\') AND (ref_id = \''.((int)$id).'\')' ))->order('type, name ASC');
                } else {
                    $select->order('type, name ASC');
                }
            }
        );
        return $resultSet;
    }

    public function getClients($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('clients_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveClients(Clients $clients)
    {
        $data = array(
        'name'            => $clients->name,
        'extraname'        => $clients->extraname,
        'homepage'        => $clients->homepage,
        'email'            => $clients->email,
        'contact'        => $clients->contact,
        'phone'            => $clients->phone,
        'statistics'    => $clients->statistics,
        );

        $id = (int)$clients->clients_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getClients($id)) {
                $this->tableGateway->update($data, array('clients_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteClients($id)
    {
        $this->tableGateway->delete(array('clients_id' => $id));
    }
}