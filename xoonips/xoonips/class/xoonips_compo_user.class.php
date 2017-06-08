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

require_once XOOPS_ROOT_PATH . '/modules/xoonips/class/base/relatedobject.class.php';

/**
 * Class XooNIpsUserCompoHandler
 */
class XooNIpsUserCompoHandler extends XooNIpsRelatedObjectHandler
{
    /**
     * XooNIpsUserCompoHandler constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $uHandler  = xoonips_getOrmHandler('xoonips', 'xoops_users');
        $xuHandler = xoonips_getOrmHandler('xoonips', 'users');
        parent::__construct($db);
        parent::__initHandler('xoops_user', $uHandler, 'uid');
        $this->addHandler('xoonips_user', $xuHandler, 'uid');
    }

    /**
     * @return XooNIpsUserCompo
     */
    public function create()
    {
        $user = new XooNIpsUserCompo();
        return $user;
    }

    /**
     *
     * @access public
     * @param int $uid uid of transferee
     * @return true if uid is activated and certified user.
     *
     */
    public function isCertifiedUser($uid)
    {
        $c = new CriteriaCompo();
        $c->add(new Criteria('uid', (int)$uid));
        $c->add(new Criteria('level', 1, '>='));
        $rows =&  $this->getObjects($c);
        if ($rows && count($rows) == 1) {
            $user = $rows[0]->getVar('xoonips_user');
            return $user->get('activate') == 1;
        }
        return false;
    }

    /**
     * delete user account and related data
     * - delete user account
     * - delete user's items
     * - delete user's private indexes
     * - remove user from groups
     * - remove user from xoonips groups
     * - remove user from notifications
     * @access public
     * @param int $uid uid to be deleted
     *
     * @return bool
     */
    public function deleteAccount($uid)
    {
        $criteria = new Criteria('uid', (int)$uid);

        //delete user's item
        $item_typeHandler = xoonips_getOrmHandler('xoonips', 'item_type');
        foreach ($item_typeHandler->getObjects() as $itemtype) {
            if ($itemtype->get('item_type_id') == ITID_INDEX) {
                continue;
            }
            $itemHandler = xoonips_getOrmCompoHandler($itemtype->get('name'), 'item');
            if (!$itemHandler) {
                continue;
            }
            foreach ($itemHandler->getObjects($criteria) as $item) {
                $itemHandler->delete($item);
            }
        }

        //remove user from groups
        $memberHandler = xoops_getHandler('member');
        if ($memberHandler->getUser($uid)) {
            $memberHandler->deleteUser($memberHandler->getUser($uid));
        }

        //remove user from xoonips groups
        $xgroups_users_linkHandler = xoonips_getOrmHandler('xoonips', 'groups_users_link');
        $xgroups_users_linkHandler->deleteAll($criteria);

        //delete index
        $index_compoHandler = xoonips_getOrmCompoHandler('xoonips', 'index');
        foreach ($index_compoHandler->getObjects($criteria) as $index) {
            $index_compoHandler->delete($index);
        }

        //remove user from notifications
        $notificationHandler = xoops_getHandler('notification');
        $notificationHandler->deleteAll(new Criteria('not_uid', (int)$uid));

        //delete xoonips user
        $xuHandler = xoonips_getOrmHandler('xoonips', 'users');
        $xuHandler->deleteAll($criteria);

        return true;
    }
}

/**
 * Class XooNIpsUserCompo
 */
class XooNIpsUserCompo extends XooNIpsRelatedObject
{
    /**
     * XooNIpsUserCompo constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $uHandler  = xoonips_getOrmHandler('xoonips', 'xoops_users');
        $u_obj     = $uHandler->create();
        $xuHandler = xoonips_getOrmHandler('xoonips', 'users');
        $xu_obj    = $xuHandler->create();
        $this->initVar('xoops_user', $u_obj, 'uid', true);
        $this->initVar('xoonips_user', $xu_obj, 'uid', true);
    }
}
