<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Button;
use SAF\Framework\Color;

/**
 * This plugin need that :
 * the plugin Image_Wiki_Link_Parse is on
 * the default main html file import the Jquery script : images_upload.js
 */
class Images_Upload implements Plugin
{
	//--------------------------------------------------- afterDefaultEditControllerGetGeneralButtons
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterDefaultEditControllerGetGeneralButtons(AopJoinpoint $joinpoint)
	{
		$buttons = $joinpoint->getReturnedValue();
		$buttons[] = new Button(
			"Images upload",
			"Images_Upload",
			"images_upload",
			array(Color::of("red"), "#popup")
		);
		$joinpoint->setReturnedValue($buttons);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\Default_Edit_Controller->getGeneralButtons()',
			array(__CLASS__, "afterDefaultEditControllerGetGeneralButtons")
		);
	}

}
