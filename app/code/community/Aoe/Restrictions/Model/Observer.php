<?php

class Aoe_Restrictions_Model_Observer
{
    const SKIP_PARAMETER_NAME = '__skip_restriction_check__';

    public function checkRequest(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Controller_Varien_Action $controller */
        $controller = $observer->getControllerAction();
        if (!$controller instanceof Mage_Core_Controller_Varien_Action) {
            return;
        }

        // Check if this request was flagged to skip checking already
        if ($controller->getRequest()->getUserParam(self::SKIP_PARAMETER_NAME)) {
            return;
        }

        $this->checkHttpAuth();

        // Default blocking action
        $block = false;

        // Check if request is blocked by route name
        $block = $this->check(
            Mage::getStoreConfig('web/restriction/routes_mode'),
            $controller->getRequest()->getRouteName(),
            Mage::getStoreConfig('web/restriction/routes'),
            $block
        );

        // Check if request is blocked by full action name
        $block = $this->check(
            Mage::getStoreConfig('web/restriction/actions_mode'),
            $controller->getFullActionName(),
            Mage::getStoreConfig('web/restriction/actions'),
            $block
        );

        // Check if this request was blocked
        if ($block) {
            // Return a 404 page when blocking
            $controller->getRequest()
                ->initForward()
                ->setModuleName('cms')
                ->setControllerName('index')
                ->setActionName('noroute')
                ->setDispatched(false)
                ->setParam(self::SKIP_PARAMETER_NAME, true);
        }
    }

    protected function checkHttpAuth()
    {
        if (!Mage::getStoreConfig('web/restriction/http_auth_enable')) {
            return true;
        }

        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])
            || $_SERVER['PHP_AUTH_USER'] !== Mage::getStoreConfig('web/restriction/http_auth_user')
            || $_SERVER['PHP_AUTH_PW'] !== Mage::getStoreConfig('web/restriction/http_auth_pass')
            ) {
            header('WWW-Authenticate: Basic realm="Magento"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Access Denied';
            exit;
        }
    }

    protected function check($mode, $name, $names, $defaultBlock = false)
    {
        $block = $defaultBlock;

        $activeModes = array(
            Aoe_Restrictions_Helper_Data::MODE_WHITELIST,
            Aoe_Restrictions_Helper_Data::MODE_BLACKLIST,
        );

        if (in_array($mode, $activeModes)) {
            if (!is_array($names)) {
                // Extract the names array
                $names = explode(',', str_replace(array("\n", " "), ",", $names));
            }

            // Filter and clean the names array
            $names = array_filter(array_map('strtolower', array_map('trim', $names)));

            // Clean the current name
            $name = strtolower(trim($name));

            switch ($mode) {
                case Aoe_Restrictions_Helper_Data::MODE_WHITELIST:
                    $block = !in_array($name, $names);
                    break;
                case Aoe_Restrictions_Helper_Data::MODE_BLACKLIST:
                    $block = in_array($name, $names);
                    break;
            }
        }

        return $block;
    }
}
