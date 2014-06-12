<?php

class Aoe_Restrictions_Model_Config_Source_Mode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '',
                'label' => Mage::helper('Aoe_Restrictions')->__('None'),
            ),
            array(
                'value' => Aoe_Restrictions_Helper_Data::MODE_WHITELIST,
                'label' => Mage::helper('Aoe_Restrictions')->__('Whitelist'),
            ),
            array(
                'value' => Aoe_Restrictions_Helper_Data::MODE_BLACKLIST,
                'label' => Mage::helper('Aoe_Restrictions')->__('Blacklist'),
            ),
        );
    }
}
