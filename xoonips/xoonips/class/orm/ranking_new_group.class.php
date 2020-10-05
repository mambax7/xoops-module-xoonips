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

require_once __DIR__.'/abstract_ranking.class.php';

/**
 * @brief data object of ranking new group
 *
 * @li getVar('gid') :
 * @li getVar('timestamp') :
 */
class XooNIpsOrmRankingNewGroup extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('gid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('timestamp', XOBJ_DTYPE_OTHER, null, false);
    }
}

/**
 * @brief handler object of ranking new group
 */
class XooNIpsOrmRankingNewGroupHandler extends XooNIpsOrmAbstractRankingHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmRankingNewGroup', 'xoonips_ranking_new_group', 'gid', false);
        $this->_set_columns(['gid', 'timestamp']);
    }

    /**
     * insert/upldate/replace object.
     *
     * @param object &$obj
     * @param bool   $force force operation
     *
     * @return bool false if failed
     */
    public function insert($obj, $force = false)
    {
        $gid = $obj->get('gid');
        if ($gid == 0) {
            // ignore if group id is zero
            return true;
        }

        return parent::insert($obj, $force);
    }

    /**
     * delete old entries for updating/rebuilding rankings.
     *
     * @param int $num_rows number of new entries
     *
     * @return bool FALSE if failed
     */
    public function trim($num_rows)
    {
        $field = 'timestamp';
        $criteria = new CriteriaElement();
        $criteria->setSort('timestamp');
        $criteria->setOrder('DESC');
        $criteria->setStart($num_rows);
        $criteria->setLimit(1);
        $objs = &$this->getObjects($criteria, false, $field);
        if (empty($objs)) {
            return true;
        }
        $timestamp = $objs[0]->get('timestamp');
        $criteria = new Criteria($field, $timestamp, '<');
        // force deletion
        if (!$this->deleteAll($criteria, true)) {
            return false;
        }

        return true;
    }
}
