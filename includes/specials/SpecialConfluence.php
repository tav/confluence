<?php
function wfSpecialConfluence() {

	global $wgArticle;
	global $wgOut;
	global $wgRequest;
	global $wgUser;

	$notEmpty = create_function('$v', 'return !empty($v);');

	if ($wgUser->isAnon()) {
		$wgOut->addHTML('Sorry, you need to be logged in to perform this action.');
		return;
	}
	if (!(($wgRequest->GetVal('action') && ($wgRequest->GetVal('target') || $wgRequest->GetVal('member'))))) {
		$wgOut->addHTML('Sorry, you need to provide the action and target/member parameters.');
		return;
	}

	$action = $wgRequest->GetVal('action');
	$target = $wgRequest->GetVal('target');
	$projection = $target;
	$member = $wgRequest->GetVal('member');
	$user = $wgUser->getName();

	if (($action === 'follow') || ($action === 'unfollow')) {
		$memberTitle = Title::newFromText("User:" . $member);
		$memberID = $memberTitle->getArticleId();
		if (!$memberID) {
			$wgOut->addHTML('Sorry, <a class="new" href="/wiki/User:' . $member . '">User:' . $member . "</a> doesn't exist.");
			return;
		}
		$userTitle = Title::newFromText("User:" . $user);
		$userID = $userTitle->getArticleId();
		if ($userID) {
			$userArticle = Article::newFromId($userID);
			$userText = $userArticle->getRawText();
		} else {
			$userArticle = new Article($userTitle);
			$userText = "{{user}}";
		}

		$userProj = explode("}}", $userText, 2);
		$userProj = explode('{{user|', $userProj[0], 2);
		$userProj = explode('|', $userProj[1]);
		$followCount = 1;

		foreach ($userProj as &$item) {
			if (strpos($item, 'follow') === 0) {
				$followMember = explode('=', $item, 2);
				$followMember = $followMember[1];
				if ($followMember === $member) {
					$item = '';
				} else {
					$item = 'follow' . $followCount . '=' . $followMember;
					$followCount += 1;
				}
			}
		}
		unset($item);

		if ($action === 'follow') {
			array_push($userProj, 'follow' . $followCount . '=' . $member);
			$actionMsg = 'is now following';
		} else {
			$actionMsg = 'is no longer following';
		}

		$userProj = array_filter($userProj, $notEmpty);
		$userProj = implode('|', $userProj);
		$userArticle->doEdit('{{user|' . $userProj . '}}', "$user $actionMsg $member");

		$userID = $userTitle->getArticleId();
		if ($userID === $memberID) {
			$wgOut->addHTML("You can't add yourself...");
			$userArticle = Article::newFromId($userID);
			$userArticle->doEdit($userText, "Reverted invalid self-follow.");
			return;
		}

		$relTitle = Title::newFromText("User:" . $member . '/Follower/' . $user);
		$relID = $relTitle->getArticleId();
		if ($relID) {
			$relArticle = Article::newFromId($relID);
		} else {
			$relArticle = new Article($relTitle);
		}
		$relArticle->doEdit($action, "Updated follow relationship.");

		$wgOut->redirect($memberTitle->getFullURL(""), '301');
		return;
	}

	$proj = Title::newFromText("Objective:" . $projection);
	$projID = $proj->getArticleId();

	if ($action === 'create') {
		if ($projID) {
			$wgOut->addHTML('The Objective already exists: <blockquote><a href="/wiki/Objective:' . $projection . '">' . $projection . '</a></blockquote>');
			return;
		}
		$projArticle = new Article($proj);
		$projArticle->doEdit('{{objective}}', "$user created '$projection'");
		$projID = $proj->getArticleId();
		$action = 'join';
	} else {
		if (!$projID) {
			$wgOut->addHTML('Sorry, <a class="new" href="/wiki/Objective:' . $projection . '">Objective:' . $projection . "</a> doesn't exist yet. Create it.");
			return;
		}
	}

	$userID = Title::newFromText("User:" . $user)->getArticleId();
	if (!$userID) {
		$wgOut->addHTML('Sorry, <a class="new" href="/wiki/User:' . $user . '">User:' . $user . "</a> doesn't exist yet. Create it.");
		return;
	}

	// objective

	$projArticle = Article::newFromId($projID);
	$projText = $projArticle->getRawText();

	$userProj = explode("}}", $projText, 2);
	$userProj = explode('{{objective|', $userProj[0], 2);
	$userProj = explode('|', $userProj[1]);
	$userProjFound = false;

	foreach ($userProj as &$item) {
		if ($item) {
			$item = str_replace('_', ' ', $item);
			$itemID = Title::newFromText("User:" . $item)->getArticleId();
			if (!$itemID) {
				$item = '';
			}
			if ($itemID === $userID) {
				if ($action === 'leave') {
					$item = '';
				} else {
					$userProjFound = true;
				}
			}
		}
	}
	unset($item);

	if ($action === 'join') {
		if (!($userProjFound)) {
			array_push($userProj, $user);
		}
		$actionMsg = 'joined';
	} else {
		$actionMsg = 'left';
	}

	$userProj = array_filter($userProj, $notEmpty);
	$userProj = implode('|', $userProj);
	$projArticle->doEdit('{{objective|' . $userProj . '}}', "$user $actionMsg $projection");

	// user

	$userArticle = Article::newFromId($userID);
	$userText = $userArticle->getRawText();

	$userProj = explode("}}", $userText, 2);
	$userProj = explode('{{user|', $userProj[0], 2);
	$userProj = explode('|', $userProj[1]);
	$userProjFound = false;

	foreach ($userProj as &$item) {
		if (($item) && (strpos($item, 'follow') !== 0)) {
			$item = str_replace('_', ' ', $item);
			$itemID = Title::newFromText("Objective:" . $item)->getArticleId();
			if (!$itemID) {
				$item = '';
			}
			if ($itemID === $projID) {
				if ($action === 'leave') {
					$item = '';
				} else {
					$userProjFound = true;
				}
			}
		}
	}
	unset($item);

	if ($action === 'join') {
		if (!($userProjFound)) {
			// array_unshift($userProj, str_replace('_', ' ', $projection));
			array_unshift($userProj, str_replace('_', ' ', $projArticle->mTitle->getText()));
		}
		$actionMsg = 'joined';
	} else {
		$actionMsg = 'left';
	}

	$userProj = array_filter($userProj, $notEmpty);
	$userProj = implode('|', $userProj);
	$userArticle->doEdit('{{user|' . $userProj . '}}', "$user $actionMsg $projection");

	// $wgOut->addHTML('.' . $userProj . '.');
	// return;

	$wgOut->redirect($proj->getFullURL("listing=team"), '301');
	return;
 
}
?>
