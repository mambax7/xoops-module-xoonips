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

//  functions called from ItemTypeModules

require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/imexport.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/notification.inc.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';

function xnpGetBasicInformationArray($item_id, $fmt = 'n')
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->get($item_id);

    return $itemlib_obj->getBasicInformationArray($fmt);
}

function xnpGetBasicInformationDetailBlock($item_id)
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->get($item_id);

    return $itemlib_obj->getBasicInformationDetailBlock();
}

function xnpGetBasicInformationPrinterFriendlyBlock($item_id)
{
    return xnpGetBasicInformationDetailBlock($item_id);
}

function xnpGetBasicInformationRegisterBlock()
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->create();
    $itemlib_handler->fetchRequest($itemlib_obj, true);

    return $itemlib_obj->getBasicInformationEditBlock(true);
}

function xnpGetBasicInformationEditBlock($item_id)
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->get($item_id);
    $itemlib_handler->fetchRequest($itemlib_obj, true);

    return $itemlib_obj->getBasicInformationEditBlock(false);
}

function xnpGetBasicInformationConfirmBlock($item_id)
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    if ($item_id) {
        // modify item
        $itemlib_obj = &$itemlib_handler->get($item_id);
        $is_register = false;
    } else {
        // create new item
        $itemlib_obj = &$itemlib_handler->create();
        $is_register = true;
    }
    $itemlib_handler->fetchRequest($itemlib_obj, false);

    return $itemlib_obj->getBasicInformationConfirmBlock($is_register);
}

/**
 * @param int $item_id
 */
function xnpInsertBasicInformation(&$item_id)
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->create();
    $itemlib_handler->fetchRequest($itemlib_obj, false);
    if (!$itemlib_handler->insertBasicInformation($itemlib_obj)) {
        return false;
    }
    $item_id = $itemlib_obj->getItemId();

    return true;
}

function xnpUpdateBasicInformation($item_id)
{
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->get($item_id);
    $itemlib_handler->fetchRequest($itemlib_obj, false);

    return $itemlib_handler->insertBasicInformation($itemlib_obj);
}

function xnpDeleteBasicInformation($item_id)
{
    die('xnpDeleteBasicInforamation() is no longer supported function.');
}

/**
 * get item type display name by dirname.
 *
 * @param string $dirname module directory name
 * @param string $fmt     format
 *
 * @return string display name of item type
 */
function xnpGetItemTypeDisplayNameByDirname($dirname, $fmt)
{
    // TODO: move this function to felicitous class method
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $criteria = new Criteria('name', $dirname);
    $item_type_objs = &$item_type_handler->getObjects($criteria);
    if (1 != count($item_type_objs)) {
        return false;
    }
    $item_type_obj = &$item_type_objs[0];

    return $item_type_obj->getVar('display_name', $fmt);
}

/*
 * compare function for usort.
 * order indexes by open_level, owner_gid, certified, item_id
 */
function indexcmp($a, $b)
{
    if ($a['open_level'] == $b['open_level']) {
        if ($a['owner_gid'] == $b['owner_gid']) {
            if ($a['certified'] == $b['certified']) {
                return ($a['item_id'] < $b['item_id']) ? -1 : 1;
            } else {
                return ($a['certified'] < $b['certified']) ? -1 : 1;
            }
        } else {
            return ($a['owner_gid'] < $b['owner_gid']) ? -1 : 1;
        }
    } else {
        return ($a['open_level'] < $b['open_level']) ? -1 : 1;
    }
}

/**
 * return array of ListBlock's HTML from array of item_id.
 * result doesn't contain array data on item_id not existing or item_id not accessible.
 *
 * @param int $itemid ID of item or array of item id
 *
 * @return array( itemid => array of HTML made by "name of itemtype>GetListBlock", ...Repeat... )
 */
function itemid2ListBlock($itemid)
{
    $xnpsid = $_SESSION['XNPSID'];

    if (!is_array($itemid)) {
        $itemid = array($itemid);
    }

    $itemtypes = array();
    $tmp = array();
    if (RES_OK != xnp_get_item_types($tmp)) {
        xoonips_error_exit(500);
        exit();
    } else {
        foreach ($tmp as $i) {
            $itemtypes[$i['item_type_id']] = $i;
        }
    }

    $item_htmls = array();
    foreach ($itemid as $id) {
        $item = array();
        if (RES_OK != xnp_get_item($xnpsid, (int) $id, $item)) {
            continue;
        }
        if (array_key_exists($item['item_type_id'], $itemtypes)) {
            $itemtype = $itemtypes[$item['item_type_id']];
            $modname = $itemtype['name'];
            require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
            if (function_exists($modname.'GetListBlock')) {
                $html = '';
                eval('$html = '.$modname.'GetListBlock( $item );');
                $item_htmls[$id] = $html;
            }
        }
    }

    return $item_htmls;
}

/**
 * delete files not related to any sessions and any items.
 */
function xnpCleanup()
{
    global $xoopsDB;
    $fileTable = $xoopsDB->prefix('xoonips_file');
    $sessionTable = $xoopsDB->prefix('session');
    $searchTextTable = $xoopsDB->prefix('xoonips_search_text');
    $cacheTable = $xoopsDB->prefix('xoonips_search_cache');
    $cacheItemTable = $xoopsDB->prefix('xoonips_search_cache_item');
    $cacheMetadataTable = $xoopsDB->prefix('xoonips_search_cache_metadata');
    $cacheFileTable = $xoopsDB->prefix('xoonips_search_cache_file');

    // remove file if no-related sessions and files
    $sql = 'SELECT `file_id` FROM '.$fileTable.' AS `tf` LEFT JOIN '.$sessionTable.' AS `ts` ON `tf`.`sess_id`=`ts`.`sess_id` WHERE `tf`.`item_id` IS NULL AND `ts`.`sess_id` IS NULL';
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    while (list($file_id) = $xoopsDB->fetchRow($result)) {
        $path = xnpGetUploadFilePath($file_id);

        if (is_file($path)) {
            unlink($path);
        }
        $xoopsDB->queryF("DELETE FROM $searchTextTable WHERE `file_id`=$file_id");
        $xoopsDB->queryF("DELETE FROM $fileTable WHERE `file_id`=$file_id");
    }

    // get search_cache_id from timeouted session_id
    $scids = array();
    $sql = 'SELECT `search_cache_id` FROM '.$cacheTable.' AS `tc` LEFT JOIN '.$sessionTable.' AS `ts` ON `ts`.`sess_id`=`tc`.`sess_id` WHERE `ts`.`sess_id` IS NULL';
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    while (list($scid) = $xoopsDB->fetchRow($result)) {
        $scids[] = $scid;
    }

    $tmp = implode(',', $scids);
    $xoopsDB->queryF("DELETE LOW_PRIORITY FROM $cacheTable         WHERE `search_cache_id` IN (".$tmp.')');
    $xoopsDB->queryF("DELETE LOW_PRIORITY FROM $cacheItemTable     WHERE `search_cache_id` IN (".$tmp.')');
    $xoopsDB->queryF("DELETE LOW_PRIORITY FROM $cacheMetadataTable WHERE `search_cache_id` IN (".$tmp.')');
    $xoopsDB->queryF("DELETE LOW_PRIORITY FROM $cacheFileTable     WHERE `search_cache_id` IN (".$tmp.')');
}

function xnpIsCommaSeparatedNumber($str)
{
    $ar = array();

    return  1 == preg_match('/^([0-9,]+)$/', $str, $ar);
}

/**
 * get directory name stored attachment files that related to items.
 *  not contain '/' in end of character strings.
 *
 * @return string
 */
function xnpGetUploadDir()
{
    $uploadDir = '';
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $uploadDir = $xconfig_handler->getValue('upload_dir');
    if (empty($uploadDir)) {
        // upload_dir is not configured.
        return false;
    }

    if ('/' == substr($uploadDir, -1)) {
        return substr($uploadDir, 0, -1);
    }

    return $uploadDir;
}

/**
 * make path stored files from file_id.
 *
 * @param file_id file_id
 */
function xnpGetUploadFilePath($file_id)
{
    return xnpGetUploadDir().'/'.(int) $file_id;
}

/**
 * get corresponding culumns to condition from 'prefix("xoonips_file")' table.
 *
 * @param columns string culumns
 * @param condition query of SQL. t_file and t_file_type are possible to use for tablename.
 * ex.  $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.', '`t_file_type`.`name`=\'preview\' AND `is_deleted`=0 AND (`item_id`='.$item_id.' OR sid='.$sid.')');
 *
 * @return array( array( colum1, column2, ... ), ...);
 */
function xnpGetFileInfo($columns, $condition, $item_id)
{
    global $xoopsDB;

    $item_id = (int) $item_id;
    $condition2 = '(`item_id` IS NULL AND `sess_id`='.$xoopsDB->quoteString(session_id()).' OR `item_id`='.$item_id.')';

    $sql = 'SELECT '.$columns.' FROM '.
        $xoopsDB->prefix('xoonips_file').' AS `t_file`, '.
        $xoopsDB->prefix('xoonips_file_type').' AS `t_file_type` '.
        ' WHERE `t_file`.`file_type_id` = `t_file_type`.`file_type_id` AND '.$condition.' AND '.$condition2;
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }

    $files = array();
    while (false != ($row = $xoopsDB->fetchRow($result))) {
        $files[] = $row;
    }

    return $files;
}

/**
 * get details of all indexes registered item_id.
 *
 * @param SID
 * @param item_id item id of examined object
 * @param indexes return details of each index
 *
 * @return int
 * @return int
 */
function xnpGetIndexes($xnpsid, $item_id, &$indexes)
{
    $xids = array();
    $result = xnp_get_index_id_by_item_id($xnpsid, $item_id, $xids);
    if (0 == $result) {
        $len = count($xids);
        $indexes = array();
        for ($i = 0; $i < $len; ++$i) {
            $xid = $xids[$i];
            $index = array();
            $result = xnp_get_index($xnpsid, $xid, $index);
            if (0 == $result) {
                $indexes[] = $index;
            }
        }

        return RES_OK;
    }

    return RES_ERROR;
}

// make charater strings in current location in html(ex. /Private/Tools&Techniques ). from xoonips/edit.php
function xnpGetIndexPathString($xnpsid, $xid)
{
    $textutil = &xoonips_getutility('text');

    return $textutil->html_special_chars(xnpGetIndexPathServerString($xnpsid, $xid));
}

function xnpGetIndexPathServerString($xnpsid, $xid)
{
    $dirArray = array();
    $dirArrayR = array();

    for ($p_xid = $xid; IID_ROOT != $p_xid; $p_xid = (int) ($index['parent_index_id'])) {
        // get $index
        $index = array();
        $result = xnp_get_index($xnpsid, $p_xid, $index);
        if (0 != $result) {
            break;
        }

        $dirArray[] = $index;
    }
    $ct = count($dirArray);
    for ($i = 0; $i < $ct; ++$i) {
        $dirArrayR[] = $dirArray[$ct - $i - 1]['titles'][DEFAULT_INDEX_TITLE_OFFSET];
    }

    return '/ '.implode(' / ', $dirArrayR);
}

/**
 * @param string $key
 */
function xnpCreateHidden($key, $val, $do_escape = true)
{
    if ($do_escape) {
        $textutil = &xoonips_getutility('text');
        $val = $textutil->html_special_chars($val);
    }

    return '<input type="hidden" name="'.$key.'" value="'.$val.'"/>';
}

/* array of HTML named '$in' is table of '$col' rows.
    ex)
    $in = array( array(a0,a1,a2,a3,a4...), array(b0,b1,b2,...), array(c0,c1,c2,...) );
    $col = 2;
    array($in) becomes a table like the figure below.
    a0 a1
    b0 b1
    c0 c1
    a2 a3
    b2 b3
    c2 c3
    a4 a5
    ...
*/

/**
 * @param int $col
 */
function xnpMakeTable($in, $col)
{
    $inLen = count($in);

    $maxLens = array();
    for ($i = 0; $i < $inLen; ++$i) {
        $maxLens[] = count($in[$i]);
    }
    $maxLen = max($maxLens);
    if (0 == $maxLen) {
        return '';
    }

    // make table
    $out = array("<table>\n");
    for ($i = 0; $i < $maxLen; $i += $col) {
        for ($j = 0; $j < $inLen; ++$j) {
            $out[] = "<tr>\n";
            for ($k = 0; $k < $col; ++$k) {
                $out[] = '<td style="text-align: center; vertical-align: middle;">';
                if (isset($in[$j][$i + $k])) {
                    $out[] = $in[$j][$i + $k];
                }
                $out[] = "</td>\n";
            }
            $out[] = "</tr>\n";
        }
    }
    $out[] = "</table>\n";

    return implode('', $out);
}

/**
 * get PreviewBlock for detail page.
 */
function xnpGetPreviewDetailBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    // get file's information specified by item_id
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`caption`', '`t_file_type`.`name`=\'preview\' AND `is_deleted`=0 AND `sess_id` IS NULL', $item_id);
    // generate HTML
    reset($files);
    $imageHtml1 = array();
    $imageHtml2 = array();
    $fileIDs = array();
    foreach ($files as $files_) {
        list($fileID, $caption) = $files_;
        $thumbnailFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID&amp;thumbnail=1";
        $imageFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID";
        $htmlCaption = $textutil->html_special_chars($caption);
        $imageHtml1[] = '<a href="'.$imageFileName.'" target="_blank"><img src="'.$thumbnailFileName.'" alt="'.$htmlCaption.'"/></a>';
        $imageHtml2[] = "$htmlCaption";
        $fileIDs[] = $fileID;
    }

    // make a table of three rows in side.
    $html = xnpMakeTable(array($imageHtml1, $imageHtml2), 3);

    return array(
        'name' => _MD_XOONIPS_ITEM_PREVIEW_LABEL,
        'value' => $html,
        'hidden' => xnpCreateHidden('previewFileID', implode(',', $fileIDs)),
    );
}

/**
 * get AttachmentBlock for detail page.
 * display a warning dialog if link clicked( the case which download of the attachment file has been permitted ).
 *
 * @param item_id item_id
 * @param name name of file type
 */
function xnpGetAttachmentDetailBlock($item_id, $name)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    // get attachment file
    // generate html
    $uid = UID_GUEST;
    if (isset($_SESSION['xoopsUserId'])) {
        $uid = $_SESSION['xoopsUserId'];
    }

    $item = array();
    $res = xnp_get_item($_SESSION['XNPSID'], $item_id, $item);
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.original_file_name, `t_file`.`file_size`, `t_file`.`mime_type`, unix_timestamp(`t_file`.timestamp), download_count', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    if (false == $files || 0 == count($files) || RES_OK != $res) {
        $html = '';
        $hidden = '';
    } else {
        list(list($fileID, $fileName, $fileSize, $mimeType, $timestamp, $download_count)) = $files;
        $htmlFileName = $textutil->html_special_chars($fileName);
        $url = XOOPS_URL.'/modules/xoonips/download.php?file_id='.$fileID;

        list($tmp) = xnpGetFileInfo('SUM(`t_file`.`download_count`)', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL ', $item_id);
        $totalDownloads = $tmp[0];

        if ($fileSize >= 1024 * 1024) {
            $fileSizeStr = sprintf('%01.1f MB', $fileSize / (1024 * 1024));
        } elseif ($fileSize >= 1024) {
            $fileSizeStr = sprintf('%01.1f KB', $fileSize / 1024);
        } else {
            $fileSizeStr = sprintf('%d bytes', $fileSize);
        }

        $hidden = xnpCreateHidden($name.'FileID', $fileID);

        // item_id -> modname
        $itemtypes = array();
        $module_name = 'xoonips';
        if (RES_OK != ($res = xnp_get_item_types($itemtypes))) {
            xoonips_error_exit(500);
        }
        foreach ($itemtypes as $itemtype) {
            if ($itemtype['item_type_id'] != $item['item_type_id']) {
                continue;
            }
            $module_name = $itemtype['name'];
            require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
        }

        $func = $module_name.'GetDownloadConfirmationRequired';
        $warning = '';
        $button_img = '<img src="images/icon_button.php?label=download&amp;mode=normal" alt="'._MD_XOONIPS_ITEM_DOWNLOAD_LABEL.'" id="xoonips_download_button_'.$fileID.'"/>';
        $button_href = 'href="download.php?file_id='.$fileID.'"';
        $button_onfocus = 'onfocus="xoonips_download_button_change(\'focus\',\''.$fileID.'\')"';
        $button_onblur = 'onblur="xoonips_download_button_change(\'blur\',\''.$fileID.'\')"';
        $button_onmousedown = 'onmousedown="xoonips_download_button_change(\'down\',\''.$fileID.'\')"';
        $button_onmouseup = 'onmouseup="xoonips_download_button_change(\'over\',\''.$fileID.'\')"';
        $button_onmouseover = 'onmouseover="xoonips_download_button_change(\'over\',\''.$fileID.'\')"';
        $button_onmouseout = 'onmouseout="xoonips_download_button_change(\'out\',\''.$fileID.'\')"';
        $button_onkeypress = 'onkeypress="xoonips_download_button_change(\'down\',\''.$fileID.'\')"';
        $button_onclick = 'onclick="return xoonips_download_button_click(\''.$fileID.'\');"';
        if (function_exists($func) && $func($item_id, $name)) {
            $download_button = "<a $button_href $button_onfocus $button_onblur $button_onmousedown $button_onmouseup $button_onmouseover $button_onmouseout $button_onkeypress $button_onclick>$button_img</a>";
            $warning = '<noscript><span style="color: red;">'._MD_XOONIPS_ITEM_DOWNLOAD_NOSCRIPT_LABEL.'</span><br /></noscript>';
        } else {
            $download_button = "<a $button_href $button_onfocus $button_onblur $button_onmousedown $button_onmouseup $button_onmouseover $button_onmouseout $button_onkeypress>$button_img</a>";
        }

        $html = (empty($warning)) ? '' : $warning;
        $html .= "$htmlFileName<br />
        <table>
          <tr>
            <td>"._MD_XOONIPS_ITEM_TYPE_LABEL.'</td>
            <td>: '.$textutil->html_special_chars($mimeType)."</td>
            <td rowspan=\"4\" style=\"vertical-align: middle;\">
            $download_button
            </td>
          </tr>
          <tr>
            <td>"._MD_XOONIPS_ITEM_SIZE_LABEL."</td>
            <td>: $fileSizeStr</td>
          </tr>
          <tr>
            <td>"._MD_XOONIPS_ITEM_LAST_UPDATED_LABEL.'</td>
            <td>: '.date(DATE_FORMAT, $timestamp).'</td>
          </tr>
          <tr>
            <td>'._MD_XOONIPS_ITEM_DOWNLOAD_COUNT_LABEL."</td>
            <td>: $download_count</td>
          </tr>
        </table>
        <br />
        "._MD_XOONIPS_ITEM_TOTAL_DOWNLOAD_COUNT_SINCE_LABEL.date(DATE_FORMAT, $item['creation_date']).' : '.$totalDownloads.'<br />';

        $fname_dllimit = "${module_name}GetAttachmentDownloadLimitOption";
        if (function_exists($fname_dllimit) && 1 == $fname_dllimit($item_id)) {
            if (UID_GUEST == $uid) {
                $html = '<a href="'.$url.'">'.$htmlFileName.'</a> '.$fileSize.' bytes';
            }
            $html .= ' &nbsp;&nbsp;('._MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_LOGINUSER_ONLY_LABEL.')';
        }
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html, 'hidden' => $hidden);
}

/**
 * get Confirmation block for detail page.
 * display confirmation block if xoonips_download_confirmation() is called.
 *
 * @param item_id item_id
 * @param download_file_id if non-false value, automatically push download button of this file_id
 * @param name file type name
 * @param attachment_dl_notify 0:don't notify  1:notify(need download-notification agreeemnt)
 * @param use_license boolean license(need license agreement)
 * @param use_cc use creative commons license
 * @param rights license text(use_cc=0) or license html(use_cc=1)
 */
function xnpGetDownloadConfirmationBlock($item_id, $download_file_id, $attachment_dl_notify, $use_license, $use_cc, $rights)
{
    $textutil = &xoonips_getutility('text');
    if (!$attachment_dl_notify && !$use_license && !$download_file_id) {
        return '';
    }

    require_once dirname(__DIR__).'/class/base/gtickets.php';
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.original_file_name, `t_file`.`file_size`, `t_file`.`mime_type`, UNIX_TIMESTAMP(`t_file`.`timestamp`) ', '`sess_id` IS NULL AND `is_deleted`=0', $item_id);

    if (false == $files || 0 == count($files)) {
        return '';
    }

    $ar = array();
    foreach ($files as $file) {
        list($fileID, $fileName, $fileSize, $mimeType, $timestamp) = $file;
        if ($fileSize >= 1024 * 1024) {
            $fileSizeStr = sprintf('%01.1f MB', $fileSize / (1024 * 1024));
        } elseif ($fileSize >= 1024) {
            $fileSizeStr = sprintf('%01.1f KB', $fileSize / 1024);
        } else {
            $fileSizeStr = sprintf('%d bytes', $fileSize);
        }
        $ticket_area = 'xoonips_download_token'.$fileID;
        $token_ticket = $GLOBALS['xoopsGTicket']->getTicketHtml(__LINE__, 1800, $ticket_area);
        $ar[] = array(
            'fileID' => $fileID,
            'fileName' => $textutil->html_special_chars($fileName),
            'fileSizeStr' => $fileSizeStr,
            'mimeType' => $mimeType,
            'lastUpdated' => date(DATE_FORMAT, $timestamp),
            'token' => $token_ticket,
        );
        unset($token);
    }

    $tpl = new XoopsTpl();
    $tpl->assign('files', $ar);
    $tpl->assign('use_license', $use_license);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('download_file_id', $download_file_id);
    $tpl->assign('use_cc', $use_cc);
    $tpl->assign('rights', $use_cc ? $rights : $textutil->html_special_chars($rights));

    $DownloadFileName = xoonips_get_download_filename($fileID);
    $download = &xoonips_getutility('download');
    $url = XOOPS_URL.'/modules/xoonips/download.php';
    if (!$download->check_pathinfo($DownloadFileName)) {
        $url = $download->append_pathinfo($url, $DownloadFileName);
    }
    $tpl->assign('download_url', $url);

    return $tpl->fetch('db:xoonips_detail_download_confirmation.html');
}

