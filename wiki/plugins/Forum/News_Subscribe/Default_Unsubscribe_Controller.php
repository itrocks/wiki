<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Controller;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;
use SAF\Framework\User;
use SAF\Framework\View;

class Default_Unsubscribe_Controller extends Default_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = Forum_Controller_Utils::getViewParameters($parameters, $class_name);
		$short_class_name = Namespaces::shortClassName($class_name);
		$object = $parameters[$short_class_name];
		$subscribe = new Subscribe();
		$subscribe->user = User::current();
		$subscribe->from = $object;
		$subscribe->class_name = get_class($object);
		$subscribe = Dao::searchOne($subscribe);
		Dao::delete($subscribe);

		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Path_Utils::addPathAttribute($parameters, $path);
		$base_url = Forum_Url_Utils::getBaseUrl($path);
		$parameters["link"] = $base_url;
		$parameters["type"] = $short_class_name;
		$parameters["name"] = $object->title;
		$parameters["subscribe"] = "unsubscribe";
		return View::run($parameters, $form, $files, "News_Subscribe", "message");
	}
}
