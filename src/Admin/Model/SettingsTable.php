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
use Admin\Model\Settings;
use Zend\Db\Sql\Select;

class SettingsTable
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
                    $select->order('type, name ASC');
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

    public function getSettings($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('settings_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveSettings(Settings $settings)
    {
        $data = array(
        'scope'        => $settings->scope,
        'ref_id'    => $settings->ref_id,
        'type'        => $settings->type,
        'name'        => $settings->name,
        'value'        => $settings->value,
        );

        $id = (int)$settings->settings_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getSettings($id)) {
                $this->tableGateway->update($data, array('settings_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteSettings($id)
    {
        $this->tableGateway->delete(array('settings_id' => $id));
    }
}