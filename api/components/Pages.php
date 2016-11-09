<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 2016/11/9
 * Time: 下午1:56
 */

namespace api\components;

use yii;
use yii\data\Pagination;

class Pages extends Pagination
{
    //public $totalCount;

    public function run($totalCount)
    {
        $pageSize = Yii::$app->params['pageSize'];
        $pages    = new Pagination(['totalCount' => $totalCount,'pageSize' => $pageSize]);

        $p['info']['current'] = $pages->page+1;
        $p['info']['pageCount'] = $pages->pageCount;
        $p['info']['pageSize'] = $pageSize;
        $p['info']['totalCount'] = (int)$totalCount;
        $p['offset'] = $pages->offset;
        $p['limit']  = $pages->limit;

        return $p;
    }

}