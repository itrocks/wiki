<?php
namespace SAF\Wiki;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Controller;
use SAF\Framework\Feature_Controller;
use SAF\Framework\User;
use SAF\Framework\User_Authentication;

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
