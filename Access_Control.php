<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\User\Write_Access_Control;

/**
 * Wiki access control feature
 */
class Access_Control extends Write_Access_Control
{

	//--------------------------------------------------------------------------------- READ_FEATURES
	const READ_FEATURES = Feature::READ + [-1 => 'form', -2 => 'result'];

}
