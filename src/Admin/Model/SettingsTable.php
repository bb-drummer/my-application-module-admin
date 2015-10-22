<?php
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

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select(function (Select $select) {
		     $select->order('type, name ASC');
		});
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
            'type'	=> $settings->type,
            'name'		=> $settings->name,
            'value'			=> $settings->value,
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