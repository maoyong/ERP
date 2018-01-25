<?php

//------------------------
// 公共函数
//-------------------------

use think\Session;
use think\Response;
use think\Request;
use think\Url;

/**
 * CURLFILE 兼容性处理 php < 5.5
 * 一定不要修改、删除，否则 curl 可能无法上传文件
 */
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
        . ($postname ?: basename($filename))
        . ($mimetype ? ";type=$mimetype" : '');
    }
}

/**
 * 模拟tab产生空格
 * @param int $step
 * @param string $string
 * @param int $size
 * @return string
 */
function tab($step = 1, $string = ' ', $size = 4)
{
    return str_repeat($string, $size * $step);
}

/**
 * flash message
 *
 * flash("?KEY") 判断是否存在flash message KEY 返回bool值
 * flash("KEY") 获取flash message，存在返回具体值，不存在返回null
 * flash("KEY","VALUE") 设置flash message
 * @param string $key
 * @param bool|string $value
 * @return bool|mixed|null
 */
function flash($key, $value = false)
{
    $prefix = 'flash_';
    // 判断是否存在flash message
    if ('?' == substr($key, 0, 1)) {
        return Session::has($prefix . substr($key, 1));
    } else {
        $flash_key = $prefix . $key;
        if (false === $value) {
            // 获取flash
            $ret = Session::pull($flash_key);

            return null === $ret ? null : unserialize($ret);
        } else {
            // 设置flash
            return Session::set($flash_key, serialize($value));
        }
    }
}

/**
 * 表格排序筛选
 * @param string $name  单元格名称
 * @param string $field 排序字段
 * @return string
 */
function sort_by($name, $field = '')
{
    $sort = Request::instance()->param('_sort');
    $param = Request::instance()->get();
    $param['_sort'] = ($sort == 'asc' ? 'desc' : 'asc');
    $param['_order'] = $field;
    $url = Url::build(Request::instance()->action(), $param);

    return Request::instance()->param('_order') == $field ?
        "<a href='{$url}' title='点击排序' class='sorting-box sorting-{$sort}'>{$name}</a>" :
        "<a href='{$url}' title='点击排序' class='sorting-box sorting'>{$name}</a>";
}

/**
 * 用于高亮搜索关键词
 * @param string $string 原文本
 * @param string $needle 关键词
 * @param string $class  span标签class名
 * @return mixed
 */
function high_light($string, $needle = '', $class = 'c-red')
{
    return $needle !== '' ? str_replace($needle, "<span class='{$class}'>" . $needle . "</span>", $string) : $string;
}

/**
 * 用于显示状态操作按钮
 * @param int $status        0|1|-1状态
 * @param int $id            对象id
 * @param string $field      字段，默认id
 * @param string $controller 默认当前控制器
 * @return string
 */
function show_status($status, $id, $field = 'id', $controller = '')
{
    $controller === '' && $controller = Request::instance()->controller();
    switch ($status) {
        // 恢复
        case 0 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/resume', [$field => $id]) . '\',{},change_status,[this,\'resume\'])" class="label label-success radius" title="点击恢复">恢复</a>';
            break;
        // 禁用
        case 1 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/forbid', [$field => $id]) . '\',{},change_status,[this,\'forbid\'])" class="label label-warning radius" title="点击禁用">禁用</a>';
            break;
        // 还原
        case -1 :
            $ret = '<a href="javascript:;" onclick="ajax_req(\'' . Url::build($controller . '/recycle', [$field => $id]) . '\')" class="label label-secondary radius" title="点击还原">还原</a>';
            break;
    }

    return $ret;
}

/**
 * 显示状态
 * @param int $status     0|1|-1
 * @param bool $imageShow true只显示图标|false只显示文字
 * @return string
 */
