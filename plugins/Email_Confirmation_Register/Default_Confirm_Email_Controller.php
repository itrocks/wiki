<?php
namespace ITRocks\Wiki\Plugins\Email_Confirmation_Register;

use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\User;

/**
 * Default confirm email controller
 */
class Default_Confirm_Email_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Confirm the activation when user click on activation link
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$key                 = $parameters->getRawParameter('action');
		$email_confirm       = Search_Object::create(Email_Confirmation::class);
		$email_confirm->link = $key;
		$email_confirm       = Dao::searchOne($email_confirm);
		if (Dao::delete($email_confirm)){
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
