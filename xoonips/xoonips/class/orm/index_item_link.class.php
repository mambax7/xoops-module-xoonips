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

/**
 * @brief data object of index item link
 *
 * @li    getVar('index_item_link_id') :
 * @li    getVar('index_id') :
 * @li    getVar('item_id') :
 * @li    getVar('certify_state') :
 */
class XooNIpsOrmIndexItemLink extends XooNIpsTableObject
{
    /**
     * XooNIpsOrmIndexItemLink constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('index_item_link_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('index_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('certify_state', XOBJ_DTYPE_INT, 0, false);
    }
}

/**
 * @brief handler object of index item link
 */
class XooNIpsOrmIndexItemLinkHandler extends XooNIpsTableObjectHandler
{
    /**
     * XooNIpsOrmIndexItemLinkHandler constructor.
     * @param XoopsDatabase $db
     */
    public function __construct($db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmIndexItemLink', 'xoonips_index_item_link', 'index_item_link_id');
    }

    /**
     * get index_id(s) that the item is registerd(certify state is ignored)
     * @param $item_id     id of item
     * @param $open_levels array of open levels of index to get
     * @return array of index id(s)
     */
    public function getIndexIdsByItemId($item_id, $open_levels
    = array(
        OL_PRIVATE,
        OL_GROUP_ONLY,
        OL_PUBLIC,
    )
    ) {
        $join     = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tindex');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', '(' . implode(',', $open_levels) . ')', 'IN'));
        $indexes =&  $this->getObjects($criteria, false, '', false, $join);

        $ret = array();
        foreach ($indexes as $i) {
            $ret[] = $i->get('index_id');
        }

