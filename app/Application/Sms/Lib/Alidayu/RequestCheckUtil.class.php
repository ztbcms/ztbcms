<?php
namespace Sms\Lib\Alidayu;

/**
 * API 参数检查
 *
 **/
class RequestCheckUtil
{
	/**
	 * 检查 $fieldName ֵֵ的 $value 是否为空
	 *
	 **/
	public static function checkNotNull($value,$fieldName) {
		
		if(self::checkEmpty($value)){
			throw new \Exception("client-check-error:Missing Required Arguments: " .$fieldName , 40);
		}
	}

	/**
	 * 检查 $fieldName ֵֵ的 $value length 是否大于 $maxLength
	 *
	 **/
	public static function checkMaxLength($value,$maxLength,$fieldName){		
		if(!self::checkEmpty($value) && mb_strlen($value , "UTF-8") > $maxLength){
			throw new \Exception("client-check-error:Invalid Arguments:the length of " .$fieldName . " can not be larger than " . $maxLength . "." , 41);
		}
	}

	/**
	 * 检查 $fieldName ֵֵ的 $value size 是否大于 $maxSize
	 *
	 **/
	public static function checkMaxListSize($value,$maxSize,$fieldName) {	

		if(self::checkEmpty($value))
			return ;

		$list=preg_split("/,/",$value);
		if(count($list) > $maxSize){
				throw new \Exception("client-check-error:Invalid Arguments:the listsize(the string split by \",\") of ". $fieldName . " must be less than " . $maxSize . " ." , 41);
		}
	}

	/**
	 * 检查 $fieldName ֵֵ的 $value 是否大于 $maxValue
	 *
	 **/
	public static function checkMaxValue($value,$maxValue,$fieldName){	

		if(self::checkEmpty($value))
			return ;

		self::checkNumeric($value,$fieldName);

		if($value > $maxValue){
				throw new \Exception("client-check-error:Invalid Arguments:the value of " . $fieldName . " can not be larger than " . $maxValue ." ." , 41);
		}
	}

	/**
	 * 检查 $fieldName 的 $value 是否小于 $minValue
	 *
	 **/
	public static function checkMinValue($value,$minValue,$fieldName) {
		
		if(self::checkEmpty($value))
			return ;

		self::checkNumeric($value,$fieldName);
		
		if($value < $minValue){
				throw new \Exception("client-check-error:Invalid Arguments:the value of " . $fieldName . " can not be less than " . $minValue . " ." , 41);
		}
	}

	/**
	 * 检查 $fieldName 的 $value 类型是否为 number
	 *
	 **/
	protected static function checkNumeric($value,$fieldName) {
		if(!is_numeric($value))
			throw new \Exception("client-check-error:Invalid Arguments:the value of " . $fieldName . " is not number : " . $value . " ." , 41);
	}

	/**
	 *  检查 $value 是否为空
	 *  if not set ,return true;
	 *	if is null , return true;
	 *	
	 *
	 **/
	public static function checkEmpty($value) {
		if(!isset($value))
			return true ;
		if($value === null )
			return true;
		if(is_array($value) && count($value) == 0)
			return true;
		if(is_string($value) &&trim($value) === "")
			return true;
		
		return false;
	}

}
?>