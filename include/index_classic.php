<?php

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2007  Phorum Development Team                              //
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
////////////////////////////////////////////////////////////////////////////////

if(!defined("PHORUM")) return;

$forums = phorum_db_get_forums( 0, $parent_id );

$PHORUM["DATA"]["FORUMS"] = array();

$forums_shown=false;

foreach( $forums as $forum ) {

    if ( $forum["folder_flag"] ) {

        $forum["URL"]["LIST"] = phorum_get_url( PHORUM_INDEX_URL, $forum["forum_id"] );

    } else {

        if($PHORUM["hide_forums"] && !phorum_user_access_allowed(PHORUM_USER_ALLOW_READ, $forum["forum_id"])){
            continue;
        }

        $forum["url"] = phorum_get_url( PHORUM_LIST_URL, $forum["forum_id"] );

        // if there is only one forum in Phorum, redirect to it.
        if ( $parent_id==0 && count( $forums ) < 2 ) {
            phorum_redirect_by_url($forum['url']);
            exit();
        }

        if ( $forum["message_count"] > 0 ) {
            $forum["raw_last_post"] = $forum["last_post_time"];
            $forum["last_post"] = phorum_date( $PHORUM["long_date_time"], $forum["last_post_time"] );
        } else {
            $forum["last_post"] = "&nbsp;";
        }

        $forum["URL"]["LIST"] = phorum_get_url( PHORUM_LIST_URL, $forum["forum_id"] );
        $forum["URL"]["MARK_READ"] = phorum_get_url( PHORUM_INDEX_URL, $forum["forum_id"], "markread" );
        if(isset($PHORUM['use_rss']) && $PHORUM['use_rss']) {
            $forum["URL"]["FEED"] = phorum_get_url( PHORUM_FEED_URL, $forum["forum_id"], "type=".$PHORUM["default_feed"] );
        }

        if($PHORUM["DATA"]["LOGGEDIN"] && $PHORUM["show_new_on_index"]){
            $newflagcounts = null;
            if($PHORUM['cache_newflags']) {
                $newflagkey    = $forum["forum_id"]."-".$PHORUM['user']['user_id'];
                $newflagcounts = phorum_cache_get('newflags_index',$newflagkey);
            }

            if($newflagcounts == null) {
                $newflagcounts = phorum_db_newflag_get_unread_count($forum["forum_id"]);
                if($PHORUM['cache_newflags']) {
                    phorum_cache_put('newflags_index',$newflagkey,$newflagcounts,86400);
                }
            }

            list($forum["new_messages"], $forum["new_threads"]) = $newflagcounts;
        }
    }

    $forums_shown=true;

    if($forum["folder_flag"]){
        $PHORUM["DATA"]["FOLDERS"][] = $forum;
    } else {
        $PHORUM["DATA"]["FORUMS"][] = $forum;
    }
}

if(!$forums_shown){
    // we did not show any forums here, show an error-message
    // set all our URL's
    phorum_build_common_urls();
    unset($PHORUM["DATA"]["URL"]["TOP"]);
    $PHORUM["DATA"]["OKMSG"] = $PHORUM["DATA"]["LANG"]["NoForums"];

    include phorum_get_template( "header" );
    if (isset($PHORUM["hooks"]["after_header"]))
        phorum_hook( "after_header" );
    include phorum_get_template( "message" );
    if (isset($PHORUM["hooks"]["before_footer"]))
        phorum_hook( "before_footer" );
    include phorum_get_template( "footer" );

} else {

    if (isset($PHORUM["hooks"]["index"]))
        $PHORUM["DATA"]["FORUMS"]=phorum_hook("index", $PHORUM["DATA"]["FORUMS"]);

    // set all our URL's
    phorum_build_common_urls();

    // should we show the top-link?
    if($PHORUM['forum_id'] == 0 || $PHORUM['vroot'] == $PHORUM['forum_id']) {
        unset($PHORUM["DATA"]["URL"]["INDEX"]);
    }

    include phorum_get_template( "header" );
    if (isset($PHORUM["hooks"]["after_header"]))
        phorum_hook("after_header");
    include phorum_get_template( "index_classic" );
    if (isset($PHORUM["hooks"]["before_footer"]))
        phorum_hook("before_footer");
    include phorum_get_template( "footer" );
}

?>
