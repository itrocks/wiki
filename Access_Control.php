<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\User\Write_Access_Control;

/**
 * Wiki access control feature
 */
class Access_Control extends Write_Access_Control
{

	//------------------------------------------------------------------------- WIKI_DISABLE_FEATURES
	const WIKI_DISABLE_FEATURES = [Feature::F_PRINT];

	//---------------------------------------------------------------------------- WIKI_READ_FEATURES
	const WIKI_READ_FEATURES = ['form', 'result', 'image'];

	//---------------------------------------------------------------------------------- readFeatures
	/**
	 * @return string[]
	 */
	public function readFeatures() : array
	{
		$read_features = array_merge(parent::readFeatures(), static::WIKI_READ_FEATURES);
		foreach (static::WIKI_DISABLE_FEATURES as $disable) {
			if ($disable = array_search($disable, $read_features)) {
				unset($read_features[$disable]);
			}
		}
		return $read_features;
	}

}
