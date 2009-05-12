<?php
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2009  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
//                                                                            //
////////////////////////////////////////////////////////////////////////////////

define('phorum_page','index');
require_once './common.php';

// Check if the user has read permission for the current folder.
if (!phorum_check_read_common()) { return; }

// Handle "mark read" clicks. The arguments for such click are:
// [0] => The id of the forum to mark read (stored in $PHORUM['forum_id']).
// [1] => The string "markread"
// [2] => The id of the folder to which the user should be redirected after
//        the markread action.
if (isset($PHORUM['args'][1]) && $PHORUM['args'][1] === 'markread' &&
    !empty($PHORUM['user']['user_id'])) {

    // Mark all posts in the current forum as read.
    $phorum->newflags->markread($PHORUM['forum_id'], PHORUM_MARKREAD_FORUMS);

    // Redirect to a fresh list of the current forums without the mark read
    // parameters in the URL. This way we prevent users from bookmarking
    // the mark read URL.
    if (!empty($PHORUM["args"][2])) {
        $phorum->redirect(PHORUM_INDEX_URL, (int)$PHORUM['args'][2]);
    } else {
        $phorum->redirect(PHORUM_INDEX_URL);
    }
}

// Somehow we arrived at a forum instead of a folder.
// Redirect the user to the message list for that forum.
if (!empty($PHORUM["forum_id"]) && $PHORUM["folder_flag"] == 0) {
    $phorum->redirect(PHORUM_LIST_URL);
}

// Setup the syndication feed URLs for this folder.
$PHORUM['DATA']['FEEDS'] = array();
if (!empty($PHORUM['use_rss']))
{
    // Add the feed for new threads.
    $PHORUM['DATA']['FEEDS'][] = array(
        'URL' => $phorum->url(PHORUM_FEED_URL, $PHORUM['vroot'], 'type='.$PHORUM['default_feed']),
        'TITLE' => $PHORUM['DATA']['FEED'] . ' ('. strtolower($PHORUM['DATA']['LANG']['Threads']) . ')'
    );

    // Add the feed for new threads and their replies.
    $PHORUM['DATA']['FEEDS'][] = array(
        'URL' => $phorum->url(PHORUM_FEED_URL, $PHORUM['vroot'], 'replies=1', 'type='.$PHORUM['default_feed']),
        'TITLE' => $PHORUM['DATA']['FEED'] . ' (' . strtolower($PHORUM['DATA']['LANG']['Threads'].' + '.$PHORUM['DATA']['LANG']['replies']) . ')'
    );
}

// From here on we differentiate the code per index style that we use.
switch ($PHORUM['index_style'])
{
    case PHORUM_INDEX_FLAT:
        require_once './include/index/flat.php';
        break;

    case PHORUM_INDEX_DIRECTORY:
    default: // Should not happen
        require_once './include/index/directory.php';
        break;
}

?>
