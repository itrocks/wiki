<?php
namespace ITRocks\Wiki\History;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Class_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View;
use ITRocks\Wiki\Article;
use ITRocks\Wiki\History;

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
	 * @param $files        array[]
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $feature_name)
	{
		user_error('Unknown feature ' . $feature_name);
		return;
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 */
	public function runOutput(Parameters $parameters, array $form, array $files)
	{
		$article = $parameters->getObject(Article::class);
		if (!$article) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$article = Builder::create(Article::class);
			$article->history = Dao::search(
				['date' => Dao\Func::greaterOrEqual(Date_Time::today()->toBeginOf(Date_Time::YEAR))],
				History::class
			);
		}
		$parameters->set('article', $article);
		return View::run($parameters->getObjects(), $form, $files, History::class, Feature::F_OUTPUT);
	}

}
