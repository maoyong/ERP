<?php

namespace app\common\model;

use think\Model;

class WebLog extends Model
{
    protected $name = 'web_log_all';

    public function user()
    {
        return $this->hasOne('User', "id", "uid")->setAlias(["id" => "uuid"]);
    }

    public function map()
    {
        return $this->hasOne('NodeMap', "map", "map")->setAlias(["id" => "map_id"]);
    }
}