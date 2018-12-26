<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Edit;

/**
 * This plugin need that :
 * the plugin Image_Wiki_Link_Parse is on
 * the default main html file import the Jquery script : images_upload.js
 */
class Images_Upload implements Registerable
{

	//--------------------------------------------------- afterDefaultEditControllerGetGeneralButtons
	/**
	 * @param $result Button[]
	 */
	public static function afterDefaultEditControllerGetGeneralButtons(array &$result)
	{
		$result[] = new Button(
			'Images upload',
			'Images_Upload',
			'images_upload',
			'#popup'
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Edit\Controller::class, 'getGeneralButtons'],
			[__CLASS__, 'afterDefaultEditControllerGetGeneralButtons']
		);
	}

}
