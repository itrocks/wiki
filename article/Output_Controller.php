<?php
namespace ITRocks\Wiki\Article;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Output;
use ITRocks\Framework\Widget\Output_Setting\Output_Settings;
use ITRocks\Wiki\Article;

/**
 * Wiki article output controller
 */
class Output_Controller extends Output\Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param  $object     Article|string
	 * @param  $parameters array
	 * @param  $settings   Custom_Settings|Output_Settings
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Custom_Settings $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		unset($buttons[Feature::F_PRINT]);
		return $buttons;
	}

}
