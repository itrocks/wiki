<?php
namespace SAF\Wiki;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Dao;
use SAF\Framework\Default_Controller;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Search_Object;
use SAF\Framework\User;

class Default_Confirm_Email_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Confirm the activation when user click on activation link
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$key = $parameters->getRawParameter('action');
		$email_confirm = Search_Object::create(Email_Confirmation::class);
		$email_confirm->link = $key;
		$email_confirm = Dao::searchOne($email_confirm);
		if(Dao::delete($email_confirm)){
			return (new Default_Controller())->run(
				$parameters, $form, $files, User::class, 'emailConfirmed'
			);
		}
		else {
			return (new Default_Controller())->run(
				$parameters, $form, $files, User::class, 'emailNotConfirmed'
			);
		}
	}

}