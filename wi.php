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
		'SAF\Framework\User'   => 'SAF\Wiki\Wiki_User'
	),
	"Menu" => array(
		"Principal" => array(
			"/Accueil" => "Page d'accueil"
		),
		"Disconnect" => array(
			"/User/login" => "Se connecter",
			"/User/register" => "S'enregistrer"
		),
		"Connect" => array(
			"/User/disconnect" => "Se déconnecter"
		)
	),
	"View" => array(
		"css" => "bwiki"
	),
	"Plugins" => array(
		"normal" => array(
			"Uri_Rewriter",
			"Object_Builder",
			"Wiki",
			"Modification_Reserved_Connected"
		)
	)
);

require "index.php";
