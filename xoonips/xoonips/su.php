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

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

// TODO: token ticket

// reject non-xoops-admin
if ($xoopsUser) {
    if (!$xoopsUser->isAdmin(-1) && !isset($_SESSION['xoonips_old_uid'])) {
        redirect_header(XOOPS_URL.'/', 2, _MD_XOONIPS_ITEM_FORBIDDEN);
        exit();
    }
} else {
    redirect_header('user.php', 2, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/imexport.php';

$xnpsid = $_SESSION['XNPSID'];
$uid = $_SESSION['xoopsUserId'];

// get request variables
$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('both', 'op', 's', false, '');
xoonips_validate_request(in_array($op, ['', 'su', 'end']));

// change uid.
// don't preserve old uid.
function xoonips_change_uid($su_uid)
{
    $u = new XoopsUser($su_uid);
    $groupids = $u->getGroups();
    $_SESSION['xoopsUserId'] = $su_uid;
    $_SESSION['xoopsUserGroups'] = $groupids;
}

if ('' == $op) {
    if (isset($_SESSION['xoonips_old_uid'])) {
        redirect_header(XOOPS_URL.'/', 0, ''); // already in su-mode
        exit();
    }

    $users = [];
    $uids = [];
    xnp_dump_uids($xnpsid, [], $uids);
    xnp_get_accounts($xnpsid, $uids, [], $users);
    // Sort by user account name
    foreach ($users as $key => $values) {
        $unameValues[$key] = $values['uname'];
    }
    $users_sort = $users;
    array_multisort($unameValues, SORT_ASC, $users_sort);

    $xoopsOption['template_main'] = 'xoonips_su.html';
    require XOOPS_ROOT_PATH.'/header.php';
    // Send variables to templete
    $xoopsTpl->assign('users', $users_sort);
    $xoopsTpl->assign('su_uid', $uids[0]);
    require XOOPS_ROOT_PATH.'/footer.php';
} elseif ('su' == $op) {
    $su_uid = $formdata->getValue('post', 'su_uid', 'i', true);
    $password = $formdata->getValue('post', 'password', 'n', true);
    // check admin password
    if (is_callable('User_Utils::passwordVerify')) {
        if (!User_Utils::passwordVerify($password, $xoopsUser->get('pass'))) {
            redirect_header('su.php', 3, _MD_XOONIPS_SU_FAIL);
        }
    } else {
        $pass = is_callable('User_Utils::encryptPassword') ? User_Utils::encryptPassword($password) : md5($password);
        $sql = 'SELECT `uid` FROM '.$xoopsDB->prefix('users').' WHERE `uid`='.$uid.' AND `pass`='.$xoopsDB->quoteString($pass);
        $result = $xoopsDB->query($sql);
        if (false == $result || 0 == $xoopsDB->getRowsNum($result)) {
            redirect_header('su.php', 3, _MD_XOONIPS_SU_FAIL);
        }
    }

    // su
    $_SESSION['xoonips_old_uid'] = $uid;
    xoonips_change_uid($su_uid);

    $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_session').' SET `su_uid`='.$su_uid.' WHERE `sess_id`='.$xoopsDB->quoteString(session_id());
    $xoopsDB->query($sql);

    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordStartSuEvent($uid, $su_uid);

    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_SU_START);
}
if ('end' == $op) {
    if (isset($_SESSION['xoonips_old_uid'])) {
        if (is_object($xoopsUser)) {
            $online_handler = xoops_getHandler('online');
            $online_handler->destroy($xoopsUser->getVar('uid'));
        }

        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordEndSuEvent($_SESSION['xoonips_old_uid'], $uid);

        $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_session').' SET `su_uid`=NULL WHERE `sess_id`='.$xoopsDB->quoteString(session_id());
        $xoopsDB->queryF($sql);

        xoonips_change_uid($_SESSION['xoonips_old_uid']);
        $_SESSION['xoonips_old_uid'] = null;

        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_SU_END);
    } else {
        redirect_header(XOOPS_URL.'/', 0, ''); // not in su-mode
    }
}
