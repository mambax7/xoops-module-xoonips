<?php

// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

$itemtype_path = dirname(__DIR__);
$itemtype_dirname = basename($itemtype_path);
$xoonips_path = dirname($itemtype_path).'/xoonips';

$langman = xoonips_getUtility('languagemanager');
$langman->read('main.php', $itemtype_dirname);

/**
 * get DetailInformation by item_id.
 *
 * @param $item_id
 *
 * @return array|bool
 */
function xnpmemoGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('item_link' => '');
    }
    $sql = 'select * from '.$xoopsDB->prefix('xnpmemo_item_detail')." where memo_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo $xoopsDB->error();

        return false;
    }

    return $xoopsDB->fetchArray($result);
}

/**
 * @param $item_id
 *
 * @return array
 */
function xnpmemoGetMetaInformation($item_id)
{
    $ret = array();
    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnpmemoGetDetailInformation($item_id);

    if (!empty($basic)) {
        $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode("\n", $basic['titles']);
        $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
        $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode("\n", $basic['keywords']);
        $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
        $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
        $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
        $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
    }
    if (!empty($detail)) {
        $ret[_MD_XNPMEMO_ITEM_LINK_LABEL] = $detail['item_link'];
    }

    return $ret;
}

/**
 * @param $item_basic
 *
 * @return mixed|string|void
 */
function xnpmemoGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnpmemoHandler = xoonips_getOrmCompoHandler('xnpmemo', 'item');
    $tpl->assign('xoonips_item', $xnpmemoHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpmemo_list_block.tpl');
}

/**
 * @param $item_basic
 *
 * @return mixed|string|void
 */
