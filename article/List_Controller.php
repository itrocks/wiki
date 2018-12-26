<?php
namespace ITRocks\Wiki\Article;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\List_;
use ITRocks\Framework\Widget\List_Setting;

/**
 * Wiki article data-list controller
 */
class List_Controller extends List_\Controller
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param  $class_name    string class name
	 * @param  $parameters    string[] parameters
	 * @param  $list_settings Setting\Custom\Set|List_Setting\Set
	 * @return Button[]
	 */
	public function getSelectionButtons(
		$class_name, array $parameters, Setting\Custom\Set $list_settings = null
	) {
		$buttons = parent::getSelectionButtons($class_name, $parameters, $list_settings);
		unset($buttons[Feature::F_PRINT]);
		return $buttons;
	}

}
