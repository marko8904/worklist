<?php
//
//  Copyright (c) 2009, LoveMachine Inc.
//  All Rights Reserved. 
//  http://www.lovemachineinc.com
//

// These are the items most likely in need of being changed.

// Change this to something private to deter funny business
define("SALT", "WORKLIST");

// Refresh interval for ajax updates of the history table (in seconds)
define("AJAX_REFRESH", 300);

//Server URL's - If running as http://host.domain.com/ (not using app location), defining SERVER_NAME is all that is needed for non-ssl sites
//define("SERVER_NAME","dev.sendlove.us");
//define("APP_LOCATION",substr($_SERVER['SCRIPT_NAME'], 1, strrpos($_SERVER['SCRIPT_NAME'], "/")));
//define("SERVER_URL",'http://'.SERVER_NAME.'/'.APP_LOCATION); //Include [:port] for standard http traffic if not :80
////SSL Not enabled on development
////define("SECURE_SERVER_URL",'https://'.SERVER_NAME.'/'.APP_LOCATION); //Secure domain defaults to standard; Include [:port] for secure https traffic if not :443
////So clone the standard URL
//define("SECURE_SERVER_URL",SERVER_URL); //Secure domain defaults to standard; Include [:port] for secure https traffic if not :443
