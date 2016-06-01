<?php
namespace SAF\Wiki\History;

use FineDiff;

/**
 * Diff feature for the history
 *
 * @extends History
 */
abstract class Html_Differences
{

	//------------------------------------------------------------------------------------------ diff
	/**
	 * Compute html diff between the old and new value of a text
	 *
	 * @param $old_value string
	 * @param $new_value string
	 * @return string
	 */
	public static function diff($old_value, $new_value)
	{
		$opcodes = FineDiff::getDiffOpcodes($old_value, $new_value);
		$diff    = FineDiff::renderDiffToHTMLFromOpcodes($old_value, $opcodes);
		return $diff;
	}

}