function xnpmemoGetPrinterFriendlyListBlock($item_basic)
{
    return xnpmemoGetListBlock($item_basic);
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpmemoGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('memo_file', xnpGetAttachmentDetailBlock($item_id, 'memo_file'));

    $xnpmemoHandler = xoonips_getOrmCompoHandler('xnpmemo', 'item');
    $tpl->assign('xoonips_item', $xnpmemoHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpmemo_detail_block.tpl');
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpmemoGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('memo_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'memo_file'));

    $xnpmemoHandler = xoonips_getOrmCompoHandler('xnpmemo', 'item');
    $tpl->assign('xoonips_item', $xnpmemoHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpmemo_detail_block.tpl');
}

/**
 * @return mixed|string|void
 */
function xnpmemoGetRegisterBlock()
{
    $textutil = xoonips_getUtility('text');
    $formdata = xoonips_getUtility('formdata');
    // get DetailInformation
    if ($formdata->getValue('get', 'post_id', 's', false)) {
        $detail = array(
            'item_link' => $textutil->html_special_chars($formdata->getValue('post', 'item_link', 's', true)),
        );
    } else {
        $detail = array(
            'item_link' => '',
        );
    }

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $memo_file = xnpGetAttachmentRegisterBlock('memo_file');

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('memo_file', $memo_file);
    $tpl->assign('detail', $detail);
    // return as HTML
    return $tpl->fetch('db:xnpmemo_register_block.tpl');
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpmemoGetEditBlock($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $memo_file = xnpGetAttachmentEditBlock($item_id, 'memo_file');

    // get DetailInformation
    $item_link = $formdata->getValue('post', 'item_link', 's', false);
    if (isset($item_link)) {
        $detail = array(
            'item_link' => $item_link,
        );
    } elseif (!empty($item_id)) {
        $detail = xnpmemoGetDetailInformation($item_id);
    } else {
        $detail = array();
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('memo_file', $memo_file);
    $tpl->assign('detail', $detail);

    // return as HTML
    return $tpl->fetch('db:xnpmemo_register_block.tpl');
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpmemoGetConfirmBlock($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $memo_file = xnpGetAttachmentConfirmBlock($item_id, 'memo_file');
    // get DetailInformation
    $item_link = $formdata->getValue('post', 'item_link', 's', false);
    if (isset($item_link)) {
        $detail = array(
            'item_link' => array(
                'value' => $item_link,
            ),
        );
        xnpConfirmHtml($detail, 'xnpmemo_item_detail', array_keys($detail), _CHARSET);
    } else {
        $detail = array();
    }

    if (xnpHasWithout($basic)) {
        global $system_message;
        $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }
    if (isset($item_link)) {
        if (xnpHasWithout($detail)) {
            global $system_message;
            $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
        }
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('memo_file', $memo_file);
    $tpl->assign('detail', $detail);
    // return as HTML
    return $tpl->fetch('db:xnpmemo_confirm_block.tpl');
}

/**
 * check DetailInformation input
 * called from confirm/registered page.
 *
 * @param $message
 *
 * @return bool
 */
function xnpmemoCheckRegisterParameters($message)
{
    $messages = array();
    if (count($messages) == 0) {
        return true;
    }
    $message = "<br />\n".implode("<br />\n", $messages);

    return false;
}

/**
 * check DetailInformation input.
 *
 * @param $message
 *
 * @return bool
 */
function xnpmemoCheckEditParameters($message)
{
    return xnpmemoCheckRegisterParameters($message);
}

/**
 * @param $item_id
 *
 * @return bool
 */
function xnpmemoInsertItem($item_id)
{
    global $xoopsDB;
    $formdata = xoonips_getUtility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

    // register BasicInformation, Index, Attachment
    $item_id = 0;
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'memo_file');
            if ($result) {
            }
        }
        if (!$result) {
            xnpDeleteBasicInformation($xnpsid, $item_id);
        }
    }
    if (!$result) {
        return false;
    }

    $ar = array(
        'item_link' => preg_replace('/javascript:/i', '',
                                    preg_replace('/[\\x00-\\x20\\x22\\x27]/', '', $formdata->getValue('post', 'item_link', 's', false))),
    );
    xnpTrimColumn($ar, 'xnpmemo_item_detail', array_keys($ar), _CHARSET);

    $escval = addslashes($ar['item_link']);

    // register DetailInformation
    $sql = 'insert into '.$xoopsDB->prefix('xnpmemo_item_detail')." ( memo_id, item_link ) values ( $item_id, '$escval' ) ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        echo 'cannot insert item_detail';

        return false;
    }

    return true;
}

/**
 * @param $item_id
 *
 * @return bool
 */
function xnpmemoUpdateItem($item_id)
{
    global $xoopsDB;
    $formdata = xoonips_getUtility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

    // edit BasicInformation, Index, Preview, Attachment
    $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'memo_file');
            if ($result) {
                $result = xnp_insert_change_log($xnpsid, $item_id, $formdata->getValue('post', 'change_log', 's', false));
                $result = !$result;
                if (!$result) {
                    echo ' xnp_insert_change_log failed.';
                }
            } else {
                echo ' xnpUpdateAttachment failed.';
            }
        } else {
            echo ' xnpUpdateIndex failed.';
        }
    } else {
        echo ' xnpUpdateBasicInformation failed.';
    }
    if (!$result) {
        return false;
    }

    $ar = array(
        'item_link' => preg_replace('/javascript:/i', '',
                                    preg_replace('/[\\x00-\\x20\\x22\\x27]/', '', $formdata->getValue('post', 'item_link', 's', false))),
    );
    xnpTrimColumn($ar, 'xnpmemo_item_detail', array_keys($ar), _CHARSET);

    // register DetailInformation
    $sql = implode(',', array('item_link'.'=\''.addslashes($ar['item_link']).'\''));
    $result = $xoopsDB->queryF('update '.$xoopsDB->prefix('xnpmemo_item_detail')." set $sql where memo_id = $item_id ");
    if ($result == false) {
        return false;
    }

    return true;
}

/**
 * @param $wheres
 * @param $join
 * @param $keywords
 *
 * @return bool
 */
function xnpmemoGetDetailInformationQuickSearchQuery($wheres, &$join, $keywords)
{
    $wheres = $join = '';

    return true;
}

/**
 * @param $where
 * @param $join
 */
function xnpmemoGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $memo_table = $xoopsDB->prefix('xnpmemo_item_detail');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $wheres = array();
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpmemo');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($memo_table.'.item_link', 'xnpmemo_item_link');
    if ($w) {
        $wheres[] = $w;
    }

    $where = implode(' and ', $wheres);
    $join = '';
}

/**
 * @param $search_var
 *
 * @return mixed|string|void
 */
function xnpmemoGetAdvancedSearchBlock($search_var)
{
    // get BasicInformation / Preview / IndexKeywords block
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpmemo', $search_var);
    $search_var[] = 'xnpmemo_url';

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpmemo');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return as HTML
    return $tpl->fetch('db:xnpmemo_search_block.tpl');
}

/**
 * @param $iids
 *
 * @return float|sum
 */
function xnpmemoGetDetailInformationTotalSize($iids)
{
    return xnpGetTotalFileSize($iids);
}

