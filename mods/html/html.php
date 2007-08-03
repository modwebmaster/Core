<?php

if(!defined("PHORUM")) return;

// HTML Phorum Mod
function phorum_html($data)
{
    $PHORUM = $GLOBALS["PHORUM"];

    foreach($data as $message_id => $message)
    {
        if(isset($message["body"]))
        {
            $body = $message["body"];

            // Protect against poisoned null byte XSS attacks
            // (MSIE does not protect itself against these, so we have
            // to take care of that).
            str_replace("\0", "", $body);

            // restore tags where Phorum has killed them
            $body = preg_replace("!&lt;(\/*[a-z].*?)&gt;!i", "<$1>", $body);

            // restore escaped & and "
            $body = str_replace("&amp;", "&", $body);
            $body = str_replace("&quot;", '"', $body);

            // strip out javascript events
            if(preg_match_all("/<[a-z][^>]+>/i", $body, $matches)) {
                $tags=array_unique($matches[0]);
                foreach($tags as $tag) {
                    $newtag=preg_replace("/\son.+?=[^>]+/i", "$1", $tag);
                    $body=str_replace($tag, $newtag, $body);
                }
            }

            // turn script and meta tags into comments
            $body=preg_replace("/<(\/*(script|meta).*?)>/i", "<!--$1-->", $body);

            // strip any <phorum break> tags that got inside certain
            // blocks like tables (to prevent <table><br/><tr> like
            // code) and pre/xmp (newlines are shown, even without
            // <br/> tags).
            $block_tags="table|pre|xmp";

            preg_match_all("!(<($block_tags).*?>).+?(</($block_tags).*?>)!ms", $body, $matches);

            foreach($matches[0] as $block){
                $newblock=str_replace("<phorum break>", "", $block);
                $body=str_replace($block, $newblock, $body);
            }

            $data[$message_id]["body"] = $body;
        }
    }

    return $data;
}

?>
