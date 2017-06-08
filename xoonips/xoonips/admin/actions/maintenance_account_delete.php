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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// get requests
$formdata = xoonips_getUtility('formdata');
$uid      = $formdata->getValue('post', 'uid', 'i', true);

// is uid really xoonips user ?
$uHandler  = xoonips_getOrmHandler('xoonips', 'xoops_users');
$xuHandler = xoonips_getOrmHandler('xoonips', 'users');
$u_obj     = $uHandler->get($uid);
$xu_obj    = $xuHandler->get($uid);
if (!is_object($u_obj) || !is_object($xu_obj)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
}

// is uid really not system admin or moderator or group admin user ?
$xmemberHandler = xoonips_getHandler('xoonips', 'member');
$xgroupHandler  = xoonips_getHandler('xoonips', 'group');
if ($xmemberHandler->isAdmin($uid) || $xmemberHandler->isModerator($uid) || $xgroupHandler->isGroupAdmin($uid)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_IGNORE_USER);
}

// is uid really has not public/group items ?
$index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
if (count($index_item_linkHandler->getNonPrivateItemIds($uid)) > 0) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_ITEM_HANDOVER);
}

// check token ticket
require_once __DIR__ . '/../../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_account_delete';
if (!$xoopsGTicket->check(true, $ticket_area)) {
    exit();
}

$user_compoHandler = xoonips_getOrmCompoHandler('xoonips', 'user');
$user_compoHandler->deleteAccount($uid);

$eventHandler = xoonips_getOrmHandler('xoonips', 'event_log');
$eventHandler->recordDeleteAccountEvent($uid);

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DELETE_MSG_SUCCESS);
