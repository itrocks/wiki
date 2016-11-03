<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Controller_Parameters;
use ITRocks\Framework\Default_Controller;
use ITRocks\Framework\Feature_Controller;
use ITRocks\Framework\User;
use ITRocks\Framework\User_Authentication;

class User_Disconnect_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			User_Authentication::disconnect($current);
		}
		return (new Default_Controller())->run($parameters, $form, $files, User::class, 'disconnect');
	}

}
