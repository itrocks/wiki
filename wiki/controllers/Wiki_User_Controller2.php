<?php
namespace SAF\Wiki;
use SAF\Framework\User_Authenticate_Controller;
use SAF\Framework\Controller_Parameters;

class Wiki_User_Controller extends User_Authenticate_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		parent::run($parameters, $form, $files);
	}

}
