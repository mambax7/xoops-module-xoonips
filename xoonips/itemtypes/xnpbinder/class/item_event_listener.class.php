<?php
// $Revision: 1.1.1.7 $
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

require_once dirname(dirname(__DIR__)) . '/xoonips/class/base/itemeventlistener.class.php';
require_once dirname(dirname(__DIR__)) . '/xoonips/include/notification.inc.php';

/**
 * Class XNPBinderItemEventListener
 */
class XNPBinderItemEventListener extends XooNIpsItemEventListener
{
    /**
     * @param $item_id
     */
    public function onDelete($item_id)
    {
        //trigger_error( "Binder onDelete( $item_id )" );
        $bilinkHandler = xoonips_getOrmHandler('xnpbinder', 'binder_item_link');
        $criteria      = new Criteria('item_id', $item_id);
        $bilinks       = $bilinkHandler->getObjects($criteria);
        if (!$bilinks) {
            return;
        }

        foreach ($bilinks as $bilink) {
            $child_items = $bilinkHandler->getObjects(new Criteria('binder_id', $bilink->get('binder_id')));
            if (!$child_items) {
                continue;
            }

            $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
            $join                   = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
            $criteria               = new CriteriaCompo(new Criteria('open_level', OL_PUBLIC));
            $criteria->add(new Criteria('certify_state', CERTIFIED));
            $criteria->add(new Criteria('item_id', $bilink->get('binder_id')));
            $index_item_links = $index_item_linkHandler->getObjects($criteria, false, '', false, $join);
            if (empty($index_item_links)) {
                continue;
            }

            if (count($child_items) == 1) {
                $itemHandler = xoonips_getOrmCompoHandler('xnpbinder', 'item');
                $binder      = $itemHandler->get($bilink->get('binder_id'));
                $basic       = $binder->getVar('basic');

                // define tags here for notification message
                $tags = xoonips_notification_get_item_tags($basic->get('item_id'));

                $mhandler = xoops_getHandler('module');
                $module   = $mhandler->getByDirName('xnpbinder');

                $nhandler = xoonips_gethandler('xoonips', 'notification');
                $nhandler->triggerEvent2('user', 0, 'item_updated', _MD_XNPBINDER_USER_CONTENT_EMPTY_NOTIFYSBJ,
                                         $nhandler->getTemplateDirByMid($module->mid()), 'user_content_empty_notify', $tags,
                                         array($basic->get('uid')));
            }
            if (!$bilinkHandler->delete($bilink)) {
                die('cannnot remove a deleted item from a binder.');
            }
        }
    }

    public function _notify()
    {
        /*
  from XooNIpsLogicRemoveItem
        // if public binder becomes empty, notify to moderator.
        $empty_binder_ids = array();
        $bilinkHandler = xoonips_getOrmHandler('xoonips', 'binder_item_link');
        $xilinkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $criteria = new Criteria('item_id', $item_id);
        $bilinks = $bilinkHandler->getObjects($criteria);
        if ($bilinks) {
            foreach($bilinks as $bilink) {
                $binder_id = $bilink->get('binder_id');
                $ct = $bilinkHandler->getCount(new Criteria('binder_id', $binder_id));
                if ($ct == 1) { // deleting the last item. binder becomes empty.
                    $criteria = new CriteriaCompo(new Criteria('index_id', IID_BINDERS));
                    $criteria->add(new Criteria('item_id', $binder_id));
                    $criteria->add(new Criteria('certify_state', CERTIFIED));
                    if ($xilinkHandler->getCount($criteria)) { // public and certified
                        $empty_binder_ids[] = $binder_id; // notify later

                    }
                }
            }
        }
*/
        /*
          from XooNIpsLogicRemoveItem
                $notificationHandler =  xoops_getHandler('notification');
                foreach($empty_binder_ids as $binder_id) {
                    $binder = $itemHandler->get($binder_id);
                    $basic = $binder->getVar('basic');
                    $titles = $binder->getVar('titles');
                    //define tags here for notification message
                    $tags = array();
                    $tags['URL'] = XOOPS_URL . "/modules/xoonips/detail.php?item_id=" . $basic->get('item_id');
                    $tags['TITLE'] = $titles[0]->get('title');
                    $notificationHandler->triggerEvent('administrator', 0, 'binder_content_empty', $tags);
                }
        */
    }
}
