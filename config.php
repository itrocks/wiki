<?php
namespace ITRocks\Wiki;

use ITRocks\Wiki;
use ITRocks\Framework;
use ITRocks\Framework\Configuration;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\Plugin\Priority;

global $loc;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../framework/config.php';

//-------------------------------------------------------------------------------------------- wiki
$config['ITRocks/Wiki'] = [
	Configuration::APP         => Application::class,
	Configuration::ENVIRONMENT => $loc[Configuration::ENVIRONMENT],
	Configuration::EXTENDS_APP => 'ITRocks/Framework',

	//------------------------------------------------------------------------- CORE priority plugins
	Priority::CORE => [
		Framework\Builder::class => include(__DIR__ . SL . 'builder.php'),
	],

	//----------------------------------------------------------------------- NORMAL priority plugins
	Priority::NORMAL => [
		Framework\Component\Menu::class => include(__DIR__ . SL . 'menu.php'),
		Framework\Locale::class => [
			Locale::DATE     => 'd/m/Y',
			Locale::LANGUAGE => 'fr',
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MAXIMAL_COUNT => 2,
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_SEPARATOR     => ',',
				Number_Format::THOUSAND_SEPARATOR    => SP
			]
		],
		Framework\Tools\Wiki::class,
		Framework\User\Write_Access_Control::class,

		Wiki\Article\Redirect::class,
		Wiki\Markup\Images::class,
		Wiki\Markup\Links::class,
		Wiki\Uri_Rewriter::class
	]
];
