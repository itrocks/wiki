<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Menu;
use SAF\Framework\View;
use SAF\Framework\User;


class Menu_Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		array_unshift($parameters, Menu::current());
		$is_connected = User::current();
		if($is_connected)
			$is_connected = $is_connected->login;
		if($is_connected){
			self::deleteBlock($parameters, "Disconnect");
		} else {
			self::deleteBlock($parameters, "Connect");
		}
		View::run($parameters, $form, $files, "Menu", "output");
	}

	private function deleteBlock($parameters, $delete_item){
		foreach ($parameters[0]->blocks as $block_key => $items) {
			if($items->title == $delete_item){
				unset($parameters[0]->blocks[$block_key]);
			}
		}
	}

}