function xoonips_get_download_filename($file_id)
{
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $file = $file_handler->get($file_id);
    if (null == $file) {
        return null;
    }

    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($file->get('item_id'));
    if (null == $item_basic) {
        return null;
    }

    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type = $item_type_handler->get($item_basic->get('item_type_id'));
    if (null == $item_type) {
        return null;
    }

    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $download_file_compression = $xconfig_handler->getValue('download_file_compression');
    if (is_null($download_file_compression)) {
        return null;
    }

    if ('on' == $download_file_compression) {
        return $item_type->get('display_name').'_'.$file->get('file_id').'.zip';
    } else {
        $unicode = &xoonips_getutility('unicode');

        return mb_decode_numericentity($unicode->encode_utf8($file->get('original_file_name'), xoonips_get_server_charset()), xoonips_get_conversion_map(), 'UTF-8');
    }
}

/**
 * get FilenameBlock of attachment.
 *
 * @param item_id item_id
 * @param name name of file type
 */
function xnpGetAttachmentFilenameBlock($item_id, $name)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    // get attachment file
    // generate html
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.original_file_name', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    if (0 == count($files)) {
        $html = '';
    } else {
        list(list($fileID, $fileName)) = $files;
        if (mb_substr_count($fileName, '.') > 0) {
            $fileName = mb_substr($fileName, 0, mb_strrpos($fileName, '.'));
        }
        $htmlFileName = $textutil->html_special_chars($fileName);
        $html = "$htmlFileName";
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}
/**
 * get MimetypeBlock of Attachment.
 *
 * @param item_id item_id
 * @param name name of file type
 */
function xnpGetAttachmentMimetypeBlock($item_id, $name)
{
    $textutil = &xoonips_getutility('text');
    // get attachment file
    // generate html
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`mime_type`', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    if (0 == count($files)) {
        $html = '';
    } else {
        list(list($fileID, $mimetype)) = $files;
        $html = $textutil->html_special_chars($mimetype);
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}
/**
 * get FiletypeBlock of Attachment.
 *
 * @param item_id item_id
 * @param name name of file type
 */
function xnpGetAttachmentFiletypeBlock($item_id, $name)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    // get attachment file
    // generate html
    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.original_file_name', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    if (0 == count($files)) {
        $html = '';
    } else {
        list(list($fileID, $fileType)) = $files;
        if (0 == mb_substr_count($fileType, '.')) {
            $fileType = '';
        } else {
            $fileType = mb_substr($fileType, mb_strrpos($fileType, '.') + 1);
        }
        $htmlFileType = $textutil->html_special_chars($fileType);
        $html = "$htmlFileType";
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

/**
 * get TextFileBlock for detail page.
 *
 * @param item_id item_id
 * @param name string of file type
 */
function xnpGetTextFileDetailBlock($item_id, $name, $text)
{
    $textutil = &xoonips_getutility('text');

    return array('name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL, 'value' => '<textarea readonly="readonly" rows="5" cols="40" style="width:320px;">'.$textutil->html_special_chars($text).'</textarea>', 'hidden' => xnpCreateHidden($name.'EncText', $text));
}

/**
 * $item_id ID of item to display index
 * $button_flag if certify button, uncertify button, withdraw button are displayed, flag is set to true. (default=true)
 * return array( 'name'=>(html), 'value'=>(html) ).
 */
function xnpGetIndexDetailBlock($item_id, $button_flag = true)
{
    $xnpsid = $_SESSION['XNPSID'];

    $uid = 0;
    if (isset($_SESSION['xoopsUserId'])) {
        $uid = $_SESSION['xoopsUserId'];
    }

    $indexes = array();
    $result = xnpGetIndexes($xnpsid, $item_id, $indexes);
    if (0 == $result) {
        $len = count($indexes);
        $xids = array();
        $ar = array('<table>'."\n");
        for ($i = 0; $i < $len; ++$i) {
            $xid = $indexes[$i]['item_id'];
            $str = xnpGetIndexPathString($xnpsid, $xid);
            $state = NOT_CERTIFIED;
            if (RES_OK == xnp_get_certify_state($xnpsid, $xid, $item_id, $state)) {
                $indexes[$i]['certified'] = $state;
            }
            $xids[] = $xid;
        }
        usort($indexes, 'indexcmp');
        $groupby = array();
        for ($i = 0; $i < $len; ++$i) {
            $open_level = $indexes[$i]['open_level'];
            $owner_gid = $indexes[$i]['owner_gid'];
            $certified = $indexes[$i]['certified'];
            if (!array_key_exists($open_level, $groupby)) {
                $groupby[$open_level] = array();
            }
            if (!array_key_exists($owner_gid, $groupby[$open_level])) {
                $groupby[$open_level][$owner_gid] = array();
            }
            if (!array_key_exists($certified, $groupby[$open_level][$owner_gid])) {
                $groupby[$open_level][$owner_gid][$certified] = array();
            }
            array_push($groupby[$open_level][$owner_gid][$certified], $i);
        }

        $classes = array('odd', 'even');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        foreach ($groupby as $open_level => $i) {
            foreach ($i as $owner_gid => $j) {
                foreach ($j as $certified => $k) {
                    $xid_array = array();
                    $html = '<tr class="'.$classes[0].'">';
                    $classes = array_reverse($classes);
                    $html .= '<td style="vertical-align: middle;">';
                    foreach ($k as $id) {
                        $xid = $indexes[$id]['item_id'];
                        $html .= '<a href="listitem.php?index_id='.$xid.'">'.xnpGetIndexPathString($xnpsid, $xid).'</a>';
                        $html .= '<br />';
                        array_push($xid_array, $xid);
                    }
                    $html .= '</td>';

                    $buttons = '';
                    if ($button_flag) {
                        if ($index_item_link_handler->getPerm($xid, $item_id, $uid, 'withdraw')) {
                            $buttons .= '<input class="formButton" type="button" value="'._MD_XOONIPS_ITEM_WITHDRAW_BUTTON_LABEL.'" onclick="xoonips_certify_confirm( ['.implode(',', $xid_array).'], '.$item_id.', \'withdraw\');"/>';
                        }
                        if ($index_item_link_handler->getPerm($xid, $item_id, $uid, 'accept')) {
                            $buttons .= '<input class="formButton" type="button" value="'._MD_XOONIPS_ITEM_CERTIFY_BUTTON_LABEL.'" onclick="xoonips_certify_confirm( ['.implode(',', $xid_array).'], '.$item_id.', \'accept_certify\');"/>';
                        }
                        if ($index_item_link_handler->getPerm($xid, $item_id, $uid, 'reject')) {
                            $buttons .= '<input class="formButton" type="button" value="'._MD_XOONIPS_ITEM_UNCERTIFY_BUTTON_LABEL.'" onclick="xoonips_certify_confirm( ['.implode(',', $xid_array).'], '.$item_id.', \'reject_certify\');"/>';
                        }
                    }
                    if ('' === $buttons && CERTIFY_REQUIRED == $certified && (OL_PUBLIC == $open_level || OL_GROUP_ONLY == $open_level)) {
                        $buttons = _MD_XOONIPS_ITEM_PENDING_NOW;
                    }
                    $html .= '<td style="vertical-align: middle; text-align: left;">'.$buttons.'</td>';
                    $html .= '</tr>'."\n";
                    $ar[] .= $html;
                }
            }
        }
        $ar[] = '</table>'."\n";

        $block = array();
        $block['name'] = 'Index';
        $block['value'] = implode('', $ar);
        $block['hidden'] = xnpCreateHidden('xoonipsCheckedXID', implode(',', $xids));

        return $block;
    }
    // todo
    return false;
}

/**
 * upload file inserts into database. and file is moved at hand.
 * todo: Should I check the authority of the item_id?
 *
 * @param name input tag name used in upload
 * @param item_id  item_id. false (in register)
 * @param escKeyVal column which need to add Insert sentence of SQL
 *
 * @return return array(file_id,errorMessage) normal array:errorMessage=false, error array:file_id=false
 */
function xnpUploadFile($name, $keyval)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');

    $esc_sess_id = $xoopsDB->quoteString($_SESSION['XNPSID']);

    // get file_type_id
    $sql = 'SELECT `file_type_id` FROM '.$xoopsDB->prefix('xoonips_file_type').' WHERE `name`='.$xoopsDB->quoteString($name);
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    list($fileTypeID) = $xoopsDB->fetchRow($result);
    if (empty($fileTypeID)) {
        return array(false, 'xnpUploadFile: no filetype '.$name);
    }

    // register file table
    $formdata = &xoonips_getutility('formdata');
    $file = $formdata->getFile($name, false);
    $ar = array(
        'original_file_name' => $file['name'],
        'mime_type' => $file['type'],
    );
    xnpTrimColumn($ar, 'xoonips_file', array_keys($ar), _CHARSET);

    // record in file table
    $escOriginalFileName = $xoopsDB->quoteString($ar['original_file_name']);
    $escMimeType = $xoopsDB->quoteString($ar['mime_type']);
    $fileSize = (int) $file['size'];
    $escKeys = '';
    $escVals = '';
    if (is_array($keyval) && 0 != count($keyval)) {
        foreach ($keyval as $key => $val) {
            $escKeys .= ', '.'`'.str_replace('`', '``', $key).'`';
            $escVals .= ', '.$xoopsDB->quoteString($val);
        }
    }
    $error = (int) $file['error'];
    if (0 != $error) {
        if (UPLOAD_ERR_INI_SIZE == $error) {
            $errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_TOO_LARGE;
        } else {
            $errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_FAILED;
        }

        return array(false, $errorMessage);
    }
    $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_file').
        ' (`original_file_name`, `mime_type`, `file_size`, `sess_id`, `item_id`, `file_type_id`'.$escKeys.')'.
        ' VALUES ('.$escOriginalFileName.', '.$escMimeType.', '.$fileSize.', '.$esc_sess_id.', NULL, '.$fileTypeID.$escVals.')';
    $result = $xoopsDB->queryF($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }

    // file is moved at hand.
    $fileID = $xoopsDB->getInsertId();
    $filePath = xnpGetUploadFilePath($fileID);
    $uploadTmpDirPath = ini_get('upload_tmp_dir');
    if (!empty($uploadTmpDirPath)) {
        if (!is_writable($uploadTmpDirPath)) {
            return array(false, 'xnpUploadFile: cannot write. in '.$uploadTmpDirPath);
        }
    }
    if (!is_writable(xnpGetUploadDir())) {
        return array(false, 'xnpUploadFile: cannot write. in '.xnpGetUploadDir());
    } else {
        $result = move_uploaded_file($file['tmp_name'], $filePath);
        if (false == $result) {
            return array(false, 'xnpUploadFile: failed to move uploaded file.');
        }
    }

    // create search text
    $admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
    $admin_file_handler->updateFileSearchText($fileID, true);

    return array($fileID, false);
}

/**
 * generate PreviewBlock HTML in edit page.
 *
 * @param item_id false(no HTML)
 */
function xnpGetPreviewEditBlock($item_id)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    $errorHTML = $errorMessage = $errorMessage1 = $errorMessage2 = '';

    $files = array();
    $formdata = &xoonips_getutility('formdata');
    $previewFileID = $formdata->getValue('post', 'previewFileID', 's', false);
    if (isset($previewFileID)) { // User comes from some edit pages.
        if ('' == $previewFileID) {
            $previewFileIDs = array();
        } else {
            // illegal inputs are removed.
            if (!xnpIsCommaSeparatedNumber($previewFileID)) {
                xoonips_error_exit(400);
            }
            $previewFileIDs = explode(',', $previewFileID);
        }
        // process of upload/delete
        $mode = $formdata->getValue('post', 'mode', 's', false);
        if (empty($mode)) {
        } elseif ('Upload' == $mode) {
            // upload something
            $preview = $formdata->getFile('preview', false);
            if (!empty($preview['name'])) {
                $keyval = array();
                $keyval['caption'] = $formdata->getValue('post', 'previewCaption', 's', false);
                xnpTrimColumn($keyval, 'xoonips_file', array_keys($keyval), _CHARSET);
                if (!is_uploaded_file($preview['tmp_name'])) {
                    $errorMessage = "Unexpected file(not uploaded file?). '".$preview['name']."'";
                } else {
                    $preview = $formdata->getFile('preview', false);
                    $fileutil = &xoonips_getutility('file');
                    $keyval['thumbnail_file'] = $fileutil->get_thumbnail($preview['tmp_name'], $preview['type']);
                    if (empty($keyval['thumbnail_file'])) {
                        // unknown image formats
                        $errorMessage = _MD_XOONIPS_ITEM_THUMBNAIL_BAD_FILETYPE;
                    } else {
                        list($fileID, $errorMessage2) = xnpUploadFile('preview', $keyval);
                        if ($fileID) {
                            $previewFileIDs[] = $fileID;
                        }
                        if ($errorMessage2) {
                            $errorMessage = $errorMessage2;
                        }
                    }
                }
                $_SESSION['xoonips_preview_message'] = $errorMessage;
            } elseif (isset($_SESSION['xoonips_preview_message'])) {
                // error message is set to $_SESSION, and it is acquired in GET
                // ( POST is converted into GET at edit.php, and register.php ).
                $errorMessage = $_SESSION['xoonips_preview_message'];
            }
        } elseif ('Delete' == $mode) {
            // $_POST['file_id'] delete from $previewFileID.
            $ar = array();
            $fileID = $formdata->getValue('post', 'fileID', 'i', false);
            foreach ($previewFileIDs as $value) {
                if ($value != $fileID) {
                    $ar[] = $value;
                }
            }
            $previewFileIDs = $ar;
        }

        // previewFileID -> files
        $previewFileIDs = array_map('intval', $previewFileIDs);
        if (count($previewFileIDs)) {
            $sql = '`t_file`.`file_id` IN ('.implode(',', $previewFileIDs).')';
            $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`caption`', '`t_file_type`.`name`=\'preview\' AND '.$sql, $item_id);
        }
        // Value of previewFileID are returned to $_POST ( Value of $_POST is saved and restored on register.php ).
        $formdata->set('post', 'previewFileID', implode(',', $previewFileIDs));
    } else {
        // user comes from non-editing pages.
        // get default value from database.
        if (!empty($item_id)) {
            $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`caption`', '`t_file_type`.`name`=\'preview\' AND `is_deleted`=0 AND `item_id`='.$item_id, $item_id);
        }
    }

    // display files in HTML format.
    $ar = array();
    reset($files);
    foreach ($files as $files_) {
        list($fileID, $caption) = $files_;
        $ar[] = $fileID;
    }
    $previewFileID = implode(',', $ar);
    if ($errorMessage) {
        $errorHTML = '<span style="color: red;">'.$textutil->html_special_chars($errorMessage).'</span><br />';
    }
    $uploadHtml = '<input type="hidden" name="previewFileID" value="'.$previewFileID.'"/>'.$errorHTML;
    $uploadHtml .= '<table>';
    $uploadHtml .= '<tr>';
    $uploadHtml .= '<td style="width: 100px;">'._MD_XOONIPS_ITEM_FILE_HEAD_LABEL.'</td>';
    $uploadHtml .= '<td><input size="30" type="file" name="preview"/>';
    $uploadHtml .= '<input class="formButton" type="button" name="preview_upload_button_'.$fileID.'" value="'._MD_XOONIPS_ITEM_UPLOAD_LABEL.'" onclick="xnpSubmitFileUpload(this.form, \'preview\')"/></td>';
    $uploadHtml .= '</tr>';
    $uploadHtml .= '<tr>';
    $uploadHtml .= '<td style="width: 100px;">'._MD_XOONIPS_ITEM_CAPTION_HEAD_LABEL.'</td>';
    $uploadHtml .= '<td><input size="30" type="text" name="previewCaption"/></td>';
    $uploadHtml .= '</tr>';
    $uploadHtml .= '</table>'."\n";

    $imageHtml1 = array();
    $imageHtml2 = array();
    $imageHtml3 = array();
    reset($files);
    foreach ($files as $files_) {
        list($fileID, $caption) = $files_;
        $thumbnailFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID&amp;thumbnail=1";
        $imageFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID";
        $htmlCaption = $textutil->html_special_chars($caption);
        $imageHtml1[] = '<a href="'.$imageFileName.'" target="_blank"><img src="'.$thumbnailFileName.'" alt="thumbnail"/></a>';
        $imageHtml2[] = "$htmlCaption";
        $imageHtml3[] = '<input class="formButton" type="button" name="preview_delete_button_'.$fileID.'" value="'._MD_XOONIPS_ITEM_DELETE_BUTTON_LABEL.'" onclick="xnpSubmitFileDelete( this.form, \'preview\', '.$fileID.' )"/>';
    }
    $html = xnpMakeTable(array($imageHtml1, $imageHtml2, $imageHtml3), 3);

    return array('name' => _MD_XOONIPS_ITEM_PREVIEW_LABEL, 'value' => $uploadHtml.$html);
}

