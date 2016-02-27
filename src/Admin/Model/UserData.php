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

use \Admin\Model\User;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\InputFilter\InputFilterInterface;

class UserData extends User
{

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFilterFactory();
    
            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'user_id',
                    'required' => true,
                    'filters'  => array(
                    array('name' => 'Int'),
                    ),
                    )
                )
            );
    
            $inputFilter->add(
                $factory->createInput(
                    array(
                    'name'     => 'display_name',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                    array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 6,
                                            'max'      => 255,
                                    ),
                    ),
                    ),
                    )
                )
            );
    
            $this->inputFilter = $inputFilter;
        }
            
        return $this->inputFilter;
    
    }

}