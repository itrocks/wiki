<?php
namespace SAF\Wiki;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Session;
use SAF\Framework\Default_Controller;
use SAF\Framework\User_Authentication;

class Wiki_User_Disconnect_Controller implements Feature_Controller
{
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = Wiki_User::current();
		if ($current) {
			User_Authentication::disconnect($current);
		}
		return (new Default_Controller())->run(
			$parameters, $form, $files, "Wiki_User", "disconnect"
		);
	}
}