function get_status($status, $imageShow = true)
{
    switch ($status) {
        case 0 :
            $showText = '未审核';
            $showImg = '<i class="Hui-iconfont c-warning status" title="未审核">&#xe6e0;</i>';
            break;
        case -1 :
            $showText = '审核不通过';
            $showImg = '<i class="Hui-iconfont c-danger status" title="审核不通过">&#xe6dd;</i>';
            break;
        case 1 :
        default :
            $showText = '审核通过';
            $showImg = '<i class="Hui-iconfont c-success status" title="审核通过">&#xe6e1;</i>';

    }

    return ($imageShow === true) ? $showImg : $showText;
}

/**
 * 框架内部默认ajax返回
 * @param string $msg      提示信息
 * @param string $redirect 重定向类型 current|parent|''
 * @param string $alert    父层弹框信息
 * @param bool $close      是否关闭当前层
 * @param string $url      重定向地址
 * @param string $data     附加数据
 * @param int $code        错误码
 * @param array $extend    扩展数据
 */
function ajax_return_adv($msg = '操作成功', $redirect = 'parent', $alert = '', $close = false, $url = '', $data = '', $code = 0, $extend = [])
{
    $extend['opt'] = [
        'alert'    => $alert,
        'close'    => $close,
        'redirect' => $redirect,
        'url'      => $url,
    ];

    return ajax_return($data, $msg, $code, $extend);
}

/**
 * 返回错误json信息
 */
function ajax_return_adv_error($msg = '', $code = 1, $redirect = '', $alert = '', $close = false, $url = '', $data = '', $extend = [])
{
    return ajax_return_adv($msg, $alert, $close, $redirect, $url, $data, $code, $extend);
}

/**
 * ajax数据返回，规范格式
 * @param array $data   返回的数据，默认空数组
 * @param string $msg   信息
 * @param int $code     错误码，0-未出现错误|其他出现错误
 * @param array $extend 扩展数据
 */
function ajax_return($data = [], $msg = "", $code = 0, $extend = [])
{
    $ret = ["code" => $code, "msg" => $msg, "data" => $data];
    $ret = array_merge($ret, $extend);

    return Response::create($ret, 'json');
}

/**
 * 返回标准错误json信息
 */
function ajax_return_error($msg = "出现错误", $code = 1, $data = [], $extend = [])
{
    return ajax_return($data, $msg, $code, $extend);
}

/**
 * 从二维数组中取出自己要的KEY值
 * @param  array $arrData
 * @param string $key
 * @param $im true 返回逗号分隔
 * @return array
 */
function filter_value($arrData, $key, $im = false)
{
    $re = [];
    foreach ($arrData as $k => $v) {
        if (isset($v[$key])) $re[] = $v[$key];
    }
    if (!empty($re)) {
        $re = array_flip(array_flip($re));
        sort($re);
    }

    return $im ? implode(',', $re) : $re;
}

/**
 * 重设键，转为array(key=>array())
 * @param array $arr
 * @param string $key
 * @return array
 */
function reset_by_key($arr, $key)
{
    $re = [];
    foreach ($arr as $v) {
        $re[$v[$key]] = $v;
    }

    return $re;
}

/**
 * 节点遍历
 *
 * @param        $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int    $root
 *
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = [];
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            if ($data instanceof \think\Model) {
                $list[$key] = $data->toArray();
            }
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            if (!isset($list[$key][$child])) {
                $list[$key][$child] = [];
            }
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }

    return $tree;
}

/**
 * 统一密码加密方式，如需变动直接修改此处
 * @param $password
 * @return string
 */
function password_hash_tp($password)
{
    return hash("md5", trim($password));
}

/**
 * 生成随机字符串
 * @param string $prefix
 * @return string
 */
function get_random($prefix = '')
{
    return $prefix . base_convert(time() * 1000, 10, 36) . "_" . base_convert(microtime(), 10, 36) . uniqid();
}

/**
 * 获取自定义配置
 * @param string|int $name 配置项的key或者value，传key返回value，传value返回key
 * @param string $conf
 * @param bool $key        传递的是否是配置键名，默认是，则返回配置信息
 * @return int|string
 */