        return $ret;
    }

    /**
     * get index_item_link object(s) that the item is registerd(certify state is ignored)
     * @param $item_id     id of item
     * @param $open_levels array of open levels of index to get
     * @return array of index_item_link object(s)
     */
    public function getByItemId($item_id, $open_levels
    = array(
        OL_PRIVATE,
        OL_GROUP_ONLY,
        OL_PUBLIC,
    )
    ) {
        $join     = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tindex');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', '(' . implode(',', $open_levels) . ')', 'IN'));
        $objs =&  $this->getObjects($criteria, false, '', false, $join);

        return $objs;
    }

    /**
     * Get array of item id of private items.
     * Its returns private items except public and group shared items.
     * @param $uid  integer user id
     * @param array of integer of item id(s)
     * @return array
     */
    public function getAllPrivateOnlyItemId($uid)
    {
        // for xnp_get_private_item_id
        require_once XOOPS_ROOT_PATH . '/modules/xoonips/include/AL.php';
        $iids = array();
        if (RES_OK == xnp_get_private_item_id($_SESSION['XNPSID'], $uid, $iids)) {
            return $iids;
        }
        return array();
    }

    /**
     * get ids of private-only or not certified in any group/public index item.
     * useful for private item number/storage limit check.
     * @param uid
     * @return array of integer of item id(s)
     */
    public function getPrivateItemIdsByUid($uid)
    {
        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $join                   = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id');
        $criteria               = new Criteria('uid', $uid);
        $index_item_links       =  $index_item_linkHandler->getObjects($criteria, false, '', null, $join);
        $iids                   = array();
        $certified_iids         = array();
        foreach ($index_item_links as $index_item_link) {
            $item_id        = $index_item_link->get('item_id');
            $iids[$item_id] = $item_id;
            if ($index_item_link->get('certify_state') == CERTIFIED) {
                $certified_iids[$item_id] = $item_id;
            }
        }
        $private_iids = array_diff_assoc($iids, $certified_iids);
        return array_keys($private_iids);
    }

    /**
     * Get array of XooNIpsOrmIndexItemLink registerd in the index
     * It returns XooNIpsOrmIndexItemLink of only READABLE item by user of $uid
     * @param integer $index_id index id
     * @param integer $uid      user id to READ these items
     * @return array of XooNIpsOrmIndexItemLink(index_item_link_id is a key of an array)
     */
    public function getByIndexId($index_id, $uid)
    {
        $criteria = new CriteriaCompo(new Criteria('index_id', (int)$index_id));
        $result   = array();
        $links    =&  $this->getObjects($criteria);
        foreach ($links as $link) {
            $xoonips_itemHandler = xoonips_getOrmCompoHandler('xoonips', 'item');
            if (!$xoonips_itemHandler->getPerm($link->get('item_id'), (int)$uid, 'read')) {
                continue;
            }
            $result[$link->get('index_item_link_id')] = $link;
        }
        return $result;
    }

    /**
     * get XooNIpsOrmIndexItemLink object having specified index_id and item_id
     *
     * @access   public
     * @param id $index_id
     * @param id $item_id
     * @return bool|XooNIpsOrmIndexItemLink
     * @internal param id $index_id of index
     * @internal param id $item_id of item
     */
    public function getByIndexIdAndItemId($index_id, $item_id)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('index_id', $index_id));
        $criteria->add(new Criteria('item_id', $item_id));
        $index_item_links =&  $this->getObjects($criteria);
        if (empty($index_item_links)) {
            $ret = false;
            return $ret;
        }
        return $index_item_links[0];
    }

    /**
     *
     * return true if permitted to this item
     *
     * @access   public
     * @param id                     $index_id
     * @param id                     $item_id
     * @param                        uid       uid who access to this item
     * @param accept|reject|withdraw $operation
     * @return true if permitted
     *
     * @internal param id $index_id of index
     * @internal param id $item_id of item
     * @internal param accept|reject|withdraw $operation
     */
    public function getPerm($index_id, $item_id, $uid, $operation)
    {
        if (!in_array($operation, array(
            'accept',
            'reject',
            'withdraw'
        ))
        ) {
            // bad operation
            return false;
        }

        if ($uid == UID_GUEST) {
            // guest cannot accept/reject/withdraw
            return false;
        }

        // cannot accept/reject/withdraw to private index
        $indexHandler = xoonips_getOrmHandler('xoonips', 'index');
        $index        = $indexHandler->get($index_id);
        if ($index === false || $index->get('open_level') == OL_PRIVATE) {
            return false;
        }

        if ($operation == 'withdraw') {
            $item_lockHandler = xoonips_getOrmHandler('xoonips', 'item_lock');
            if ($item_lockHandler->isLocked($item_id)) {
                // cannot withdraw locked item
                return false;
            }
        }

        // get certify_state
        $index_item_link =  $this->getByIndexIdAndItemId($index_id, $item_id);
        if ($index_item_link == false) {
            // no such index_item_link
            return false;
        }
        $certify_state = $index_item_link->get('certify_state');

        if ($certify_state == CERTIFY_REQUIRED && $operation == 'accept' || $certify_state == CERTIFY_REQUIRED && $operation == 'reject'
            || $certify_state == CERTIFIED && $operation == 'withdraw'
        ) {

            // moderator or admin?
            $memberHandler = xoonips_getHandler('xoonips', 'member');
            if ($memberHandler->isModerator($uid) || $memberHandler->isAdmin($uid)) {
                return true;
            }

            // group admin && group index ?
            if ($index->get('open_level') == OL_GROUP_ONLY) {
                $xgroupHandler = xoonips_getHandler('xoonips', 'group');
                if ($xgroupHandler->isGroupAdmin($uid, $index->get('gid'))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * find whether that user have permission to read private index of the item
     * @access public
     * @param int $item_id
     * @param int $uid
     * @return bool
     */
    public function privateIndexReadable($item_id, $uid)
    {
        $join                   = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER');
        $indexHandler           = xoonips_getOrmHandler('xoonips', 'index');
        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $criteria               = new CriteriaCompo(new Criteria('item_id', (int)$item_id));
        $criteria->add(new Criteria('open_level', OL_PRIVATE));
        $index_item_links =  $index_item_linkHandler->getObjects($criteria, false, '', false, $join);
        foreach ($index_item_links as $link) {
            if (!$indexHandler->getPerm($link->get('index_id'), $uid, 'read')) {
                return false;
            }
        }
        return true;
    }

    /**
     * get item ids of group shared or public item(ignore certify state)
     * @access public
     * @param int $uid user id item
     * @return array array of item id
     */
    public function getNonPrivateItemIds($uid)
    {
        $result   = array();
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('open_level', '(' . implode(',', array(
                                                    OL_GROUP_ONLY,
                                                    OL_PUBLIC
                                                )) . ')', 'IN'));
        $criteria->add(new Criteria('uid', $uid, '=', 'basic'));
        $criteria->setSort('basic.item_id');
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
        $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic'));
        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        foreach ($index_item_linkHandler->getObjects($criteria, false, '', true, $join) as $index_item_link) {
            $result[] = $index_item_link->get('item_id');
        }
        return $result;
    }

    /**
     * add item to index.
     * @access public
     * @param int $index_id      index to add item
     * @param int $item_id       item to be added to index
     * @param int $certify_state certify_state(optional)
     * @return bool
     */
    public function add($index_id, $item_id, $certify_state)
    {
        if (!in_array($certify_state, array(
            NOT_CERTIFIED,
            CERTIFY_REQUIRED,
            CERTIFIED
        ))
        ) {
            trigger_error("unknown certify_state: $certify_state");
            return false;
        }

        if ($this->getByIndexIdAndItemId($index_id, $item_id)) {
            // already exists
            return true;
        }

        $obj =  $this->create();
        $obj->set('index_id', $index_id);
        $obj->set('item_id', $item_id);
        $obj->set('certify_state', $certify_state);

        return $this->insert($obj);
    }
}
