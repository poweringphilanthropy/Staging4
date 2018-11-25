<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: DetailsLog.php 394 2015-06-21 21:40:04Z timoreithde $
 * @package
 */
require_once 'DetailsAbstract.php';

class Psn_Module_DeferredSending_ListTable_Ajax_DetailsLog extends Psn_Module_DeferredSending_ListTable_Ajax_DetailsAbstract
{
    public $action = 'load-psn-def-detaillog';

    protected $_modelName = 'Psn_Module_DeferredSending_Model_MailQueueLog';
}