/**
 * create XML for exporting detail information
 * see xnpExportItem for detail.
 *
 * @see      xnpExportItem
 *
 * @param folder $export_path
 * @param file   $fhdl
 * @param item   $item_id
 * @param true   $attachment
 *
 * @return true : success
 *
 * @internal param folder $export_path that export file is written to
 * @internal param file $fhdl handle that items are exported to
 * @internal param item $item_id id that is exported
 * @internal param true $attachment if attachment files are exported, else false
 */
function xnpmemoExportItem($export_path, $fhdl, $item_id, $attachment)
{
    global $xoopsDB;

    if (!$fhdl) {
        return false;
    }

    // get DetailInformation
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpmemo_item_detail')." where memo_id=$item_id");
    if (!$result) {
        return false;
    }
    $detail = $xoopsDB->fetchArray($result);
    if (!fwrite($fhdl, "<detail id=\"${item_id}\">\n".'<item_link>'.htmlspecialchars($detail['item_link'], ENT_QUOTES)."</item_link>\n")) {
        return false;
    }
    if (!($attachment ? xnpExportFile($export_path, $fhdl, $item_id) : true)) {
        return false;
    }
    if (!fwrite($fhdl, "</detail>\n")) {
        return false;
    }

    return true;
}

/**
 * @param $item_id
 *
 * @return array
 */
function xnpmemoGetModifiedFields($item_id)
{
    $ret = array();
    $formdata = xoonips_getUtility('formdata');
    $detail = xnpmemoGetDetailInformation($item_id);
    if ($detail) {
        foreach (array('item_link' => _MD_XNPMEMO_ITEM_LINK_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }
        // was banner file modified?
        if (xnpIsAttachmentModified('memo_file', $item_id)) {
            array_push($ret, _MD_XNPMEMO_MEMO_FILE_LABEL);
        }
    }

    return $ret;
}

/**
 * @param $itemtype
 *
 * @return mixed|string|void
 */
function xnpmemoGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_memo.gif', _MD_XNPMEMO_EXPLANATION, false, false);
}

/**
 * @param $metadataPrefix
 * @param $item_id
 *
 * @return bool
 */
function xnpmemoSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix === 'oai_dc' || $metadataPrefix === 'junii2') {
        return true;
    }

    return false;
}

/**
 * @param $prefix
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpmemoGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array(
        'oai_dc',
        'junii2',
    ))
    ) {
        return false;
    }

    // detail information
    $detailHandler = xoonips_getOrmHandler($mydirname, 'item_detail');
    $detail_obj = $detailHandler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
    // basic information
    $basic = xnpGetBasicInformationArray($item_id);
    $basic['publication_date_iso8601'] = xnpISO8601($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    // indexes
    $indexes = array();
    if (xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $xids) == RES_OK) {
        foreach ($xids as $xid) {
            if (xnp_get_index($_SESSION['XNPSID'], $xid, $index) == RES_OK) {
                $indexes[] = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
            }
        }
    }
    // files
    $files = array();
    $mimetypes = array();
    $fileHandler = xoonips_gethandler('xoonips', 'file');
    $files = $fileHandler->getFilesInfo($item_id, 'memo_file');
    foreach ($files as $file) {
        if (!in_array($file['mime_type'], $mimetypes)) {
            $mimetypes[] = $file['mime_type'];
        }
    }
    // related to
    $related_toHandler = xoonips_getOrmHandler('xoonips', 'related_to');
    $related_to_ids = $related_toHandler->getChildItemIds($item_id);
    $related_tos = array();
    foreach ($related_to_ids as $related_to_id) {
        $related_tos[] = array(
            'item_id' => $related_to_id,
            'item_url' => XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$related_to_id,
        );
    }
    // repository configs
    $xconfigHandler = xoonips_getOrmHandler('xoonips', 'config');
    $myxoopsConfigMetaFooter = xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);
    $repository = array(
        'download_file_compression' => $xconfigHandler->getValue('download_file_compression'),
        'nijc_code' => $xconfigHandler->getValue('repository_nijc_code'),
        'publisher' => $xconfigHandler->getValue('repository_publisher'),
        'institution' => $xconfigHandler->getValue('repository_institution'),
        'meta_author' => $myxoopsConfigMetaFooter['meta_author'],
    );
    // assign template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->plugins_dir[] = XOONIPS_PATH.'/class/smarty/plugins';
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('detail', $detail);
    $tpl->assign('indexes', $indexes);
    $tpl->assign('files', $files);
    $tpl->assign('mimetypes', $mimetypes);
    $tpl->assign('related_tos', $related_tos);
    $tpl->assign('repository', $repository);
    $xml = $tpl->fetch('db:'.$mydirname.'_oaipmh_'.$prefix.'.xml');

    return $xml;
}
