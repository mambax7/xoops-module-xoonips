<?php
// $Revision: 1.1.4.1.2.15 $
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

// check token ticket
require_once __DIR__ . '/../../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_item_delete';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
}

// load libraries
require __DIR__ . '/../../include/lib.php';
require __DIR__ . '/../../include/libitem.php';
require_once __DIR__ . '/../../class/base/logicfactory.class.php';
require_once __DIR__ . '/../../class/xoonipsresponse.class.php';
require_once __DIR__ . '/../../class/xoonipserror.class.php';
require_once __DIR__ . '/../../class/xoonips_item_event_dispatcher.class.php';

// get _MD_XOONIPS_NOTIFICATION_*SBJ
$langman = xoonips_getUtility('languagemanager');
$langman->read('main.php');

// get requests
$get_keys = array(
    'tree' => array(
        'i',
        true,
        false,
    ),
);
$get_vals = xoonips_admin_get_requests('post', $get_keys);
$tree_ids = $get_vals['tree'];

// function
/**
 * @param $iid
 * @return bool
 */
function xoonips_admin_maintenance_item_delete_item($iid)
{
    $factory           = new XooNIpsLogicFactory();
    $remove_item_logic = $factory->create('removeItem');
    $vars              = array(
        $_SESSION['XNPSID'],
        $iid,
        'item_id',
    );
    $response          = new XooNIpsResponse();
    $remove_item_logic->execute($vars, $response);
    return $response->getResult();
}

/**
 *
 * @brief unlock item
 *
 * @param [in] $item_id item id
 */
function xoonips_admin_maintenance_item_unlock_item($item_id)
{
    // unlock item
    $indexHandler           = xoonips_getOrmHandler('xoonips', 'index');
    $item_lockHandler       = xoonips_getOrmHandler('xoonips', 'item_lock');
    $item_basicHandler      = xoonips_getOrmHandler('xoonips', 'item_basic');
    $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
    $event_logHandler       = xoonips_getOrmHandler('xoonips', 'event_log');
    if ($item_lockHandler->isLocked($item_id)) {
        $lock_type = $item_lockHandler->getLockType($item_id);
        if ($lock_type == XOONIPS_LOCK_TYPE_CERTIFY_REQUEST) {
            $index_item_links =  $index_item_linkHandler->getObjects(new Criteria('item_id', $item_id));
            foreach ($index_item_links as $index_item_link) {
                if ($index_item_link->get('certify_state') == CERTIFY_REQUIRED) {
                    $index_id = $index_item_link->get('index_id');
                    $index    = $indexHandler->get($index_id);
                    if ($index->getVar('open_level', 'n') == OL_PUBLIC || $index->getVar('open_level', 'n') == OL_GROUP_ONLY) {
                        $item_basicHandler->unlockItemAndIndexes($item_id, $index_id);
                        $event_logHandler->recordRejectItemEvent($item_id, $index_id);
                        $index_item_linkHandler->delete($index_item_link);
                        xoonips_notification_item_rejected($item_id, $index_id);
                    }
                }
            }
        } else {
            // TODO: unlock if transfer request
        }
    }
}

// logic
$empty_tree_ids = true;
$results        = array();
$xnpsid         = $_SESSION['XNPSID'];

if (count($tree_ids) > 0) {
    $textutil = xoonips_getUtility('text');
    $treelist = xnpitmgrListIndexTree(XNPITMGR_LISTMODE_PRIVATEONLY);
    $treemap  = array();
    foreach ($treelist as $tree) {
        $treemap[$tree['id']] = $tree['fullpath'];
    }
    $empty_tree_ids = false;
    $evenodd        = 'odd';

    // execute item delete
    foreach ($tree_ids as $xid) {
        $succeed = 0;
        $failed  = 0;
        $iids    = xnpitmgrListIndexItems(array($xid));
        if ($iids === false) {
            // no item in tree
            continue;
        }
        foreach ($iids as $iid) {
            xoonips_admin_maintenance_item_unlock_item($iid);

            if (xoonips_admin_maintenance_item_delete_item($iid)) {
                // succeed
                $succeed++;
            } else {
                // error occured
                $failed++;
            }
        }
        $results[] = array(
            'id'      => $xid,
            'evenodd' => $evenodd,
            'index'   => $textutil->html_special_chars($treemap[$xid]),
            'succeed' => $succeed,
            'failed'  => $failed,
        );
        $evenodd   = ($evenodd === 'even') ? 'odd' : 'even';
    }
}

// title
$title = _AM_XOONIPS_MAINTENANCE_ITEM_DUPDATE_TITLE;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type'  => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url'   => $xoonips_admin['admin_url'] . '/',
    ),
    array(
        'type'  => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_TITLE,
        'url'   => $xoonips_admin['myfile_url'],
    ),
    array(
        'type'  => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_ITEM_TITLE,
        'url'   => $xoonips_admin['mypage_url'],
    ),
    array(
        'type'  => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_ITEM_DELETE_TITLE,
        'url'   => $xoonips_admin['mypage_url'] . '&amp;action=delete',
    ),
    array(
        'type'  => 'label',
        'label' => $title,
        'url'   => '',
    ),
);

// templates
require_once __DIR__ . '/../../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBasedir('templates');
$tmpl->readTemplatesFromFile('maintenance_item_dupdate.tmpl.tpl');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'index', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_INDEX);
$tmpl->addVar('main', 'succeed', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_SUCCEED);
$tmpl->addVar('main', 'failed', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_FAILED);
if ($empty_tree_ids) {
    $tmpl->setAttribute('empty_results', 'visibility', 'visible');
    $tmpl->setAttribute('results', 'visibility', 'hidden');
    $tmpl->addVar('empty_results', 'empty', _AM_XOONIPS_MAINTENANCE_ITEM_INDEX_EMPTY);
} else {
    $tmpl->addRows('results', $results);
}
$tmpl->addVar('main', 'back', _AM_XOONIPS_LABEL_BACK);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
