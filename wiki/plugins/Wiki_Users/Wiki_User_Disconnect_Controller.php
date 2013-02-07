<?php
namespace SAF\Wiki;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Session;
use SAF\Framework\Default_Controller;

class Wiki_User_Disconnect_Controller implements Feature_Controller
{

	/**
	 * Remove current user from script and session
	 *
	 * Called each time a user disconnects
	 *
	 * @param $user User
	 */
	private function disconnect(
		/** @noinspection PhpUnusedParameterInspection needed for plugins or overriding */
		Wiki_User $user
	) {
		Wiki_User::current(new Wiki_User());
		Session::current()->removeAny(__NAMESPACE__ . "\\Wiki_User");
	}

	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = Wiki_User::current();
		if ($current) {
			$this->disconnect($current);
		}
		(new Default_Controller())->run(
			$parameters, $form, $files, "Wiki_User", "disconnect"
		);
	}
}
