<?php
namespace SAF\Wiki;

use SAF\Framework\Button;
use SAF\Framework\Color;
use SAF\Framework\Default_Edit_Controller;
use SAF\Plugins;

/**
 * This plugin need that :
 * the plugin Image_Wiki_Link_Parse is on
 * the default main html file import the Jquery script : images_upload.js
 */
class Images_Upload implements Plugins\Registerable
{

	//--------------------------------------------------- afterDefaultEditControllerGetGeneralButtons
	/**
	 * @param $result Button[]
	 */
	public static function afterDefaultEditControllerGetGeneralButtons(&$result)
	{
		$result[] = new Button(
			'Images upload',
			'Images_Upload',
			'images_upload',
			[ Color::of('red'), '#popup' ]
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$register->aop->afterMethod(
			[ Default_Edit_Controller::class, 'getGeneralButtons' ],
			[ __CLASS__, 'afterDefaultEditControllerGetGeneralButtons' ]
		);
	}

}
