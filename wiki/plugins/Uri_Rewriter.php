<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Namespaces;

class Uri_Rewriter implements Plugin
{

	//------------------------------------------------------------- beforeMainControllerRunController
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeMainControllerRunController(AopJoinpoint $joinpoint)
	{

		$lien = $joinpoint->getArguments()[0];
		$short_class = "Page";
		if($lien){
			$str = Uri_Rewriter::uriToArray($lien);
			if(!$str || $str && count($str) > 0
				&& ($str[0] == "new" || $str[0] == "New")
				|| ($str && count($str) < 1)){
				$str[0] = $short_class;
				$str[1] = "new";
				Uri_Rewriter::putInArguments($str, $joinpoint);
			}
			else if($str && count($str) > 0
				&& !($str && count($str) > 1 && ($str[1] == "write" || is_numeric($str[1])))){
				$class = Namespaces::fullClassName($short_class);
				if(@class_exists($class)){
					$object = new $class();
					$name = $str[0];
					$object->name = str_replace("_", " ", $name);
					$result = Dao::searchOne($object, $class);
					if($result){
						$str[0] = $short_class;
						$str[1] = Dao::getObjectIdentifier($result);
					} else {
							$str[0] = $short_class;
							$str[1] = "new";
							/*
							// Permet de passer le nom de la page voulue par les gets
							$gets = Uri_Rewriter::returnGets($lien);
						  if($gets){
								$str[] = "&name=" . $name;
							} else {
							  $str[] = "?name=" . $name;
						  }*/
					}
					Uri_Rewriter::putInArguments($str, $joinpoint);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			"SAF\\Framework\\Main_Controller->runController()",
			array(__CLASS__, "beforeMainControllerRunController")
		);
	}

	private static function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		if (end($uri) === "") array_pop($uri);
		return $uri;
	}

	private static function arrayToUri($array)
	{
		$uri = "";
		$isGets = false;
		foreach($array as $element){
			if(strstr($element, "?") == true)
				$isGets = true;
			if($isGets)
				$uri .= $element;
			else
				$uri .= "/" . $element;
		}
		return $uri;
	}

	private static function returnGets($lien){
		return strstr($lien, "?");
	}

	private static function putInArguments($str, AopJoinpoint $joinpoint){
		$tab = $joinpoint->getArguments();
		$tab[0] = Uri_Rewriter::arrayToUri($str);
		$joinpoint->setArguments($tab);
	}

}