function get_conf($name, $conf, $key = true)
{
    $arr = config("conf." . $conf);
    if ($key) return $arr[$name];
    foreach ($arr as $k => $v) {
        if ($v == $name) {
            return $k;
        }
    }
}


/**
 * 多维数组合并（支持多数组）
 * @return array
 */
function array_merge_multi()
{
    $args = func_get_args();
    $array = [];
    foreach ($args as $arg) {
        if (is_array($arg)) {
            foreach ($arg as $k => $v) {
                if (is_array($v)) {
                    $array[$k] = isset($array[$k]) ? $array[$k] : [];
                    $array[$k] = array_merge_multi($array[$k], $v);
                } else {
                    $array[$k] = $v;
                }
            }
        }
    }

    return $array;
}


/**
 * 将list_to_tree的树还原成列表
 * @param array $tree
 * @param string $child
 * @param string $order
 * @param int $level
 * @param null $filter
 * @param array $list
 * @return array
 */
function tree_to_list($tree, $filter = null, $child = '_child', $order = 'id', $level = 0, &$list = [])
{
    if (is_array($tree)) {
        if (!is_callable($filter)) {
            $filter = function (&$refer, $level) {
                $refer['level'] = $level;
            };
        }
        foreach ($tree as $key => $value) {
            $refer = $value;
            unset($refer[$child]);
            $filter($refer, $level);
            $list[] = $refer;
            if (isset($value[$child])) {
                tree_to_list($value[$child], $filter, $child, $order, $level + 1, $list);
            }
        }
    }

    return $list;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list   查询结果
 * @param string $field 排序的字段名
 * @param array $sortBy 排序类型
 *                      asc正向排序 desc逆向排序 nat自然排序
 * @return array|bool
 */
function list_sort_by($list, $field, $sortBy = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = [];
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortBy) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];

        return $resultSet;
    }

    return false;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;

    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 生成一定长度的UUID
 *
 * @param int $length
 *
 * @return string
 */
function get_uuid($length = 16)
{
    mt_srand((double)microtime()*10000);
    $uuid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    $str = base64_encode($uuid);
    return substr($str,  mt_rand(0, strlen($str) - $length), $length);
}

/**
 * 根据模型名称获取模型
 *
 * @param $modelName
 *
 * @return \think\Model|\think\db\Query
 */
function get_model($modelName)
{
    if (false !== strpos($modelName, '\\')) {
        // 指定模型类
        $db = new $modelName;
    } else {
        try {
            $db = \think\Loader::model($modelName);
        } catch (\think\exception\ClassNotFoundException $e) {
            $db = \think\Db::name($modelName);
        }
    }

    return $db;
}

/**
*数字金额转换成中文大写金额的函数
*String Int $num 要转换的小写数字或小写字符串
*return 大写字母
*小数位为两位
**/
function num_to_rmb($num){
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2); 
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    } 
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int)$num;
        //结束循环
        if ($num == 0) {
            break;
        } 
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        } 
        $j = $j + 3;
    } 
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    }else{
        return $c . "整";
    }
}

/**
 * 验证规则扩展
 */
\think\Validate::extend([
    // 验证字段是否在模型中存在
    'checkExist' => function($value, $rule, $data, $field) {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        $db = get_model($rule[0]);
        $key = isset($rule[1]) ? $rule[1] : $field;

        if (strpos($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                $map[$key] = $data[$key];
            }
        } elseif (strpos($key, '=')) {
            parse_str($key, $map);
        } else {
            $map[$key] = $data[$field];
        }

        $pk = strval(isset($rule[3]) ? $rule[3] : $db->getPk());
        if (isset($rule[2])) {
            $map[$pk] = ['neq', $rule[2]];
        } elseif (isset($data[$pk])) {
            $map[$pk] = ['neq', $data[$pk]];
        }

        if ($db->where($map)->field($pk)->find()) {
            return true;
        }
        return false;
    }
]);
