<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Dao;
use SAF\Framework\Input;
use SAF\Framework\Namespaces;
use SAF\Framework\Plugin;
use SAF\Framework\Session;
use SAF\Framework\String;
use SAF\Framework\View;

class Anti_Bot implements Plugin
{

	//--------------------------------------------------------------------------------- $content_name
	/**
	 * @var string
	 */
	private static $content_name = "text";

	//--------------------------------------------------------------------------------- $number_cases
	/**
	 * @var integer
	 */
	private static $number_cases = 12;

	//---------------------------------------------------------------------------------- $table_pages
	/**
	 * @var string
	 */
	private static $table_pages = 'SAF\Wiki\Page';

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterUserAuthenticationControlRegisterFormParameters(AopJoinpoint $joinpoint)
	{
		$word = Session::current()->get(Namespaces::fullClassName("Anti_Bot_Word"));
		if ($joinpoint->getArguments()[0]["Anti_bot"] != $word->word) {
			$value = $joinpoint->getReturnedValue();
			$value[] = array(
				"name" => "Anti Bot error", "message" => "The security word was not copied correctly."
			);
			$joinpoint->setReturnedValue($value);
		}
	}

	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterUserAuthenticationGetRegisterInputs(AopJoinpoint $joinpoint)
	{
		$text = self::choosePage(self::$table_pages, self::$content_name);
		$tab = self::generateTab($text);
		$word = self::chooseWord($tab);
		$text = self::generateTextSelected($tab, $word);
		$parameters = self::getViewParameters($text, $word);
		$form = array();
		$files = array();
		$class_name = "Anti_Bot";
		$feature_name = "output";
		$view = View::run($parameters, $form, $files, $class_name, $feature_name);
		Session::current()->set(new Anti_Bot_Word($word["word"]));
		$list_inputs = $joinpoint->getReturnedValue();
		$list_inputs[] = new Input("Anti_bot", $view, "text");
		$joinpoint->setReturnedValue($list_inputs);
	}

	//------------------------------------------------------------------------------------ choosePage
	/**
	 * @param $table_page   string
	 * @param $content_name string
	 * @return string
	 */
	private static function choosePage($table_page, $content_name)
	{
		$class = $table_page;
		$list_pages = Dao::readAll($class);
		$possible_pages = array();
		foreach ($list_pages as $page) {
			$text = $page->$content_name;
			if ($text && str_replace(" ", "", $text) && count(explode(" ", $text)) > 50) {
				$possible_pages[] = $page;
			}
		}
		$index = rand(0, count($possible_pages) -1);
		return isset($possible_pages[$index]) ? $possible_pages[$index]->$content_name : "";
	}

	//------------------------------------------------------------------------------------ chooseWord
	/**
	 * @param $tab string
	 * @return mixed[]
	 */
	private static function chooseWord($tab)
	{
		$row = rand(0, count($tab) - 1);
		$col = rand(0, (isset($tab[$row]) ? count($tab[$row]) : 1) - 1);
		$word = (new String(isset($tab[$row]) ? $tab[$row][$col] : ""))->cleanWord();
		return array("col" => $col, "row" => $row, "word" => $word);
	}

	//----------------------------------------------------------------------- explodeRowInArrayToWord
	/**
	 * @param $text_explode string
	 * @return array
	 */
	private static function explodeRowsInArrayToWord($text_explode)
	{
		$main_delimiter = " ";
		$delimiters = array(
			"." => ". ", ":" => ": ", "," => ", ", "(" => " (", ") ", "  " => " ",
			"[" => " [", "]" => "] "
		);
		$text_explode = str_replace("  ", " ", $text_explode);
		foreach ($delimiters as $delimiter => $newDelimiter) {
			$text_explode = str_replace($delimiter, $newDelimiter, $text_explode);
		}
		$text_explode = explodeStringInArrayToDoubleArray($main_delimiter, $text_explode);
		$text_clean = array();
		$key_row = 0;
		foreach ($text_explode as $row) {
			$row_clean = array();
			$key_col = 0;
			foreach ($row as $col) {
				if ((new String($col))->isWord()) {
					$row_clean[$key_col] = $col;
					$key_col++;
				}
			}
			if (!empty($row_clean)) {
				$text_clean[$key_row] = $row_clean;
				$key_row++;
			}
		}
		return $text_clean;
	}

	//----------------------------------------------------------------------------------- generateTab
	/**
	 * @param $text string
	 * @return array
	 */
	private static function generateTab($text)
	{
		$text_explode = explode("\n\r", $text);
		$text_explode = explodeStringInArrayToSimpleArray("\n", $text_explode);
		$text_explode = self::explodeRowsInArrayToWord($text_explode);
		$tab = array();
		$tab_size = self::$number_cases;
		$text_max_row = count($text_explode);
		if ($text_max_row > $tab_size) {
			$text_row = rand(0, $text_max_row - 1);
		}
		else {
			$text_row = 0;
		}
		$text_col = 0;
		for ($row = 0; $row < $tab_size; $row++) {
			if ($text_row > $text_max_row - 1) {
				break;
			}
			for ($col = 0; $col < $tab_size; $col++) {
				if ($text_col > count($text_explode[$text_row]) -1) {
					$text_col = 0;
					$text_row++;
					break;
				}
				$tab[$row][$col] = $text_explode[$text_row][$text_col];
				$text_col++;
			}
		}
		return $tab;
	}

	//-------------------------------------------------------------------------- generateTextSelected
	/**
	 * @param $tab array
	 * @return array
	 */
	private static function generateTextSelected($tab)
	{
		$text = array();
		$row_number = 0;
		foreach ($tab as $row) {
			$cols = array();
			foreach ($row as $col) {
				$cols[] = array("value" => $col . " ");
			}
			$text[] = array("row_number" => $row_number + 1, "cols" => $cols);
			$row_number++;
		}
		return $text;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $text array
	 * @param $word array
	 * @return array
	 */
	private static function getViewParameters($text, $word)
	{
		$parameters = array();
		$parameters["row_number"] = $word["row"] + 1;
		$parameters["col_number"] = $word["col"] + 1;
		$numbering = array();
		for ($number = 0; $number < self::$number_cases; $number++) {
			$numbering[] = array("number" => self::numberToCharacter($number));
		}
		$parameters["col_numbering"] = $numbering;
		$parameters["rows"]          = $text;
		return $parameters;
	}

	//----------------------------------------------------------------------------- numberToCharacter
	/**
	 * @param $number integer
	 * @return string[]
	 */
	private static function numberToCharacter($number)
	{
		$numbering = array(
			"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
			"N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		return $numbering[$number % count($numbering)];
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\User_Authentication->getRegisterInputs()',
			array(__CLASS__, "afterUserAuthenticationGetRegisterInputs")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->controlRegisterFormParameters()',
			array(__CLASS__, "afterUserAuthenticationControlRegisterFormParameters")
		);
	}

}