function xnpGetAttachmentEditBlock($item_id, $name)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $sql = '`t_file`.`file_id`, `t_file`.`original_file_name`, `t_file`.`file_size`';
    // get file information.
    $fileID = $formdata->getValue('post', $name.'FileID', 'i', false);
    if (isset($fileID)) {
        // user comes from Confirm/Edit/Register page.
        if ($fileID) {
            $fileInfo = xnpGetFileInfo($sql, '`t_file`.`file_id`='.$fileID, $item_id);
        }
        // there is a deletion demand of a file
        $deleteFileID = $formdata->getValue('post', 'fileID', 'i', false);
        if ('Delete' == $formdata->getValue('post', 'mode', 's', false, '') && $fileID == $deleteFileID) {
            $fileInfo = false;
        }
    } elseif (!empty($item_id)) {
        // get default value from database.
        $fileInfo = xnpGetFileInfo($sql, '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    }

    // generate html
    if (empty($fileInfo)) {
        $fileID = false;
        $fileInfoLine = '';
    } else {
        list(list($fileID, $fileName, $fileSize)) = $fileInfo;
        $fileName = $textutil->html_special_chars($fileName);
        $fileInfoLine = $fileName.' ('.$fileSize.' Bytes)'.
           ' <input class="formButton" type="button" name="file_delete_button_'.$fileID.'" value="'._MD_XOONIPS_ITEM_DELETE_BUTTON_LABEL.'" onclick="xnpSubmitFileDelete(this.form, \''.$name.'\', '.$fileID.')" />';
    }
    $html = '<input type="hidden" name="'.$name.'FileID" value="'.$fileID.'" />'.
        '<input type="file" name="'.$name.'" size="50" /><br />'.
        $fileInfoLine;

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

/**
 * @param dirname string dirname
 * @param option download limit option(0:everyone, 1:login user only)
 */
function xnpGetDownloadLimitationOptionEditBlock($dirname, $option)
{
    return xnpGetDownloadLimitationOptionRegisterBlock($dirname, $option);
}

function xnpGetDownloadLimitationOptionRegisterBlock($dirname, $option = 0)
{
    global $xoopsDB;
    $mhandler        =xoops_getHandler('module');
    $module          = $mhandler->getByDirname($dirname);
    $chandler        =xoops_getHandler('config');
    $assoc           = $chandler->getConfigsByCat(false, $module->mid());
    $enable_dl_limit = 0;
    if (isset($assoc['enable_dl_limit']) && '1' == $assoc['enable_dl_limit']) {
        $enable_dl_limit = 1;
    }

    if (1 == $enable_dl_limit) {
        $formdata = &xoonips_getutility('formdata');
        $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
        if (isset($attachment_dl_limit)) {
            $option = $attachment_dl_limit;
        }
        $html = '<input type="radio" name="attachment_dl_limit" value="1"';
        if (1 == $option) {
            $html .= ' checked="checked"';
        }

        $html .= ' />'._MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_LOGINUSER_LABEL.
            '<input type="radio" name="attachment_dl_limit" value="0"';
        if (1 != $option) {
            $html .= ' checked="checked"';
        }
        $html .= ' />'._MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_EVERYONE_LABEL;
    } else {
        $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_EVERYONE_LABEL.
            '<input type="hidden" name="attachment_dl_limit" value="0" />';
    }

    return array('name' => _MD_XOONIPS_DOWNLOAD_LIMITATION_OPTION_LABEL, 'value' => $html);
}

/**
 * @param string $dirname
 */
function xnpGetDownloadLimitationOptionConfirmBlock($dirname)
{
    global $xoopsDB;
    $mhandler        =xoops_getHandler('module');
    $module          = $mhandler->getByDirname($dirname);
    $chandler        =xoops_getHandler('config');
    $assoc           = $chandler->getConfigsByCat(false, $module->mid());
    $enable_dl_limit = 0;
    if (isset($assoc['enable_dl_limit']) && '1' == $assoc['enable_dl_limit']) {
        $enable_dl_limit = 1;
    }
    $html = '';
    if (1 == $enable_dl_limit) {
        $formdata = &xoonips_getutility('formdata');
        $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
        if (isset($attachment_dl_limit) && 1 == $attachment_dl_limit) {
            $html .= _MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_LOGINUSER_LABEL
                ."<input type='hidden' name='attachment_dl_limit' value='1'/>";
        } else {
            $html .= _MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_EVERYONE_LABEL
                ."<input type='hidden' name='attachment_dl_limit' value='0'/>";
        }
    } else {
        $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_LIMIT_EVERYONE_LABEL
            ."<input type='hidden' name='attachment_dl_limit' value='0'/>";
    }

    return array('name' => _MD_XOONIPS_DOWNLOAD_LIMITATION_OPTION_LABEL, 'value' => $html);
}

/**
 * @param dirname string dirname
 * @param option download limit option(0:everyone, 1:login user only)
 */
function xnpGetDownloadNotificationOptionEditBlock($dirname, $option)
{
    return xnpGetDownloadNotificationOptionRegisterBlock($dirname, $option);
}

function xnpGetDownloadNotificationOptionRegisterBlock($dirname, $option = 0)
{
    global $xoopsDB;
    $mhandler =xoops_getHandler('module');
    $module   = $mhandler->getByDirname($dirname);
    $chandler =xoops_getHandler('config');
    $assoc    = $chandler->getConfigsByCat(false, $module->mid());

    if (isset($assoc['enable_dl_limit']) && '1' == $assoc['enable_dl_limit']) {
        $formdata = &xoonips_getutility('formdata');
        $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
        if (isset($attachment_dl_notify)) {
            $option = $attachment_dl_notify;
        }
        $html = "<input type='radio' name='attachment_dl_notify' value='1'";
        if (1 == $option) {
            $html .= ' checked="checked"';
        }

        $html .= ' />'._MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DO_LABEL
            ."<input type='radio' name='attachment_dl_notify' value='0'";
        if (1 != $option) {
            $html .= ' checked="checked"';
        }
        $html .= ' />'._MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DONT_LABEL
            .'<br /> '._MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_EXPLANATION;
    } else {
        $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DONT_LABEL
            ."<input type='hidden' name='attachment_dl_notify' value='0'/>";
    }

    return array('name' => _MD_XOONIPS_DOWNLOAD_NOTIFICATION_OPTION_LABEL, 'value' => $html);
}
/**
 * @param string $dirname
 */
function xnpGetDownloadNotificationOptionConfirmBlock($dirname)
{
    global $xoopsDB;
    $mhandler =xoops_getHandler('module');
    $module   = $mhandler->getByDirname($dirname);
    $chandler =xoops_getHandler('config');
    $assoc    = $chandler->getConfigsByCat(false, $module->mid());
    if (isset($assoc['enable_dl_limit']) && '1' == $assoc['enable_dl_limit']) {
        $formdata = &xoonips_getutility('formdata');
        $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
        if (isset($attachment_dl_notify) && 1 == $attachment_dl_notify) {
            $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DO_LABEL
                ."<input type='hidden' name='attachment_dl_notify' value='1'/>";
        } else {
            $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DONT_LABEL
                ."<input type='hidden' name='attachment_dl_notify' value='0'/>";
        }
    } else {
        $html = _MD_XOONIPS_ITEM_ATTACHMENT_DL_NOTIFY_DONT_LABEL
            ."<input type='hidden' name='attachment_dl_notify' value='0'/>";
    }

    return array('name' => _MD_XOONIPS_DOWNLOAD_NOTIFICATION_OPTION_LABEL, 'value' => $html);
}

function xnpHeadText($text)
{
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);
    $ar = preg_split("/[\r\n]+/", $text);
    if (count($ar) > 4 || 4 == count($ar) && '' != $ar[3]) {
        $text = $ar[0]."\n".$ar[1]."\n".$ar[2].' ...';
    }

    return $text;
}

function xnpGetTextFileEditBlock($item_id, $name, $defaultText)
{
    $textutil = &xoonips_getutility('text');
    // select, text, fileInfo
    $item_id = (int) $item_id;
    $formdata = &xoonips_getutility('formdata');
    $text = $formdata->getValue('post', $name.'EncText', 's', false);
    if (!isset($text)) {
        // There is no initial value specification by POST. use the value of $defaultText.
        $text = $defaultText;
    }

    $showText = xnpHeadText($text);
    $encText = $textutil->html_special_chars($text);
    $htmlShowText = nl2br($textutil->html_special_chars($showText));
    if ('' == $htmlShowText) {
        $htmlShowText = '&nbsp;'; // div.firstChild is prevented being set to null.
    }
    $html = "
    <table width='100%'><tr>
    <td>
        <div id='${name}ShowText'>$htmlShowText</div>
    </td>\n
    <td style='vertical-align: text-bottom; text-align:right'><a href='#' onclick=\"return xnpOpenTextFileInputWindow('$name',$item_id)\">"._MD_XOONIPS_ITEM_TEXT_FILE_EDIT_LABEL."</a></td>\n
    </tr></table>\n
    <input type='hidden' name='${name}EncText' value='$encText'  id='${name}EncText' />";

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

function xnpGetIndexEditBlock($item_id)
{
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (null == $xoonipsCheckedXID) {
        $indexes = array();
        $result = xnpGetIndexes($xnpsid, $item_id, $indexes);
        if (0 == $result) {
            $xids = array();
            foreach ($indexes as $x) {
                $xids[] = $x['item_id'];
            }
            $formdata->set('post', 'xoonipsCheckedXID', implode(',', $xids));
        }
    }
    //generate html to display index from $_POST
    return xnpGetIndexRegisterBlock($item_id);
}

function xnpGetPreviewPrinterFriendlyBlock($item_id)
{
    return xnpGetPreviewDetailBlock($item_id);
}

/**
 * @param string $name
 */
function xnpGetAttachmentPrinterFriendlyBlock($item_id, $name)
{
    return xnpGetAttachmentDetailBlock($item_id, $name);
}

/**
 * @param string $name
 */
function xnpGetTextFilePrinterFriendlyBlock($item_id, $name, $text)
{
    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts =MyTextSanitizer::getInstance();
    $textutil = &xoonips_getutility('text');

    return array('name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL, 'value' => $myts->nl2Br($textutil->html_special_chars($text)));
}

function xnpGetIndexPrinterFriendlyBlock($item_id)
{
    return xnpGetIndexDetailBlock($item_id, false);
}

/**
 * @return string
 */
function xnpWithinWithoutHtml($within, $without)
{
    $textutil = &xoonips_getutility('text');
    if ($without) {
        return sprintf('%s<span style="color:red;">%s</span>', $textutil->html_special_chars($within), $textutil->html_special_chars($without));
    } else {
        return $textutil->html_special_chars($within);
    }
}

function xnpGetPreviewConfirmBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $previewFileID = $formdata->getValue('post', 'previewFileID', 's', false);
    if (empty($previewFileID)) {
        $html = '';
    } else {
        // illegal inputs are removed.
        if (!xnpIsCommaSeparatedNumber($previewFileID)) {
            xoonips_error_exit(400);
        }

        // get preview file
        $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`caption`', '`t_file_type`.`name`=\'preview\' AND `t_file`.`file_id` IN ('.$previewFileID.')', $item_id);

        // generate html
        reset($files);
        $imageHtml1 = array();
        $imageHtml2 = array();
        foreach ($files as $files_) {
            list($fileID, $caption) = $files_;
            $thumbnailFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID&amp;thumbnail=1";
            $imageFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID";
            $htmlCaption = $textutil->html_special_chars($caption);
            $imageHtml1[] = "<a href='$imageFileName' target='_blank'><img src='$thumbnailFileName' alt='thumbnail'/></a>";
            $imageHtml2[] = "$htmlCaption";
        }
        $html = xnpMakeTable(array($imageHtml1, $imageHtml2), 3)."<input type='hidden' name='previewFileID' value='$previewFileID' />";
    }

    return array('name' => _MD_XOONIPS_ITEM_PREVIEW_LABEL, 'value' => $html);
}

/**
 * @param string $name
 */
function xnpGetAttachmentConfirmBlock($item_id, $name)
{
    global $xoopsDB;
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    if (!empty($_FILES[$name]['name'])) {
        // Upload file
        list($fileID, $errorMessage) = xnpUploadFile($name, false);
        if (false == $fileID) {
            global $system_message;
            $system_message = $system_message."\n".'<br /><span style="color: red;">'.$textutil->html_special_chars($errorMessage).'</span><br />';

            return false;
        } else {
            $sql = '`t_file`.`file_id`='.$fileID;
        }
    } else {
        $attachmentFileID = $formdata->getValue('post', $name.'FileID', 'i', false, 0);
        if (0 == $attachmentFileID) {
            // no attachment file.
            $sql = ' 0 ';
        } else {
            $sql = '`t_file`.`file_id`='.$attachmentFileID;
        }
    }

    $files = xnpGetFileInfo('`t_file`.`file_id`, `t_file`.`original_file_name`, `t_file`.`file_size`, `t_file`.`mime_type`, UNIX_TIMESTAMP(`t_file`.`timestamp`)', '`t_file_type`.`name`='.$xoopsDB->quoteString($name).' AND `is_deleted`=0 AND '.$sql, $item_id);

    if (0 == count($files)) {
        $html = "<input type='hidden' name='${name}FileID' value='' />";
    } else {
        list(list($fileID, $fileName, $fileSize, $mimeType, $timestamp)) = $files;
        $html =
        "<input type='hidden' name='${name}FileID' value='$fileID' /> ".$textutil->html_special_chars($fileName).'<br />
        <table>
         <tr>
            <td>'._MD_XOONIPS_ITEM_TYPE_LABEL.'</td>
            <td>: '.$textutil->html_special_chars($mimeType).'</td>
         </tr>
         <tr>
            <td>'._MD_XOONIPS_ITEM_SIZE_LABEL."</td>
            <td>: $fileSize bytes</td>
         </tr>
         <tr>
            <td>"._MD_XOONIPS_ITEM_LAST_UPDATED_LABEL.'</td>
            <td>: '.date(DATE_FORMAT, $timestamp).'</td>
         </tr>
        </table>';
    }

    // get attachment file
    // generate html
    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

/**
 * @param string $name
 */
function xnpGetTextFileConfirmBlock($item_id, $name, $maxlen = 65535)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $text = $formdata->getValue('post', $name.'EncText', 's', false);
    list($within, $without) = xnpTrimString($text, $maxlen, _CHARSET);

    $htmlShowWithin = nl2br($textutil->html_special_chars($within));
    $htmlShowWithout = nl2br($textutil->html_special_chars($without));

    $html = $htmlShowWithin;
    if (!empty($htmlShowWithout)) {
        $html .= '<span style="color: red;">'.$htmlShowWithout.'</span>';
    }
    $html .= "<input type='hidden' name='${name}EncText' value='".$textutil->html_special_chars($within.$without)."' />";

    return array('name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL, 'value' => $html, 'within' => $within, 'without' => $without);
}

function xnpGetIndexConfirmBlock($item_id)
{
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    $index_ids = explode(',', $xoonipsCheckedXID);
    $indexes = array();
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    foreach ($index_ids as $xid) {
        if (!$index_handler->getPerm($xid, $_SESSION['xoopsUserId'], 'read')) {
            continue;
        }
        $str = xnpGetIndexPathString($xnpsid, $xid);
        $indexes[$xid] = "$str";
    }

    return array('name' => _MD_XOONIPS_ITEM_INDEX_LABEL, 'value' => implode('<br />', array_values($indexes)));
}

function xnpGetPreviewRegisterBlock()
{
    return xnpGetPreviewEditBlock(false);
}

/**
 * @param string $name
 */
function xnpGetAttachmentRegisterBlock($name)
{
    return xnpGetAttachmentEditBlock(false, $name);
}

/**
 * @param string $name
 */
function xnpGetTextFileRegisterBlock($name)
{
    return xnpGetTextFileEditBlock(false, $name, '');
}

function xnpGetIndexRegisterBlock()
{
    $xnpsid = $_SESSION['XNPSID'];
    $indexes = array();
    $formdata = &xoonips_getutility('formdata');
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (isset($xoonipsCheckedXID)) {
        $index_ids = explode(',', $xoonipsCheckedXID);
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_ids as $xid) {
            if ($xid > 0) {
                if (!$index_handler->getPerm($xid, $_SESSION['xoopsUserId'], 'read')) {
                    continue;
                }
                $str = xnpGetIndexPathString($xnpsid, $xid);
                $indexes[$xid] = "$str";
            }
        }
    }
    if (0 == count($indexes)) {
        return array('name' => _MD_XOONIPS_ITEM_INDEX_LABEL._MD_XOONIPS_ITEM_REQUIRED_MARK);
    } else {
        return array('name' => _MD_XOONIPS_ITEM_INDEX_LABEL._MD_XOONIPS_ITEM_REQUIRED_MARK, 'value' => implode('<br />', array_values($indexes)));
    }
}

function xnpUpdateIndex($item_id)
{
    //1. get $_POST['xoonipsCheckedXID'].
    //2. get registered index (before change index) using item_id.
    //3. function 'unregisterItem' is executed for index (2-(1 and 2)) deleted by change.
    //4. function 'registerItem' is executed for index (1-(1 and 2)) added by change.
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (null === $xoonipsCheckedXID) {
        return true;
    }
    $xids_new = explode(',', $xoonipsCheckedXID);

    $item = array();
    $xids_now = array();
    if (RES_OK == ($result = xnp_get_item($xnpsid, $item_id, $item))) {
        //retrieve index id if item exists
        if (RES_OK != xnp_get_index_id_by_item_id($xnpsid, $item_id, $xids_now)) {
            return false;
        }
    }

    $intersect = array_intersect($xids_new, $xids_now);
    $del = array_diff($xids_now, $intersect); // index id shuld be removed
    $add = array_diff($xids_new, $intersect); // index id shuld be inserted
    foreach ($del as $i) {
        xnp_unregister_item($xnpsid, $i, $item_id);
    }
    foreach ($add as $i) {
        xnp_register_item($xnpsid, $i, $item_id);
    }

    return true;
}

// insert event(REQUEST_CERTIFY_ITEM, CERTIFY_ITEM) and send notification(certify request, certified auto).
// don't call this if only private index was modified.
// should be called after inserting/updating basic information.
function xoonips_insert_event_and_send_notification_of_certification($item_id)
{
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (empty($xoonipsCheckedXID) || !xnpIsCommaSeparatedNumber($xoonipsCheckedXID)) {
        return;
    }
    $index_ids = explode(',', $xoonipsCheckedXID);

    $certify_item = $xconfig_handler->getValue('certify_item');
    if (is_null($certify_item)) {
        $certify_item = 'on';
    }

    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    foreach ($index_ids as $i) {
        $index = array();
        $result = xnp_get_index($xnpsid, $i, $index);
        if (RES_OK == $result) {
            if (OL_PRIVATE == $index['open_level']) {
                continue;
            }

            // record events(request certify item)
            $eventlog_handler->recordRequestCertifyItemEvent($item_id, $i);
            if ('auto' == $certify_item) {
                // record events(certify item)
                $eventlog_handler->recordCertifyItemEvent($item_id, $i);
            }
        }
    }
    if ('auto' == $certify_item) {
        xoonips_notification_item_certified_auto($item_id, $index_ids);
    } elseif ('on' == $certify_item) {
        xoonips_notification_item_certify_request($item_id, $index_ids);
    }
}

function xnpUpdatePreview($item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    // File under registration relates to this item.
    $previewFileID = $formdata->getValue('post', 'previewFileID', 's', false, '');
    $table = $xoopsDB->prefix('xoonips_file');
    $esc_sess_id = $xoopsDB->quoteString(session_id());
    $file_type_id = 1;

    if (empty($previewFileID)) {
        $sql = 'UPDATE '.$table.' SET `sess_id`='.$esc_sess_id.', `item_id`=NULL WHERE `item_id`='.$item_id.' AND `file_type_id`='.$file_type_id;
        $result = $xoopsDB->queryF($sql);
    } else {
        if (!xnpIsCommaSeparatedNumber($previewFileID)) {
            xoonips_error_exit(400);
        }
        $sql = 'UPDATE '.$table.' SET `sess_id`='.$esc_sess_id.', `item_id`=NULL WHERE `item_id`='.$item_id.' AND `file_id` NOT IN ('.$previewFileID.') AND `file_type_id`='.$file_type_id;
        $result = $xoopsDB->queryF($sql);
        if (false == $result) {
            xoonips_error_exit(500);
        }
        $sql = 'UPDATE '.$table.' SET `sess_id`=NULL, `item_id`='.$item_id.' WHERE `sess_id`='.$esc_sess_id.' AND `file_id` IN ('.$previewFileID.') AND `file_type_id`='.$file_type_id;
        $result = $xoopsDB->queryF($sql);
    }
    if (false === $result) {
        xoonips_error_exit(500);
    }

    return $result;
}

/**
 * @param string $name
 */
function xnpUpdateAttachment($item_id, $name)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    // File under registration relates to this item.
    $fileID = $formdata->getValue('post', $name.'FileID', 'i', false, 0);
    $table = $xoopsDB->prefix('xoonips_file');
    $esc_sess_id = $xoopsDB->quoteString(session_id());

    // name -> file_type_id
    $sql = 'SELECT `file_type_id` FROM '.$xoopsDB->prefix('xoonips_file_type').' WHERE `name`='.$xoopsDB->quoteString($name);
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    list($file_type_id) = $xoopsDB->fetchRow($result);

    // delete old file
    $sql = 'SELECT `file_id`, `is_deleted` FROM '.$table.' WHERE `item_id`='.$item_id.' AND `file_type_id`='.$file_type_id.' AND `is_deleted`=0 AND `file_id`<>'.$fileID;
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    while (list($file_id, $is_deleted) = $xoopsDB->fetchRow($result)) {
        $path = xnpGetUploadFilePath($file_id);
        if (is_file($path)) {
            unlink($path);
        }
        $result = $xoopsDB->queryF('UPDATE '.$table.' SET `is_deleted`=1 WHERE `file_id`='.$file_id);
        if (false === $result) {
            xoonips_error_exit(500);
        }
    }

    if (!empty($fileID)) {
        $sql = 'UPDATE '.$table.' SET `sess_id`=NULL, `item_id`='.$item_id.' WHERE `sess_id`='.$esc_sess_id.' AND `file_id`='.$fileID.' AND `file_type_id`='.$file_type_id;
        $result = $xoopsDB->queryF($sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }
    }

    return true;
}

/**
 * function of getting readme/rights contents on the following page of confirm.
 *
 * @param name  string
 *
 * @return contents empty character strings in error
 */
function xnpGetTextFile($name)
{
    $formdata = &xoonips_getutility('formdata');

    return $formdata->getValue('post', $name.'EncText', 's', false);
}

/**
 * @param string $moduleName
 */
function xnpGetBasicInformationAdvancedSearchBlock($moduleName, &$search_var)
{
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign('prefixFrom', $moduleName.'_publication_date_from');
    $tpl->assign('prefixTo', $moduleName.'_publication_date_to');
    $tpl->assign('gmtimeFrom', time());
    $tpl->assign('gmtimeTo', time());

    $search_var[] = $moduleName;
    $search_var[] = $moduleName.'_title';
    $search_var[] = $moduleName.'_keywords';
    $search_var[] = $moduleName.'_description';
    $search_var[] = $moduleName.'_doi';
    $search_var[] = $moduleName.'_publication_date_from';
    $search_var[] = $moduleName.'_publication_date_fromYear';
    $search_var[] = $moduleName.'_publication_date_fromMonth';
    $search_var[] = $moduleName.'_publication_date_fromDay';
    $search_var[] = $moduleName.'_publication_date_to';
    $search_var[] = $moduleName.'_publication_date_toYear';
    $search_var[] = $moduleName.'_publication_date_toMonth';
    $search_var[] = $moduleName.'_publication_date_toDay';

    return array(
        'title' => array('name' => _MD_XOONIPS_ITEM_TITLE_LABEL, 'value' => '<input type="text" name="'.$moduleName.'_title" value="" size="50"/>'),
        'keywords' => array('name' => _MD_XOONIPS_ITEM_KEYWORDS_LABEL, 'value' => '<input type="text" name="'.$moduleName.'_keywords" value="" size="50"/>'),
        'description' => array('name' => _MD_XOONIPS_ITEM_DESCRIPTION_LABEL, 'value' => '<input type="text" name="'.$moduleName.'_description" value="" size="50"/>'),
        'doi' => array('name' => _MD_XOONIPS_ITEM_DOI_LABEL, 'value' => '<input type="text" name="'.$moduleName.'_doi" value="" size="50"/>'),
        'publication_date' => array('name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL, 'value' => $tpl->fetch('db:xoonips_search_date.html')),
        'publication_year' => array('name' => _MD_XOONIPS_ITEM_PUBLICATION_YEAR_LABEL, 'value' => $tpl->fetch('db:xoonips_search_year.html')),
        'publication_month' => array('name' => _MD_XOONIPS_ITEM_PUBLICATION_MONTH_LABEL, 'value' => $tpl->fetch('db:xoonips_search_month.html')),
        'publication_mday' => array('name' => _MD_XOONIPS_ITEM_PUBLICATION_MDAY_LABEL, 'value' => $tpl->fetch('db:xoonips_search_mday.html')),
    );
}

/**
 * Input keyword is divided into the unit of the retrieval.
 *  the unit of the retrieval: character strings enclosed with delimitation by blank or double-quote.
 *
 * @param keywords Input keyword
 *
 * @return result divided into the unit of the retrieval. ex) 'foo "bar fobar"' -> array('foo', 'bar fobar')
 */
function xnpSplitKeywords($keywords)
{
    $match = array();
    preg_match_all('/(([^ "]+)|"([^"]+)")/', $keywords, $match, PREG_PATTERN_ORDER);

    /*
    input a% b% "hoge huga", the content of $match:
    array(
        array( "a%", "b%", '"hoge huga"' ),
        array( "a%", "b%", '"hoge huga"' ),
        array( "a%", "b%", "" ),
        array( "",    "",  "hoge huga" )
    )
    */

    $ar = array();
    for ($j = 2; $j <= 3; ++$j) {
        $len = count($match[$j]);
        for ($i = 0; $i < $len; ++$i) {
            $word = $match[$j][$i];
            if ('' == $word) {
                continue;
            }
            $ar[] = $word;
        }
    }

    return $ar;
}

/* memo:

    検索時:
        英語では
        m&#252;ller -> "m&#252;ller"
        m &#252;ller -> m "&#252;ller"
        単語境界は空白だけとする．&#を含む単語はフレーズ検索する．

        日本語なら
        ほげ&#252;ふが -> ウィンドウをかけて "ほげ げ &#252; ふ ふが"
        ほげ&#252;huga -> ウィンドウをかけて "ほげ げ &#252;huga"
            &#252;huga -> ウィンドウをかけて         "&#252;huga"
        &#はsbとみなす．&#を含むsb文字列はフレーズ検索する

        numericでascii単語が切られてしまうのは仕方が無い．
        252が&#252;にヒットするのは仕方が無い．
*/

/**
 * the unit of the retrieval and syntax are pulled out from input keyword.
 * the unit of the retrieval: 1.character strings don't contain blank and parentheses, and double-quote.
 *                            2.character strings enclosed with double-quote.
 * syntax: the unit of the retrieval in input keyword change 'string', and 'and' operator is supplemented to the 'string'.
 *
 * @param keyword input keyword
 *
 * @return array( elements, keywords, errorMessage )
 *                elements => syntax
 *                keywords => array of the unit of the retrieval
 *                errorMessage => error message
 *
 * ex: keyword  = '(a or b) "c(d or e)"'
 *  -> keywords = array('a', 'b', '"c(d or e)"')
 *  -> elements = array( '(', 'string', 'or', 'string', ')', 'and', 'string' )
 *  -> errorMessage = false
 *
 * WHERE of SQL is character strings that user inputs in Quick Search.
 *   list( keywords, elements, errorMessage ) = xnpSplitKeywords2( keyword ); // divide into the unit of the retrieval and syntax.
 *   wheres = xnpGetKeywordsQueries( array(...), keywords ); // the unit of the retrieval is converted into SQL.
 *   where = xnpUnsplitKeywords2( elements, wheres ); // SQL is applied to syntax.
 */
function xnpSplitKeywords2($keyword)
{
    $match = array();
    preg_match_all('/([^ "()]+)|(\()|(\))|"([^"]+)"/', $keyword, $match, PREG_SET_ORDER);
    $keywords = array();
    $elements = array();

    $nest = 0; // Depth of parentheses
    $expectTerm = true; // string or '(' is expected to get next time.

    foreach ($match as $match1) {
        $str = $match1[0];
        $lowerstr = strtolower($str);
        if ('(' == $str) {
            ++$nest;
            if (!$expectTerm) {
                $elements[] = 'and';
            }
            $expectTerm = true;
            $elements[] = $lowerstr;
        } elseif (')' == $str) {
            --$nest;
            if ($expectTerm || $nest < 0) {
                return array(array(), array(), _MD_XOONIPS_ITEM_SEARCH_SYNTAX_ERROR);
            }
            $expectTerm = false;
            $elements[] = $lowerstr;
        } elseif ('and' == $lowerstr || 'or' == $lowerstr) {
            if ($expectTerm) {
                return array(array(), array(), _MD_XOONIPS_ITEM_SEARCH_SYNTAX_ERROR);
            }
            $expectTerm = true;
            $elements[] = $lowerstr;
        } else {
            if ('"' == substr($str, 0, 1)) {
                $str = substr($str, 1, -1);
            }  // remove double-quote at both ends
            if (!$expectTerm) {
                $elements[] = 'and';
            }
            $expectTerm = false;
            $elements[] = 'string';

            $separated = xnpWordSeparation($str, false, false);
            $keywords[] = $str;
        }
    }
    if (0 != $nest || $expectTerm) {
        return array(array(), array(), _MD_XOONIPS_ITEM_SEARCH_SYNTAX_ERROR);
    }

    return array($elements, $keywords, false);
}

/**
 * generate a sentense from retrieval keyword (sentense is used in WHERE of SQL).
 *
 * @param elements input keyword is resolved with xnpSplitKeywords2
 * @param wheres character strings in retrieval keyword is converted into SQL sentense
 */
function xnpUnsplitKeywords2($elements, $wheres)
{
    $ar = array();
    $len = count($elements);

    if (0 == $len) {
        return ' 1 ';
    }
    $j = 0;
    for ($i = 0; $i < $len; ++$i) {
        $op = $elements[$i];
        if ('string' == $op) {
            $ar[] = '( '.$wheres[$j++].' )';
        } else {
            $ar[] = $op;
        }
    }

    return '('.implode(' ', $ar).')';
}

/**
 * return query of SQL generated from input keywords. If there is no condition, return "".
 *
 * @param dbVarName    string name, and column name in database
 * @param postVarName  string of variables posted
 */
function xnpGetKeywordQuery($dbVarName, $postVarName)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $postvar = $formdata->getValue('post', $postVarName, 'n', false);
    if (empty($postvar)) {
        return '';
    }
    $keywords = xnpSplitKeywords($postvar);
    if (0 == count($keywords)) {
        return '';
    }

    $ar = array();
    foreach ($keywords as $keyword) {
        $escKeyword = str_replace(array('_', '%'), array('\\_', '\\%'), substr($xoopsDB->quoteString($keyword), 1, -1));
        $ar[] = xnpGetKeywordQueryEntity($dbVarName, $escKeyword);
    }

    return implode(' AND ', $ar);
}

