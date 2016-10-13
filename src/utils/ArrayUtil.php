<?php

namespace swag;

/**
 * Array utilities.
 */
class ArrayUtil {

	/**
	 * Flatten an array.
	 */
	public static function flattenArray($a) {
		if (is_array($a) && !$a)
			return array();

		if (!is_array($a))
			$a=array($a);

		$res=array();

		foreach ($a as $item) {
			if (is_array($item))
				$res=array_merge($res,$item);

			else
				$res[]=$item;
		}

		return $res;
	}
}