<?php
namespace ITRocks\Wiki\Plugins\Wiki_Users;

use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;

/**
 * User disconnect controller
 */
class User_Disconnect_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$current = User::current();
		if ($current) {
			Authentication::disconnect($current);
		}
		return (new Default_Controller())->run($parameters, $form, $files, User::class, 'disconnect');
	}

}
