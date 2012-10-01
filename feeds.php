<?php
/*
 * Copyright (c) 2010, LoveMachine Inc.
 * All Rights Reserved. 
 * http://www.lovemachineinc.com
 * 
 * Feeds.php
 * Paramters:  name= name_of_feed (priority | completed)
 * 			   format=atom | rss
 */
require_once('config.php');
require_once('db.php');
require_once('Zend/Feed/Writer/Feed.php');

function addEntry($writer, $entryData, $entryDescription) {

    global $name;

	$entry = $writer->createEntry();
	$entry->setLink(SERVER_URL . 'workitem.php?job_id=' . $entryData['worklist_id'] . '&action=view'); 
	// must supply a non-empty value for description
	$content = !empty($entryData['content']) ? html_entity_decode($entryData['content'], ENT_QUOTES) : "N/A";
	$entry->setDescription($content);   
	//$entry->setContent($entryData['content']); 
	$entry->addAuthor($entryData['author'], $entryData['email']); 

    if ($name == 'comments') {
        $entry->setTitle($entryData['author'] . ' added a comment to job #' . $entryData['worklist_id']);
        $entry->setDateCreated(strtotime($entryData['timestamp'])); 
        $entry->setDateCreated(strtotime($entryData['timestamp'])); 
    } else {
        $entry->setTitle(html_entity_decode($entryData['title'], ENT_QUOTES));
        $entry->setDateCreated(time()); 
        $entry->setDateModified(time()); 
    }
	$writer->addEntry($entry); // manual addition post-creation 
}

function loadFeed($writer, $query, $entryDescription) {
	$db = Zend_Registry::get('db');
	$result = $db->fetchAll($query);
	if ($result) {
		foreach($result as $row) {
			addEntry($writer, $row, $entryDescription);
		}
	}
}

$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'rss';
$job_id = isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : false;

if (!($format == 'atom' || $format == 'rss')) {
	$format = 'rss';
} 
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : 'completed';
// Connect to db
try {
	$db = Zend_Registry::get('db');
	$db->getConnection();
} catch (Zend_Db_Adapter_Exception $e) {
    // failed login, db not running
    
} catch (Zend_Exception $e) {
	// factory() failed load of mysqli
} 

switch ($name) {
	case 'priority' :
		$name = 'priority';
		$title = 'Worklist Top Priority Bidding Jobs';
		$description = SERVER_NAME.' Worklist, highest priority jobs Bidding';
		$entryDescription = 'Worklist priority item';
		$query = "SELECT w.id as worklist_id, u1.nickname as author, username as email, summary as title, notes as content
					FROM ".WORKLIST." w
					JOIN ".USERS." u1 ON u1.id = w.creator_id AND w.status = 'Bidding'
					ORDER BY priority LIMIT 20";
		break;
    case 'comments' :
        $name = 'comments';
        if (isset($job_id) && $job_id) {
            $where = "WHERE worklist_id = " . $job_id;
            $title = 'Worklist - Comments for Job #' . $job_id;
            $description = $_SERVER['SERVER_NAME'] . ' Worklist, latest Comments for Job #' . $job_id;
            $entryDescription = 'Worklist latest Comments for Job #' . $job_id;
        } else {
            $where = "";
            $title = 'Worklist Latest Comments';
            $description =  $_SERVER['SERVER_NAME'] . ' Worklist, latest comments';
            $entryDescription = 'Worklist latest comments';
        }
        $query = "
            SELECT 
                c.id AS comment_id, 
                c.worklist_id AS worklist_id,
                c.date AS timestamp,
                c.comment AS summary,
                c.comment AS content,
                u.nickname as author, 
                u.username as email
            FROM ".COMMENTS." c
            JOIN ".USERS." u on u.id = c.user_id
            " . $where . "
            ORDER BY timestamp DESC LIMIT 20";
        break;
	case 'completed' : 
	default :
		$name = 'completed';
		$title = 'Worklist most Recent completed jobs';
		$description = SERVER_NAME.' Worklist, Most recent completed Jobs';
		$entryDescription = 'Worklist priority item';
		$query = "SELECT w.id as worklist_id, u1.nickname as author, username as email, summary as title, notes as content
					FROM ".WORKLIST." w
					JOIN ".USERS." u1 ON u1.id = w.creator_id AND w.status = 'Done'
					ORDER BY created DESC LIMIT 20";
}

$url = 'https://'.SERVER_NAME.'/';
$link = $url . "feed.php?name=$name&format=$format";
$writer = new Zend_Feed_Writer_Feed; 
$writer->setTitle($title); 
$writer->setLink(SERVER_URL); 
$writer->setFeedLink(SERVER_URL . $link, $format);
$writer->setDescription($description);
$writer->setDateModified(time()); //Atom needs this  
loadFeed($writer, $query, $entryDescription);

echo $writer->export($format); 
