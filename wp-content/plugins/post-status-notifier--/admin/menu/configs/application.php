<?php
$applicationRoot = $this->_pm->getPathinfo()->getRootAdminMenu();
return array(
    'appnamespace' => $this->_pm->getAbbr(),
//    'phpSettings' => array(
//        'display_startup_errors' => 0,
//        'display_errors' => 0,
//    ),
    'bootstrap' => array(
        'path' => $applicationRoot . 'Bootstrap.php',
        'class' => $this->_pm->getAbbr() . '_Admin_Menu_Bootstrap'
    ),
    'includepaths' => array(
        $this->_pm->getPathinfo()->getRootLib(),
    ),
    'resources' => array(
        'FrontController' => array(
            'controllerDirectory' => $applicationRoot . 'controllers',
            'pluginDirectory' => $applicationRoot . 'plugins',
            'params' => array(
                'displayExceptions' => 0
            ),
        ),
        'layout' => array(
            'layoutPath' => $applicationRoot . 'layouts/scripts/'
        )
    ),
    'pluginmanager' => $this->_pm
);