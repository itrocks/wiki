<?php
namespace SAF\Wiki;

use SAF\Framework\Configuration;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Locale;
use SAF\Framework\Locale\Number_Format;
use SAF\Framework\Plugin\Priority;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\Widget\Menu;

global $pwd;
require 'loc.php';
require 'pwd.php';
require 'saf.php';

//-------------------------------------------------------------------------------------------- wiki
$config['SAF/Wiki'] = [
	Configuration::APP         => Application::class,
	Configuration::EXTENDS_APP => 'SAF/Framework',

	//---------------------------------------------------------------------------------------- normal
	Priority::NORMAL => [
		Dao::class => [
			Link::DATABASE => 'saf_wiki',
			Link::LOGIN    => 'saf_wiki',
			Link::PASSWORD => $pwd['saf_wiki']
		],
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
		Wiki::class,
		Menu::class => [
			Menu::TITLE => ['/', 'Home', '#main'],
			'Articles' => [
				'/SAF/Wiki/Articles/Article/add' => 'Add a new article',
				'/SAF/Wiki/Articles/Articles'    => 'Full articles list',
				'/SAF/Wiki/Search/form'          => 'Search'
			],
			'Tools' => [
				'/SAF/Wiki/Attachments/Attachments' => 'Attachment files'
			]
		],
		Markup\Images::class,
		Markup\Links::class,
		Uri_Rewriter::class
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