/**
 * return query of SQL generated from the keywords input.
 *
 * @param dbVarNames    array of table name and column name in database
 * @param keywords      array of keywords
 *
 * @return array of query ([n]: $keywords[n] is contained in one column in array of $dbVarNames at least.)
 */
function xnpGetKeywordsQueries($dbVarNames, $keywords)
{
    global $xoopsDB;
    $wheres = array();
    foreach ($keywords as $keyword) {
        $escKeyword = str_replace(array('_', '%'), array('\\_', '\\%'), substr($xoopsDB->quoteString($keyword), 1, -1));
        $ar = array(' 0 ');
        foreach ($dbVarNames as $dbVarName) {
            $ar[] = xnpGetKeywordQueryEntity($dbVarName, $escKeyword);
        }
        $wheres[] = implode(' OR ', $ar);
    }

    return $wheres;
}

/*
    avoid a number to hit numeric character reference(e.g. keyword '123' hits '&#11234;' ).
*/
/**
 * @param string $escKeyword
 */
function xnpGetKeywordQueryEntity($dbVarName, $escKeyword)
{
    return $dbVarName.' LIKE \'%'.$escKeyword.'%\'';
    /*
    // doesn't work
    if (preg_match("/\d{1,8}/", $escKeyword)) {
        if (preg_match("/&#\d{1,8};/", $escKeyword)) {
            $wk = "$dbVarName LIKE '%$escKeyword%'";
        } else {
            $num = sprintf('%d', $escKeyword);
            if ($num <= 0x10FFFF) {
                $digit = 7 - strlen($num);
                $wk = '(';
                $wk .= "$dbVarName = '$escKeyword'";
                $wk .= " OR $dbVarName LIKE '$escKeyword%'";
                $wk .= " OR $dbVarName RLIKE '$escKeyword"."[0-9]{0,$digit}[ -/:<-~]'";
                $wk .= " OR $dbVarName RLIKE '$escKeyword"."[0-9]{1,$digit}$'";
                $wk .= " OR $dbVarName LIKE '%$escKeyword'";
                $wk .= " OR $dbVarName RLIKE '[ -".'"'."$-/:-~][0-9]{0,$digit}$escKeyword'";
                $wk .= " OR $dbVarName RLIKE ".'"'."[ -%'-/:-~]#[0-9]{0,$digit}$escKeyword".'"';
                $wk .= " OR $dbVarName RLIKE '^[0-9]{1,$digit}$escKeyword'";
                $wk .= ')';
            } else {
                $wk = "$dbVarName LIKE '%$escKeyword%'";
            }
        }
    } else {
        $wk = "$dbVarName LIKE '%$escKeyword%'";
    }

    return $wk;
    */
}

/**
 * generate query of SQL
 "ifnull(y,0)*10000+ifnull(m,0)*100+ifnull(d,0)" is compared.
 * @param string $dbVarName
 * @param string $postVarName
 */
function xnpGetFromQuery($dbVarName, $postVarName)
{
    $formdata = &xoonips_getutility('formdata');
    $y = $formdata->getValue('post', $postVarName.'Year', 'i', false, 0);
    $m = $formdata->getValue('post', $postVarName.'Month', 'i', false, 0);
    $d = $formdata->getValue('post', $postVarName.'Day', 'i', false, 0);
    if (0 == $m) {
        $d = 0;
    }
    $yyyymmdd = $y * 10000 + $m * 100 + $d;
    $yyyymm = $y * 10000 + $m * 100;
    $yyyy = $y * 10000;

    return " ( ($yyyymmdd <= IFNULL(${dbVarName}_year,0)*10000 + IFNULL(${dbVarName}_month,0)*100 + IFNULL(${dbVarName}_mday,0)) OR (${dbVarName}_mday = 0 AND $yyyymm <= IFNULL(${dbVarName}_year,0)*10000 + IFNULL(${dbVarName}_month,0)*100) OR (${dbVarName}_month = 0 AND ${dbVarName}_mday = 0 AND $yyyy <= IFNULL(${dbVarName}_year,0)*10000) )";
}

/**
 * @param string $dbVarName
 * @param string $postVarName
 */
function xnpGetToQuery($dbVarName, $postVarName)
{
    $formdata = &xoonips_getutility('formdata');
    $y = $formdata->getValue('post', $postVarName.'Year', 'i', false, 0);
    $m = $formdata->getValue('post', $postVarName.'Month', 'i', false, 0);
    $d = $formdata->getValue('post', $postVarName.'Day', 'i', false, 0);
    if (0 == $y) {
        $y = 9999;
    }
    if (0 == $m) {
        $m = 99;
        $d = 0;
    }
    if (0 == $d) {
        $d = 99;
    }
    $yyyymmdd = $y * 10000 + $m * 100 + $d;
    $yyyymm = $y * 10000 + $m * 100;
    $yyyy = $y * 10000;

    return " ( ($yyyymmdd >= IFNULL(${dbVarName}_year,0)*10000 + IFNULL(${dbVarName}_month,0)*100 + IFNULL(${dbVarName}_mday,0)) OR (${dbVarName}_mday = 0 AND $yyyymm >= IFNULL(${dbVarName}_year,0)*10000 + IFNULL(${dbVarName}_month,0)*100) OR (${dbVarName}_month = 0 AND ${dbVarName}_mday = 0 AND $yyyy >= IFNULL(${dbVarName}_year,0)*10000) )";
}

/**
 * return query of SQL for retrieve Basic Information in Advanced Search. If there is no condition in input, return empty character strings.
 *
 * @param moduleName string of module
 *
 * @return string of SQL
 */
function xnpGetBasicInformationAdvancedSearchQuery($moduleName)
{
    $wheres = array();
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $title_table = $xoopsDB->prefix('xoonips_item_title');
    $keyword_table = $xoopsDB->prefix('xoonips_item_keyword');
    $w = xnpGetKeywordQuery($title_table.'.title', $moduleName.'_title');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($keyword_table.'.keyword', $moduleName.'_keywords');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($basic_table.'.description', $moduleName.'_description');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($basic_table.'.doi', $moduleName.'_doi');
    if ($w) {
        $keyword = $formdata->getValue('post', $moduleName.'_doi', 's', false, '');
        $w2 = sprintf('`%s`.`item_id`=%s', $basic_table, $xoopsDB->quoteString($keyword));
        $wheres[] = sprintf('(%s OR %s)', $w, $w2);
    }
    $publication_date_from = $formdata->getValue('post', $moduleName.'_publication_date_from', 'n', false);
    $publication_date_to = $formdata->getValue('post', $moduleName.'_publication_date_to', 'n', false);
    $creation_date_from = $formdata->getValue('post', $moduleName.'_creation_date_from', 'n', false);
    if (!empty($publication_date_from)) {
        $wheres[] = xnpGetFromQuery($basic_table.'.'.'publication', $moduleName.'_publication_date_from');
    }
    if (!empty($publication_date_to)) {
        $wheres[] = xnpGetToQuery($basic_table.'.'.'publication', $moduleName.'_publication_date_to');
    }
    if (!empty($creation_date_from)) {
        $wheres[] = $basic_table.'.'.'creation_date >= '.(int) $creation_date_from;
    }

    return implode(' AND ', $wheres);
}

/**
 * sum of file size in items specified with iids.
 *
 * @param iids  array of item_id
 *
 * @return float of file size
 */
function xnpGetTotalFileSize($iids)
{
    global $xoopsDB;
    if (0 == count($iids)) {
        return 0.0;
    }

    $file_table = $xoopsDB->prefix('xoonips_file');
    $iids_str = implode(',', $iids);

    // calculate amount of use file_table and file
    $sql = 'SELECT SUM(`file_size`) FROM '.$file_table.' WHERE `item_id` IN ('.$iids_str.') AND `is_deleted`=0';
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    list($file_size) = $xoopsDB->fetchRow($result);

    return  (float) $file_size;
}

/**
 * check that item is pending now, return ture.
 * Pending: when item has as much as one index waiting for certified.
 *
 * @param item_id ID of retrieval item
 *
 * @return bool item has index waiting for certified(Pending)
 * @return bool item has no index waiting for certified
 */
function xnpIsPending($item_id)
{
    $index_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx');
    $criteria = new CriteriaCompo(new Criteria('item_id', $item_id));
    $criteria->add(new Criteria('certify_state', CERTIFY_REQUIRED));
    $criteria->add(new Criteria('open_level', OL_PRIVATE, '!=', 'idx'));
    $cnt = $index_link_handler->getCount($criteria, $join);

    return  0 != $cnt;
}

/**
 * @param op string 'advancedsearch' 'itemsubtypesearch' 'itemtypesearch'
 * @param keyword search keyword
 * @param search_itemtype how to search ('all', 'basic' or name of itemtype (ex.xnppaper) )
 * @param private_flag true if search private indexes
 * @param msg reference to variables that receive  error message
 * @param iids reference to array that receive item id that match query condition
 * @param search_cache_id search cache id(in/out)
 * @param search_tab 'item'/'metadata'/'file' (it regards illegal value as 'item')
 * @param file_or_item_metadata  'file'=search_text table only, 'item_metadata'=other than search_text table, 'all'=all. effective only if op==quicksearch && search_itemtype!=basic
 *
 * @return true  search succeed
 * @return false search failed. make sure $msg for detail.
 *               this function needs $xoopsDB, $xoopsUser, $_SESSION.
 */
function xnpSearchExec($op, $keyword, $search_itemtype, $private_flag, &$msg, &$iids, &$search_var, &$search_cache_id, $search_tab, $file_or_item_metadata = 'all')
{
    global $xoopsDB, $xoopsUser;

    $xnpsid = $_SESSION['XNPSID'];
    if (!xnp_is_valid_session_id($xnpsid)) {
        // guest access is forbidden
        return array();
    } elseif ($xoopsUser) {
        // identified user
        $uid = $xoopsUser->getVar('uid');
    } else {
        // guest access is permitted
        $uid = 0;
    }

    $cache_table = $xoopsDB->prefix('xoonips_search_cache');
    $cache_item_table = $xoopsDB->prefix('xoonips_search_cache_item');
    $cache_file_table = $xoopsDB->prefix('xoonips_search_cache_file');
    $cache_meta_table = $xoopsDB->prefix('xoonips_search_cache_metadata');
    $meta_table = $xoopsDB->prefix('xoonips_oaipmh_metadata');
    $repo_table = $xoopsDB->prefix('xoonips_oaipmh_repositories');
    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $title_table = $xoopsDB->prefix('xoonips_item_title');
    $keyword_table = $xoopsDB->prefix('xoonips_item_keyword');
    $file_table = $xoopsDB->prefix('xoonips_file');
    $xlink_table = $xoopsDB->prefix('xoonips_index_item_link');
    $index_table = $xoopsDB->prefix('xoonips_index');
    $glink_table = $xoopsDB->prefix('xoonips_groups_users_link');
    $search_text_table = $xoopsDB->prefix('xoonips_search_text');
    $user_table = $xoopsDB->prefix('users');
    $event_log_table = $xoopsDB->prefix('xoonips_event_log');

    // if search_cache_id exists then get results from search_cache
    if ($search_cache_id) {
        $search_cache_id = (int) $search_cache_id;
        $sql = 'SELECT UNIX_TIMESTAMP(`timestamp`) FROM '.$cache_table.' WHERE `search_cache_id`='.$search_cache_id.' AND `sess_id`='.$xoopsDB->quoteString(session_id());
        $result = $xoopsDB->query($sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }
        if (0 == $xoopsDB->getRowsNum($result)) {
            $msg = _MD_XOONIPS_ITEM_SEARCH_ERROR;

            return false; // bad search_cache_id
        }
        list($timestamp) = $xoopsDB->fetchRow($result);

        // this events modify search result. if one of this event is newer than search cache, don't use search cache.
        $event_type_ids = array(
            ETID_INSERT_ITEM,
            ETID_UPDATE_ITEM,
            ETID_DELETE_ITEM,
            ETID_DELETE_GROUP,
            ETID_INSERT_GROUP_MEMBER,
            ETID_DELETE_GROUP_MEMBER,
            ETID_DELETE_INDEX,
            ETID_CERTIFY_ITEM,
            ETID_REJECT_ITEM,
            ETID_TRANSFER_ITEM,
        );
        $sql = 'SELECT COUNT(*) FROM '.$event_log_table.' WHERE `event_type_id` IN ('.implode(',', $event_type_ids).') AND `timestamp`>='.$timestamp;
        $result = $xoopsDB->query($sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }
        list($count) = $xoopsDB->fetchRow($result);
        if (0 == $count) {
            if ('metadata' == $search_tab) {
                $sql = 'SELECT `identifier` FROM '.$cache_meta_table.' WHERE `search_cache_id`='.$search_cache_id;
            } elseif ('file' == $search_tab) {
                $sql = 'SELECT `tf`.`item_id` FROM '.$cache_file_table.' AS `tcf`'.
                    ' LEFT JOIN '.$file_table.' AS `tf` ON `tcf`.`file_id`=`tf`.`file_id`'.
                    ' LEFT JOIN '.$basic_table.' AS `tb` ON `tb`.`item_id`=`tf`.`item_id`'.
                    ' LEFT JOIN '.$search_text_table.' AS `tst` ON `tf`.`file_id`=`tst`.`file_id`'.
                    ' WHERE `search_cache_id`='.$search_cache_id.' AND `tb`.`item_id` IS NOT NULL AND `tf`.`file_id` IS NOT NULL AND `tf`.`is_deleted`=0';
            } else {
                $sql = 'SELECT `tci`.`item_id` FROM '.$cache_item_table.' AS `tci`'.
                    ' LEFT JOIN '.$basic_table.' AS `tb` ON `tb`.`item_id`=`tci`.`item_id`'.
                    ' WHERE `search_cache_id`='.$search_cache_id.' AND `tb`.`item_id` IS NOT NULL';
            }
            $result = $xoopsDB->query($sql);
            if (false === $result) {
                xoonips_error_exit(500);
            }
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $iids[] = (int) $iid;
            }

            return true;
        }
    }

    $cachable = ('quicksearch' == $op || 'advancedsearch' == $op || 'itemtypesearch' == $op || 'itemsubtypesearch' == $op);
    $search_cache_id = 0;
    if ($cachable) {
        // create new search cache
        $sql = 'INSERT INTO '.$cache_table.' (`sess_id`) VALUES ('.$xoopsDB->quoteString(session_id()).')';
        $result = $xoopsDB->queryF($sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }
        $search_cache_id = $xoopsDB->getInsertId();
    }

    $itemtypes = array();
    $itemtype_names = array();
    $tmp = array();
    if (RES_OK != ($res = xnp_get_item_types($tmp))) {
        xoonips_error_exit(500);
    } else {
        foreach ($tmp as $i) {
            $itemtypes[$i['item_type_id']] = $i;
            $itemtype_names[$i['name']] = $i;
        }
    }
    $join1 = 'LEFT JOIN '.$xlink_table.' ON '.$xlink_table.'.`item_id`='.$basic_table.'.`item_id`'.
        ' LEFT JOIN '.$index_table.' ON '.$index_table.'.`index_id`='.$xlink_table.'.`index_id`'.
        ' LEFT JOIN '.$glink_table.' ON '.$glink_table.'.`gid`='.$index_table.'.`gid`'.
        ' LEFT JOIN '.$user_table.' ON '.$user_table.'.`uid`='.$basic_table.'.`uid`';
    $iids = array();

    if ($private_flag) {
        // operation to add item into index. search for only the user's item.
        $privilege = '('.$index_table.'.`open_level`='.OL_PRIVATE.' AND '.$index_table.'.`uid`='.$uid.')';
    } else { // search for readable items.
        $xmember_handler = &xoonips_gethandler('xoonips', 'member');
        if ($xmember_handler->isAdmin($uid) || xnp_is_moderator($xnpsid, $uid)) {
            $privilege = ' 1 ';
        } else {
            $privilege = '('.$index_table.'.`open_level`='.OL_PUBLIC.' OR'.
                ' '.$index_table.'.`open_level`='.OL_PRIVATE.' AND '.$index_table.'.`uid`='.$uid.' OR'.
                ' '.$index_table.'.`open_level`='.OL_GROUP_ONLY.' AND '.$glink_table.'.`uid`='.$uid.')';
        }
    }

    if ('advancedsearch' == $op || 'itemsubtypesearch' == $op) {
        // ignore $search_tab and hide tab panel.
        // results of `file` search will store to search_cache_items table.
        $formdata = &xoonips_getutility('formdata');
        foreach ($itemtypes as $itemtype_id => $itemtype) {
            $wheres = array(' 0 ');
            $module_name = $itemtype['name'];
            if ($formdata->getValue('post', $module_name, 'n', false)) {
                require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
                $f = $module_name.'GetAdvancedSearchQuery';
                $table = $xoopsDB->prefix($module_name.'_item_detail');
                $key_name = $table.'.`'.substr($module_name, 3).'_id`'; // xnppaper -> paper_id

                $where = '';
                $join = '';
                // require retrieve additional query string to item type module
                $f($where, $join);
                if ('' != $where) {
                    $sql = 'SELECT '.$basic_table.'.`item_id`, '.$search_cache_id.' FROM '.$basic_table.
                       ' '.$join1.
                       ' LEFT JOIN '.$file_table.' ON '.$file_table.'.`item_id`='.$basic_table.'.`item_id`'.
                       ' LEFT JOIN '.$title_table.' ON '.$title_table.'.`item_id`='.$basic_table.'.`item_id`'.
                       ' LEFT JOIN '.$keyword_table.' ON '.$keyword_table.'.`item_id`='.$basic_table.'.`item_id`'.
                       ' LEFT JOIN '.$table.' ON '.$key_name.'='.$basic_table.'.`item_id`'.
                       ' LEFT JOIN '.$search_text_table.' ON '.$search_text_table.'.`file_id`='.$file_table.'.`file_id`'.
                       ' '.$join.
                       ' WHERE '.$key_name.' IS NOT NULL AND ('.$where.') AND '.$privilege.
                       ' GROUP BY '.$basic_table.'.`item_id`';
                    if ($cachable) {
                        // write to cache at once
                        $result = $xoopsDB->queryF('INSERT IGNORE INTO '.$cache_item_table.' (`item_id`, `search_cache_id`) '.$sql);
                        if (false === $result) {
                            xoonips_error_exit(500);
                        }
                        $sql = 'SELECT `item_id` FROM '.$cache_item_table.' WHERE `search_cache_id`='.$search_cache_id;
                    }

                    $result = $xoopsDB->query($sql);
                    if (false === $result) {
                        xoonips_error_exit(500);
                    }
                    while (list($iid) = $xoopsDB->fetchRow($result)) {
                        $iids[] = $iid;
                    }
                }
            }
        }
    } elseif ('itemtypesearch' == $op) {
        $itemtype_id = $itemtype_names[$search_itemtype]['item_type_id'];
        $sql = 'SELECT '.$basic_table.'.`item_id`, '.$search_cache_id.' FROM '.$basic_table.
            ' '.$join1.
            ' WHERE '.$privilege.' AND '.$basic_table.'.`item_type_id`='.$itemtype_id.
            ' GROUP BY '.$basic_table.'.`item_id`';
        // inserting results to cache
        $result = $xoopsDB->queryF('INSERT IGNORE INTO '.$cache_item_table.' (`item_id`, `search_cache_id`) '.$sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }

        $sql = 'SELECT `item_id` FROM '.$cache_item_table.' WHERE `search_cache_id`='.$search_cache_id;
        $result = $xoopsDB->query($sql);
        if (false === $result) {
            xoonips_error_exit(500);
        }
        while (list($iid) = $xoopsDB->fetchRow($result)) {
            $iids[] = $iid;
        }
    } elseif ('quicksearch' == $op && '' != trim($keyword)) {
        $search_var[] = 'keyword';
        $search_var[] = 'search_itemtype';
        list($elements, $keywords, $errorMessage) = xnpSplitKeywords2($keyword);
        $keywordsLen = count($keywords);
        if ($errorMessage) {
            $msg = $errorMessage;

            return false;
        }

        if ('basic' == $search_itemtype) { // search titles and keywords
            $wheres_title_keyword = xnpGetKeywordsQueries(array($title_table.'.title', $keyword_table.'.keyword'), $keywords);
            $where = $basic_table.'.`item_type_id`!='.ITID_INDEX.' AND '.xnpUnsplitKeywords2($elements, $wheres_title_keyword);
            $sql = 'SELECT '.$basic_table.'.`item_id`, '.$search_cache_id.' FROM '.$basic_table.
                ' '.$join1.
                ' LEFT JOIN '.$title_table.' ON '.$basic_table.'.`item_id`='.$title_table.'.`item_id`'.
                ' LEFT JOIN '.$keyword_table.' ON '.$basic_table.'.`item_id`='.$keyword_table.'.`item_id`'.
                ' WHERE '.$where.' AND '.$privilege.
                ' GROUP BY '.$basic_table.'.`item_id`';

            // inserting results to cache
            $result = $xoopsDB->queryF('INSERT IGNORE INTO '.$cache_item_table.' (`item_id`, `search_cache_id`) '.$sql);
            if (false === $result) {
                xoonips_error_exit(500);
            }

            $sql = 'SELECT `item_id` FROM '.$cache_item_table.' WHERE `search_cache_id`='.$search_cache_id;
            $result = $xoopsDB->query($sql);
            if (false === $result) {
                xoonips_error_exit(500);
            }
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $iids[] = $iid;
            }
        }

        if ('metadata' == $search_itemtype || 'all' == $search_itemtype) {
            // if 'metadata' then set result of search to cache and $iids
            // if 'all' then write to cache

            $searchutil = &xoonips_getutility('search');
            $encoding = mb_detect_encoding($keyword);
            $fulltext_criteria = &$searchutil->getFulltextSearchCriteria('search_text', $keyword, $encoding);

            $sql = 'SELECT `identifier`, '.$search_cache_id.
                ' FROM '.$meta_table.' AS `data`, '.$repo_table.' AS `repo`'.
                ' WHERE `repo`.`enabled`=1 AND `repo`.`deleted`!=1 AND `repo`.`repository_id`=`data`.`repository_id`'.
                ' AND '.$fulltext_criteria->render().' ORDER BY `identifier`, `data`.`repository_id`';

            // inserting results to cache
            $result = $xoopsDB->queryF('INSERT INTO '.$cache_meta_table.' (`identifier`, `search_cache_id`) '.$sql);
            if (false === $result) {
                xoonips_error_exit(500);
            }

            $sql = 'SELECT `item_id` FROM '.$cache_item_table.' WHERE `search_cache_id`='.$search_cache_id;
            $result = $xoopsDB->query($sql);
            if (false === $result) {
                xoonips_error_exit(500);
            }
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $iids[] = $iid;
            }
        }

        if (isset($itemtype_names[$search_itemtype]) || 'all' == $search_itemtype) {
            /* where_condition[item_type] = "item_type_id=$itemtype_id AND " ( query that combines 'wheres2' AND 'and or ( )' ).
               wheres2[keyword] = ( where_basic[keyword] or where_detail[keyword] )
            */
            // require file search when search_itemtype == (itemtype)
            // do file search and store results to search_cache_file
            $wheres_basic = xnpGetKeywordsQueries(array($title_table.'.title', $keyword_table.'.keyword', $basic_table.'.description', $basic_table.'.doi', $user_table.'.uname', $user_table.'.name'), $keywords);
            foreach ($itemtypes as $itemtype_id => $itemtype) {
                if (ITID_INDEX == $itemtype['item_type_id']) {
                    continue;
                }
                $module_name = $itemtype['name'];
                if ($search_itemtype == $module_name || 'all' == $search_itemtype) {
                    $itemtype_id = $itemtype['item_type_id'];
                    if ('all' == $file_or_item_metadata || 'item_metadata' == $file_or_item_metadata) {
                        require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
                        $f = $module_name.'GetDetailInformationQuickSearchQuery';
                        if (!function_exists($f)) {
                            continue;
                        }

                        $table = $xoopsDB->prefix($module_name.'_item_detail');
                        $wheres_detail = array();
                        $f($wheres_detail, $join, $keywords);

                        $wheres2 = array();
                        for ($i = 0; $i < $keywordsLen; ++$i) {
                            // search by item_id
                            $where3 = sprintf(' OR `%s`.`item_id`=%s', $basic_table, $xoopsDB->quoteString($keywords[$i]));
                            if (empty($wheres_detail[$i])) {
                                $wheres_detail[$i] = '0';
                            }
                            $wheres2[] = $wheres_basic[$i].' OR '.$wheres_detail[$i].$where3;
                        }

                        $where = " $basic_table.item_type_id=$itemtype_id AND ".xnpUnsplitKeywords2($elements, $wheres2);
                        $key_name = "${table}.".substr($module_name, 3).'_id'; // xnppaper -> paper_id
                        $sql = 'SELECT '.$basic_table.'.`item_id`, '.$search_cache_id.' FROM '.$basic_table.
                            ' '.$join1.
                            ' LEFT JOIN '.$file_table.' ON '.$file_table.'.`item_id`='.$basic_table.'.`item_id`'.
                            ' LEFT JOIN '.$title_table.' ON '.$basic_table.'.`item_id`='.$title_table.'.`item_id`'.
                            ' LEFT JOIN '.$keyword_table.' ON '.$basic_table.'.`item_id`='.$keyword_table.'.`item_id`'.
                            ' LEFT JOIN '.$table.' ON '.$key_name.'='.$basic_table.'.`item_id`'.
                            ' '.$join.
                            ' WHERE '.$where.' AND '.$privilege.
                            ' GROUP BY '.$basic_table.'.`item_id`';

                        $result = $xoopsDB->queryF('INSERT IGNORE INTO '.$cache_item_table.' (`item_id`, `search_cache_id`) '.$sql);
                        if (false === $result) {
                            xoonips_error_exit(500);
                        }
                    }

                    if ('all' == $file_or_item_metadata || 'file' == $file_or_item_metadata) {
                        $searchutil = &xoonips_getutility('search');
                        $encoding = mb_detect_encoding($keyword);
                        $fulltext_criteria = &$searchutil->getFulltextSearchCriteria('search_text', $keyword, $encoding);
                        // search inside files
                        $sql = 'INSERT IGNORE INTO '.$cache_file_table.' (`file_id`, `search_cache_id`)'.
                            ' SELECT '.$file_table.'.`file_id`, '.$search_cache_id.' FROM '.$file_table.
                            ' LEFT JOIN '.$basic_table.' ON '.$file_table.'.`item_id`='.$basic_table.'.`item_id`'.
                            ' LEFT JOIN '.$search_text_table.' ON '.$file_table.'.`file_id`='.$search_text_table.'.`file_id`'.
                            ' WHERE `item_type_id`='.$itemtype_id.' AND '.$fulltext_criteria->render().' AND '.$file_table.'.`is_deleted`=0';

                        // write to cache at once
                        $result = $xoopsDB->queryF($sql);
                        if (false === $result) {
                            xoonips_error_exit(500);
                        }
                    }
                }
            }
            switch ($search_tab) {
            case 'metadata':
                $result = $xoopsDB->query('SELECT `item_id` FROM '.$cache_meta_table.' WHERE `search_cache_id`='.$search_cache_id);
                break;
            case 'file':
                $result = $xoopsDB->query('SELECT `item_id` FROM '.$cache_file_table.' WHERE `search_cache_id`='.$search_cache_id);
                break;
            case 'item':
            default:
                $result = $xoopsDB->query('SELECT `item_id` FROM '.$cache_item_table.' WHERE `search_cache_id`='.$search_cache_id);
                break;
            }
            if (false === $result) {
                xoonips_error_exit(500);
            }
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $iids[] = $iid;
            }
        }
    }

    return true;
}

