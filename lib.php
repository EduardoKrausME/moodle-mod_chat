<?PHP  // $Id$

/// Library of functions and constants for module chat


define("CHAT_REFRESH_ROOM", 10);
define("CHAT_REFRESH_USERLIST", 10);
define("CHAT_OLD_PING", 30);

define("CHAT_DRAWBOARD", false);  // Look into this later


// The HTML head for the message window to start with (<!-- nix --> is used to get some browsers starting with output
$CHAT_HTMLHEAD = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head></head>\n<body bgcolor=\"#FFFFFF\">\n\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n";

// The HTML head for the message window to start with (with js scrolling)
$CHAT_HTMLHEAD_JS = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><script language=\"JavaScript\">\n<!--\nfunction move()\n{\nif (scroll_active) window.scroll(1,400000);\nwindow.setTimeout(\"move()\",100);\n}\nscroll_active = true;\nmove();\n//-->\n</script>\n</head>\n<body bgcolor=\"#FFFFFF\" onBlur=\"scroll_active = true\" onFocus=\"scroll_active = false\">\n\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n<!-- nix -->\n";

// The HTML code for standard empty pages (e.g. if a user was kicked out)
$CHAT_HTMLHEAD_OUT = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><title>You are out!</title></head><body bgcolor=\"$THEME->body\"></body></html>";

// The HTML head for the message input page
$CHAT_HTMLHEAD_MSGINPUT = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><title>Message Input</title></head><body bgcolor=\"$THEME->body\">";

// The HTML code for the message input page, with JavaScript
$CHAT_HTMLHEAD_MSGINPUT_JS = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\"><html><head><title>Message Input</title>\n<script language=\"Javascript\">\n<!--\nscroll_active = true;\nfunction empty_field_and_submit()\n{\ndocument.fdummy.arsc_message.value=document.f.arsc_message.value;\ndocument.fdummy.submit();\ndocument.f.arsc_message.focus();\ndocument.f.arsc_message.select();\nreturn false;\n}\n// -->\n</script>\n</head><body bgcolor=\"$THEME->body\" OnLoad=\"document.f.arsc_message.focus();document.f.arsc_message.select();\">";


function chat_add_instance($chat) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.

    $chat->timemodified = time();

    # May have to add extra stuff in here #
    
    return insert_record("chat", $chat);
}


function chat_update_instance($chat) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

    $chat->timemodified = time();
    $chat->id = $chat->instance;

    # May have to add extra stuff in here #

    return update_record("chat", $chat);
}


function chat_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    if (! $chat = get_record("chat", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("chat", "id", "$chat->id")) {
        $result = false;
    }

    return $result;
}

function chat_user_outline($course, $user, $mod, $chat) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    return $return;
}

function chat_user_complete($course, $user, $mod, $chat) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

    return true;
}

function chat_print_recent_activity(&$logs, $isteacher=false) {
/// Given a list of logs, assumed to be those since the last login 
/// this function prints a short list of changes related to this module
/// If isteacher is true then perhaps additional information is printed.
/// This function is called from course/lib.php: print_recent_activity()

    global $CFG, $COURSE_TEACHER_COLOR;

    return $content;  // True if anything was printed, otherwise false
}

function chat_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;

    return true;
}

function chat_grades($chatid) {
/// Must return an array of grades for a given instance of this module, 
/// indexed by user.  It also returns a maximum allowed grade.

    $return->grades = NULL;
    $return->maxgrade = NULL;

    return $return;
}


//////////////////////////////////////////////////////////////////////
/// Functions that require some SQL

