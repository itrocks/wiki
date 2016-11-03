<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Configuration;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\Plugin\Priority;
use ITRocks\Framework\Tools\Wiki;
use ITRocks\Framework\User\Write_Access_Control;
use ITRocks\Framework\Widget\Menu;
use ITRocks\Wiki\Article;

global $loc;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../framework/config.php';

//-------------------------------------------------------------------------------------------- wiki
$config['ITRocks/Wiki'] = [
	Configuration::APP         => Application::class,
	Configuration::ENVIRONMENT => $loc[Configuration::ENVIRONMENT],
	Configuration::EXTENDS_APP => 'ITRocks/Framework',

	//---------------------------------------------------------------------------------------- normal
	Priority::NORMAL => [
		Write_Access_Control::class,
		Article\Redirect::class,
		Locale::class => [
			Locale::DATE     => 'd/m/Y',
			Locale::LANGUAGE => 'fr',
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MAXIMAL_COUNT => 2,
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_SEPARATOR     => ',',
				Number_Format::THOUSAND_SEPARATOR    => ' '
			]
		],
		Markup\Images::class,
		Markup\Links::class,
		Menu::class => [
			Menu::TITLE => ['/', 'Home', '#main'],
			'Articles' => [
				'/'                     => 'Home',
				'/ITRocks/Wiki/Articles'    => 'Full articles list',
				'/ITRocks/Wiki/Article/add' => 'Add a new article',
				'/ITRocks/Wiki/Search/form' => 'Search'
			],
			'Tools' => [
				'/ITRocks/Wiki/Attachments'     => 'Attachment files',
				'/ITRocks/Framework/User/login' => 'Connect user'
			]
		],
		Uri_Rewriter::class,
		Wiki::class
		/*
		Wiki\Anti_Bot::class,
		Wiki\Change_Name_Page_Refactor::class,
		Wiki\Email_Confirmation_Register::class,
		Wiki\Images_Upload::class,
		Wiki\Links_Recognition::class,
		Wiki\Modification_Reserved_Connected::class,
		Wiki\Register_Email::class,
		Wiki\Stay_Connected::class,
		*/
	]
];
