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
require __DIR__ . '/include/common.inc.php';
require __DIR__ . '/include/group.inc.php';

// privileges check : user
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
if ($uid == UID_GUEST) {
    redirect_header(XOOPS_URL . '/', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
}

$breadcrumbs = array(
    array(
        'name' => _MD_XOONIPS_BREADCRUMBS_USER
    ),
    array(
        'name' => _MD_XOONIPS_TITLE_GROUP_LIST,
        'url'  => 'groups.php'
    ),
);

$xgroupHandler = xoonips_getHandler('xoonips', 'group');
$gids          = $xgroupHandler->getGroupIds();
$groups        = xoonips_group_get_groups($uid, $gids);

$GLOBALS['xoopsOption']['template_main'] = 'xoonips_group_list.tpl';
require XOOPS_ROOT_PATH . '/header.php';
$xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
$xoopsTpl->assign('groups', $groups);
require XOOPS_ROOT_PATH . '/footer.php';
