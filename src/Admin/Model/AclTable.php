<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Admin\Model\Acl;
use Zend\Db\Sql\Select;

class AclTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getAcl($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('acl_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getAclByRoleResource($role_id, $resource_id)
    {
        $role_id		= (int) $role_id;
        $resource_id	= (int) $resource_id;
        $rowset = $this->tableGateway->select(array(
        	'aclroles_id' => $role_id, 
        	'aclresources_id' => $resource_id,	
        ));
        $row = $rowset->current();
        if (!$row) {
        	return false;
        }
        return $row;
    }

    public function saveAcl(Acl $Acl)
    {
        $data = array(
            'aclroles_id'			=> $Acl->aclroles_id,
            'aclresources_id'		=> $Acl->aclresources_id,
            'state'					=> $Acl->state,
        );

        $id = (int)$Acl->acl_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAcl($id)) {
                $this->tableGateway->update($data, array('acl_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteAcl($id)
    {
        $this->tableGateway->delete(array('acl_id' => $id));
    }
}

