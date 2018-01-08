<?php
namespace app\admin\model;
use think\Model;
class Purchase extends Model
{
	public function createOrderNo(){
		$id = $this::max('id');
		$id  = ++$id;
		return 'CGD' . date('Ym') . sprintf('%08d', $id);
	}
}
