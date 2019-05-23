<?php
namespace ITRocks\Wiki\Article;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Wiki\Article;

/**
 * Wiki article output controller
 */
class Output_Controller extends Output\Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param  $object     Article|string
	 * @param  $parameters array
	 * @param  $settings   Setting\Custom\Set|Output_Setting\Set
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		unset($buttons[Feature::F_PRINT]);
		return $buttons;
	}

}
