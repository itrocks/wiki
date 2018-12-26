<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Widget\Menu;

return [
	Menu::TITLE => [SP, 'Home', '#main'],
	'Articles' => [
		SL                          => 'Home',
		'/ITRocks/Wiki/Articles'    => 'Full articles list',
		'/ITRocks/Wiki/Article/add' => 'Add a new article',
		'/ITRocks/Wiki/Search/form' => 'Search'
	],
	'Tools' => [
		'/ITRocks/Wiki/Attachments'     => 'Attachment files',
		'/ITRocks/Framework/User/login' => 'Connect user'
	]
];
