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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/relatedobject.class.php';

define('XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL', 'transfer_item_detail');
define('XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST', 'transfer_item_list');
define('XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL', 'item_detail');
define('XOONIPS_TEMPLATE_TYPE_ITEM_LIST', 'item_list');

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Handlers
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief class of item data object handler.
 *
 *
 * @li    base class of items depends on xoonips_item_basic table.
 */
class XooNIpsItemCompoHandler extends XooNIpsRelatedObjectHandler
{
    public $db = null;

    /**
     * XooNIpsItemCompoHandler constructor.
     *
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        parent::__construct($db);
        parent::__initHandler('basic', xoonips_getOrmHandler('xoonips', 'item_basic'), 'item_id');
        $ormTitleHandler = xoonips_getOrmHandler('xoonips', 'title');
        $this->addHandler('titles', $ormTitleHandler, 'item_id', true);
        $ormKeywordHandler = xoonips_getOrmHandler('xoonips', 'keyword');
        $this->addHandler('keywords', $ormKeywordHandler, 'item_id', true);
        $ormItemLinkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $this->addHandler('indexes', $ormItemLinkHandler, 'item_id', true);
        $ormChangelogHandler = xoonips_getOrmHandler('xoonips', 'changelog');
        $this->addHandler('changelogs', $ormChangelogHandler, 'item_id', true);
        $ormRelatedtoHandler = xoonips_getOrmHandler('xoonips', 'related_to');
        $this->addHandler('related_tos', $ormRelatedtoHandler, 'parent_id', true);
    }

    /**
     * @return XooNIpsItemCompo
     */
    public function create()
    {
        $item = new XooNIpsItemCompo();

        return $item;
    }

    /**
     * gets a value object.
     *
     * @param $id
     *
     * @return bool
     *
     * @internal param string $ext_id extended item_id
     * @retval   XooNIpsItemCompo
     * @retval   false
     */
    public function &getByExtId($id)
    {
        static $falseVar = false;

        $handler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $objs = $handler->getObjects(new Criteria('doi', addslashes($id)));
        if (!$objs || 1 != count($objs)) {
            return $falseVar;
        }

        $obj = $this->get($objs[0]->get('item_id'));
        if ($obj) {
            return $obj;
        }

        return $falseVar;
    }

    /**
     * return true if permitted to this item.
     *
     * @param                   id        id of item
     * @param                   uid       uid who access to this item
     * @param delete|read|write $operation
     *
     * @return true if permitted
     *
     * @internal param delete|read|write $operation
     */
    public function getPerm($id, $uid, $operation)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getPerm($id, $uid, $operation);
    }

    /**
     * get parent item ids.
     *
     * @param $item_id
     *
     * @return array
     *
     * @internal param item_id $int
     */
    public function getParentItemIds($item_id)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getParentItemIds($item_id);
    }

    /**
     * @param $item_id
     *
     * @return mixed
     */
    public function getItemAbstractTextById($item_id)
    {
        $handler = xoonips_getOrmCompoHandler('xoonips', 'item');
        $item = $handler->get($item_id);

        return $item->getItemAbstractText();
    }

    /**
     * return url to show item detail.
     *
     * @param $item_id
     *
     * @return string
     */
    public function getItemDetailUrl($item_id)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getItemDetailUrl($item_id);
    }
}

/**
 * @brief class of item data object handler according to iteminfo
 *
 *
 * @li    base class of items depends on xoonips_item_basic table.
 */
class XooNIpsItemInfoCompoHandler extends XooNIpsRelatedObjectHandler
{
    public $iteminfo = null;
    public $db = null;

    /**
     * XooNIpsItemInfoCompoHandler constructor.
     *
     * @param      $db
     * @param null $module
     */
    public function __construct($db, $module = null)
    {
        parent::__construct($db);
        $this->db = $db;
        if (isset($module) && null === $this->iteminfo) {
            require XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = $iteminfo;
            //
            // add orm handler according to $iteminfo['orm']
            foreach ($this->iteminfo['orm'] as $orminfo) {
                if ($orminfo['field'] == $this->iteminfo['ormcompo']['primary_orm']) { //orm of primary table
                    parent::__initHandler($orminfo['field'], xoonips_getOrmHandler($orminfo['module'], $orminfo['name']), $orminfo['foreign_key']);
                } else {
                    $this->addHandler($orminfo['field'], xoonips_getOrmHandler($orminfo['module'], $orminfo['name']), $orminfo['foreign_key'],
                                      isset($orminfo['multiple']) ? $orminfo['multiple'] : false,
                                      isset($orminfo['criteria']) ? $orminfo['criteria'] : null);
                }
            }
        }
    }

