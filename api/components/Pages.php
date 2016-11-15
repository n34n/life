<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 2016/11/9
 * Time: 下午1:56
 */

namespace api\components;

class Pages
{
    //public $totalCount;

    public static function Pages($list)
    {
        $p['total-count'] = $list->getPagination()->totalCount;
        $p['page-size'] = $list->getPagination()->pageSize;
        $p['page-count'] = $list->getPagination()->pageCount;
        $p['current-page'] = $list->getPagination()->page+1;
        return $p;
    }

}