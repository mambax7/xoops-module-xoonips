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

// class files
require_once '../class/base/pattemplate.class.php';

// title
$title = _AM_XOONIPS_POLICY_TITLE;
$description = _AM_XOONIPS_POLICY_DESC;

// breadcrumbs
$breadcrumbs = [
    [
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ],
    [
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ],
];

// menu
$menu = [
    [
        'label' => _AM_XOONIPS_POLICY_ACCOUNT_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=account',
    ],
    [
        'label' => _AM_XOONIPS_POLICY_GROUP_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=group',
    ],
    [
        'label' => _AM_XOONIPS_POLICY_ITEM_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=item',
    ],
    [
        'label' => _AM_XOONIPS_POLICY_MODERATOR_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=moderator',
    ],
    [
        'label' => _AM_XOONIPS_POLICY_POSITION_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=position',
    ],
    [
        'label' => _AM_XOONIPS_POLICY_RANKING_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=ranking',
    ],
];

// templates
$tmpl = new PatTemplate();
$tmpl->setBasedir('templates');
$tmpl->readTemplatesFromFile('adminmenu.tmpl.html');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addRows('menu', $menu);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
