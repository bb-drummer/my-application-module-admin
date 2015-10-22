<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Admin\Model\Aclresource;

class AclresourceTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select(function (Select $select) {
		     $select->order('resourceslug ASC');
		});
        return $resultSet;
    }

    public function getAclresource($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('aclresources_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getAclresourceBySlug($resource_slug)
    {
        $resource_slug  = trim(strip_tags($resource_slug));
        $rowset = $this->tableGateway->select(array(
        	'resourceslug' => $resource_slug,	
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $resource_slug");
        }
        return $row;
    }

    public function saveAclresource(Aclresource $Aclresource)
    {
        $data = array(
            'resourceslug'			=> $Aclresource->resourceslug,
            'resourcename'			=> $Aclresource->resourcename,
        );

        $id = (int)$Aclresource->aclresources_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAclresource($id)) {
                $this->tableGateway->update($data, array('aclresources_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteAclresource($id)
    {
        $this->tableGateway->delete(array('aclresources_id' => $id));
    }
}

