<?php
namespace SAF\Wiki\History;

use SAF\Framework\Controller\Class_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao;
use SAF\Framework\View;
use SAF\Wiki\Article;
use SAF\Wiki\History;

/**
 * Search class controller
 */
class Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * No default controller
	 *
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $feature_name)
	{
		user_error('Unknown feature ' . $feature_name);
	}

	//------------------------------------------------------------------------------------ runChanges
	/**
	 * The controller searches history and print changes between 2.
	 *
	 * @param $parameters Parameters history id
	 * @return string
	 */
	public function runChanges(Parameters $parameters)
	{
		/** @var $history History */
		$history = Dao::read($parameters->shift(), History::class);
		return Html_Differences::diff(
			$history->old_value,
			($parameters->shift() === 'current') ? $history->article->text : $history->new_value
		);
	}

	//------------------------------------------------------------------------------------- runOutput
	/**
	 * Output the history for a given article
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return string
	 */
	public function runOutput(Parameters $parameters, $form, $files)
	{
		$parameters->set('article', $parameters->getObject(Article::class));
		return View::run($parameters->getObjects(), $form, $files, History::class, Feature::F_OUTPUT);
	}

}
