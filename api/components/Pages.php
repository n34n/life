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
        $p['total_count'] = $list->getPagination()->totalCount;
        $p['page_size'] = $list->getPagination()->pageSize;
        $p['page_count'] = $list->getPagination()->pageCount;
        $p['current_page'] = $list->getPagination()->page+1;
        return $p;
    }

}