/**
 * get relative path string of $xid to $base_index_id.
 *
 * @param $xid
 * @param $base_index_id
 */
function xnpGetExportPathString($xid, $base_index_id)
{
    $xnpsid = $_SESSION['XNPSID'];
    $ar = array();
    while (true) {
        if ($xid == $base_index_id) {
            return implode('/', array_reverse($ar));
        } elseif (IID_ROOT == $xid) {
            return false;
        }

        $index = array();
        $res = xnp_get_index($xnpsid, $xid, $index);
        if (RES_OK != $res) {
            return false;
        }
        $ar[] = addcslashes($index['titles'][0], '\\/');
        $xid = $index['parent_index_id'];
    }
}

/**
 * function returns XML that is converted into Basic Information of items.
 *
 * When item_id is unknown or error occurs in database, function returns NULL.
 *
 * @param fhdl file handle writes outputs
 * @param item item information to make XML
 * @param is_absolute  true:index tags are absolute path. false: index tags are relative path to base_index_id.
 * @param base_index_id  is_absolute == false            && base_index_id == false: outputs only 1 empty index tag "<index></index>"
 *                                                          is_absolute == false && base_index_id != false: outputs only descendants
 *                                                          of base_index_id is_absolute == true: ignored
 *
 * @return true:success, false:failure
 */
function xnpBasicInformation2XML($fhdl, $item, $is_absolute, $base_index_id = false)
{
    $textutil = &xoonips_getutility('text');
    if (!$fhdl) {
        return false;
    }

    $xnpsid = $_SESSION['XNPSID'];
    $account = array();

    $res = xnp_get_account($xnpsid, $item['uid'], $account);
    if (RES_OK != $res) {
        return false;
    } else {
        $contributor = $account['name'].'('.$account['uname'].')';
    }

    $itemtypes = array();
    $res = xnp_get_item_types($itemtypes);
    if (RES_OK != $res) {
        return false;
    } else {
        foreach ($itemtypes as $i) {
            if ($i['item_type_id'] == $item['item_type_id']) {
                $itemtype = $i['name'];
                break;
            }
        }
    }
    if (!isset($itemtype)) {
        return false;
    }

    $last_update_date = gmdate('Y-m-d\TH:i:s\Z', $item['last_update_date']);
    $creation_date = gmdate('Y-m-d\TH:i:s\Z', $item['creation_date']);

    $index_id = array();
    $res = xnp_get_index_id_by_item_id($xnpsid, $item['item_id'], $index_id);
    if (RES_OK != $res) {
        return false;
    }

    //generate <title>xxx</title> for each title
    $titles = '';
    foreach ($item['titles'] as $title) {
        $titles .= '<title>'.$textutil->html_special_chars($title).'</title>'."\n";
    }
    $keywords = '';
    foreach ($item['keywords'] as $keyword) {
        $keywords .= '<keyword>'.$textutil->html_special_chars($keyword).'</keyword>'."\n";
    }

    if (!fwrite(
        $fhdl, "<basic id=\"${item['item_id']}\">\n"
                ."<itemtype>${itemtype}</itemtype>\n"
                .'<titles>'.$titles."</titles>\n"
                ."<contributor uname='".$textutil->html_special_chars($account['uname'])."'>".$textutil->html_special_chars($contributor)."</contributor>\n"
                .'<keywords>'.$keywords."</keywords>\n"
                .'<description>'.$textutil->html_special_chars($item['description'])."</description>\n"
                .'<doi>'.$textutil->html_special_chars($item['doi'])."</doi>\n"
                ."<last_update_date>$last_update_date</last_update_date>\n"
                ."<creation_date>$creation_date</creation_date>\n"
                ."<publication_year>${item['publication_year']}</publication_year>\n"
                ."<publication_month>${item['publication_month']}</publication_month>\n"
                ."<publication_mday>${item['publication_mday']}</publication_mday>\n"
                ."<lang>${item['lang']}</lang>\n"
        .'<url>'.XOOPS_URL."/modules/xoonips/detail.php?item_id=${item['item_id']}</url>\n"
    )
    ) {
        return false;
    }
    if (!xnpExportChangeLog($fhdl, $item['item_id'])) {
        return false;
    }
    $ar = array();

    $open_level_str = array(OL_PUBLIC => 'public', OL_GROUP_ONLY => 'group', OL_PRIVATE => 'private');

    if ($is_absolute) {
        $base_index_id = IID_ROOT;
        $head = '/';
    } else {
        $head = '';
    }

    if ($base_index_id) {
        foreach ($index_id as $i) {
            $str = xnpGetExportPathString($i, $base_index_id);
            if (false === $str) {
                continue;
            }
            $index = array();
            if (RES_OK != xnp_get_index($xnpsid, $i, $index)) {
                continue;
            }
            if (!fwrite($fhdl, "<index open_level='".$open_level_str[$index['open_level']]."'>".$textutil->html_special_chars($head.$str)."</index>\n")) {
                return false;
            }
        }
    } else {
        if (!fwrite($fhdl, "<index></index>\n")) {
            return false;
        }
    }
    if (!fwrite($fhdl, "</basic>\n")) {
        return false;
    }

    return true;
}

/**
 * @param string       $iconPath
 * @param string       $explanation
 * @param false|string $subtypeVarName
 */
function xnpGetTopBlock($moduleName, $displayName, $iconPath, $explanation, $subtypeVarName, $subtypes)
{
    // variables are set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars()); // Variables set to $xoopsTpl is copied to $tpl.

    $tpl->assign('icon', XOOPS_URL."/modules/$moduleName/".$iconPath);
    $tpl->assign('explanation', $explanation); //**

    $tpl->assign('moduleName', $moduleName);
    $tpl->assign('displayName', $displayName);
    $tpl->assign('formName', $moduleName.'_form');
    $tpl->assign('subtypeVarName', $subtypeVarName);

    if (!empty($subtypes)) {
        $searchURLs = array();
        foreach ($subtypes as $subtypeName => $subtypeDisplayName) {
            $searchURLs[] = array(
                'subtypeDisplayName' => $subtypeDisplayName,
                'subtypeName' => $subtypeName,
            );
        }
        $tpl->assign('searchURLs', $searchURLs);
    }
    // Output in HTML.
    return $tpl->fetch('db:xoonips_top_itemtype_block.html');
}

function xnpGetModifiedFields($item_id)
{
    $xnpsid = $_SESSION['XNPSID'];

    $ret = array();
    $item = array();
    $formdata = &xoonips_getutility('formdata');
    if (RES_OK == xnp_get_item($xnpsid, $item_id, $item)) {
        foreach (array('contributor' => _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL,
                        'description' => _MD_XOONIPS_ITEM_DESCRIPTION_LABEL,
                        'doi' => _MD_XOONIPS_ITEM_DOI_LABEL,
                        'last_update_date' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
                        'creation_date' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
                        'item_type' => _MD_XOONIPS_ITEM_ITEM_TYPE_LABEL,
                        'change_logs' => _MD_XOONIPS_ITEM_CHANGELOGS_LABEL,
                        'lang' => _MD_XOONIPS_ITEM_LANG_LABEL, ) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 'n', false);
            if (!array_key_exists($k, $item)
                || null === $tmp
            ) {
                continue;
            }
            if (str_replace("\r\n", "\r", $item[$k]) != str_replace("\r\n", "\r", $tmp)) {
                array_push($ret, $v);
            }
        }
    }

    //has been title modified ?
    $titles = array();
    foreach (preg_split("/[\r\n]+/", $formdata->getValue('post', 'title', 's', false, '')) as $title) {
        if ('' != trim($title)) {
            $titles[] = $title;
        }
    }
    $diff = array_diff($titles, $item['titles']);
    if (!empty($diff)) {//modified
        array_push($ret, _MD_XOONIPS_ITEM_TITLE_LABEL);
    }

    //has been keyword modified ?
    $keywords = $formdata->getValue('post', 'keywords', 's', false);
    $keywords = !empty($keywords) ? explode(',', $keywords) : array();
    $diff = array_diff($keywords, $item['keywords']);
    if (count($keywords) != count($item['keywords']) || !empty($diff)) {//modified
        array_push($ret, _MD_XOONIPS_ITEM_KEYWORDS_LABEL);
    }

    //is indexes modified ?
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (isset($xoonipsCheckedXID)) {
        $new_index = explode(',', $xoonipsCheckedXID);
        $old_index = array();
        $res = xnp_get_index_id_by_item_id($xnpsid, $item['item_id'], $old_index);
        if (RES_OK == $res) {
            if (count(array_diff($old_index, $new_index)) > 0
                || count(array_diff($new_index, $old_index)) > 0
            ) {
                array_push($ret, _MD_XOONIPS_ITEM_INDEX_LABEL); // if you change this label, don't forget to modify xnpUpdateBasicInformation()
            }
        }
    }

    //is related to modified ?
    $related_to_check = $formdata->getValueArray('post', 'related_to_check', 'i', false, null);
    $new_related_to =
    (!isset($related_to_check) || '' === $related_to_check) ? array() :
    (is_string($related_to_check) ? preg_split("/[\r\n]+/", $related_to_check) :
    $related_to_check);
    $related_to = $formdata->getValue('post', 'related_to', 's', false);
    $related_to = (isset($related_to) ? $related_to : '');
    foreach (preg_split("/[\r\n]+/", $related_to) as $id) {
        $tmp_item = array();
        if (RES_OK != xnp_get_item($xnpsid, (int) $id, $tmp_item)) {
            continue;
        }
        $new_related_to[] = $id;
    }
    $old_related_to = array();
    $res = xnp_get_related_to($xnpsid, $item['item_id'], $old_related_to);

    if (RES_OK == $res) {
        if (count(array_diff($old_related_to, $new_related_to)) > 0
            || count(array_diff($new_related_to, $old_related_to)) > 0
        ) {
            array_push($ret, _MD_XOONIPS_ITEM_RELATED_TO_LABEL);
        }
    }

    // get file_id of preview file before change
    $tmp = xnpGetFileInfo('`t_file`.`file_id`', '`t_file_type`.`name`=\'preview\' AND `is_deleted`=0 AND `sess_id` IS NULL', $item_id);
    $old_files = array();
    foreach ($tmp as $i) {
        $old_files[] = $i[0];
    }
    $new_files = array();
    $previewFileID = $formdata->getValue('post', 'previewFileID', 's', false);
    if (isset($previewFileID) && '' != $previewFileID) {
        $new_files = explode(',', $previewFileID);
    }
    if (count(array_diff($old_files, $new_files)) > 0
        || count(array_diff($new_files, $old_files)) > 0
    ) {
        //preview is modified
        array_push($ret, _MD_XOONIPS_ITEM_PREVIEW_LABEL);
    }

    return $ret;
}

/**
 * @param string $file_type
 */
function xnpIsAttachmentModified($file_type, $item_id)
{
    global $xoopsDB;
    //return true if uploaded successfully
    $formdata = &xoonips_getutility('formdata');
    $file = $formdata->getFile($file_type, false);
    if (isset($file) && 0 == $file['error']) {
        return true;
    }

    // get file_id of preview file before change
    $tmp = xnpGetFileInfo('`t_file`.`file_id`', '`t_file_type`.`name`='.$xoopsDB->quoteString($file_type).' AND `sess_id` IS NULL AND `is_deleted`=0', $item_id);
    $old_files = array();
    $new_files = array();
    foreach ($tmp as $i) {
        $old_files[] = $i[0];
    }
    $fileID = $formdata->getValue('post', $file_type.'FileID', 's', false);
    if (isset($fileID) && '' != $fileID) {
        $new_files = explode(',', $fileID);
    }

    return count(array_diff($old_files, $new_files)) > 0
        || count(array_diff($new_files, $old_files)) > 0;
}

