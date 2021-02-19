<?php
use yxtools\ArrayTools;
if (!function_exists('dump')) {
    /**
     * Notes: 打印信息
     * User : lzy
     */
    function dump()
    {
        $data = func_get_args();
        $data=[
            'time'=>time(),
            'data'=>$data
        ];
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> ';
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('halt')) {
    /**
     * Notes: 断点调试，并打印信息
     * User : lzy
     */
    function halt()
    {
        $args = func_get_args();
        call_user_func_array('dump', $args);
        die();
    }
}
// 数组方面

if (!function_exists('yx_arrayUniqueFb')) {
    /**
     * @desc： 二维数组去掉重复值
     * @param array $array2D
     * @return multitype:
     */
    function yx_arrayUniqueFb($array2D){

        return ArrayTools::arrayUniqueFb($array2D);
    }
}