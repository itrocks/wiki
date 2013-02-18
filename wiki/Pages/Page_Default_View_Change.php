<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Html_Configuration;

class Page_Default_View_Change implements Plugin
{

	//-------------------------------------------------------------- aroundHtmlTemplateParseContainer
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundHtmlTemplateParseContainer(AopJoinpoint $joinpoint){
		$object = $joinpoint->getObject();
		if($object->getParameter("SAF\\Wiki\\Page")){
			$main_view = Html_Configuration::$main_template;
			Html_Configuration::$main_template = "Page_main.html";
			$joinpoint->process();
			Html_Configuration::$main_template = $main_view;
		}
		else{
			$joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			"SAF\\Framework\\Html_Template->parseContainer()",
			array(__CLASS__, "aroundHtmlTemplateParseContainer")
		);
	}
}