function xnpGetBasicInformationMetadata($metadataPrefix, $item_id)
{
    $textutil = &xoonips_getutility('text');
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);
    $basic = array();
    xnp_get_item($_SESSION['XNPSID'], $item_id, $basic);

    $tmparray = array();
    if (RES_OK == xnp_get_item_types($tmparray)) {
        foreach ($tmparray as $i) {
            if ($i['item_type_id'] == $basic['item_type_id']) {
                $itemtype = $i;
                break;
            }
        }
    }
    $nijc_code = $xconfig_handler->getValue('repository_nijc_code');
    if ('' == $basic['doi']) {
        $identifier = $nijc_code.'/'.$basic['item_type_id'].'.'.$basic['item_id'];
    } else {
        $identifier = $nijc_code.':'.XNP_CONFIG_DOI_FIELD_PARAM_NAME.'/'.$basic['doi'];
    }
    if ('junii' == $metadataPrefix || 'junii2' == $metadataPrefix) {
        $lines = array();

        $publisher = $xconfig_handler->getValue('repository_publisher');
        $institution = $xconfig_handler->getValue('repository_institution');
        $meta_author = $myxoopsConfigMetaFooter['meta_author'];

        if (0 == strcasecmp($publisher, 'meta_author')) {
            $publisher = $meta_author;
        } elseif (0 == strcasecmp($publisher, 'creator')) {
            $publisher = _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL;
        } elseif (0 == strcasecmp($publisher, 'none')) {
            $publisher = null;
        }
        if (0 == strcasecmp($institution, 'meta_author')) {
            $institution = $meta_author;
        } elseif (0 == strcasecmp($institution, 'creator')) {
            $institution = _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL;
        } elseif (0 == strcasecmp($institution, 'none')) {
            $institution = null;
        }

        $lines[] = '<title>'.$textutil->xml_special_chars(reset($basic['titles'])).'</title>';
        while (next($basic['titles'])) {
            $lines[] = '<title>'.$textutil->xml_special_chars(current($basic['titles'])).'</title>';
        }
        $lines[] = '<identifier>'.$textutil->xml_special_chars($identifier).'</identifier>';
        $lines[] = '<identifier xsi:type="URL">'.$textutil->xml_special_chars(xnpGetItemDetailURL($basic['item_id'], $basic['doi'])).'</identifier>';
        $lines[] = '<type>itemType:'.$textutil->xml_special_chars($itemtype['name']).'</type>';
        $lines[] = '<language xsi:type="ISO639-2">'.$textutil->xml_special_chars($basic['lang']).'</language>';
        if (null != $institution) {
            $lines[] = '<institution>'.$textutil->xml_special_chars($institution).'</institution>';
        }
        if (null != $publisher) {
            $lines[] = '<publisher>'.$textutil->xml_special_chars($publisher).'</publisher>';
        }

        $subject = array();
        $index_ids = array();
        $res = xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $index_ids);
        if (RES_OK == $res) {
            foreach ($index_ids as $xid) {
                if ($xid > 0) {
                    $index = array();
                    $result = xnp_get_index($_SESSION['XNPSID'], $xid, $index);
                    if (0 == $result) {
                        $str = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
                        $subject[] = "$str";
                    }
                }
            }
        }
        if (!empty($basic['keywords'])) {
            $subject = array_merge($subject, $basic['keywords']);
        }
        $lines[] = '<subject>'.$textutil->xml_special_chars(implode(', ', $subject)).'</subject>';
        $lines[] = '<description>comment:'.$textutil->xml_special_chars($basic['description']).'</description>';

        return implode("\n", $lines);
    } elseif ('oai_dc' == $metadataPrefix) {
        /* title, identifier, type, language, subject, description */
        $lines = array();

        $publisher = $xconfig_handler->getValue('repository_publisher');
        $meta_author = $myxoopsConfigMetaFooter['meta_author'];

        if (0 == strcasecmp($publisher, 'meta_author')) {
            $publisher = $meta_author;
        } elseif (0 == strcasecmp($publisher, 'creator')) {
            $publisher = _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL;
        } elseif (0 == strcasecmp($publisher, 'none')) {
            $publisher = null;
        }

        $lines[] = '<dc:title>'.$textutil->xml_special_chars($basic['title']).'</dc:title>';
        $lines[] = '<dc:identifier>'.$textutil->xml_special_chars($identifier).'</dc:identifier>';
        $lines[] = '<dc:identifier>'.$textutil->xml_special_chars(xnpGetItemDetailURL($basic['item_id'], $basic['doi'])).'</dc:identifier>';
        $lines[] = '<dc:type>itemType:'.$textutil->xml_special_chars($itemtype['name']).'</dc:type>';
        $lines[] = '<dc:language>'.$textutil->xml_special_chars($basic['lang']).'</dc:language>';
        if (null != $publisher) {
            $lines[] = '<dc:publisher>'.$textutil->xml_special_chars($publisher).'</dc:publisher>';
        }

        $subject = array();
        $index_ids = array();
        $res = xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $index_ids);
        if (RES_OK == $res) {
            foreach ($index_ids as $xid) {
                if ($xid > 0) {
                    $index = array();
                    $result = xnp_get_index($_SESSION['XNPSID'], $xid, $index);
                    if (0 == $result) {
                        $str = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
                        $subject[] = "$str";
                    }
                }
            }
        }
        if (!empty($basic['keywords'])) {
            $subject = array_merge($subject, $basic['keywords']);
        }
        foreach ($subject as $str) {
            $lines[] = '<dc:subject>'.$textutil->xml_special_chars($str).'</dc:subject>';
        }
        $lines[] = '<dc:description>comment:'.$textutil->xml_special_chars($basic['description']).'</dc:description>';

        return implode("\n", $lines)."\n";
    }

    return false;
}

/**
 * get Rights in detail page.
 *
 * @param item_id item_id
 * @param text Rights text or html
 */
function xnpGetRightsDetailBlock($item_id, $use_cc = 1, $text = '', $cc_commercial_use = 1, $cc_modification = 2)
{
    $textutil = &xoonips_getutility('text');
    $hidden =
    xnpCreateHidden('rightsUseCC', $use_cc).
    xnpCreateHidden('rightsEncText', $text).
    xnpCreateHidden('rightsCCCommercialUse', $cc_commercial_use).
    xnpCreateHidden('rightsCCModification', $cc_modification);

    if ($use_cc) {
        return
        array(
        'name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL,
        'value' => "$text",
        'hidden' => $hidden, );
    } else {
        return
        array(
        'name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL,
        'value' => '<textarea readonly="readonly" rows="5" cols="40" style="width:320px">'.$textutil->html_special_chars($text).'</textarea>',
        'hidden' => $hidden, );
    }
}
// input(POST): rightsEncText, rightsUseCC, rightsCCCommercialUse, rightsCCModification
// output(POST): rightsEncText, rightsUseCC, rightsCCCommercialUse, rightsCCModification
function xnpGetRightsEditBlock($item_id, $use_cc = 1, $text = '', $cc_commercial_use = 1, $cc_modification = 2)
{
    $textutil = &xoonips_getutility('text');
    // select, text, fileInfo
    $item_id = (int) $item_id;
    $formdata = &xoonips_getutility('formdata');
    $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', false);
    if (isset($rightsUseCC)) { // There is initial value specification by POST.
        $text = $formdata->getValue('post', 'rightsEncText', 's', false, '');
        $use_cc = $rightsUseCC;
        $cc_commercial_use = $formdata->getValue('post', 'rightsCCCommercialUse', 'i', false, 0);
        $cc_modification = $formdata->getValue('post', 'rightsCCModification', 'i', false, 0);
    } else { // There is no initial value specification by POST. use the value of Argument.
    }

    $check_cc = array('', '');
    $check_cc[$use_cc] = "checked='checked'";
    $check_com = array('', '');
    $check_com[$cc_commercial_use] = "checked='checked'";
    $check_mod = array('', '', '');
    $check_mod[$cc_modification] = "checked='checked'";

    if ($use_cc) {
        $encText = '';
        $htmlShowText = '&nbsp;'; // div.firstChild is prevented being set to null.
    } else {
        $encText = $textutil->html_special_chars($text);
        $htmlShowText = nl2br($textutil->html_special_chars(xnpHeadText($text)));
        if ('' == $htmlShowText) {
            $htmlShowText = '&nbsp;'; // div.firstChild is prevented being set to null.
        }
    }
    $html = "
    <table>
     <tr>
        <td><input type='radio' name='rightsUseCC' value='1' {$check_cc[1]} /></td>
        <td>"._MD_XOONIPS_RIGHTS_SOME_RIGHTS_RESERVED.'</td>
     </tr>
     <tr>
        <td></td>
        <td>
        <ul>
        <li>'._MD_XOONIPS_RIGHTS_ALLOW_COMMERCIAL_USE."<br />
        <div style='padding-left: 20px;'>
        <input type='radio' name='rightsCCCommercialUse' value='1' {$check_com[1]} />"._YES."<br />
        <input type='radio' name='rightsCCCommercialUse' value='0' {$check_com[0]} />"._NO.'<br />
        </div></li>
        <li>'._MD_XOONIPS_RIGHTS_ALLOW_MODIFICATIONS."<br />
        <div style='padding-left: 20px;'>
        <input type='radio' name='rightsCCModification' value='2' {$check_mod[2]} />"._YES."<br />
        <input type='radio' name='rightsCCModification' value='1' {$check_mod[1]} />"._MD_XOONIPS_RIGHTS_YES_SA."<br />
        <input type='radio' name='rightsCCModification' value='0' {$check_mod[0]} />"._NO."<br />
        </div></li>
        </ul>
        </td>
     </tr>
     <tr>
        <td><input type='radio' name='rightsUseCC' value='0' {$check_cc[0]} /></td>
        <td>"._MD_XOONIPS_RIGHTS_ALL_RIGHTS_RESERVED."</td>
     </tr>
     <tr>
        <td></td>
        <td>
        <div id='rightsShowText' style='width: 100%;'>$htmlShowText</div>
        <div style='vertical-align: text-bottom; text-align:right'>
         <a href='#' onclick=\"return xnpOpenTextFileInputWindow('rights',$item_id)\">"._MD_XOONIPS_ITEM_TEXT_FILE_EDIT_LABEL."</a>
        </div>
        <input type='hidden' name='rightsEncText' value='$encText'  id='rightsEncText' />
        </td>
     </tr>
    </table>
    ";

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}
function xnpGetRightsPrinterFriendlyBlock($item_id, $use_cc, $text)
{
    $textutil = &xoonips_getutility('text');
    if ($use_cc) {
        return
        array(
        'name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL,
        'value' => "$text", );
    } else {
        return
        array(
        'name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL,
        'value' => nl2br($textutil->html_special_chars($text)), );
    }
}

// input(POST):  rightsEncText, rightsUserCC, rightsCCCommercialUse, rightsCCModification
// output: rightsEncText
function xnpGetRightsConfirmBlock($item_id, $maxlen = 65535)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', false, 0);
    $rightsCCCommercialUse = $formdata->getValue('post', 'rightsCCCommercialUse', 'i', false, 0);
    $rightsCCModification = $formdata->getValue('post', 'rightsCCModification', 'i', false, 0);
    if (1 == $rightsUseCC) {
        $htmlText = xoonips_get_cc_license($rightsCCCommercialUse, $rightsCCModification, 4.0, 'INTERNATIONAL');
        $within = $htmlText;
        $without = '';
    } else {
        $text = $formdata->getValue('post', 'rightsEncText', 's', false, '');
        list($within, $without) = xnpTrimString($text, $maxlen, _CHARSET);
        $htmlText = nl2br(xnpWithinWithoutHtml($within, $without));
    }

    $html = $htmlText."
        <input type='hidden' name='rightsEncText' value='".$textutil->html_special_chars($within.$without)."' />
        <input type='hidden' name='rightsUseCC'           value='".((int) $rightsUseCC)."' />
        <input type='hidden' name='rightsCCCommercialUse' value='".((int) $rightsCCCommercialUse)."' />
        <input type='hidden' name='rightsCCModification' value='".((int) $rightsCCModification)."' />
        ";

    return array('name' => _MD_XOONIPS_ITEM_TEXTFILE_LABEL, 'value' => $html, 'within' => $within, 'without' => $without);
}

function xnpGetRightsRegisterBlock()
{
    return xnpGetRightsEditBlock(false);
}

/**
 * function of getting rights contents on the following page of confirm.
 *
 * @return contents empty character strings in error
 */
function xnpGetRights()
{
    $formdata = &xoonips_getutility('formdata');

    return array(
    $formdata->getValue('post', 'rightsEncText', 's', false, ''),
    $formdata->getValue('post', 'rightsUseCC', 'i', false, 0),
    $formdata->getValue('post', 'rightsCCCommercialUse', 'i', false, 0),
    $formdata->getValue('post', 'rightsCCModification', 'i', false, 0),
    );
}

/**
 * check rights to access to item_id. to control displaying PDF Reprint and Abstract.
 *
 * @return OL_PRIVATE    accessible by way of private index
 * @return OL_GROUP_ONLY accessible by way of group index
 * @return OL_PUBLIC     accessible by way of public index
 * @reutrn false         can't access or error
 */
function xnpGetAccessRights($item_id)
{
    $xnpsid = $_SESSION['XNPSID'];
    $xids = array();
    $result = xnp_get_index_id_by_item_id($xnpsid, $item_id, $xids);
    if (RES_OK != $result) {
        return false;
    }

    $len = count($xids);
    $indexes = array();
    $open_levels = array();
    for ($i = 0; $i < $len; ++$i) {
        $xid = $xids[$i];
        $index = array();
        $result = xnp_get_index($xnpsid, $xid, $index);
        if (RES_OK == $result) {
            $open_levels[$index['open_level']] = true;
        }
    }

    if (isset($open_levels[OL_PRIVATE])) {
        return OL_PRIVATE;
    }
    if (isset($open_levels[OL_GROUP_ONLY])) {
        return OL_GROUP_ONLY;
    }
    if (isset($open_levels[OL_PUBLIC])) {
        return OL_PUBLIC;
    }

    return false;
}

function xoonips_error_log($message)
{
    error_log($message, 0);
}

/**
 * eucのmultibyte文字列にwindowをかけてbin2hex()する。
 *
 * @param string $str
 * @param bool   $output_leading
 * @param bool   $output_trailing
 */
function xnpWindowString($str, $output_leading, $output_trailing)
{
    $w0 = 0; // windowの左端
    $w1 = 0; // windowの右端
    $words = array();
    $encoding = mb_detect_encoding($str);
    $end = mb_strlen($str, $encoding);

    // leading
    for ($j = 0; $j < XOONIPS_WINDOW_SIZE; ++$j) {
        if ($output_leading && $w1) {
            $words[] = bin2hex(mb_substr($str, $w0, $w1 - $w0, $encoding));
        }
        ++$w1;
        if ($w1 >= $end) {
            break;
        }
    }

    // middle
    while (true) {
        $words[] = bin2hex(mb_substr($str, $w0, $w1 - $w0, $encoding));

        if ($w1 >= $end) {
            break;
        }

        ++$w0;
        if ($w1 < $end) {
            ++$w1;
            if ($w1 >= $end) {
                $w1 = $end;
            }
        }
    }

    // trailing
    if ($output_trailing) {
        while (true) {
            ++$w0;
            if ($w0 >= $end) {
                break;
            }
            $words[] = bin2hex(mb_substr($str, $w0, $w1 - $w0, $encoding));
        }
    }

    return $words;
}

/**
 * 検索用に文字列を分割する．
 * マルチバイト文字列にたいし，長さXOONIPS_WINDOW_SIZE文字のウィンドウ処理を適用する．
 * 1バイト文字列は変換しない．
 * output_leading: strの先頭がmultibyte-wordの場合にそのleadingを出力する
 * output_trailing: strの末尾がmultibyte-wordの場合にそのtrailingを出力する
 * 大まかに言って
 *   search_text生成時   output_leading = true, output_trailing = true
 *                        ただし，ファイルの場合はもはやxnpWordSeparationは使用されない． class XoonipsWordSeparator が使用される
 *   検索時(部分一致)    output_leading = false, output_trailing = false.
 */
function xnpWordSeparation($str, $output_leading = true, $output_trailing = true)
{
    $words = array();
    $w0 = 0; // wordの左端
    $w1 = 0; // wordの右端

    $regex_encoding = mb_regex_encoding();
    $encoding = mb_detect_encoding($str);
    mb_regex_encoding($encoding);
    $end = mb_strlen($str, $encoding);

    $mb_env = XOOPS_USE_MULTIBYTES;
    $multibyte_mode = (mb_ereg('[^\x20-\x7e]', mb_substr($str, 0, 1, $encoding)) && $mb_env);

    while ($w1 < $end) {
        if ($multibyte_mode) {
            while ($w1 < $end && mb_ereg('[^\x20-\x7e]', mb_substr($str, $w1, 1, $encoding)) && $mb_env) { // 連続するmultibyteを抽出
                ++$w1;
            }
            $ar = xnpWindowString(
                mb_substr($str, $w0, $w1 - $w0, $encoding),
                0 != $w0 || $output_leading,
                $w1 != $end || $output_trailing
            );
        } else {
            while ($w1 < $end && (mb_ereg('[\x20-\x7e]', mb_substr($str, $w1, 1, $encoding)) || !$mb_env)) { // 連続するsinglebyteを抽出
                ++$w1;
            }
            $ar = explode(' ', mb_substr($str, $w0, $w1 - $w0, $encoding));
        }

        $ct = count($ar); // $arを$wordsの末尾に追加．array_mergeは遅いので．
        for ($j = 0; $j < $ct; ++$j) {
            $words[] = $ar[$j];
        }

        $w0 = $w1;
        $multibyte_mode = !$multibyte_mode;
    }
    mb_regex_encoding($regex_encoding);

    return $words;
}

/**
 * 指定文字列を，指定バイト数以内で最大長になるように末尾を切り落とす．
 * $src == $within.$without;.
 *
 * @param src 処理対象文字列
 * @param enc srcのencoding(省略可)
 *
 * @return array( $within, $without )
 *                within 指定バイト数以内に収まる部分文字列
 *                without 切り落とされる部分文字列
 */
function xnpTrimString($src, $len, $enc = null)
{
    //1 部分文字列を得る
    //1.1 日本語対応サーバ(mbstring有効)ならば，mb_strcutを使って部分文字列を得る
    //1.2 日本語非対応サーバならば，substrで部分文字列を得る
    //2 部分文字列が数値文字参照の途中で終了していたら，その数値文字参照を削除する
    //multi byte charset or numeric character reference
    $dst = mb_substr($src, 0, $len, is_null($enc) ? mb_detect_encoding($src) : $enc);

    // if the last numeric character reference is incompleted, remove it
    $within = preg_replace('/^(.*)&[^;]*$/s', '$1', $dst);
    $without = substr($src, strlen($within));
    if ($within == $dst) {    // $dstの末尾が、数値文字参照の途中ではない。
        return array($within, $without);
    }
    if (preg_match('/^&#([0-9]+|[Xx][0-9A-Fa-f]+);/', $without)) { // $withoutの頭が、数値文字参照である
        return array($within, $without);
    }

    return array($dst, substr($src, strlen($dst)));
}

// $ar['without'] が文字列型かつ空でないならtrueを返す
// $ar[key]['without'] が文字列型かつ空でないようなkeyがあるならtrueを返す
function xnpHasWithout($ar)
{
    foreach ($ar as $key => $val) {
        if ('without' == $key && 0 != strlen($val)) {
            return true;
        }
        if (is_array($val) && isset($val['without']) && 0 != strlen($val['without'])) {
            return true;
        }
    }

    return false;
}

/**
 * テーブルのカラム長(文字列型のカラムのみ)を得る.
 *
 * @param $table_wo_prefix: テーブル名(prefixを除く)
 *
 * @return array( name1 => length1, name2 => length2, ... ) あるいはエラーならfalse
 */
function xnpGetColumnLengths($table_wo_prefix)
{
    global $xoopsDB;
    $table = $xoopsDB->prefix($table_wo_prefix);
    $result = $xoopsDB->queryF('SHOW COLUMNS FROM `'.$table.'`');
    if (false === $result) {
        xoonips_error_exit(500);
    }
    $ret = array();
    while ($row = $xoopsDB->fetchArray($result)) {
        $name = $row['Field'];
        if (preg_match('/(?:varchar|char)\((\d+)\)/', $row['Type'], $matches)) {
            $ret[$name] = $matches[1];
        } elseif (preg_match('/(tiny|medium|long)?(?:text|blob)/', $row['Type'], $matches)) {
            $sizes = array(
                'tiny' => 255,
                'medium' => 16777215,
                'long' => 4294967295,
            );
            $ret[$name] = isset($sizes[$matches[1]]) ? $sizes[$matches[1]] : 65535;
        }
    }

    return $ret;
}

/**
 * @param $assoc: 連想配列．
 *        array( column_name1 => value1, column_name2 => value2, ... )
 * @param $table_wo_prefix: テーブル名(prefixを除く)
 * @param $names: チェックするカラム名.  array( 'readme', 'rights' ) など(省略時は全カラムをチェック)
 * @param $enc: valueのエンコード(省略可)
 * $assoc に 切り詰めた後の値を書く。
 *          array( column_name1 => within1, column_name2 => within2, ... )
 * @param string   $table_wo_prefix
 * @param string[] $names
 */
function xnpTrimColumn(&$assoc, $table_wo_prefix, $names = null, $enc = null)
{
    $lengths = xnpGetColumnLengths($table_wo_prefix);
    if (false == $lengths) {
        return false;
    }

    foreach ($lengths as $name => $len) {
        if (isset($assoc[$name]) && (is_null($names) || in_array($name, $names))) {
            list($within, $without) = xnpTrimString($assoc[$name], $len, $enc);
            $assoc[$name] = $within;
        }
    }
}

/**
 * @param $assoc: 連想配列．
 *        array( column_name => array( 'value' => value ), ... )
 * @param $table_wo_prefix: prefixを除いたテーブル名
 * @param $names: チェックするカラム名.  array( 'readme', 'rights' ) など
 * $assoc に value, within, without, html_string を書く。
 *          array( column_name => array( 'value'=>value, 'within'=>within, 'without'=>without, 'html_string'=>html_string ), ... )
 * @param string $table_wo_prefix
 */
function xnpConfirmHtml(&$assoc, $table_wo_prefix, $names = null, $enc = null)
{
    $textutil = &xoonips_getutility('text');
    $lengths = xnpGetColumnLengths($table_wo_prefix);
    if (false == $lengths) {
        return false;
    }

    foreach ($lengths as $name => $len) {
        if (isset($assoc[$name]) && (is_null($names) || in_array($name, $names))) {
            $assoc[$name]['html_string'] = $textutil->html_special_chars($assoc[$name]['value']);
            list($assoc[$name]['within'], $assoc[$name]['without']) = xnpTrimString($assoc[$name]['value'], $len, $enc);
            $assoc[$name]['value'] = xnpWithinWithoutHtml($assoc[$name]['within'], $assoc[$name]['without']);
        }
    }
}

function xnpDate($year, $month, $day)
{
    $int_year = intval($year);
    $int_month = intval($month);
    $int_day = intval($day);
    if (0 == $int_month) {
        $date = date(YEAR_FORMAT, mktime(0, 0, 0, 1, 1, $int_year));
    } else {
        if (0 == $int_day) {
            $date = date(YEAR_MONTH_FORMAT, mktime(0, 0, 0, $int_month, 1, $int_year));
        } else {
            $date = date(DATE_FORMAT, mktime(0, 0, 0, $int_month, $int_day, $int_year));
        }
    }
    if ($int_year < 0) {
        $date = str_replace('1970', strval(abs($int_year)), $date);
        $date .= 'B.C.';
    } elseif ($int_year < 1970) {
        $date = str_replace('1970', strval($int_year), $date);
    } elseif ($int_year >= 2070) {
        $date = str_replace('1970', strval($int_year), $date);
    }

    return $date;
}

function xnpISO8601($year, $month, $day)
{
    $int_year = intval($year);
    $int_month = intval($month);
    $int_day = intval($day);
    if (0 == $int_month) {
        $date = sprintf('%04s', $int_year);
    } elseif (0 == $int_day) {
        $date = sprintf('%04s-%02s', $int_year, $int_month);
    } else {
        $date = sprintf('%04s-%02s-%02s', $int_year, $int_month, $int_day);
    }

    return $date;
}

