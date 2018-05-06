<?php
namespace ITRocks\Wiki\Article;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Data_List;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Wiki article data-list controller
 */
class Data_List_Controller extends Data_List\Data_List_Controller
{

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param  $class_name    string class name
	 * @param  $parameters    string[] parameters
	 * @param  $list_settings Custom_Settings|Data_List_Settings
	 * @return Button[]
	 */
	public function getSelectionButtons(
		$class_name, array $parameters, Custom_Settings $list_settings = null
	) {
		$buttons = parent::getSelectionButtons($class_name, $parameters, $list_settings);
		unset($buttons[Feature::F_PRINT]);
		return $buttons;
	}

}
