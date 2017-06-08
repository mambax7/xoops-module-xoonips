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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

if (!function_exists('session_regenerate_id')) {
    if (!defined('XOOPS_SALT')) {
        define('XOOPS_SALT', substr(md5(XOOPS_DB_PREFIX . XOOPS_DB_USER . XOOPS_ROOT_PATH), 5, 8));
    }
    // session_regenerate_id compatible function for PHP Version< PHP4.3.2
    /**
     * @return bool
     */
    function session_regenerate_id()
    {
        mt_srand(microtime() * 100000);
        $random = md5(XOOPS_SALT . uniqid(mt_rand(), true));
        if (session_id($random)) {
            return true;
        } else {
            return false;
        }
    }
}

// Regenerate New Session ID & Delete OLD Session
function xoonips_session_regenerate()
{
    $old_sessid = session_id();
    session_regenerate_id();
    $new_sessid = session_id();
    session_id($old_sessid);
    session_destroy();
    $old_session = $_SESSION;
    session_id($new_sessid);
    $sessHandler = xoops_getHandler('session');
    session_set_saveHandler(array(
                                &$sessHandler,
                                'open'
                            ), array(
                                &$sessHandler,
                                'close'
                            ), array(
                                &$sessHandler,
                                'read'
                            ), array(
                                &$sessHandler,
                                'write'
                            ), array(
                                &$sessHandler,
                                'destroy'
                            ), array(
                                &$sessHandler,
                                'gc'
                            ));
    session_start();
    $_SESSION = array();
    foreach (array_keys($old_session) as $key) {
        $_SESSION[$key] = $old_session[$key];
    }
    // write and close session for xnp_is_valid_session_id()
    session_write_close();
    // restart session
    session_set_saveHandler(array(
                                &$sessHandler,
                                'open'
                            ), array(
                                &$sessHandler,
                                'close'
                            ), array(
                                &$sessHandler,
                                'read'
                            ), array(
                                &$sessHandler,
                                'write'
                            ), array(
                                &$sessHandler,
                                'destroy'
                            ), array(
                                &$sessHandler,
                                'gc'
                            ));
    session_start();
    $_SESSION = array();
    foreach (array_keys($old_session) as $key) {
        $_SESSION[$key] = $old_session[$key];
    }
}
