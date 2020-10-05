<?php

// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
$mydirname = basename(dirname(__DIR__));

// load mainfile.php
require dirname(__DIR__, 3) . '/mainfile.php';

// set other D3 variables
$mod_path = XOOPS_ROOT_PATH.'/modules/'.$mydirname;
$mod_url = XOOPS_URL.'/modules/'.$mydirname;
if (file_exists($mod_path.'/mytrustdirname.php')) {
    require $mod_path.'/mytrustdirname.php';
} else {
    $mytrustdirname = '';
}

// load condition definitions
require $mod_path.'/condefs.php';

// load basic functions
require $mod_path.'/include/functions.php';

// initialize xoonips session
$xsession_handler = &xoonips_getormhandler('xoonips', 'session');
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
$xsession_handler->initSession($uid);
$xsession_handler->validateUser($uid, true);
unset($xsession_handler);
