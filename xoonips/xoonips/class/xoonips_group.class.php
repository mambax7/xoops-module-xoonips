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

/**
 * XooNIps Group Handler class
 */
class XooNIpsGroupHandler
{
    public $_xgHandler;
    public $_xglHandler;

    /**
     * XooNIpsGroupHandler constructor.
     */
    public function __construct()
    {
        $this->_xgHandler  = xoonips_getOrmHandler('xoonips', 'groups');
        $this->_xglHandler = xoonips_getOrmHandler('xoonips', 'groups_users_link');
    }

    /**
     * check group existance
     *
     * @access public
     * @param string $gname      group name
     * @param int    $except_gid ignoring group id
     * @return bool true if group exists
     */
    public function existsGroup($gname, $except_gid = null)
    {
        $criteria = new CriteriaCompo(new Criteria('gid', GID_DEFAULT, '!='));
        if (null !== $except_gid) {
            $criteria->add(new Criteria('gid', $except_gid, '!='));
        }
        $criteria->add(new Criteria('gname', $gname));
        $cnt = $this->_xgHandler->getCount($criteria);
        return ($cnt != 0);
    }

    /**
     * check user is group member
     *
     * @access public
     * @param int $uid user id
     * @param int $gid group id
     * @return bool true if user is group member
     */
    public function isGroupMember($uid, $gid)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('uid', $uid));
        $criteria->add(new Criteria('gid', $gid));
        $cnt = $this->_xglHandler->getCount($criteria);
        return ($cnt == 0) ? false : true;
    }

    /**
     * check user is group admin
     *
     * @access public
     * @param int $uid user id
     * @param int $gid group id
     * @return bool true if user is group admin
     */
    public function isGroupAdmin($uid, $gid = null)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('uid', $uid));
        $criteria->add(new Criteria('is_admin', 1));
        if (null === $gid) {
            $criteria->add(new Criteria('gid', GID_DEFAULT, '!='));
        } else {
            $criteria->add(new Criteria('gid', $gid));
        }
        $cnt = $this->_xglHandler->getCount($criteria);
        return ($cnt == 0) ? false : true;
    }

    /**
     * get group ids
     *
     * @access public
     * @param int  $uid           user id if null to get all group ids
     * @param bool $is_admin_only true if limit to user is administrator only
     * @return array subscribed group ids
     */
    public function getGroupIds($uid = null, $is_admin_only = false)
    {
        if (null !== $uid) {
            $criteria = new CriteriaCompo(new Criteria('gid', GID_DEFAULT, '!=', 'xgl'));
            $criteria->add(new Criteria('uid', $uid, '=', 'xgl'));
            if ($is_admin_only) {
                $criteria->add(new Criteria('is_admin', 1, '=', 'xgl'));
            }
            $join    = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'INNER', 'xgl');
            $xg_objs =  $this->_xgHandler->getObjects($criteria, false, 'xgl.gid', true, $join);
        } else {
            $criteria = new CriteriaCompo(new Criteria('gid', GID_DEFAULT, '!='));
            $xg_objs  =  $this->_xgHandler->getObjects($criteria, false, 'gid');
        }
        $gids = array();
        foreach ($xg_objs as $xg_obj) {
            $gids[] = $xg_obj->get('gid');
        }
        return $gids;
    }

    /**
     * get group joined user ids
     *
     * @access public
     * @param int  $gid           group id
     * @param bool $is_admin_only true if limit to user is administrator only
     * @retrun array user ids
     * @return array
     */
    public function getUserIds($gid, $is_admin_only = false)
    {
        $criteria = new CriteriaCompo(new Criteria('gid', GID_DEFAULT, '!='));
        $criteria->add(new Criteria('gid', $gid));
        if ($is_admin_only) {
            $criteria->add(new Criteria('is_admin', 1));
        }
        $join = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'u');
        $criteria->setSort('u.uname');
        $criteria->setOrder('ASC');
        $xgl_objs =  $this->_xglHandler->getObjects($criteria, false, 'u.uid', false, $join);
        $uids     = array();
        foreach ($xgl_objs as $xgl_obj) {
            $uids[] = $xgl_obj->get('uid');
        }
        return $uids;
    }

    /**
     * get group root index id
     *
     * @access public
     * @param int $gid
     * @return int index id
     */
    public function getGroupRootIndexId($gid)
    {
        $obj = $this->_xgHandler->get($gid);
        if (!is_object($obj)) {
            return false;
        }
        return $obj->get('group_index_id');
    }

    /**
     * get group root index ids
     *
     * @access public
     * @param int  $uid           user id
     * @param bool $is_admin_only true if limit to user is administrator only
     * @retrun array index ids
     * @return array
     */
    public function getGroupRootIndexIds($uid = null, $is_admin_only = false)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('gid', GID_DEFAULT, '!=', 'xg'));
        if (null !== $uid) {
            $criteria->add(new Criteria('uid', $uid));
            if ($is_admin_only) {
                $criteria->add(new Criteria('is_admin', 1));
            }
        }
        $join     = new XooNIpsJoinCriteria('xoonips_groups', 'gid', 'gid', 'INNER', 'xg');
        $xgl_objs =  $this->_xglHandler->getObjects($criteria, false, 'xg.group_index_id AS gxid', false, $join);
        $gxids    = array();
        foreach ($xgl_objs as $xgl_obj) {
            $gxids[] = $xgl_obj->getExtraVar('gxid');
        }
        return $gxids;
    }

    /**
     * get group item ids
     *
     * @access public
     * @param int $gid group id
     * @param int $uid user id
     * @return array item ids
     */
    public function getGroupItemIds($gid, $uid = null)
    {
        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $join                   = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx');
        $criteria               = new CriteriaCompo(new Criteria('gid', $gid, '=', 'idx'));
        $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib'));
        if (null !== $uid) {
            $criteria->add(new Criteria('uid', $uid, '=', 'ib'));
        }
        $res  =  $index_item_linkHandler->open($criteria, 'ib.item_id', true, $join);
        $iids = array();
        while ($xil_obj = $index_item_linkHandler->getNext($res)) {
            $iids[] = $xil_obj->get('item_id');
        }
        $index_item_linkHandler->close($res);
        return $iids;
    }

    /**
     * get group object
     *
     * @access public
     * @param int $gid group id
     * @return object instance of XooNIpsOrmGroups
     */
    public function &getGroupObject($gid)
    {
        $temp = $this->_xgHandler->get($gid);
        return $temp;
    }

    /**
     * get group objects
     *
     * @access public
     * @param array $gids array of group ids
     * @return array object instance array of XooNIpsOrmGroups
     */
    public function &getGroupObjects($gids = null)
    {
        $criteria = new CriteriaCompo(new Criteria('gid', GID_DEFAULT, '!='));
        if (null !== $gids) {
            $criteria->add(new Criteria('gid', '(' . implode(',', $gids) . ')', 'IN'));
        }
        $criteria->setSort('gname');
        $criteria->setOrder('ASC');
        return $this->_xgHandler->getObjects($criteria);
    }
}
