<?php
//--------------------------------------------------------------------------------------- wiki
$CONFIG["wiki"] = array(
	"app" => "Wiki",
	"extends" => "framework",
	"Dao" => array(
		"user"     => "wiki",
		"password" => "cd93Rjfz4",
		"databases" => "wiki"
	),
	"Object_Builder" => array(
	),
	"Menu" => array(
		"wiki" => array(
			"/Accueil" => "Page d'accueil",
			"/pages" => "Liste des pages"
		)
	),
	"View" => array(
		"css" => "bwiki"
	),
	"Plugins" => array(
		"normal" => array(
			"Uri_Rewriter",
			"Wiki"
		)
	)
);

require "index.php";
