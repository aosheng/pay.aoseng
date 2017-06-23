<?php
class Util
{
	static function json_encode($input){
		if(is_string($input)){
			$text = $input;
			$text = str_replace('\\', '\\\\', $text);
			$text = str_replace(
				array("\r", "\n", "\t", "\""),
				array('\r', '\n', '\t', '\\"'),
				$text);
			$text = str_replace("\\/", "/", $text);	
			return '"' . $text . '"';
		}else if(is_array($input) || is_object($input)){
			$arr = array();
			$is_obj = is_object($input) || (array_keys($input) !== range(0, count($input) - 1));
			foreach($input as $k=>$v){
				if($is_obj){
					$arr[] = self::json_encode($k) . ':' . self::json_encode($v);
				}else{
					$arr[] = self::json_encode($v);
				}
			}
			if($is_obj){
				$arr = str_replace("\\/", "/", $arr);
				return '{' . join(',', $arr) . '}';
			}else{
				$arr = str_replace("\\/", "/", $arr);
				return '[' . join(',', $arr) . ']';
			}
		}else{
			$input = str_replace("\\/", "/", $input);
			return $input . '';
		}
	}
 
}