/**
 * get item id by doi(xoonips_basic_item table).
 *
 * @param doi       DOI of examined object
 * @param iids      return item id of each doi(array)
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnpGetItemIdByDoi($doi, &$iids)
{
    $iids = array();

    global $xoopsDB;

    $sql = 'SELECT `t1`.`item_id` FROM '.$xoopsDB->prefix('xoonips_item_basic').' AS t1 '.
        ' WHERE `t1`.`doi`='.$xoopsDB->quoteString($doi);
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    while (list($iid) = $xoopsDB->fetchRow($result)) {
        $iids[] = $iid;
    }

    return RES_OK;
}

/**
 * get doi(xoonips_basic_item table) by item id.
 *
 * @param item_id   item id of examined object
 * @param doi       return doi, according in id. return "" if item id not found.
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnpGetDoiByItemId($item_id, &$doi)
{
    global $xoopsDB;
    $doi = '';
    $sql = 'SELECT doi FROM '.$xoopsDB->prefix('xoonips_item_basic');
    $sql .= ' WHERE item_id = '.intval($item_id);
    $result = $xoopsDB->query($sql);
    if (false === $result) {
        xoonips_error_exit(500);
    }
    $result = $xoopsDB->fetchRow($result);
    if ($result) {
        list($doi) = $result;
    }

    return RES_OK;
}

/**
 *  get item detail URL from item id and doi.
 *
 *  @param item_id item id
 *  @param dois    doi array( use index 0 item only ) or doi value. if doi is NULL, search from item_id
 *
 *  @return string item detail url
 */
function xnpGetItemDetailURL($item_id, $dois = null)
{
    $handler = &xoonips_getormcompohandler('xoonips', 'item');

    return $handler->getItemDetailUrl($item_id);
}

/**
 * check doi field exists in db.
 *
 * @param doi doi
 *
 * @return true: doi is exists, false: doi is not exists
 */
function xnpIsDoiExists($doi)
{
    $iids = array();
    if (RES_OK == xnpGetItemIdByDoi($doi, $iids)) {
        if (count($iids) > 0) {
            return true;
        }
    }

    return false;
}

/**
 * get item basic information.
 *
 *  @param item_id item id
 *
 *  @return item detail<br />
 *     format:
 *       $result['item_id']<br />
 *       $result['doi']<br />
 *       $result[''] and set other xoonips_item_basic field value
 *  @return false: error
 */
function xnpGetItemBasicInfo($item_id)
{
    global $xoopsDB;
    $basic = $xoopsDB->prefix('xoonips_item_basic');
    $sql = 'SELECT * FROM '.$basic.' WHERE `item_id`='.$item_id;
    $db_result = $xoopsDB->query($sql);
    if (false === $db_result) {
        xoonips_error_exit(500);
    }
    $result = $xoopsDB->fetchArray($db_result);
    if (!$result) {
        $result = array();
    }

    return $result;
}

/**
 * list index tree.
 *
 *  @param mode integer />
 *                  return public tree only.<br />
 *                XOONIPS_LISTINDEX_MODE_PRIVATEONLY<br />
 *                  return private tree only.<br />
 *                XOONIPS_LISTINDEX_MODE_ALL<br />
 *                  return all index tree.
 *  @param assoc_array_mode true: return index id assoc array, false: return normal array
 *
 *  @return array: return index tree<br />
 *     format(if assoc_array_mode true):<br />
 *             array[0]['id']       = id(index id)<br />
 *             array[0]['fullpath'] = index title(full path).<br />
 *             array[0]['id_fullpath'] = index id list(full path. comma separated index id). ex. 11,10,20<br />
 *                   .<br />
 *             array[n]['id']<br />
 *             array[n]['fullpath']<br />
 *             array[n]['id_fullpath']
 *     format(if assoc_array_mode false):<br />
 *             array[(index id)]['id']       = id(index id)<br />
 *             array[(index id)]['fullpath'] = index title(full path).<br />
 *             array[(index id)]['id_fullpath'] = index id list(full path. comma separated index id). ex. 11,10,20<br />
 *  @return false: query error
 */
function xnpListIndexTree($mode = XOONIPS_LISTINDEX_MODE_ALL, $assoc_array_mode = false)
{
    global $xoopsDB;
    $index = $xoopsDB->prefix('xoonips_index');
    $item_basic = $xoopsDB->prefix('xoonips_item_basic');
    $item_title = $xoopsDB->prefix('xoonips_item_title');
    $where_level = '';
    switch ($mode) {
    case XOONIPS_LISTINDEX_MODE_ALL:
        $where_level = '1';
        break;
    case XOONIPS_LISTINDEX_PUBLICONLY:
        $where_level .= '`tx`.`open_level`='.OL_PUBLIC;
        break;
    case XOONIPS_LISTINDEX_PUBLICONLY:
        $where_level .= '`tx`.`open_level`='.OL_PRIVATE.' OR `ti`.`item_id`='.IID_ROOT.' ';
        break;
    }

    $sql = 'SELECT `tx`.`index_id`, `tx`.`parent_index_id`, `tx`.`uid`, `tx`.`gid`, `tx`.`open_level`, `tx`.`sort_number`, `ti`.`item_type_id`, `tt`.`title`'.
        ' FROM '.$item_title.' AS tt, '.$index.' AS `tx`'.
        ' LEFT JOIN '.$item_basic.' AS `ti` ON `tx`.`index_id`=`ti`.`item_id`'.
        ' WHERE ('.$where_level.')'.
        ' AND `tt`.`title_id`='.DEFAULT_ORDER_TITLE_OFFSET.' AND `tt`.`item_id`=`ti`.`item_id`'.
        ' ORDER BY `tx`.`uid`, `tx`.`parent_index_id`, `tx`.`sort_number`';
    $db_result = $xoopsDB->query($sql);
    if (false === $db_result) {
        xoonips_error_exit(500);
    }
    $tree_items = array();
    $parent_full_path = array();
    $parent_id_full_path = array();
    $result = array();
    while ($ar = $xoopsDB->fetchArray($db_result)) {
        $index_id = intval($ar['index_id']);
        $tree_items[$index_id] = $ar;
        $pid = intval($ar['parent_index_id']);
        if (!isset($parent_full_path[$pid])) {
            $parent_full_path[$pid] = '';
        }
        if (!isset($parent_id_full_path[$pid])) {
            $parent_id_full_path[$pid] = '';
        }
    }
    // extract to full path
    foreach ($parent_full_path as $k => $v) {
        if (0 == $k) {
            continue;
        }
        $idx = $k;
        $fullpath = '';
        $id_fullpath = '';
        while (0 != $idx) {
            if (!isset($tree_items[$idx])) {
                break;
            }
            $fullpath = $tree_items[$idx]['title'].'/'.$fullpath;
            $id_fullpath = $tree_items[$idx]['index_id'].','.$id_fullpath;
            $idx = $tree_items[$idx]['parent_index_id'];
        }
        $parent_full_path[$k] = $fullpath;
        $parent_id_full_path[$k] = $id_fullpath;
    }
    $result = array();
    // set result from tree_items and parent_full_path.
    foreach ($tree_items as $k => $v) {
        $parent_path = $parent_full_path[$v['parent_index_id']];
        $parent_id_path = $parent_id_full_path[$v['parent_index_id']];
        // exclude check.
        if (IID_ROOT == $v['index_id']) {
            continue;
        }
        // delete "ROOT" string.
        $idx = strpos($parent_path, '/');
        $parent_path = substr($parent_path, $idx, strlen($parent_path));
        // delete "ROOT" id.
        $idx = strpos($parent_id_path, ',');
        $parent_id_path = substr($parent_id_path, $idx + 1, strlen($parent_id_path));
        // set value to result array
        $a = array();
        $a['id'] = $k;
        $a['fullpath'] = $parent_path.$v['title'];
        $a['id_fullpath'] = $parent_id_path.$v['index_id'];
        if ($assoc_array_mode) {
            $result[intval($k)] = $a;
        } else {
            $result[] = $a;
        }
    }

    return $result;
}

/**
 * wrapper API for item type programming library.
 */
class XooNIpsItemLibraryObject
{
    public $_item_basic_obj = null;
    public $_xoops_users_obj = null;
    public $_xoonips_users_obj = null;
    public $_item_type_obj = null;
    public $_item_title_objs = array();
    public $_item_keyword_objs = array();
    public $_related_to_ids = array();
    public $_changelog_objs = array();
    public $_related_to_check_ids = array(); // for edit
    public $_related_to_check_all_ids = array(); // for edit
    public $_changelog = ''; // for edit

    public function __construct(&$meta)
    {
        $this->_item_basic_obj = &$meta['item_basic'];
        $this->_xoops_users_obj = &$meta['xoops_user'];
        $this->_xoonips_users_obj = &$meta['xoonips_user'];
        $this->_item_type_obj = &$meta['item_type'];
        $this->_item_title_objs = &$meta['item_title_arr'];
        $this->_item_keyword_objs = &$meta['item_keyword_arr'];
        $this->_related_to_ids = $meta['related_to_arr'];
        $this->_changelog_objs = $meta['changelog_arr'];
    }

    /**
     * get item list block.
     *
     * @return string html
     */
    public function getItemListBlock()
    {
        $modname = $this->_item_type_obj->get('name');
        $viewphp = $this->_item_type_obj->get('viewphp');
        require_once XOOPS_ROOT_PATH.'/modules/'.$viewphp;
        $ret = '';
        $func = $modname.'GetListBlock';
        if (function_exists($func)) {
            $item_basic = $this->getBasicInformationArray('e');
            $ret = $func($item_basic);
        }

        return $ret;
    }

    /**
     * get item id.
     *
     * @return int item id
     */
    public function getItemId()
    {
        return $this->_item_basic_obj->get('item_id');
    }

    /**
     * get item basic information array.
     *
     * @param string $fmt format
     *
     * @return array formated strings
     */
    public function getBasicInformationArray($fmt)
    {
        $textutil = &xoonips_getutility('text');
        // item type
        if (is_object($this->_item_type_obj)) {
            $item_type_id = $this->_item_type_obj->get('item_type_id');
            $item_type = $this->_item_type_obj->getVar('display_name', $fmt);
        } else {
            $item_type_id = 0;
            $item_type = '';
        }
        // contributor
        if (is_object($this->_xoops_users_obj)) {
            $user_name = $this->_xoops_users_obj->getVar('name', $fmt);
            $user_uname = $this->_xoops_users_obj->getVar('uname', $fmt);
            if ('' == $user_name) {
                $contributor = $user_uname;
            } else {
                $contributor = $user_name.' ('.$user_uname.')';
            }
        } else {
            $contributor = '(Zombie User)';
        }
        // titles
        $titles = array();
        foreach ($this->_item_title_objs as $item_title_obj) {
            $titles[] = $item_title_obj->getVar('title', $fmt);
        }
        // keywords
        $keywords = array();
        foreach ($this->_item_keyword_objs as $item_keyword_obj) {
            $keywords[] = $item_keyword_obj->getVar('keyword', $fmt);
        }
        // last update date
        $last_update_date = xoops_getUserTimestamp($this->_item_basic_obj->get('last_update_date'));
        // creation date
        $creation_date = xoops_getUserTimestamp($this->_item_basic_obj->get('creation_date'));
        // publication date (year, month, mday)
        $publication_year = $this->_item_basic_obj->get('publication_year');
        $publication_month = $this->_item_basic_obj->get('publication_month');
        $publication_mday = $this->_item_basic_obj->get('publication_mday');
        $publication_date = xnpDate($publication_year, $publication_month, $publication_mday);
        // language
        $lang_map = array();
        $lang_ids = explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS);
        $lang_names = explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_NAMES);
        foreach ($lang_ids as $num => $lang_id) {
            $lang_map[$lang_id] = $lang_names[$num];
        }
        $lang = $this->_item_basic_obj->get('lang');
        $language = isset($lang_map[$lang]) ? $lang_map[$lang] : '';
        // change log
        if ('e' == $fmt || 's' == $fmt) {
            $change_log = $textutil->html_special_chars($this->_changelog);
        } else {
            $change_log = $this->_changelog;
        }
        // return array
        return array(
            'item_id' => $this->_item_basic_obj->get('item_id'),
            'item_url' => $this->getItemDetailUrl(),
            'uid' => $this->_item_basic_obj->get('uid'),
            'item_type_id' => $item_type_id,
            'item_type' => $item_type,
            'titles' => $titles,
            'contributor' => $contributor,
            'keywords' => $keywords,
            'description' => $this->_item_basic_obj->getVar('description', $fmt),
            'doi' => $this->_item_basic_obj->getVar('doi', $fmt),
            'last_update_date' => date(DATETIME_FORMAT, $last_update_date),
            'creation_date' => date(DATETIME_FORMAT, $creation_date),
            'publication_year' => $publication_year,
            'publication_month' => $publication_month,
            'publication_mday' => $publication_mday,
            'publication_date' => $publication_date,
            'lang' => $lang,
            'language' => $language,
            'related_to' => $this->_related_to_ids,
            'related_to_check' => $this->_related_to_check_ids,
            'related_to_check_all' => $this->_related_to_check_all_ids,
            'change_log' => $change_log,
        );
    }

    /**
     * get basic information detail block.
     *
     * @return array detail block of basic information
     */
    public function getBasicInformationDetailBlock()
    {
        // show values
        $basic = $this->getBasicInformationArray('s');
        // related to
        $tpl = new XoopsTpl();
        $tpl->assign('item_htmls', $this->_getRelatedToHtmlArray('s'));
        $related_to = $tpl->fetch('db:xoonips_detail_related_to.html');

        return array(
            'uid' => array(
                'name' => _MD_XOONIPS_ITEM_UID_LABEL,
                'value' => $basic['uid'],
            ),
            'contributor' => array(
                'name' => _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL,
                'value' => $basic['contributor'],
            ),
            'title' => array(
                'name' => _MD_XOONIPS_ITEM_TITLE_LABEL,
                'value' => implode("\n", $basic['titles']),
            ),
            'keywords' => array(
                'name' => _MD_XOONIPS_ITEM_KEYWORDS_LABEL,
                'value' => implode(', ', $basic['keywords']),
            ),
            'description' => array(
                'name' => _MD_XOONIPS_ITEM_DESCRIPTION_LABEL,
                'value' => $basic['description'],
            ),
            'doi' => array(
                'name' => _MD_XOONIPS_ITEM_DOI_LABEL,
                'value' => $basic['doi'],
            ),
            'last_update_date' => array(
                'name' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
                'value' => $basic['last_update_date'],
            ),
            'creation_date' => array(
                'name' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
                'value' => $basic['creation_date'],
            ),
            'item_type' => array(
                'name' => _MD_XOONIPS_ITEM_ITEM_TYPE_LABEL,
                'value' => $basic['item_type'],
            ),
            'change_logs' => array(
                'name' => _MD_XOONIPS_ITEM_CHANGELOGS_LABEL,
                'value' => $this->_getChangeLogHtml(),
            ),
            'publication_date' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
                'value' => $basic['publication_date'],
            ),
            'publication_year' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_YEAR_LABEL,
                'value' => $basic['publication_year'],
            ),
            'publication_month' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MONTH_LABEL,
                'value' => $basic['publication_month'],
            ),
            'publication_mday' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MDAY_LABEL,
                'value' => $basic['publication_mday'],
            ),
            'lang' => array(
                'name' => _MD_XOONIPS_ITEM_LANG_LABEL,
                'value' => $basic['language'],
            ),
            'related_to' => array(
                'name' => _MD_XOONIPS_ITEM_RELATED_TO_LABEL,
                'value' => $related_to,
            ),
            'hidden' => $this->getHiddenHtml(),
        );
    }

    /**
     * get basic information edit block.
     *
     * @param bool $is_register
     *
     * @return array edit block of basic information
     */
    public function getBasicInformationEditBlock($is_register)
    {
        $textutil = &xoonips_getutility('text');

        // edit values
        $basic = $this->getBasicInformationArray('e');

        // change log
        if ($is_register) {
            $change_log = '';
        } else {
            $change_log = sprintf('<input type="text" name="change_log" value="%s" size="50"/>', $textutil->html_special_chars($this->_changelog));
        }
        // publication date (year, month, mday)
        $tpl = new XoopsTpl();
        $pubyear = isset($basic['publication_year']) ? intval($basic['publication_year']) : 0;
        $pubmonth = isset($basic['publication_month']) ? intval($basic['publication_month']) : 0;
        $pubmday = isset($basic['publication_mday']) ? intval($basic['publication_mday']) : 0;
        $gmtime = 0 == $pubyear ? '' : sprintf('%04d-%02d-%02d', $pubyear, $pubmonth, $pubmday);
        $tpl->assign('gmtime', $gmtime);
        $publcation_date = $tpl->fetch('db:xoonips_publication_date.html');
        $publication_year = $tpl->fetch('db:xoonips_publication_year.html');
        $publication_month = $tpl->fetch('db:xoonips_publication_month.html');
        $publication_mday = $tpl->fetch('db:xoonips_publication_mday.html');
        // lang
        $tpl = new XoopsTpl();
        $tpl->assign('lang_option_ids', explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS));
        $tpl->assign('lang_option_names', explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_NAMES));
        $tpl->assign('lang_option_default_id', $basic['lang']);

        $lang = $tpl->fetch('db:xoonips_lang.html');
        // related to
        $tpl = new XoopsTpl();
        $tpl->assign('related_to', implode("\n", $basic['related_to']));
        if ($is_register) {
            $related_to = $tpl->fetch('db:xoonips_register_related_to.html');
        } else {
            $item_htmls = array();
            foreach ($this->_getRelatedToHtmlArray('e') as $id => $html) {
                $item_htmls[$id] = array(
                'html' => $html,
                'check' => empty($basic['related_to_check_all']) || in_array($id, $basic['related_to_check']),
                );
            }
            $tpl->assign('item_htmls', $item_htmls);
            $related_to = $tpl->fetch('db:xoonips_edit_related_to.html');
        }

        return array(
            'contributor' => array(
                'name' => _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL,
                'value' => ($is_register) ? '' : $basic['contributor'],
            ),
            'title' => array(
                'name' => _MD_XOONIPS_ITEM_TITLE_LABEL._MD_XOONIPS_ITEM_REQUIRED_MARK,
                'value' => sprintf('<textarea name="title" rows="3" cols="50" wrap="off" style="width:400px;">%s</textarea>', implode("\n", $basic['titles'])),
            ),
            'keywords' => array(
                'name' => _MD_XOONIPS_ITEM_KEYWORDS_LABEL,
                'value' => sprintf('<input size="50" type="text" name="keywords" value="%s"/><br /> Separate the words or phrases with commas.', implode(',', $basic['keywords'])),
            ),
            'description' => array(
                'name' => _MD_XOONIPS_ITEM_DESCRIPTION_LABEL,
                'value' => sprintf('<textarea rows="5" cols="50" name="description" style="width:400px;">%s</textarea>', $basic['description']),
            ),
            'doi' => array(
                'name' => _MD_XOONIPS_ITEM_DOI_LABEL,
                'value' => sprintf('<input type="text" name="doi" value="%s" size="50"/>', $basic['doi']),
            ),
            'last_update_date' => array(
                'name' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
                'value' => ($is_register) ? '' : $basic['last_update_date'],
            ),
            'creation_date' => array(
                'name' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
                'value' => ($is_register) ? '' : $basic['creation_date'],
            ),
            'change_log' => array(
                'name' => _MD_XOONIPS_ITEM_CHANGELOG_LABEL,
                'value' => $change_log,
            ),
            'change_logs' => array(
                'name' => _MD_XOONIPS_ITEM_CHANGELOGS_LABEL,
                'value' => ($is_register) ? '' : $this->_getChangeLogHtml(),
            ),
            'publication_date' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
                'value' => $publcation_date,
            ),
            'publication_year' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_YEAR_LABEL,
                'value' => $publication_year,
            ),
            'publication_month' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MONTH_LABEL,
                'value' => $publication_month,
            ),
            'publication_mday' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MDAY_LABEL,
                'value' => $publication_mday,
            ),
            'lang' => array(
                'name' => _MD_XOONIPS_ITEM_LANG_LABEL,
                'value' => $lang,
            ),
            'related_to' => array(
                'name' => _MD_XOONIPS_ITEM_RELATED_TO_LABEL,
                'value' => $related_to,
            ),
        );
    }

    public function getBasicInformationConfirmBlock($is_register)
    {
        $textutil = &xoonips_getutility('text');

        // show values
        $basic = $this->getBasicInformationArray('s');

        // change log
        $changelog = $textutil->html_special_chars($this->_changelog);

        // related to
        $tpl = new XoopsTpl();
        $tpl->assign('related_to', implode("\n", $basic['related_to']));
        $tpl->assign('item_htmls', $this->_getRelatedToHtmlArray('c'));
        $related_to = $tpl->fetch('db:xoonips_confirm_related_to.html');

        // description
        $description = $this->_getConfirmTemplateVars(_MD_XOONIPS_ITEM_DESCRIPTION_LABEL, $this->_item_basic_obj->get('description'), $this->_item_basic_obj->getMaxLength('description'));

        // doi
        $doi = $this->_getConfirmTemplateVars(_MD_XOONIPS_ITEM_DOI_LABEL, $this->_item_basic_obj->get('doi'), $this->_item_basic_obj->getMaxLength('doi'));

        // title
        $title['name'] = _MD_XOONIPS_ITEM_TITLE_LABEL;
        foreach ($this->_item_title_objs as $obj) {
            $t = $this->_getConfirmTemplateVars('', $obj->get('title'), $obj->getMaxLength('title'));
            foreach ($t as $k => $v) {
                if ('name' == $k || ('without' == $k && '' == $v)) {
                    continue;
                }
                if (!isset($title[$k])) {
                    $title[$k] = $t[$k];
                } else {
                    $title[$k] .= "\n".$t[$k];
                }
            }
        }

        // keywords
        $keywords['name'] = _MD_XOONIPS_ITEM_KEYWORDS_LABEL;
        foreach ($this->_item_keyword_objs as $obj) {
            $t = $this->_getConfirmTemplateVars('', $obj->get('keyword'), $obj->getMaxLength('keyword'));
            foreach ($t as $k => $v) {
                if ('name' == $k || ('without' == $k && '' == $v)) {
                    continue;
                }
                if (!isset($keywords[$k])) {
                    $keywords[$k] = $t[$k];
                } else {
                    $keywords[$k] .= ','.$t[$k];
                }
            }
        }

        return array(
            'item_type' => array(
                'name' => _MD_XOONIPS_ITEM_ITEM_TYPE_LABEL,
                'value' => $basic['item_type'],
            ),
            'contributor' => array(
                'name' => _MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL,
                'value' => ($is_register ? '' : $basic['contributor']),
            ),
            'last_update_date' => array(
                'name' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
                'value' => ($is_register ? '' : $basic['last_update_date']),
            ),
            'creation_date' => array(
                'name' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
                'value' => ($is_register ? '' : $basic['creation_date']),
            ),
            'change_log' => array(
                'name' => _MD_XOONIPS_ITEM_CHANGELOG_LABEL,
                'value' => $changelog,
            ),
            'change_logs' => array(
                'name' => _MD_XOONIPS_ITEM_CHANGELOGS_LABEL,
                'value' => $this->_getChangeLogHtml(),
            ),
            'publication_date' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
                'value' => $basic['publication_date'],
            ),
            'publication_year' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_YEAR_LABEL,
                'value' => $basic['publication_year'],
            ),
            'publication_month' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MONTH_LABEL,
                'value' => $basic['publication_month'],
            ),
            'publication_mday' => array(
                'name' => _MD_XOONIPS_ITEM_PUBLICATION_MDAY_LABEL,
                'value' => $basic['publication_mday'],
            ),
            'lang' => array(
                'name' => _MD_XOONIPS_ITEM_LANG_LABEL,
                'value' => $basic['language'],
            ),
            'related_to' => array(
                'name' => _MD_XOONIPS_ITEM_RELATED_TO_LABEL,
                'value' => $related_to,
            ),
            'description' => $description,
            'doi' => $doi,
            'title' => $title,
            'keywords' => $keywords,
        );
    }

    /**
     * truncate object for insert object to database.
     *
     * @return bool false if failure
     */
    public function truncateObject()
    {
        // item basic
        $this->_truncateSimpleObject($this->_item_basic_obj);
        // titles
        foreach ($this->_item_title_objs as $item_title_obj) {
            $this->_truncateSimpleObject($item_title_obj);
        }
        // keywords
        foreach ($this->_item_keyword_objs as $item_keyword_obj) {
            $this->_truncateSimpleObject($item_keyword_obj);
        }

        return true;
    }

    /**
     * get item detail url.
     *
     * @return string url
     */
    public function getItemDetailUrl()
    {
        $doi = $this->_item_basic_obj->get('doi');
        $mydirname = basename(dirname(__DIR__));
        if ('' != $doi && XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') {
            $opt = XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi);
        } else {
            $opt = 'item_id='.$this->_item_basic_obj->get('item_id');
        }

        return XOOPS_URL.'/modules/'.$mydirname.'/detail.php?'.$opt;
    }

    /**
     * get item basic object.
     *
     * @return object
     */
    public function &getItemBasicObject()
    {
        return $this->_item_basic_obj;
    }

    /**
     * get title objects.
     *
     * @return array
     */
    public function &getTitleObjects()
    {
        return $this->_item_title_objs;
    }

    /**
     * set title objects.
     *
     * @param array objects
     */
    public function setTitleObjects(&$objs)
    {
        $this->_item_title_objs = &$objs;
    }

    /**
     * get keyword objects.
     *
     * @return array
     */
    public function &getKeywordObjects()
    {
        return $this->_item_keyword_objs;
    }

    /**
     * set keyword objects.
     *
     * @param array objects
     */
    public function setKeywordObjects(&$objs)
    {
        $this->_item_keyword_objs = &$objs;
    }

    /**
     * set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_item_basic_obj->set('description', $description);
    }

    /**
     * set item type id.
     *
     * @param int $item_type_id
     */
    public function setItemTypeId($item_type_id)
    {
        $this->_item_basic_obj->set('item_type_id', $item_type_id);
    }

    /**
     * set doi.
     *
     * @param string $doi
     */
    public function setDOI($doi)
    {
        if (strlen($doi) <= XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN && preg_match('/'.XNP_CONFIG_DOI_FIELD_PARAM_PATTERN.'/', $doi)) {
            $this->_item_basic_obj->set('doi', $doi);
        }
    }

    /**
     * set change log.
     */
    public function setChangeLog($change_log)
    {
        $this->_changelog = $change_log;
    }

    /**
     * set publication year.
     *
     * @param int $year
     */
    public function setPublicationYear($year)
    {
        $this->_item_basic_obj->set('publication_year', $year);
    }

    /**
     * set publication month.
     *
     * @param int $month
     */
    public function setPublicationMonth($month)
    {
        if ($month >= 0 && $month <= 12) {
            $this->_item_basic_obj->set('publication_month', $month);
        }
    }

    /**
     * set publication day.
     *
     * @param int $mday
     */
    public function setPublicationDay($mday)
    {
        if ($mday >= 0 && $mday <= 31) {
            $this->_item_basic_obj->set('publication_mday', $mday);
        }
    }

    /**
     * set language.
     *
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        $lang_ids = explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS);
        if (in_array($lang, $lang_ids)) {
            $this->_item_basic_obj->set('lang', $lang);
        }
    }

    /**
     * get related to item ids.
     *
     * @param array
     */
    public function getRelatedTo()
    {
        return $this->_related_to_ids;
    }

    /**
     * set related to.
     */
    public function setRelatedTo($related_to)
    {
        $related_to = trim($related_to);
        $ids = preg_split("/[\r\n]+/", $related_to);
        $related_to_ids = array();
        foreach ($ids as $id) {
            $id = intval($id);
            if (0 != $id) {
                $related_to_ids[] = $id;
            }
        }
        $this->_related_to_ids = $related_to_ids;
    }

    /**
     * get related to check item ids.
     *
     * @param array
     */
    public function getRelatedToCheck()
    {
        return $this->_related_to_check_ids;
    }

    /**
     * set related to check (for edit).
     *
     * @param array $item_ids
     * @param array $item_all_ids
     */
    public function setRelatedToCheck($item_ids, $item_all_ids)
    {
        $this->_related_to_check_ids = $item_ids;
        $this->_related_to_check_all_ids = $item_all_ids;
    }

    /**
     * get hidden html.
     */
    public function getHiddenHtml()
    {
        $basic = $this->getBasicInformationArray('e');
        $hidden = array();
        // single value
        $keys = array(
            'item_type_id' => $basic['item_type_id'],
            'title' => implode("\n", $basic['titles']),
            'keywords' => implode(',', $basic['keywords']),
            'description' => $basic['description'],
            'doi' => $basic['doi'],
            'publicationDateYear' => $basic['publication_year'],
            'publicationDateMonth' => $basic['publication_month'],
            'publicationDateDay' => $basic['publication_mday'],
            'lang' => $basic['lang'],
            'related_to' => implode("\n", $basic['related_to']),
            'change_log' => $basic['change_log'],
        );
        foreach ($keys as $key => $value) {
            $hidden[] = $this->_renderHiddenHtml($key, $value);
        }
        // multiple values
        $keys = array(
            'related_to_check' => $basic['related_to_check'],
            'related_to_check_all' => $basic['related_to_check_all'],
        );
        foreach ($keys as $key => $values) {
            foreach ($values as $value) {
                $hidden[] = $this->_renderHiddenHtml($key.'[]', $value);
            }
        }

        return implode("\n", $hidden);
    }

    /**
     * check need to cerity.
     *
     * @return bool true if required
     */
    public function isCertifyRequired()
    {
        $item_id = $this->_item_basic_obj->get('item_id');
        // check modified fields
        $modname = $this->_item_type_obj->get('name');
        $viewphp = $this->_item_type_obj->get('viewphp');
        require_once XOOPS_ROOT_PATH.'/modules/'.$viewphp;
        $func = $modname.'GetModifiedFields';
        $modified = xnpGetModifiedFields($item_id) + (function_exists($func) ? $func($item_id) : array());
        if (0 == count($modified)) {
            // modified field not found, no need to certify
            return false;
        }
        // fetch new index ids
        $formdata = &xoonips_getutility('formdata');
        $xids_new = array_map('intval', explode(',', $formdata->getValue('post', 'xoonipsCheckedXID', 's', false, '')));
        if (1 == count($modified) && _MD_XOONIPS_ITEM_INDEX_LABEL == $modified[0]) {
            // only indexes are modified
            // get old index ids
            $xids_old = array();
            $item_id = $this->_item_basic_obj->get('item_id');
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $criteria = new Criteria('item_id', $item_id);
            $index_item_link_objs = &$index_item_link_handler->getObjects();
            foreach ($index_item_link_objs as $index_item_link_obj) {
                $xids_old[] = $index_item_link_obj->get('index_id');
            }
            // get newly arrived index ids
            $xids = array_diff($xids_new, $xids_old);
        } else {
            // set all new index ids
            $xids = $xids_new;
        }
        if (0 == count($xids)) {
            // indexes are not changed
            return false;
        }
        // check open_level of modified indexes
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new CriteriaCompo(new Criteria('open_level', OL_PRIVATE, '!='));
        $criteria->add(new Criteria('index_id', '('.implode(',', $xids).')', 'IN'));

        return  $index_handler->getCount($criteria) > 0;
    }

    /**
     * get related to htmls.
     *
     * @param string $type block type
     *                     's' : show
     *                     'e' : edit
     *                     'c' : confirm
     *
     * @return array renderd list block htmls
     */
    public function _getRelatedToHtmlArray($type)
    {
        $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
        $htmls = array();
        switch ($type) {
        case 's':
            // show block
            $item_ids = $this->_related_to_ids;
            break;
        case 'e':
            // edit block
            $item_ids = array_merge($this->_related_to_ids, $this->_related_to_check_all_ids);
            break;
        case 'c':
            // confirm block
            $item_ids = array_merge($this->_related_to_ids, $this->_related_to_check_ids);
            break;
        }
        $self_item_id = $this->_item_basic_obj->get('item_id');
        foreach ($item_ids as $item_id) {
            if ($self_item_id == $item_id) {
                continue;
            }
            $itemlib_obj = &$itemlib_handler->get($item_id);
            if (!is_object($itemlib_obj)) {
                // broken related to item id found
                if (XOONIPS_DEBUG_MODE) {
                    error_log('BROKEN RELATED TO ITEM ID FOUND : PARENT('.$this->_item_basic_obj->get('item_id').') - CHILD('.$item_id.')');
                }
                continue;
            }
            $htmls[$item_id] = $itemlib_obj->getItemListBlock();
        }

        return $htmls;
    }

    /**
     * get change log html.
     *
     * @return string rendered html
     */
    public function _getChangeLogHtml()
    {
        $ret = '';
        if (count($this->_changelog_objs) > 0) {
            $ret = '<table>'."\n";
            foreach ($this->_changelog_objs as $changelog_obj) {
                $ret .= '<tr><td nowrap="nowrap">';
                $ret .= date(DATE_FORMAT, $changelog_obj->get('log_date'));
                $ret .= '&nbsp;</td><td>';
                $ret .= nl2br($changelog_obj->getVar('log', 's'));
                $ret .= '</td></tr>'."\n";
            }
            $ret .= '</table>'."\n";
        }

        return $ret;
    }

    /**
     * render hidden html.
     *
     * @param string $key
     * @param mixed  $val
     *
     * @return string rendered html
     */
    public function _renderHiddenHtml($key, $val)
    {
        return sprintf('<input type="hidden" name="%s" value="%s"/>', $key, $val);
    }

    /**
     * get confirmation template variables array.
     *
     * @param string $name
     * @param string $text
     * @param int    $len  string maximum length
     *
     * @return array template variables
     *               - 'name' : label
     *               - 'html_string' : escaped form string
     *               - 'value' : escaped display data
     *               - 'within' : trimed string (escaped)
     *               - 'without' : overflowed string (escaped)
     */
    public function _getConfirmTemplateVars($name, $text, $len)
    {
        $textutil = &xoonips_getutility('text');
        // truncate
        $within_raw = $this->_truncateSimpleText($text, $len);
        // remove broken html entity if truncated
        if (strlen($text) != strlen($within_raw)) {
            // get overflowd string
            $without_raw = substr($text, strlen($within_raw));
        } else {
            $without_raw = '';
        }
        // escaped
        $within = $textutil->html_special_chars($within_raw);
        $without = $textutil->html_special_chars($without_raw);
        $html_string = $textutil->html_special_chars($text);
        // create value
        $value = $within.('' != $without ? sprintf('<span style="color: red;">%s</span>', $without) : '');

        return array(
            'name' => $name,
            'html_string' => $html_string,
            'within' => $within,
            'without' => $without,
            'value' => $value,
        );
    }

    /**
     * truncate simple text.
     *
     * @param string $text source text
     * @param string $len  text length
     * @retrun string truncated string
     */
    public function _truncateSimpleText($text, $len)
    {
        $textutil = &xoonips_getutility('text');
        // truncate
        $trunc = mb_substr($text, 0, $len, mb_detect_encoding($text));
        // remove broken html entity if truncated
        if (strlen($text) != strlen($trunc)) {
            $trunc = preg_replace('/&#[^;]*$/s', '', $trunc);
        }

        return $trunc;
    }

    /**
     * truncate simple object.
     *
     * @param object $obj
     * @retrun bool false if failure
     */
    public function _truncateSimpleObject(&$obj)
    {
        $keys = $obj->getKeysArray();
        foreach ($keys as $key) {
            $len = $obj->getMaxLength($key);
            if (false !== $len) {
                $value = $this->_truncateSimpleText($obj->get($key), $len);
                $obj->set($key, $value);
            }
        }

        return true;
    }
}

