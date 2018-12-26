<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Input;
use ITRocks\Wiki\Article;
use ITRocks\Wiki\Plugins\Anti_Bot\Anti_Bot_Word;

/**
 * Anti-bot
 */
class Anti_Bot implements Registerable
{

	//--------------------------------------------------------------------------------- $content_name
	/**
	 * @var string
	 */
	private $content_name = 'text';

	//--------------------------------------------------------------------------------- $number_cases
	/**
	 * @var integer
	 */
	private $number_cases = 12;

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	/**
	 * Control if the word in forms results is the same as the current session anti bot word
	 *
	 * @param $form   array
	 * @param $result array
	 */
	public function afterUserAuthenticationControlRegisterFormParameters(array $form, array &$result)
	{
		$word = Session::current()->get(Anti_Bot_Word::class);
		if ($form['Anti_bot'] != $word->word) {
			$result[] = [
				'name' => 'Anti Bot error', 'message' => 'The security word was not copied correctly.'
			];
		}
	}

	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	/**
	 * Find the control word, generate the test and add input
	 *
	 * @param $result Input[]
	 */
	public function afterUserAuthenticationGetRegisterInputs(array &$result)
	{
		$text         = $this->choosePage(Article::class, $this->content_name);
		$tab          = $this->generateTab($text);
		$word         = $this->chooseWord($tab);
		$text         = $this->generateTextSelected($tab);
		$parameters   = $this->getViewParameters($text, $word);
		$form         = [];
		$files        = [];
		$class_name   = Anti_Bot::class;
		$feature_name = 'output';
		$view         = View::run($parameters, $form, $files, $class_name, $feature_name);
		Session::current()->set(new Anti_Bot_Word($word['word']));
		$result[] = new Input('Anti_bot', $view, 'text');
	}

	//------------------------------------------------------------------------------------ choosePage
	/**
	 * Choose a random page from the database
	 *
	 * @param $class        string The class corresponding to the table in database.
	 * @param $content_name string The field's name where take content.
	 * @return string the content property value
	 */
	private function choosePage($class, $content_name)
	{
		$list_pages = Dao::readAll($class);
		$possible_pages = [];
		foreach ($list_pages as $page) {
			$text = $page->$content_name;
			if ($text && str_replace(' ', '', $text) && count(explode(' ', $text)) > 50) {
				$possible_pages[] = $page;
			}
		}
		$index = rand(0, count($possible_pages) -1);
		return isset($possible_pages[$index]) ? $possible_pages[$index]->$content_name : '';
	}

	//------------------------------------------------------------------------------------ chooseWord
	/**
	 * Chose a word in an array
	 *
	 * @param $tab string[] An array of words
	 * @return mixed[] Returns all attributes necessary of this word :
	 *         'col' for the column number,
	 *         'row' for the row number,
	 *         'word' for the word choose
	 */
	private function chooseWord(array $tab)
	{
		$row  = rand(0, count($tab) - 1);
		$col  = rand(0, (isset($tab[$row]) ? count($tab[$row]) : 1) - 1);
		$word = (new String_Class(isset($tab[$row]) ? $tab[$row][$col] : ''))->cleanWord();
		return ['col' => $col, 'row' => $row, 'word' => $word];
	}

	//---------------------------------------------------------------------- explodeRowsInArrayToWord
	/**
	 * Explode an array corresponding of rows in an array of array, corresponding of rows and columns,
	 * the columns separate the words
	 *
	 * @param $text_rows string[] The array to explode
	 * @return array An array of array, represent rows and words
	 */
	private function explodeRowsInArrayToWord(array $text_rows)
	{
		$main_delimiter = ' ';
		$delimiters = [
			'.' => '. ', ':' => ': ', ',' => ', ', '(' => ' (', ') ', '  ' => ' ',
			'[' => ' [', ']' => '] '
		];
		$text_rows = str_replace('  ', ' ', $text_rows);
		foreach ($delimiters as $delimiter => $new_delimiter) {
			$text_rows = str_replace($delimiter, $new_delimiter, $text_rows);
		}
		$text_rows = explodeStringInArrayToDoubleArray($main_delimiter, $text_rows);
		$text_clean = [];
		$key_row = 0;
		foreach ($text_rows as $row) {
			$row_clean = [];
			$key_col = 0;
			foreach ($row as $col) {
				if ((new String_Class($col))->isWord()) {
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
	 * Parse a text in parameter to an array of array, to represent rows and columns
	 *
	 * @param $text string The string to parse
	 * @return array An array of array
	 */
	private function generateTab($text)
	{
		$text_explode = explode("\n\r", $text);
		$text_explode = explodeStringInArrayToSimpleArray("\n", $text_explode);
		$text_explode = $this->explodeRowsInArrayToWord($text_explode);
		$tab = [];
		$tab_size = $this->number_cases;
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
	 * Transform the tab result to be used in views parameters
	 *
	 * @param $tab array
	 * @return array
	 */
	private function generateTextSelected(array $tab)
	{
		$text = [];
		$row_number = 0;
		foreach ($tab as $row) {
			$cols = [];
			foreach ($row as $col) {
				$cols[] = ['value' => $col . ' '];
			}
			$text[] = ['row_number' => $row_number + 1, 'cols' => $cols];
			$row_number++;
		}
		return [[$text]];
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $text array
	 * @param $word array
	 * @return array
	 */
	private function getViewParameters(array $text, array $word)
	{
		$parameters = [];
		$parameters['row_number'] = $word['row'] + 1;
		$parameters['col_number'] = $word['col'] + 1;
		$numbering = [];
		for ($number = 0; $number < $this->number_cases; $number++) {
			$numbering[] = ['number' => $this->numberToCharacter($number)];
		}
		$parameters['col_numbering'] = $numbering;
		$parameters['rows']          = $text;
		return $parameters;
	}

	//----------------------------------------------------------------------------- numberToCharacter
	/**
	 * Get the character corresponding of the number put in parameters
	 *
	 * @param $number integer
	 * @return string The character
	 */
	private function numberToCharacter($number)
	{
		$numbering = [
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
			'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
		];
		return $numbering[$number % count($numbering)];
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[Authentication::class, 'getRegisterInputs'],
			[$this, 'afterUserAuthenticationGetRegisterInputs']
		);
		$aop->afterMethod(
			[Authentication::class, 'controlRegisterFormParameters'],
			[$this, 'afterUserAuthenticationControlRegisterFormParameters']
		);
	}

}