function chat_get_users($chatid) {

    global $CFG;
   
    return get_records_sql("SELECT u.id, u.firstname, u.lastname, u.picture, c.lastmessageping
                              FROM {$CFG->prefix}chat_users c,
                                   {$CFG->prefix}user u
                             WHERE c.chatid = '$chatid'
                               AND u.id = c.userid
                             GROUP BY u.id
                             ORDER BY c.firstping ASC");
}

function chat_get_latest_message($chatid) {

    global $CFG;

    return get_record_sql("SELECT * 
                             FROM {$CFG->prefix}chat_messages 
                            WHERE chatid = '$chatid' 
                         ORDER BY timestamp DESC");
}

//////////////////////////////////////////////////////////////////////

function chat_login_user($chatid, $version="header_js") {
    global $USER;

    $chatuser->chatid   = $chatid;
    $chatuser->userid   = $USER->id;
    $chatuser->version  = $version;
    $chatuser->ip       = $USER->lastIP;
    $chatuser->lastping = $chatuser->firstping = $chatuser->lastmessageping = time();
    $chatuser->sid      = random_string(32);

    if (!insert_record("chat_users", $chatuser)) {
        return false;
    }

    return $chatuser->sid;
}



function chat_browser_detect($HTTP_USER_AGENT) {

 if(eregi("(opera) ([0-9]{1,2}.[0-9]{1,3}){0,1}", $HTTP_USER_AGENT, $match)
 || eregi("(opera/)([0-9]{1,2}.[0-9]{1,3}){0,1}", $HTTP_USER_AGENT, $match))
 {
  $BName = "Opera"; $BVersion=$match[2];
 }
 elseif( eregi("(konqueror)/([0-9]{1,2}.[0-9]{1,3})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "Konqueror"; $BVersion=$match[2];
 }
 elseif( eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "Lynx"; $BVersion=$match[2];
 }
 elseif( eregi("(links) \(([0-9]{1,2}.[0-9]{1,3})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "Links"; $BVersion=$match[2];
 }
 elseif( eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "MSIE"; $BVersion=$match[2];
 }
 elseif( eregi("(netscape6)/(6.[0-9]{1,3})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "Netscape"; $BVersion=$match[2];
 }
 elseif( eregi("mozilla/5", $HTTP_USER_AGENT) )
 {
  $BName = "Netscape"; $BVersion="Unknown";
 }
 elseif( eregi("(mozilla)/([0-9]{1,2}.[0-9]{1,3})", $HTTP_USER_AGENT, $match) )
 {
  $BName = "Netscape"; $BVersion=$match[2];
 }
 elseif( eregi("w3m", $HTTP_USER_AGENT) )
 {
  $BName = "w3m"; $BVersion="Unknown";
 }
 else
 {
  $BName = "Unknown"; $BVersion="Unknown";
 }

 if(eregi("linux", $HTTP_USER_AGENT))
 {
  $BPlatform = "Linux";
 }
 elseif( eregi("win32", $HTTP_USER_AGENT) )
 {
  $BPlatform = "Windows";
 }
 elseif( (eregi("(win)([0-9]{2})", $HTTP_USER_AGENT, $match) )
 ||      (eregi("(windows) ([0-9]{2})", $HTTP_USER_AGENT, $match) ))
 {
  $BPlatform = "Windows $match[2]";
 }
 elseif( eregi("(winnt)([0-9]{1,2}.[0-9]{1,2}){0,1}", $HTTP_USER_AGENT, $match) )
 {
  $BPlatform = "Windows NT $match[2]";
 }
 elseif( eregi("(windows nt)( ){0,1}([0-9]{1,2}.[0-9]{1,2}){0,1}", $HTTP_USER_AGENT, $match) )
 {
  $BPlatform = "Windows NT $match[3]";
 }
 elseif( eregi("mac", $HTTP_USER_AGENT) )
 {
  $BPlatform = "Macintosh";
 }
 elseif( eregi("(sunos) ([0-9]{1,2}.[0-9]{1,2}){0,1}", $HTTP_USER_AGENT, $match) )
 {
  $BPlatform = "SunOS $match[2]";
 }
 elseif( eregi("(beos) r([0-9]{1,2}.[0-9]{1,2}){0,1}", $HTTP_USER_AGENT, $match) )
 {
  $BPlatform = "BeOS $match[2]";
 }
 elseif( eregi("freebsd", $HTTP_USER_AGENT) )
 {
  $BPlatform = "FreeBSD";
 }
 elseif( eregi("openbsd", $HTTP_USER_AGENT) )
 {
  $BPlatform = "OpenBSD";
 }
 elseif( eregi("irix", $HTTP_USER_AGENT) )
 {
  $BPlatform = "IRIX";
 }
 elseif( eregi("os/2", $HTTP_USER_AGENT) )
 {
  $BPlatform = "OS/2";
 }
 elseif( eregi("plan9", $HTTP_USER_AGENT) )
 {
  $BPlatform = "Plan9";
 }
 elseif( eregi("unix", $HTTP_USER_AGENT)
 ||      eregi("hp-ux", $HTTP_USER_AGENT) )
 {
  $BPlatform = "Unix";
 }
 elseif( eregi("osf", $HTTP_USER_AGENT) )
 {
  $BPlatform = "OSF";
 }
 else
 {
  $BPlatform = "Unknown";
 }
 
 $return["name"] = $BName;
 $return["version"] = $BVersion;
 $return["platform"] = $BPlatform;
 return $return;
}

function chat_display_version($version, $browser)
{
 GLOBAL $CFG;

 $checked = "";
 if (($version == "sockets") OR ($version == "push_js"))
 {
  $checked = "checked";
 }
 if (($version == "sockets" OR $version == "push_js")
     AND
     ($browser["name"] == "Lynx"
      OR
      $browser["name"] == "Links"
      OR
      $browser["name"] == "w3m"
      OR
      $browser["name"] == "Konqueror"
      OR
      ($browser["name"] == "Netscape" AND substr($browser["version"], 0, 1) == "2")))
 {
  $checked = "";
 }
 if (($version == "text")
     AND
     ($browser["name"] == "Lynx"
      OR
      $browser["name"] == "Links"
      OR
      $browser["name"] == "w3m"))
 {
  $checked = "checked";
 }
 if (($version == "header")
     AND
     ($browser["name"] == "Konqueror"))
 {
  $checked = "checked";
 }
 if (($version == "header_js")
     AND
     ($browser["name"] == "Netscape" AND substr($browser["version"], 0, 1) == "2"))
 {
  $checked = "checked";
 }
  ?>
  <tr>
   <td valign="top">
    <input type="radio" name="chat_chatversion" value="<?php echo $version; ?>"<?php echo $checked; ?>>
   </td>
   <td valign="top" align="left">
    <font face="Arial" size="2">
     <?php echo $chat_lang["gui_".$version]; ?>
    </font>
   </td>
  </tr>
  <?php

}


function chat_format_message($userid, $chatid, $timestamp, $message, $system=false) {
/// Given a message and some information, this function formats it appropriately
/// for displaying on the web, and returns the formatted string.

    global $CFG, $USER;

    if (!$user = get_record("user", "id", $userid)) {
        return "Error finding user id = $userid";
    }

    $picture = print_user_picture($user->id, 0, $user->picture, false, true, false);

    $strtime = userdate($timestamp, get_string("strftimemessage", "chat"));

    if ($system) {                     /// It's a system message
        $message = get_string("message$message", "chat", "$user->firstname $user->lastname");
        $message = addslashes($message);
        $output  = "<table><tr><td valign=top>$picture</td><td>";
        $output .= "<font size=2 color=\"#AAAAAA\">$strtime $message</font>";
        $output .= "</td></tr></table>";
        return $output;
    }

    if (substr($message, 0, 1) == "/") {  /// It's a user command

        if (substr($message, 0, 4) == "/me ") {
            $output = $chat_parameters["template_me"];
            $output = str_replace("{user}", $userid, $output);
            $output = str_replace("{message}", $message, $output);
            $output = str_replace("{sendtime}", substr($timestamp, 0, 5), $output);
            $output = str_replace("/me ", "", $output);
        }
    }

    convert_urls_into_links($message);
    replace_smilies($message);

    $output  = "<table><tr><td valign=top>$picture</td><td>";
    $output .= "<font size=2><font color=\"#888888\">$strtime $user->firstname</font>: $message</font>";
    $output .= "</td></tr></table>";

    return addslashes($output);

}


?>
