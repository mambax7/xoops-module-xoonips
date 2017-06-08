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

// check token ticket
require_once __DIR__ . '/../../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_module';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
}

// main logic
// - $fct = 'preferences'
// - $op = 'save'
require_once XOOPS_ROOT_PATH . '/class/template.php';
$xoopsTpl = new XoopsTpl();
$xoopsTpl->clear_all_cache();
// regenerate admin menu file
xoops_module_write_admin_menu(xoops_module_get_admin_menu());
$conf_ids         = (!empty($_POST['conf_ids'])) ? $_POST['conf_ids'] : array();
$count            = count($conf_ids);
$tpl_updated      = false;
$theme_updated    = false;
$startmod_updated = false;
$lang_updated     = false;
if ($count > 0) {
    for ($i = 0; $i < $count; $i++) {
        $config    = $configHandler->getConfig($conf_ids[$i]);
        $new_value = $_POST[$config->getVar('conf_name')];
        if (is_array($new_value) || $new_value != $config->getVar('conf_value')) {
            // if language has been changed
            if (!$lang_updated && $config->getVar('conf_catid') == XOOPS_CONF && $config->getVar('conf_name') === 'language') {
                // regenerate admin menu file
                $xoopsConfig['language'] = $_POST[$config->getVar('conf_name')];
                xoops_module_write_admin_menu(xoops_module_get_admin_menu());
                $lang_updated = true;
            }

            // if default theme has been changed
            if (!$theme_updated && $config->getVar('conf_catid') == XOOPS_CONF && $config->getVar('conf_name') === 'theme_set') {
                $memberHandler = xoops_getHandler('member');
                $memberHandler->updateUsersByField('theme', $_POST[$config->getVar('conf_name')]);
                $theme_updated = true;
            }

            // if default template set has been changed
            if (!$tpl_updated && $config->getVar('conf_catid') == XOOPS_CONF && $config->getVar('conf_name') === 'template_set') {
                // clear cached/compiled files and regenerate them if default theme has been changed
                if ($xoopsConfig['template_set'] != $_POST[$config->getVar('conf_name')]) {
                    $newtplset = $_POST[$config->getVar('conf_name')];

                    // clear all compiled and cachedfiles
                    $xoopsTpl->clear_compiled_tpl();

                    // generate compiled files for the new theme
                    // block files only for now..
                    $tplfileHandler = xoops_getHandler('tplfile');
                    $dtemplates     =  $tplfileHandler->find('default', 'block');
                    $dcount         = count($dtemplates);

                    // need to do this to pass to xoops_template_touch function
                    $GLOBALS['xoopsConfig']['template_set'] = $newtplset;

                    for ($j = 0; $j < $dcount; $j++) {
                        $found = $tplfileHandler->find($newtplset, 'block', $dtemplates[$j]->getVar('tpl_refid'), null);
                        if (count($found) > 0) {
                            // template for the new theme found, compile it
                            xoops_template_touch($found[0]->getVar('tpl_id'));
                        } else {
                            // not found, so compile 'default' template file
                            xoops_template_touch($dtemplates[$j]->getVar('tpl_id'));
                        }
                    }

                    // generate image cache files from image binary data, save them under cache/
                    $imageHandler = xoops_getHandler('imagesetimg');
                    $imagefiles   =  $imageHandler->getObjects(new Criteria('tplset_name', $newtplset), true);
                    foreach (array_keys($imagefiles) as $i) {
                        if (!$fp = fopen(XOOPS_CACHE_PATH . '/' . $newtplset . '_' . $imagefiles[$i]->getVar('imgsetimg_file'), 'wb')) {
                        } else {
                            fwrite($fp, $imagefiles[$i]->getVar('imgsetimg_body'));
                            fclose($fp);
                        }
                    }
                }
                $tpl_updated = true;
            }

            // add read permission for the start module to all groups
            if (!$startmod_updated && $new_value != '--' && $config->getVar('conf_catid') == XOOPS_CONF
                && $config->getVar('conf_name') === 'startpage'
            ) {
                $memberHandler     = xoops_getHandler('member');
                $groups            = $memberHandler->getGroupList();
                $modulepermHandler = xoops_getHandler('groupperm');
                $moduleHandler     = xoops_getHandler('module');
                $module            = $moduleHandler->getByDirname($new_value);
                foreach ($groups as $groupid => $groupname) {
                    if (!$modulepermHandler->checkRight('module_read', $module->getVar('mid'), $groupid)) {
                        $modulepermHandler->addRight('module_read', $module->getVar('mid'), $groupid);
                    }
                }
                $startmod_updated = true;
            }
            $config->setConfValueForInput($new_value);
            $configHandler->insertConfig($config);
        }
        unset($new_value);
    }
}
redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
