<?php

// $Revision:$
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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/condefs.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/functions.php';

/**
 * xoonips uninstall function.
 *
 * @param object $xoopsMod module instance
 *
 * @return bool false if failure
 */
function xoops_module_uninstall_xoonips($xoopsMod)
{
    $mydirname = basename(__DIR__);

    $uid = $GLOBALS['xoopsUser']->getVar('uid', 'n');
    $mid = $xoopsMod->getVar('mid', 'n');

    // get xoops administration handler
    $admin_xoops_handler = &xoonips_gethandler('xoonips', 'admin_xoops');

    // show original 'user' and 'login' blocks
    $sys_blocks = array('user' => array(), 'login' => array());
    if (defined('XOOPS_CUBE_LEGACY')) {
        // for XOOPS Cube Legacy 2.1
        $sys_blocks['user'][] = array('legacy', 'b_legacy_usermenu_show');
        $sys_blocks['login'][] = array('user', 'b_user_login_show');
    }
    $sys_blocks['user'][] = array('system', 'b_system_user_show');
    $sys_blocks['login'][] = array('system', 'b_system_login_show');
    foreach ($sys_blocks as $type => $sys_type_blocks) {
        foreach ($sys_type_blocks as $sys_block) {
            list($dirname, $show_func) = $sys_block;
            $sysmid = $admin_xoops_handler->getModuleId($dirname);
            if ($sysmid === false) {
                continue; // module not found
            }
            $bids = $admin_xoops_handler->getBlockIds($sysmid, $show_func);
            foreach ($bids as $bid) {
                $admin_xoops_handler->setBlockPosition($bid, true, 0, 0);
            }
            if (count($bids) != 0) {
                break; // found this type's block
            }
        }
    }

    return true;
}
