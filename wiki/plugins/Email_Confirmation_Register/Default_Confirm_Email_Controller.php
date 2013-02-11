<?php
namespace SAF\Wiki;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Controller;
use SAF\Framework\Search_Object;
use SAF\Framework\Dao;

class Default_Confirm_Email_Controller implements Feature_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$key = $parameters->getRawParameter("action");
		$email_confirm = Search_Object::newInstance("Email_Confirmation");
		$email_confirm->link = $key;
		$email_confirm = Dao::searchOne($email_confirm);
		if(Dao::delete($email_confirm)){
			(new Default_Controller())->run(
				$parameters, $form, $files, "User", "emailConfirmed"
			);
		}
		else {
			(new Default_Controller())->run(
				$parameters, $form, $files, "User", "emailNotConfirmed"
			);
		}
	}

}