class XooNIpsItemLibraryHandler
{
    public $_item_basic_handler;
    public $_xoops_users_handler;
    public $_xoonips_users_handler;
    public $_item_type_handler;
    public $_item_keyword_handler;
    public $_item_title_handler;
    public $_related_to_handler;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->_item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $this->_xoops_users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $this->_xoonips_users_handler = &xoonips_getormhandler('xoonips', 'users');
        $this->_item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $this->_item_title_handler = &xoonips_getormhandler('xoonips', 'title');
        $this->_item_keyword_handler = &xoonips_getormhandler('xoonips', 'keyword');
        $this->_related_to_handler = &xoonips_getormhandler('xoonips', 'related_to');
        $this->_changelog_handler = &xoonips_getormhandler('xoonips', 'changelog');
    }

    /**
     * get handler instance.
     *
     * @return XooNIpsItemLibraryHandler instance of class XooNIpsItemLibraryHandler
     */
    public static function &getInstance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * create object instance.
     *
     * @return XooNIpsItemLibraryObject instance of class XooNIpsItemLibraryObject
     */
    public function &create()
    {
        $meta = array();
        $uid = $GLOBALS['xoopsUser']->getVar('uid');
        $meta['item_basic'] = &$this->_item_basic_handler->create();
        $meta['item_basic']->set('uid', $uid);
        $meta['xoops_user'] = &$this->_xoops_users_handler->get($uid);
        $meta['xoonips_user'] = &$this->_xoonips_users_handler->get($uid);
        $meta['item_type'] = null;
        $meta['item_title_arr'] = array();
        $meta['item_keyword_arr'] = array();
        $meta['related_to_arr'] = array();
        $meta['changelog_arr'] = array();
        $ret = new XooNIpsItemLibraryObject($meta);

        return $ret;
    }

    /**
     * get xoonips item basic objects.
     *
     * @param int $item_id
     *
     * @return object instance of class XooNIpsItemLibraryObject
     */
    public function &get($item_id)
    {
        $ret = false;
        $meta = array();
        $meta['item_basic'] = &$this->_item_basic_handler->get($item_id);
        if (!is_object($meta['item_basic'])) {
            return $ret;
        }
        $uid = $meta['item_basic']->get('uid');
        $meta['xoops_user'] = &$this->_xoops_users_handler->get($uid);
        $meta['xoonips_user'] = &$this->_xoonips_users_handler->get($uid);
        $item_type_id = $meta['item_basic']->get('item_type_id');
        if (ITID_INDEX == $item_type_id) {
            return $ret;
        } // ignore if item_id is index_id
        $meta['item_type'] = &$this->_item_type_handler->get($item_type_id);
        $meta['item_title_arr'] = &$this->_item_title_handler->getTitles($item_id);
        $meta['item_keyword_arr'] = &$this->_item_keyword_handler->getKeywords($item_id);
        $meta['related_to_arr'] = $this->_related_to_handler->getChildItemIds($item_id);
        $meta['changelog_arr'] = $this->_changelog_handler->getChangeLogs($item_id);
        $ret = new XooNIpsItemLibraryObject($meta);

        return $ret;
    }

    /**
     * fetch previous page's form request data.
     *
     * @param object $itemlib_obj
     * @param bool   $do_check_post_id if check post_id sended
     */
    public function fetchRequest(&$itemlib_obj, $do_check_post_id)
    {
        $formdata = &xoonips_getutility('formdata');
        if ($do_check_post_id) {
            $post_id = $formdata->getValue('get', 'post_id', 's', false);
            if (is_null($post_id)) {
                // first item registeration
                return;
            }
        }
        // title
        $value = $formdata->getValue('post', 'title', 's', false);
        if (!is_null($value)) {
            $objs_old = &$itemlib_obj->getTitleObjects();
            $values = preg_split("/[\r\n]+/", $value);
            $objs_new = array();
            foreach ($values as $num => $val) {
                $val = trim($val);
                if ('' == $val) {
                    continue;
                }
                if (isset($objs_old[$num])) {
                    $obj = &$objs_old[$num];
                } else {
                    $obj = &$this->_item_title_handler->create();
                }
                $obj->set('title', $val);
                $obj->set('title_id', $num);
                $objs_new[] = &$obj;
                unset($obj);
            }
            $itemlib_obj->setTitleObjects($objs_new);
            unset($objs_old);
            unset($objs_new);
        }
        // keywords
        $value = $formdata->getValue('post', 'keywords', 's', false);
        if (!is_null($value)) {
            $objs_old = &$itemlib_obj->getKeywordObjects();
            $values = explode(',', $value);
            $objs_new = array();
            foreach ($values as $num => $val) {
                $val = trim($val);
                if ('' == $val) {
                    continue;
                }
                if (isset($objs_old[$num])) {
                    $obj = &$objs_old[$num];
                } else {
                    $obj = &$this->_item_keyword_handler->create();
                }
                $obj->set('keyword', $val);
                $obj->set('keyword_id', $num);
                $objs_new[] = &$obj;
                unset($obj);
            }
            $itemlib_obj->setKeywordObjects($objs_new);
            unset($objs_old);
            unset($objs_new);
        }
        // item type id
        $value = $formdata->getValue('post', 'item_type_id', 'i', false);
        if (!is_null($value)) {
            $itemlib_obj->setItemTypeId($value);
        }
        // description
        $value = $formdata->getValue('post', 'description', 's', false);
        if (!is_null($value)) {
            $itemlib_obj->setDescription($value);
        }
        // doi
        $value = $formdata->getValue('post', 'doi', 's', false);
        if (!is_null($value)) {
            $itemlib_obj->setDOI($value);
        }
        // change log
        $value = $formdata->getValue('post', 'change_log', 's', false);
        if (!is_null($value)) {
            $itemlib_obj->setChangeLog($value);
        }
        // publication date (yaar, month, mday)
        $value = $formdata->getValue('post', 'publicationDateYear', 'i', false);
        if (!is_null($value)) {
            $itemlib_obj->setPublicationYear($value);
        }
        $value = $formdata->getValue('post', 'publicationDateMonth', 'i', false);
        if (!is_null($value)) {
            $itemlib_obj->setPublicationMonth($value);
        }
        $value = $formdata->getValue('post', 'publicationDateDay', 'i', false);
        if (!is_null($value)) {
            $itemlib_obj->setPublicationDay($value);
        }
        // lang
        $value = $formdata->getValue('post', 'lang', 's', false);
        if (!is_null($value)) {
            $itemlib_obj->setLanguage($value);
        }
        // related to
        $value = $formdata->getValue('post', 'related_to', 's', false);
        if (!is_null($value)) {
            $itemlib_obj->setRelatedTo($value);
        }
        // related to check (for edit)
        $value = $formdata->getValueArray('post', 'related_to_check', 'i', false);
        $value2 = $formdata->getValueArray('post', 'related_to_check_all', 'i', false);
        $itemlib_obj->setRelatedToCheck($value, $value2);

        return;
    }

    /**
     * insert/update basic information.
     *
     * @param object $itemlib_obj
     *
     * @return bool false if failure
     */
    public function insertBasicInformation($itemlib_obj)
    {
        $item_basic_obj = &$itemlib_obj->getItemBasicObject();
        $is_new = (0 == $item_basic_obj->get('item_id'));
        $certify_required_onupdate = $is_new ? false : $itemlib_obj->isCertifyRequired();
        $itemlib_obj->truncateObject();
        // transaction
        $transaction = &XooNIpsTransaction::getInstance();
        $transaction->start();
        // basic information
        if (!$this->_item_basic_handler->insert($item_basic_obj)) {
            $transaction->rollback();

            return false;
        }
        // get inserted item_id
        $item_id = $item_basic_obj->get('item_id');
        // title
        $item_title_objs = &$itemlib_obj->getTitleObjects();
        if (!$this->_item_title_handler->updateAllObjectsByForeignKey('item_id', $item_id, $item_title_objs)) {
            $transaction->rollback();

            return false;
        }
        // keyword
        $item_keyword_objs = &$itemlib_obj->getKeywordObjects();
        if (!$this->_item_keyword_handler->updateAllObjectsByForeignKey('item_id', $item_id, $item_keyword_objs)) {
            $transaction->rollback();

            return false;
        }
        // related to
        $related_to_ids = array_unique(array_merge($itemlib_obj->getRelatedTo(), $itemlib_obj->getRelatedToCheck()));
        if (!$this->_related_to_handler->insertChildItemIds($item_id, $related_to_ids)) {
            $transaction->rollback();

            return false;
        }

        // update certify state on update
        if ($certify_required_onupdate) {
            $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
            $certify_item = $xconfig_handler->getValue('certify_item');
            $certify_state = 'auto' == $certify_item ? CERTIFIED : CERTIFY_REQUIRED;
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $index_item_link_objs = &$index_item_link_handler->getByItemId($item_id, array(OL_PUBLIC, OL_GROUP_ONLY));
            foreach ($index_item_link_objs as $index_item_link_obj) {
                $index_item_link_obj->set('certify_state', $certify_state);
                $index_item_link_handler->insert($index_item_link_obj);
            }
        }

        // update item status
        $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');
        $item_status_handler->updateItemStatus($item_id);

        // success
        $transaction->commit();

        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if ($is_new) {
            // record event log (insert item)
            $eventlog_handler->recordInsertItemEvent($item_id);
            // notification
            xoonips_insert_event_and_send_notification_of_certification($item_id);
        } else {
            // record event log (update item)
            $eventlog_handler->recordUpdateItemEvent($item_id);
            // notification
            xoonips_insert_event_and_send_notification_of_certification($item_id);
        }

        return true;
    }
}
