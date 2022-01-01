<?php

namespace IsotopeKit\Utility;

class ArrayUtils
{
    public static function objArraySearch($array, $index, $value)
    {
        foreach($array as $arrayInf) {
            if($arrayInf->{$index} == $value) {
                return $arrayInf;
            }
        }
        return null;
	}

	public static function objArraySearchwithKey($array, $index, $value)
    {
        foreach($array as $key => $arrayInf) {
            if($arrayInf->{$index} == $value) {
                return $key;
            }
        }
        return null;
	}
	
	public static function arraySearch($array, $index, $value)
    {
        foreach($array as $arrayInf) {
            if($arrayInf[$index] == $value) {
                return $arrayInf;
            }
        }
        return null;
	}
	
	public static function arrayItemSearchNot($array, $index, $value)
    {
		if($array[$index] != $value) {
			return $array;
		}
        return null;
    }
}