    /**
     * gets a value object.
     *
     * @param $id
     *
     * @return bool
     *
     * @internal param string $ext_id extended item_id
     * @retval   XooNIpsItemInfoCompo
     * @retval   false
     */
    public function &getByExtId($id)
    {
        static $falseVar = false;

        $handler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $objs = $handler->getObjects(new Criteria('doi', addslashes($id)));
        if (!$objs || 1 != count($objs)) {
            return $falseVar;
        }

        $obj = $this->get($objs[0]->get('item_id'));
        if ($obj) {
            return $obj;
        }

        return $falseVar;
    }

    /**
     * return true if permitted to this item.
     *
     * @param id $item_id
     * @param                          uid       uid who access to this item
     * @param delete|export|read|write $operation
     *
     * @return true if permitted
     *
     * @internal param id $item_id of item
     * @internal param delete|export|read|write $operation
     */
    public function getPerm($item_id, $uid, $operation)
    {
        if (!in_array($operation, array(
            'read',
            'write',
            'delete',
            'export',
        ))
        ) {
            return false; // bad operation
        }

        $item_basicHandler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $item_basic = $item_basicHandler->get($item_id);
        if (!$item_basic || $item_basic->get('item_type_id') == ITID_INDEX) {
            return false; // no such item
        }

        $item_lockHandler = xoonips_getOrmHandler('xoonips', 'item_lock');
        if (($operation == 'write' || $operation == 'delete') && $item_lockHandler->isLocked($item_id)) {
            return false; // cannot write/delete locked item
        }

        $index_group_index_linkHandler = xoonips_getOrmHandler('xoonips', 'index_group_index_link');
        if (($operation == 'write' || $operation == 'delete')
            && $index_group_index_linkHandler->getObjectsByItemId($item_id)
        ) {
            //cannot write/delete if item is in group index that is publication required.
            return false;
        }

        // moderator or admin
        $memberHandler = xoonips_getHandler('xoonips', 'member');
        if ($memberHandler->isModerator($uid) || $memberHandler->isAdmin($uid)) {
            return true; // moderator or admin
        }

        if ($uid == UID_GUEST) {
            $xconfigHandler = xoonips_getOrmHandler('xoonips', 'config');
            $target_user = $xconfigHandler->getValue(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY);
            if ($target_user != XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL) {
                return false; // guest not allowed
            }
            // only allowed to read public certified item
            if ($operation != 'read') {
                return false;
            }
        }

        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        if ($operation == 'write') {
            // update: item.uid == $uid
            //permit owner
            if ($item_basic->get('uid') == $uid) {
                return true;
            }
            //permit group admin if group share certified
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY));
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'));
            $criteria->add(new Criteria('is_admin', 1));
            $criteria->add(new Criteria('certify_state', CERTIFIED));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = $index_item_linkHandler->getObjects($criteria, false, '', null, $join1);
            if ($index_item_links) {
                return true;
            }

