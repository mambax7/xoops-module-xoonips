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

require_once XOOPS_ROOT_PATH . '/modules/xoonips/condefs.php';
require_once XOOPS_ROOT_PATH . '/modules/xoonips/include/functions.php';

/**
 * xoonips install function
 *
 * @param object $xoopsMod module instance
 * @return bool false if failure
 */
function xoops_module_install_xoonips($xoopsMod)
{
    $mydirname = basename(__DIR__);

    $uid = $GLOBALS['xoopsUser']->getVar('uid', 'n');
    $mid = $xoopsMod->getVar('mid', 'n');

    // get xoops administration handler
    $admin_xoopsHandler = xoonips_getHandler('xoonips', 'admin_xoops');

    // fix invalid group permissions
    if (!$admin_xoopsHandler->fixGroupPermissions()) {
        return false;
    }

    // create and join moderator group
    $mgid = $admin_xoopsHandler->createGroup('moderator', 'platform moderator');
    if ($mgid === false) {
        return false;
    }
    if (!$admin_xoopsHandler->addUserToXoopsGroup($mgid, $uid)) {
        return false;
    }

    // define groups
    $memberHandler = xoops_getHandler('member');
    $gids          = array_keys($memberHandler->getGroupList());
    $ogids         = array_diff($gids, array(
        XOOPS_GROUP_ADMIN,
        XOOPS_GROUP_USERS,
        XOOPS_GROUP_ANONYMOUS,
        $mgid
    ));

    // set module access permission to all known groups
    foreach ($gids as $gid) {
        $right = in_array($gid, $ogids) ? false : true;
        $admin_xoopsHandler->setModuleReadRight($mid, $gid, $right);
    }

    // set block parameters (read permissions and positions)
    // php-indent: disable
    $block_params = array(
        // 'show_func' => array(
        //   'rights' => array( 'registered user', 'guest', 'moderator', 'other' ),
        //   'positions' => array( 'visible', 'side', 'weight' 'show all pages' ),
        // ),
        'b_xoonips_quick_search_show' => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                true,
                0,
                10,
                true
            ),
        ),
        'b_xoonips_moderator_show'    => array(
            'rights'    => array(
                false,
                false,
                true,
                false
            ),
            'positions' => array(
                true,
                1,
                20,
                true
            ),
        ),
        'b_xoonips_tree_show'         => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                true,
                0,
                20,
                true
            ),
        ),
        'b_xoonips_login_show'        => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                true,
                0,
                0,
                true
            ),
        ),
        'b_xoonips_user_show'         => array(
            'rights'    => array(
                true,
                false,
                false,
                false
            ),
            'positions' => array(
                true,
                1,
                0,
                true
            ),
        ),
        'b_xoonips_group_show'        => array(
            'rights'    => array(
                true,
                false,
                false,
                false
            ),
            'positions' => array(
                true,
                1,
                10,
                true
            ),
        ),
        'b_xoonips_itemtypes_show'    => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                true,
                5,
                20,
                false
            ),
        ),
        'b_xoonips_ranking_new_show'  => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                false,
                0,
                0,
                false
            ),
        ),
        'b_xoonips_ranking_show'      => array(
            'rights'    => array(
                true,
                true,
                false,
                false
            ),
            'positions' => array(
                false,
                0,
                0,
                false
            ),
        ),
        'b_xoonips_userlist_show'     => array(
            'rights'    => array(
                true,
                false,
                false,
                false
            ),
            'positions' => array(
                false,
                0,
                0,
                false
            ),
        ),
    );
    // php-indent: enable
    foreach ($block_params as $show_func => $block_param) {
        $bids = $admin_xoopsHandler->getBlockIds($mid, $show_func);
        foreach ($bids as $bid) {
            // - rights
            $rights = $block_param['rights'];
            list($uright, $gright, $mright, $oright) = $block_param['rights'];
            $admin_xoopsHandler->setBlockReadRight($bid, XOOPS_GROUP_USERS, $uright);
            $admin_xoopsHandler->setBlockReadRight($bid, XOOPS_GROUP_ANONYMOUS, $gright);
            $admin_xoopsHandler->setBlockReadRight($bid, $mgid, $mright);
            foreach ($ogids as $gid) {
                $admin_xoopsHandler->setBlockReadRight($bid, $gid, $oright);
            }
            // - positions
            list($visible, $side, $weight, $allpage) = $block_param['positions'];
            $admin_xoopsHandler->setBlockPosition($bid, $visible, $side, $weight);
            $admin_xoopsHandler->setBlockShowPage($bid, 0, $allpage);
            if ($allpage) {
                // unset top page
                $admin_xoopsHandler->setBlockShowPage($bid, -1, false);
            }
        }
    }

    // hide 'user' and 'login' blocks
    $sys_blocks = array();
    // php-indent: disable
    $sys_blocks[] = array(
        'system',
        'b_system_user_show'
    );
    $sys_blocks[] = array(
        'system',
        'b_system_login_show'
    );
    // php-indent: enable
    if (defined('XOOPS_CUBE_LEGACY')) {
        // for XOOPS Cube Legacy 2.1
        // php-indent: disable
        $sys_blocks[] = array(
            'legacy',
            'b_legacy_usermenu_show'
        );
        $sys_blocks[] = array(
            'user',
            'b_user_login_show'
        );
        // php-indent: enable
    }
    foreach ($sys_blocks as $sys_block) {
        list($dirname, $show_func) = $sys_block;
        $sysmid = $admin_xoopsHandler->getModuleId($dirname);
        if ($sysmid === false) {
            // this case will occur when system module does not installed on
            // XOOPS Cube Legacy 2.1
            continue;
        }
        $bids = $admin_xoopsHandler->getBlockIds($sysmid, $show_func);
        foreach ($bids as $bid) {
            $admin_xoopsHandler->setBlockPosition($bid, false, 0, 0);
        }
    }

    // set start up page to xoonips module
    // if ( ! $admin_xoopsHandler->set_startup_page( $mydirname ) ) {
    //  return false;
    // }
    // set moderator id to xoonips config
    $xconfigHandler = xoonips_getOrmHandler('xoonips', 'config');
    if (!$xconfigHandler->setValue('moderator_gid', $mgid)) {
        return false;
    }

    // register my xoonips user information
    $xmemberHandler = xoonips_getHandler('xoonips', 'member');
    if (!$xmemberHandler->pickupXoopsUser($uid, true)) {
        return false;
    }

    // create XooNIps session
    $sessionHandler = xoonips_getOrmHandler('xoonips', 'session');
    $sessionHandler->initSession($uid);

    // set notifications
    $uids = array_keys($memberHandler->getUsers(null, true));
    // php-indent: disable
    $notifications = array(
        'administrator' => array(
            'item_transfer',
            'account_certify',
            'item_certify',
            'group_item_certify_request',
        ),
        'user'          => array(
            'item_transfer',
            'item_updated',
            'item_certified',
            'item_rejected',
            'file_downloaded',
            'group_item_certified',
            'group_item_rejected',
        ),
    );
    // php-indent: enable
    foreach ($notifications as $category => $events) {
        foreach ($events as $event) {
            // enable event
            $admin_xoopsHandler->enableNotification($mid, $category, $event);
            // subscribe all notifications to all users
            foreach ($uids as $uid) {
                $admin_xoopsHandler->subscribeNotification($mid, $uid, $category, $event);
            }
        }
    }

    return true;
}
