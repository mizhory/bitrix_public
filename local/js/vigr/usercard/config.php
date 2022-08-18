<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
 
return [
	'css' => 'dist/usercard.bundle.css', 
	'js' => 'dist/usercard.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];