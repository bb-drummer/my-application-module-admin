<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link      http://gitlab.dragon-projects.de:81/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\Aclrole;
use Zend\Db\Sql\Select;

class AclroleTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select(
            function (Select $select) {
                $select->order('roleslug ASC');
            }
        );
        return $resultSet;
    }

    public function getAclrole($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('aclroles_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getAclroleBySlug($role_slug)
    {
        $role_slug  = trim(strip_tags($role_slug));
        $rowset = $this->tableGateway->select(
            array(
            'roleslug' => $role_slug,    
            )
        );
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $role_slug");
        }
        return $row;
    }

    public function saveAclrole(Aclrole $Aclrole)
    {
        $data = array(
        'roleslug'            => $Aclrole->roleslug,
        'rolename'            => $Aclrole->rolename,
        );

        $id = (int)$Aclrole->aclroles_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAclrole($id)) {
                $this->tableGateway->update($data, array('aclroles_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteAclrole($id)
    {
        $this->tableGateway->delete(array('aclroles_id' => $id));
    }
}