            return false;
        } elseif ($operation == 'delete') {
            // delete: item.uid == $uid && not_group && not_public
            if ($item_basic->get('uid') != $uid) {
                return false;
            }

            // get non-private index_item_link
            // index_item_link <- index
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_PRIVATE, '!='));
            $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
            $index_item_links = $index_item_linkHandler->getObjects($criteria, false, '', null, $join);

            return count($index_item_links) == 0;
        } elseif ($operation == 'export') {
            // export: item.uid == $uid || group && group admin
            if ($item_basic->get('uid') == $uid) {
                return true;
            }

            // group && group admin ?
            // index_item_link <- index <- groups_users_link
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY));
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'));
            $criteria->add(new Criteria('is_admin', 1));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = $index_item_linkHandler->getObjects($criteria, false, '', null, $join1);

            return count($index_item_links) != 0;
        } elseif ($operation == 'read') {
            // read: item.uid == $uid || group_ceritfied && group_member || group && group admin || public_certified
            if ($item_basic->get('uid') == $uid) {
                return true;
            }

            // index_item_link <- index <- groups_users_link
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'tx'), 'and');
            $criteria->add(new Criteria('certify_state', CERTIFIED, '='), 'and'); // public index && certified
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'tx'), 'or'); // group index && group admin
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('is_admin', 1, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'tx'), 'or'); // group index && group member && certified
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('certify_state', CERTIFIED, '='), 'and');
            $criteria = new CriteriaCompo($criteria);
            $criteria->add(new Criteria('item_id', $item_id));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = $index_item_linkHandler->getObjects($criteria, false, '', null, $join1);
            if (count($index_item_links) != 0) {
                return true;
            }

            // item transferee?
            $transfer_requestHandler = xoonips_getOrmHandler('xoonips', 'transfer_request');
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('to_uid', $uid));
            $transfer_request = $transfer_requestHandler->getObjects($criteria);

            return !empty($transfer_request);
        }

        return false;
    }

    /**
     * @brief    search item
     *
     * @param      query  query ( string or CriteriaElement )
     * @param the  $limit
     * @param the  $offset
     * @param user $uid
     *
     * @return array of item id
     *
     * @internal param the $limit maximum number of rows to return(0 = no limit)
     * @internal param the $offset offset of the first row to return(0 = from beginning)
     * @internal param user $uid ID
     */
    public function search($query, $limit, $offset, $uid)
    {
        if (!$this->iteminfo) {
            return array();
        }

        $modulename = $this->iteminfo['ormcompo']['module'];
        $dummy = false;
        $search_cache_id = false;
        // save xoopsUser
        if (isset($GLOBALS['xoopsUser'])) {
            $old_xoopsUser = $GLOBALS['xoopsUser'];
        } else {
            $old_xoopsUser = null;
        }
        // prepare for xnpSearchExec
        $memberHandler = xoops_getHandler('member');
        $GLOBALS['xoopsUser'] = $memberHandler->getUser($uid);
        // search
        $item_ids = array();
        if (xnpSearchExec('quicksearch', $query, $modulename, false, $dummy, $dummy, $dummy, $search_cache_id, false, 'item_metadata')) {
            $search_cache_itemHandler = xoonips_getOrmHandler('xoonips', 'search_cache_item');
            $criteria = new Criteria('search_cache_id', $search_cache_id);
            $criteria->setSort('item_id');
            $criteria->setStart($offset);
            if ($limit) {
                $criteria->setLimit($limit);
            }
            $search_cache_items = $search_cache_itemHandler->getObjects($criteria);
            foreach ($search_cache_items as $search_cache_item) {
                $item_ids[] = $search_cache_item->get('item_id');
            }
        }
        // restore xoopsUser
        $GLOBALS['xoopsUser'] = $old_xoopsUser;

        return $item_ids;
    }

    /**
     * get iteminfo array.
     */
    public function getIteminfo()
    {
        return $this->iteminfo;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return string|template
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
            case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
                return 'xoonips_transfer_item_detail.tpl';
            case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
                return 'xoonips_transfer_item_list.tpl';
            default:
                return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *                        , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id item id
     * @param int    $uid     user id
     *
     * @return array
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        if (!in_array($type, array(
            XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL,
            XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST,
            XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL,
            XOONIPS_TEMPLATE_TYPE_ITEM_LIST,
        ))
        ) {
            return array();
        }
        $item = $this->get($item_id);

        return $this->getBasicTemplateVar($type, $item, $uid);
    }

    /**
     * return template variables of item basic part.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *                     , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param object $item item compo object
     * @param int    $uid
     *
     * @return array variables
     */
    public function getBasicTemplateVar($type, $item, $uid)
    {
        if (!in_array($type, array(
            XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL,
            XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST,
            XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL,
            XOONIPS_TEMPLATE_TYPE_ITEM_LIST,
        ))
        ) {
            return array();
        }
        $textutil = xoonips_getUtility('text');
        $item_typeHandler = xoonips_getOrmHandler('xoonips', 'item_type');

        $basic = $item->getVar('basic');
        $item_id = $basic->get('item_id');
        $result = array(
            'basic' => array(
                'item_id' => $basic->getVar('item_id', 's'),
                'description' => $basic->getVar('description', 's'),
                'doi' => $basic->getVar('doi', 's'),
                'creation_date' => $basic->getVar('creation_date', 's'),
                'last_update_date' => $basic->getVar('last_update_date', 's'),
                'publication_year' => $this->get_year_template_var($basic->get('publication_year'), $basic->get('publication_month'),
                                                                    $basic->get('publication_mday')),
                'publication_month' => $this->get_month_template_var($basic->get('publication_year'), $basic->get('publication_month'),
                                                                     $basic->get('publication_mday')),
                'publication_mday' => $this->get_mday_template_var($basic->get('publication_year'), $basic->get('publication_month'),
                                                                    $basic->get('publication_mday')),
                'lang' => $this->get_lang_label($basic->get('lang')),
            ),
            'contributor' => array(),
            'item_type' => array(),
            'titles' => array(),
            'keywords' => array(),
            'changelogs' => array(),
            'indexes' => array(),
            'related_tos' => array(),
        );

        $userHandler = xoonips_getOrmHandler('xoonips', 'xoops_users');
        $user = $userHandler->get($basic->get('uid'));
        if (is_object($user)) {
            $result['contributor']['name'] = $user->getVar('name', 's');
            $result['contributor']['uname'] = $user->getVar('uname', 's');
        } else {
            $result['contributor']['name'] = 'unknown';
            $result['contributor']['uname'] = 'Zombie User';
        }

        $item_type = $item_typeHandler->get($basic->get('item_type_id'));
        $result['item_type']['display_name'] = $item_type->getVar('display_name', 's');

        foreach ($item->getVar('titles') as $title) {
            $result['titles'][] = array('title' => $title->getVar('title', 's'));
        }

        foreach ($item->getVar('keywords') as $keyword) {
            $result['keywords'][] = array('keyword' => $keyword->getVar('keyword', 's'));
        }

        $changelogs = $item->getVar('changelogs');
        usort($changelogs, array(
            'XooNIpsItemInfoCompoHandler',
            'usort_desc_by_log_date',
        ));
        foreach ($changelogs as $changelog) {
            $result['changelogs'][] = array(
                'log_date' => $changelog->getVar('log_date', 's'),
                'log' => $changelog->getVar('log', 's'),
            );
        }

        // get indexes only not item listing for loading performance improvement
        if ($type != XOONIPS_TEMPLATE_TYPE_ITEM_LIST && $type != XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST) {
            $xoonips_userHandler = xoonips_getOrmHandler('xoonips', 'users');
            $xoonips_user = $xoonips_userHandler->get($basic->get('uid'));
            $private_index_id = $xoonips_user->get('private_index_id');
            foreach ($item->getVar('indexes') as $link) {
                $result['indexes'][] = array(
                    'path' => $this->get_index_path_by_index_id($link->get('index_id'), $private_index_id, 's'),
                );
            }
        }

        // get related to part only not item listing for disable to recursive calling
        if ($type != XOONIPS_TEMPLATE_TYPE_ITEM_LIST && $type != XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST) {
            $basicHandler = xoonips_getOrmHandler('xoonips', 'item_basic');
            foreach ($item->getVar('related_tos') as $related_to) {
                $related_basic = $basicHandler->get($related_to->get('item_id'));
                if (empty($related_basic)) {
                    continue;
                } // ignore invalid item id
                $related_item_type = $item_typeHandler->get($related_basic->get('item_type_id'));
                $related_item_type_id = $related_item_type->get('item_type_id');
                if ($related_item_type_id == ITID_INDEX) {
                    continue;
                } // ignore index id
                $item_compoHandler = xoonips_getOrmCompoHandler($related_item_type->getVar('name', 's'), 'item');
                if ($item_compoHandler->getPerm($item_id, $uid, 'read')) {
                    $result['related_tos'][]
                        = array(
                        'filename' => 'db:'.$item_compoHandler->getTemplateFileName(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST),
                        'var' => $item_compoHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST, $related_basic->get('item_id'),
                                                                         $uid),
                    );
                }
            }
        }

        //additional type specific template vars
        switch ($type) {
            case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
                $result['url'] = $textutil->html_special_chars(xoonips_get_transfer_request_item_detail_url($basic->get('item_id')));
            case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
                $result['pending'] = xnpIsPending($item_id);
        }

        return $result;
    }

    /**
     * compare function of usort to sort changelog descent by log_date.
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    public function usort_desc_by_log_date($a, $b)
    {
        $a_log_date = $a->get('log_date');
        $b_log_date = $b->get('log_date');
        if ($a_log_date == $b_log_date) {
            return 0;
        }

        return ($a_log_date > $b_log_date) ? -1 : 1;
    }

    /**
     * @param $keys
     * @param $values
     *
     * @return array
     */
    public function array_combine($keys, $values)
    {
        $result = array();
        reset($keys);
        reset($values);
        while (current($keys) && current($values)) {
            $result[current($keys)] = current($values);
            next($keys);
            next($values);
        }

        return $result;
    }

    /**
     * @param $lang
     *
     * @return mixed|string
     */
    public function get_lang_label($lang)
    {
        $languages = $this->array_combine(explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS), explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_NAMES));

        if (in_array($lang, array_keys($languages))) {
            return $languages[$lang];
        }

        return '';
    }

    /**
     * @param        $index_id
     * @param        $private_index_id
     * @param string $fmt
     *
     * @return string
     */
    public function get_index_path_by_index_id($index_id, $private_index_id, $fmt = 'n')
    {
        $indexHandler = xoonips_getOrmCompoHandler('xoonips', 'index');

        return '/'.implode('/', $indexHandler->getIndexPathNames($index_id, $private_index_id, $fmt));
    }

    /**
     * return text presentation of year, month and day of month.
     *
     * @param int $year
     * @param int $month (1-12)
     * @param int $mday  (1-31)
     *
     * @return array|associative
     *                           array( 'year' => (year string),
     *                           'month' => (month string),
     *                           'mday' => day of month string );
     */
    public function get_date_template_var($year, $month, $mday)
    {
        $result = array(
            'year' => '',
            'month' => '',
            'mday' => '',
        );
        $int_year = (int) $year;
        $int_month = (int) $month;
        $int_mday = (int) $mday;

        $result['year'] = date('Y', mktime(0, 0, 0, 1, 1, $int_year));
        $result['month'] = date('M', mktime(0, 0, 0, $int_month, 1, $int_year));
        $result['mday'] = date('j', mktime(0, 0, 0, $int_month, $int_mday, $int_year));

        return $result;
    }

    /**
     * @param $year
     * @param $month
     * @param $mday
     *
     * @return mixed
     */
    public function get_year_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['year'];
    }

    /**
     * @param $year
     * @param $month
     * @param $mday
     *
     * @return mixed
     */
    public function get_month_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['month'];
    }

    /**
     * @param $year
     * @param $month
     * @param $mday
     *
     * @return mixed
     */
    public function get_mday_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['mday'];
    }

    /**
     * get attachment file template var.
     *
     * @param XooNIpsFile
     *
     * @return array|associative
     */
    public function getAttachmentTemplateVar($file)
    {
        if ($file->get('file_size') >= 1024 * 1024) {
            $fileSizeStr = sprintf('%01.1f MB', $file->get('file_size') / (1024 * 1024));
        } elseif ($file->get('file_size') >= 1024) {
            $fileSizeStr = sprintf('%01.1f KB', $file->get('file_size') / 1024);
        } else {
            $fileSizeStr = sprintf('%d bytes', $file->get('file_size'));
        }

        $file_typeHandler = xoonips_getOrmHandler('xoonips', 'file_type');
        $fileHandler = xoonips_getOrmHandler('xoonips', 'file');

        $basicHandler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $basic = $basicHandler->get($file->get('item_id'));

        $file_type = $file_typeHandler->get($file->get('file_type_id'));

        return array(
            'file_id' => $file->getVar('file_id', 's'),
            'file_name' => $file->getVar('original_file_name', 's'),
            'mime_type' => $file->getVar('mime_type', 's'),
            'file_size' => $fileSizeStr,
            'last_update_date' => $file->get('timestamp'),
            'download_count' => $file->get('download_count'),
            'item_creation_date' => $basic->get('creation_date'),
            'total_download_count' => $fileHandler->getTotalDownloadCount($file->get('item_id'), $file_type->get('name')),
        );
    }

    /**
     * get preview file template var.
     *
     * @param XooNIpsFile $preview
     *
     * @return array|associative
     */
    public function getPreviewTemplateVar($preview)
    {
        return array(
            'thumbnail_url' => XOOPS_URL.'/modules/xoonips/image.php?file_id='.$preview->get('file_id').'&amp;thumbnail=1',
            'image_url' => XOOPS_URL.'/modules/xoonips/image.php?file_id='.$preview->get('file_id'),
            'caption' => $preview->getVar('caption', 's'),
        );
    }

    /**
     * get parent item ids.
     *
     * @final
     *
     * @param $item_id
     *
     * @return array
     *
     * @internal param item_id $int
     */
    public function getParentItemIds($item_id)
    {
        $result = array();
        $item_typeHandler = xoonips_getOrmHandler('xoonips', 'item_type');
        $item_types = $item_typeHandler->getObjects(new Criteria('item_type_id', ITID_INDEX, '<>'));
        foreach ($item_types as $item_type) {
            $info_compoHandler = xoonips_getOrmCompoHandler($item_type->get('name'), 'item');
            $result = array_merge($result, $info_compoHandler->getItemTypeSpecificParentItemIds($item_id));
        }

        return $result;
    }

    /**
     * get parent item ids.
     *
     * @param int item_id
     *
     * @return array
     */
    public function getItemTypeSpecificParentItemIds($item_id)
    {
        return array();
    }

    /**
     * return url to show item detail.
     *
     * @param $item_id
     *
     * @return string
     */
    public function getItemDetailUrl($item_id)
    {
        $myts = MyTextSanitizer::getInstance();
        $basicHandler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $basic = $basicHandler->get($item_id);
        if (!$basic) {
            return '';
        }

        global $xoopsModule;
        $base_url = XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/detail.php';
        if ($basic->get('doi') == ''
            || XNP_CONFIG_DOI_FIELD_PARAM_NAME == ''
        ) {
            return $base_url.'?item_id='.(int) $item_id;
        }

        return $base_url.'?'.XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($basic->get('doi'));
    }

    /**
     * @param $uid
     * @param $file_id
     *
     * @return bool
     */
    public function hasDownloadPermission($uid, $file_id)
    {
        $fileHandler = xoonips_getOrmHandler('xoonips', 'file');
        $file = $fileHandler->get($file_id);
        if (!$file) {
            return false;
        } // no such file

        $item_id = $file->get('item_id');
        if (!$item_id) {
            return false;
        } // file is not belong to any item

        $item_compo = $this->get($item_id);
        if (!$item_compo) {
            return false;
        } // bad item

        $detail = $item_compo->getVar('detail');
        if (!$detail) {
            return false;
        } // bad item

        $iteminfo = $this->getIteminfo();
        if (empty($iteminfo)) {
            return false;
        }

        // get module option 'enable_dl_limit'
        $mhandler = xoops_getHandler('module');
        $module = $mhandler->getByDirname($iteminfo['ormcompo']['module']);
        $chandler = xoops_getHandler('config');
        $assoc = $chandler->getConfigsByCat(false, $module->mid());
        if (isset($assoc['enable_dl_limit']) && $assoc['enable_dl_limit'] == '1') {
            // guest enabled?
            if ($uid == UID_GUEST && $detail->get('attachment_dl_limit')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $item_compo
     *
     * @return bool
     */
    public function isValidForPubicOrGroupShared($item_compo)
    {
        $iteminfo = $this->getIteminfo();
        $item_type_name = $iteminfo['ormcompo']['module'];
        $detail_item_typeHandler = xoonips_getOrmHandler($item_type_name, 'item_type');
        $basic = $item_compo->getVar('basic');
        $item_type_id = $basic->get('item_type_id');
        $detail_item_type = $detail_item_typeHandler->get($item_type_id);

        // error if add to public/group and no rights input
        $detail = $item_compo->getVar('detail');
        if ($detail_item_type->getFieldByName('detail', 'rights')) {
            if ($detail_item_type->getFieldByName('detail', 'use_cc')) {
                $use_cc = $detail->get('use_cc');
            } else {
                $use_cc = 0;
            }
            if ($detail->get('rights') == '' && $use_cc == 0) {
                return false;
            }
        }
        // error if add to public/group and no readme input
        if ($detail_item_type->getFieldByName('detail', 'readme')) {
            if ($detail->get('readme') == '') {
                return false;
            }
        }

        return true;
    }
}

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Data object
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief data object of xoonips item(basic fields)
 *
 * @li    getVar('basic') : {@link XooNIpsItemCompoBasic}
 * @li    getVar('titles') : array of {@link XooNIpsTitle}
 * @li    getVar('keywords') : array of {@link XooNIpsKeyword}
 * @li    getVar('related_tos') : array of {@link XooNIpsRelatedTo}
 * @li    getVar('changelogs') : array of {@link XooNIpsChangelog}
 * @li    getVar('indexes') : array of {@link XooNIpsIndexItemLink}
 */
class XooNIpsItemCompo extends XooNIpsRelatedObject
{
    /**
     * XooNIpsItemCompo constructor.
     */
    public function __construct()
    {
        // basic
        $basicHandler = xoonips_getOrmHandler('xoonips', 'item_basic');
        $this->initVar('basic', $basicHandler->create(), true);
        $this->initVar('titles', $titles = array(), true);
        $this->initVar('keywords', $keywrods = array());
        $this->initVar('related_tos', $related_tos = array());
        $this->initVar('changelogs', $changelogs = array());
        $this->initVar('indexes', $indexes = array());
    }

    /**
     * @return string
     */
    public function getItemAbstractText()
    {
        $basic = &$this->getVar('basic');
        $titles = &$this->getVar('titles');
        $indexes = &$this->getVar('indexes');
        $handler = xoonips_getOrmHandler('xoonips', 'item_type');
        $itemtype = $handler->get($basic->get('item_type_id'));

        $userHandler = xoops_getHandler('user');
        $user = $userHandler->get($basic->get('uid'));

        $ret = array();
        foreach ($titles as $title) {
            $ret[] = $title->get('title');
        }
        $ret[] = $user->getVar('uname', 'n');
        if ($itemtype) {
            $ret[] = $itemtype->get('display_name');
        }

        $indexHandler = xoonips_getOrmCompoHandler('xoonips', 'index');
        $index_item_linkHandler = xoonips_getOrmHandler('xoonips', 'index_item_link');
        $criteria = new Criteria('item_id', $basic->get('item_id'));
        $index_item_links = $index_item_linkHandler->getObjects($criteria);
        foreach ($index_item_linkHandler->getObjects($criteria) as $link) {
            $ret[] = '/'.implode('/', $indexHandler->getIndexPathNames($link->get('index_id')));
        }

        return implode("\n", $ret);
    }
}

/**
 * @brief data object of xoonips item according to iteminfo
 */
class XooNIpsItemInfoCompo extends XooNIpsRelatedObject
{
    public $iteminfo = null;

    /**
     * XooNIpsItemInfoCompo constructor.
     *
     * @param null $module
     */
    public function __construct($module = null)
    {
        if (isset($module) && null === $this->iteminfo) {
            require XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = $iteminfo;
            // add orm object according to $this -> iteminfo['orm']
            foreach ($this->iteminfo['orm'] as $orminfo) {
                $handler = xoonips_getOrmHandler($orminfo['module'], $orminfo['name']);
                if (isset($orminfo['multiple']) ? $orminfo['multiple'] : false) {
                    $ary = array();
                    $this->initVar($orminfo['field'], $ary, isset($orminfo['required']) ? $orminfo['required'] : false);
                    unset($ary);
                } else {
                    $this->initVar($orminfo['field'], $handler->create(), isset($orminfo['required']) ? $orminfo['required'] : false);
                }
            }
        }
    }

    /**
     * get iteminfo array.
     */
    public function getIteminfo()
    {
        return $this->iteminfo;
    }

    /**
     * get child item ids of this item.
     *
     * @return array array of child item ids
     */
    public function getChildItemIds()
    {
        return array();
    }
}
