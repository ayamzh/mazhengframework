<?php
namespace ctrl\test;


use common\ServiceLocator;
use framework\view\JSONView;

/**
 * 成就相关控制逻辑
 *
 * @package       CampaignCtrl
 * @subpackage    CtrlBase
 */
class IndexCtrl
{


    public function index()
    {
        $data = ServiceLocator::getTestService()->test();
        return new JsonView((array)$data);
    }
}