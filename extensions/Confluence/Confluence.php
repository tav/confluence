<?php

$wgExtensionFunctions[] = "wfConfluence";

$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Confluence',
	'author' => 'tav',
	'url' => 'http://www.mediawiki.org/wiki/Extension:Confluence',
	'description' => 'Support extension for Confluence'
);
 
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Confluence',
	'author' => 'tav',
	'url' => 'http://www.mediawiki.org/wiki/Extension:Confluence',
	'description' => 'Support page for Confluence'
);

$wgHooks['LanguageGetMagic'][] = 'wfConfluenceMagicWords';

function wfConfluenceMagicWords(&$magicWords, $langCode) {
	$magicWords['ifrecent'] = array(0, 'ifrecent');
	return true;
}

function wfConfluence() {
	global $wgMessageCache;
	global $wgParser;
 	require_once('includes/SpecialPage.php');
	$wgMessageCache->addMessages(array('confluence' => 'Confluence'));
	SpecialPage::addPage(new SpecialPage('Confluence'));
	$wgParser->setFunctionHook("ifrecent", "ifRecentConfluenceFunction");
	// $wgParser->setFunctionHook("ifrecent", "ifRecentConfluenceObj", SFH_OBJECT_ARGS);
}

function ifRecentConfluenceObbj(&$parser, $frame, $args) {
	return 'hello';
//	return ifRecentConfluenceFunction($parser, $title, Â$then, $else, $max);
}

function ifRecentConfluenceFunction(&$parser, $title='', $then='', $else='', $max=120) {
	$output = ifRecentConfluenceFunctionInner($parser, $title, $then, $else, $max);
	return $output;
	return array($output, 'nowiki' => true, 'noparse' => true);
}

function ifRecentConfluenceFunctionInner(&$parser, $title='', $then='', $else='', $max=120) {
	$parser->disableCache();
	if (!$title) {
		return $else;
	}
	$title = Title::newFromText($title);
	$pageId = $title->getArticleId();
	if (!$pageId) {
		return $else;
	}
	$article = Article::newFromId($pageId);
	$ts = $article->getTimestamp();
	$ts = wfTimestamp(TS_UNIX, $ts);
	$max = time() - ($max * 3600);
	if ($max > $ts) {
		return $else;
	}
	return $then;
}

/* account creation hook */

function confluenceAccountCreation($user) {
	global $wgOut;
	$userTitle = Title::newFromText("User:" . $user->getName());
	$userArticle = new Article($userTitle);
	$userArticle->doEdit('{{user}}', "Created an automated user page.");
	$wgOut->redirect($userTitle->getFullURL(""), '301');
	return true;
}

?>
