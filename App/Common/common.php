<?php

//用户输入数据过滤
function do_str($var, $is_drop_space = true) {
    if (is_array($var)) {
    	foreach ($var as $key => $value) {
		    if (! get_magic_quotes_gpc ()) {
        		$var[$key] = addslashes ( $value );
    		}
            if ($is_drop_space) {
                $value = preg_replace("/\s+/", "", $value);
            }
    		$var[$key] = htmlentities($value);
    	}
    	return $var;
    } else {
	    if (! get_magic_quotes_gpc ()) {
    		$var = addslashes ( $var );
    	}
        if ($is_drop_space) {
            $var = preg_replace("/\s+/", "", $var);
        }
    	return htmlentities($var);
   	}
}

//搜索特殊字符过滤
function do_keyword($keyword){
    $pattern = '/[%&_]/';
    if (preg_match($pattern, $keyword)) {
         $keyword = str_replace("%", "\%", $keyword);
         $keyword = str_replace("_", "\_", $keyword);
         $keyword = str_replace("&", "\&", $keyword);
         return $keyword;
    }
    return $keyword;
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

    //屏蔽的ID
    function undisplay(){
        return array('not in','4111,6297,6326,7967,9747,9871,10277,10343,4445,3652,9920,10279,10193,5787,9576,3887,9535,4810,99605,78569,78579,99597,10210,352,3798,3696,4937,9510,5832,10195,4047,4251,6398,9492,9506,9505,5724,107990,107624,6444');
    }
    
    //书籍类型映射
    function get_type($x) {
        $tytes = array(
            '1' => "悬疑",
            '2' => "历史",
            '3' => "军事",
            '4' => "玄幻",
            '5' => "奇幻",
            '6' => "仙侠",
            '7' => "武侠",
            '8' => "科幻",
            '9' => "游戏",
            '10' => "同人",
            '11' => "都市",
            '12' => "校园",
            '13' => "言情",
            '14' => "穿越",
            '15' => "重生",
            '16' => "豪门",
            '17' => "职场",
        );
        return $tytes[$x];
    }

    //解决低版本array_column()不兼容
    function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){ 
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
        $indexKeyIsNull            = (is_null($indexKey))?true :false; 
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
        $result                         = array(); 
        foreach((array)$input as $key=>$row){ 
            if($columnKeyIsNumber){ 
                $tmp= array_slice($row, $columnKey, 1); 
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
            }else{ 
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
            } 
            if(!$indexKeyIsNull){ 
                if($indexKeyIsNumber){ 
                  $key = array_slice($row, $indexKey, 1); 
                  $key = (is_array($key) && !empty($key))?current($key):null; 
                  $key = is_null($key)?0:$key; 
                }else{ 
                  $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                } 
            } 
            $result[$key] = $tmp; 
        } 
        return $result; 
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}