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
require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/notification.inc.php';
require 'class/base/gtickets.php';

$xnpsid = $_SESSION['XNPSID'];

xoonips_deny_guest_access('user.php');

//User(Not Moderater) can't control(except XOOPS administrator).
if (!$xoopsUser->isAdmin($xoopsModule->getVar('mid'))
    && !xnp_is_moderator($xnpsid, $xoopsUser->getVar('uid'))
) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
    exit();
}

$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');

$op_list = ['certify', 'uncertify_confirm', 'uncertify'];
$op = $formdata->getValue('post', 'op', 's', false, '');
$certify_uid = $formdata->getValue('post', 'certify_uid', 'i', false, 0);

if ('' == $op) {
} elseif (in_array($op, $op_list)) {
    if (0 == $certify_uid) {
        die('illegal request');
    }
} else {
    die('illegal request');
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);

require XOOPS_ROOT_PATH.'/header.php';

if ('certify' == $op) {
    // check token ticket
    if (!$xoopsGTicket->check(true, 'xoonips_certify_user')) {
        exit();
    }
    //certify user
    $user = [];
    $result = xnp_get_account($xnpsid, $certify_uid, $user);
    if (RES_OK != $result) {
        xoonips_error_exit(500);
    } elseif (empty($user)) {
        redirect_header('certifyuser.php', 3, _MD_XOONIPS_ACCOUNT_CANNOT_ACQUIRE_USER_INFO);
    }
    if (1 == $user['activate']) {
        redirect_header('certifyuser.php', 3, _MD_XOONIPS_ACCOUNT_ALREADY_CERTIFIED);
    }
    $user['activate'] = 1;
    $result = xnp_update_account($xnpsid, $user);
    if (0 != $result) {
        redirect_header('certifyuser.php', 3, _MD_XOONIPS_ACCOUNT_CANNOT_UPDATE_USER_INFO);
    }
    // record events(certify account)
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordCertifyAccountEvent($certify_uid);

    $xoopsTpl->assign('certified_user', $user);

    xoonips_notification_account_certified($certify_uid);

    //
    // notify a completion of certification to the certified user by e-mail
    //
    $langman     = &xoonips_getutility('languagemanager');
    $xoopsMailer = getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setTemplateDir($langman->mail_template_dir());
    $xoopsMailer->setTemplate('xoonips_account_certified.tpl');
    $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
    $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
    $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
    $xoopsMailer->setToUsers(new XoopsUser($user['uid']));
    $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
    $xoopsMailer->setFromName($myxoopsConfig['sitename']);
    $xoopsMailer->setSubject(_MD_XOONIPS_ACCOUNT_CERTIFIED);
    if (!$xoopsMailer->send()) {
        redirect_header('certifyuser.php', 3, sprintf(_US_ACTVMAILNG, $textutil->html_special_chars($user['uname'])));
    }
} elseif ('uncertify_confirm' == $op) {
    $xoopsTpl->assign('op', $op);
    $xoopsTpl->assign('certify_uid', $certify_uid);
    $xoopsOption['template_main'] = 'xoonips_certifyuser_uncertify_confirm.html';

    $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_certify_user_uncertfy');
    $xoopsTpl->assign('token_ticket', $token_ticket);

    require XOOPS_ROOT_PATH.'/footer.php';
    exit(); //terminate rendering
} elseif ('uncertify' == $op) {
    if (!isset($_POST['is_exec'])) {
        redirect_header('certifyuser.php', 3, _TAKINGBACK);
    }

    // check token ticket
    if (!$xoopsGTicket->check(true, 'xoonips_certify_user_uncertfy')) {
        exit();
    }

    $comment = $_POST['comment'] ?? '';

    $user = [];
    $result_get_account = xnp_get_account($xnpsid, $certify_uid, $user);
    if (RES_OK != $result_get_account) {
        redirect_header('certifyuser.php', 3, _MD_XOONIPS_ACCOUNT_CANNOT_ACQUIRE_USER_INFO);
    }

    xoonips_notification_account_rejected($certify_uid, $comment);

    $user_compo_handler = &xoonips_getormcompohandler('xoonips', 'user');
    $user_compo_handler->deleteAccount($certify_uid);

    $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $event_handler->recordDeleteAccountEvent($certify_uid);
    $event_handler->recordUncertifyAccountEvent($certify_uid, $comment);

    //
    // notify a uncertified to the user by e-mail
    //
    $langman     = &xoonips_getutility('languagemanager');
    $xoopsMailer = getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setTemplateDir($langman->mail_template_dir());
    $xoopsMailer->setTemplate('xoonips_account_uncertified.tpl');
    $xoopsMailer->assign('X_UNAME', $user['uname']);
    $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);
    $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
    $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
    $xoopsMailer->assign('UNCERTIFY_COMMENT', $comment);
    $xoopsMailer->setToEmails([$user['email']]);
    $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
    $xoopsMailer->setFromName($xoopsConfig['sitename']);
    $xoopsMailer->setSubject(_MD_XOONIPS_ACCOUNT_REJECTED);

    if (!$xoopsMailer->send()) {
        redirect_header('certifyuser.php', 3, sprintf(_US_ACTVMAILNG, $textutil->html_special_chars($user['uname'])));
    }

    redirect_header(XOOPS_URL.'/modules/xoonips/certifyuser.php', 3, _MD_XOONIPS_MODERATOR_UNCERTIFY_SUCCESS);
}

$xoopsTpl->assign('op', $op);
$xoopsOption['template_main'] = 'xoonips_certifyuser.html';

$users = [];
$uids = [];
if (0 != xnp_dump_uids($xnpsid, [], $uids)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_ERROR_SELECT_USER);
    exit();
}
if (count($uids) > 0) {
    foreach ($uids as $i) {
        $user = [];
        if (0 == xnp_get_account($xnpsid, $i, $user)) {
            if (1 != @$user['activate'] && 0 != @$user['level']) {
                // list acitvated & uncertified users only
                $users[] = [
                    'uid' => $user['uid'],
                    'uname' => $textutil->html_special_chars($user['uname']),
                    'name' => $textutil->html_special_chars($user['name']),
                    'email' => $textutil->html_special_chars($user['email']),
                ];
            }
        }
    }
    $xoopsTpl->assign('users', $users);

    // token ticket
    $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_certify_user');
    $xoopsTpl->assign('token_ticket', $token_ticket);
}

require XOOPS_ROOT_PATH.'/footer.php';
