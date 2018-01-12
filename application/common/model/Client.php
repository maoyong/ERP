<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Client extends Model
{
	public $field = true;
    //自定义初始化
    protected static function init()
    {
        //TODO:自定义的初始化
    }

 	public function find($id){
 		$info = $this::get($id);
 		if (empty($info)) {
 			return [];
 		}

 		$info['bank'] = DB::name('bank')->where(['band_id'=>$id, 'bank_type'=>0])->find();
		$info['link'] = DB::name('link')->where(['link_id'=>$id, 'link_type'=>0])->select();
 		return $info;
 	}
}
