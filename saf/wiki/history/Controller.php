<?php
namespace SAF\Wiki\History;

use SAF\Framework\Dao;
use SAF\Framework\Controller\Class_Controller;
use SAF\Framework\Controller\Parameters;
use FineDiff;
use SAF\Wiki\History;

/**
 * Search class controller
 */
class Controller implements Class_Controller
{

	const F_CHANGES = 'changes';

	//------------------------------------------------------------------------------------------- run
	/**
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
	 * The controller search history and print changes between 2. If newest is not provided,
	 * it takes the most recent for same article
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function runChanges(Parameters $parameters)
	{

		$oldHistory = Dao::read($parameters->getRawParameter('Old'), History::class);

		if ($parameters->getRawParameter('New')) {
			$newHistory = Dao::read($parameters->getRawParameter('New'), History::class);
		}
		else {
			$newHistory = $oldHistory->article->getChanges($oldHistory->property_name)[0];
		}

		return $this->diff($oldHistory->old_value, $newHistory->new_value);
	}

	//----------------------------------------------------------------------------------------- $diff
	/**
	 * Compute html diff between the old and new value
	 * @param $old_value
	 * @param $new_value
	 * @return string
	 */
	public function diff($old_value, $new_value)
	{
		$opcodes = FineDiff::getDiffOpcodes($old_value, $new_value);
		$fulldiff = FineDiff::renderDiffToHTMLFromOpcodes($old_value, $opcodes);
		return $fulldiff;
	}
}
