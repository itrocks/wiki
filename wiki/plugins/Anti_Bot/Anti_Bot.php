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
	 * Control if the word in forms results is the same as the current session anti bot word.
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
	 * Find the control word, generate the test and add input.
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
	 * Choose a random page in the bdd.
	 * @param $class        string The class corresponding to the table in database.
	 * @param $content_name string The field's name where take content.
	 * @return Page The chosen page.
	 */
	private static function choosePage($class, $content_name)
	{
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
	 * Chose a word in an array.
	 * @param $tab string[] An array of words.
	 * @return mixed[] Returns all attributes necessary of this word :
	 * "col" for the column number,
	 * "row" for the row number,
	 * "word" for the word choose.
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
	 * Explode an array corresponding of rows in an array of array, corresponding of rows and columns,
	 * the columns separate the words.
	 * @param $text_rows string[] The array to explode.
	 * @return string[][] An array of array, represent rows and words.
	 */
	private static function explodeRowsInArrayToWord($text_rows)
	{
		$main_delimiter = " ";
		$delimiters = array(
			"." => ". ", ":" => ": ", "," => ", ", "(" => " (", ") ", "  " => " ",
			"[" => " [", "]" => "] "
		);
		$text_rows = str_replace("  ", " ", $text_rows);
		foreach ($delimiters as $delimiter => $new_delimiter) {
			$text_rows = str_replace($delimiter, $new_delimiter, $text_rows);
		}
		$text_rows = explodeStringInArrayToDoubleArray($main_delimiter, $text_rows);
		$text_clean = array();
		$key_row = 0;
		foreach ($text_rows as $row) {
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
	 * Parse a text in parameter to an array of array, to represent rows and columns.
	 * @param $text string The string to parse.
	 * @return string[][] An array of array.
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
	 * Transform the tab result to be used in views parameters.
	 * @param $tab string[][]
	 * @return string[][]
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
	 * Get the character corresponding of the number put in parameters.
	 * @param $number integer
	 * @return string The character.
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
