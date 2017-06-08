<?php
// $Revision: 1.1.2.5 $
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
require __DIR__ . '/include/common.inc.php';
require_once __DIR__ . '/include/lib.php';
require __DIR__ . '/class/base/gtickets.php';

$xnpsid   = $_SESSION['XNPSID'];
$formdata = xoonips_getUtility('formdata');
$textutil = xoonips_getUtility('text');

// If not a user, redirect
if (!$xoopsUser) {
    redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
}

$uid = $_SESSION['xoopsUserId'];

// Only Moderator can access this page.
$memberHandler = xoonips_getHandler('xoonips', 'member');
if (!$memberHandler->isModerator($uid)) {
    redirect_header(XOOPS_URL . '/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
}

// get requests
$op = $formdata->getValue('post', 'op', 's', false, '');

$index_ids      = $formdata->getValueArray('post', 'index_ids', 'i', false);
$group_index_id = $formdata->getValue('post', 'group_index_id', 'i', false);
// check request variables
if ($op === 'certify' || $op === 'uncertify') {
    if ($group_index_id == 0) {
        die('illegal request');
    }
} elseif ($op != '') {
    die('illegal request');
}

// pankuzu for administrator
$pankuzu = _MI_XOONIPS_ACCOUNT_PANKUZU_MODERATOR . _MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR . _MI_XOONIPS_ITEM_PANKUZU_CERTIFY_GROUP_PUBLIC_ITEMS;

if ($op === 'certify') {
    if (!$xoopsGTicket->check(true, 'xoonips_group_certify_index')) {
        exit();
    }

    certify($index_ids, $group_index_id);
    exit();
} elseif ($op === 'uncertify') {
    if (!$xoopsGTicket->check(true, 'xoonips_group_certify_index')) {
        exit();
    }

    uncertify($index_ids, $group_index_id);
    exit();
}

$group_indexes                 = array();
$index_compoHandler            = xoonips_getOrmCompoHandler('xoonips', 'index');
$index_group_index_linkHandler = xoonips_getOrmHandler('xoonips', 'index_group_index_link');
foreach ($index_group_index_linkHandler->getObjects() as $link) {
    if (!array_key_exists($link->get('group_index_id'), $group_indexes)) {
        $group_index_path = $index_compoHandler->getIndexPathNames($link->get('group_index_id'));
        if (!$group_index_path) {
            continue;
        }
        $group_indexes[$link->get('group_index_id')] = array(
            'group_index_id'   => $link->get('group_index_id'),
            'indexes'          => array(),
            'group_index_path' => $textutil->html_special_chars('/' . implode('/', $group_index_path)),
        );
    }
    array_push($group_indexes[$link->get('group_index_id')]['indexes'], array(
        'id'   => $link->get('index_id'),
        'path' => $textutil->html_special_chars('/' . implode('/', $index_compoHandler->getIndexPathNames($link->get('index_id'))))
    ));
}

$GLOBALS['xoopsOption']['template_main'] = 'xoonips_groupcertify.tpl';
require XOOPS_ROOT_PATH . '/header.php';

$xoopsTpl->assign('pankuzu', $pankuzu);
$xoopsTpl->assign('certify_button_label', _MD_XOONIPS_ITEM_CERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('uncertify_button_label', _MD_XOONIPS_ITEM_UNCERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('group_index_label', _MD_XOONIPS_ITEM_GROUP_INDEX_LABEL);
$xoopsTpl->assign('index_label', _MD_XOONIPS_ITEM_INDEX_LABEL);
if (count($group_indexes) > 0) {
    $xoopsTpl->assign('group_indexes', $group_indexes);
}
$xoopsTpl->assign('xoonips_editprofile_url', XOOPS_URL . '/modules/xoonips/edituser.php?uid=' . $uid);
// token ticket
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_group_certify_index');
$xoopsTpl->assign('token_ticket', $token_ticket);

require XOOPS_ROOT_PATH . '/footer.php';

/**
 * @param $to_index_ids
 * @param $group_index_id
 */
function certify($to_index_ids, $group_index_id)
{
    // transaction
    require_once __DIR__ . '/class/base/transaction.class.php';
    $transaction = XooNIpsTransaction::getInstance();
    $transaction->start();

    $index_group_index_linkHandler = xoonips_getOrmHandler('xoonips', 'index_group_index_link');
    foreach ($to_index_ids as $to_index_id) {
        if (!$index_group_index_linkHandler->makePublic($to_index_id, array($group_index_id))) {
            $transaction->rollback();
            redirect_header(XOOPS_URL . '/', 3, _MD_XOONIPS_GROUP_TREE_TO_PUBLIC_INDEX_TREE_FAILED);
            exit();
        }
    }
    $eventlogHandler = xoonips_getOrmHandler('xoonips', 'event_log');
    $eventlogHandler->recordCertifyGroupIndexEvent($group_index_id);

    $transaction->commit();
    $index_group_index_linkHandler->notifyMakePublicGroupIndex($to_index_ids, array($group_index_id), 'group_item_certified');
    redirect_header(XOOPS_URL . '/modules/xoonips/groupcertify.php', 3, 'Succeed');
}

/**
 * @param $to_index_ids
 * @param $group_index_id
 */
function uncertify($to_index_ids, $group_index_id)
{
    // transaction
    require_once __DIR__ . '/class/base/transaction.class.php';
    $transaction = XooNIpsTransaction::getInstance();
    $transaction->start();

    $index_group_index_linkHandler = xoonips_getOrmHandler('xoonips', 'index_group_index_link');
    foreach ($to_index_ids as $to_index_id) {
        if (!$index_group_index_linkHandler->rejectMakePublic($to_index_id, array($group_index_id))) {
            $transaction->rollback();
            redirect_header(XOOPS_URL . '/', 3, _MD_XOONIPS_GROUP_TREE_TO_PUBLIC_INDEX_TREE_FAILED);
        }
    }
    $index_group_index_linkHandler->notifyMakePublicGroupIndex($to_index_ids, array($group_index_id), 'group_item_rejected');

    $eventlogHandler = xoonips_getOrmHandler('xoonips', 'event_log');
    $eventlogHandler->recordRejectGroupIndexEvent($group_index_id);

    $transaction->commit();
    redirect_header(XOOPS_URL . '/modules/xoonips/groupcertify.php', 3, 'Succeed');
}
