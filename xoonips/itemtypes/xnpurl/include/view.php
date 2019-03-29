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
 * retrieve Detail Information that specified by item_id.
 *
 * @param $item_id
 *
 * @return array|bool
 */
function xnpurlGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array(
            'url' => '',
            'url_count' => '',
        );
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnpurl_item_detail')." where url_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo " $sql ".$GLOBALS['xoopsDB']->error();

        return false;
    }

    return $xoopsDB->fetchArray($result);
}

/**
 * @param $item_id
 *
 * @return array|bool
 */
function xnpurlGetMetaInformation($item_id)
{
    $metainfo = xnpurlGetDetailInformation($item_id);
    if ($metainfo == false) {
        return array();
    }

    return $metainfo;
}

/**
 * @param $item_basic
 *
 * @return mixed|string|void
 */
function xnpurlGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnpurlHandler = xoonips_getOrmCompoHandler('xnpurl', 'item');
    $tpl->assign('xoonips_item', $xnpurlHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return HTML content
    return $tpl->fetch('db:xnpurl_list_block.tpl');
}

/**
 * @param $item_basic
 *
 * @return mixed|string|void
 */
function xnpurlGetPrinterFriendlyListBlock($item_basic)
{
    return xnpurlGetListBlock($item_basic);
}

/**
 * @param $item_id
 * @param $url
 *
 * @return array
 */
function xnpurlGetUrlBannerFileDetailBlock($item_id, $url)
{
    // retrieve file information that specified by item_id
    $files = xnpGetFileInfo('t_file.file_id, t_file.caption', 't_file_type.name=\'url_banner_file\' and sess_id is NULL ', $item_id);
    // generate html
    if (count($files) != 0) {
        reset($files);
        list($dummy, list($fileID, $caption)) = each($files);
        $imageFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID";
        $html = '<a href="'.$url.'"><img src="'.$imageFileName.'" alt="'.$url.'"/></a>';
        $hidden = xnpCreateHidden('url_banner_fileFileID', $fileID);
    } else {
        $html = '';
        $hidden = '';
    }

    return array(
        'name' => 'Banner',
        'value' => $html,
        'hidden' => $hidden,
    );
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpurlGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('url_banner_file', xnpGetAttachmentDetailBlock($item_id, 'url_banner_file'));

    $xnpurlHandler = xoonips_getOrmCompoHandler('xnpurl', 'item');
    $tpl->assign('xoonips_item', $xnpurlHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpurl_detail_block.tpl');
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpurlGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('url_banner_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'url_banner_file'));

    $xnpurlHandler = xoonips_getOrmCompoHandler('xnpurl', 'item');
    $tpl->assign('xoonips_item', $xnpurlHandler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpurl_detail_block.tpl');
}

/**
 * @return mixed|string|void
 */
function xnpurlGetRegisterBlock()
{
    $textutil = xoonips_getUtility('text');
    $formdata = xoonips_getUtility('formdata');
    // get DetailInformation
    if ($formdata->getValue('get', 'post_id', 's', false)) {
        $detail = array(
            'url' => $textutil->html_special_chars($formdata->getValue('post', 'url', 's', true)),
        );
    } else {
        $detail = array(
            'url' => '',
        );
    }

    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $preview = xnpGetPreviewRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $url_banner_file = xnpGetAttachmentRegisterBlock('url_banner_file');

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('url_banner_file', $url_banner_file);
    $tpl->assign('detail', $detail);
    // return HTML content
    return $tpl->fetch('db:xnpurl_register_block.tpl');
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpurlGetEditBlock($item_id)
{
    global $xoopsDB;
    $formdata = xoonips_getUtility('formdata');

    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $preview = xnpGetPreviewEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $url_banner_file = xnpGetAttachmentEditBlock($item_id, 'url_banner_file');

    // retrieve detail information
    $url = $formdata->getValue('post', 'url', 's', false);
    if (isset($url)) {
        $detail = array(
            'url' => $url,
        );
    } elseif (!empty($item_id)) {
        $detail = xnpurlGetDetailInformation($item_id);
    } else {
        $detail = array();
    }

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('url_banner_file', $url_banner_file);
    $tpl->assign('detail', $detail);

    // return HTML content
    return $tpl->fetch('db:xnpurl_register_block.tpl');
}

// see also xnpGetAttachmentConfirmBlock
/**
 * @param $item_id
 *
 * @return array
 */
function xnpurlGetUrlBannerFileConfirmBlock($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    $name = 'url_banner_file';
    $url_banner_file = $formdata->getFile($name, false);
    if (!empty($url_banner_file['name'])) {
        // file has been Uploaded
        list($fileID, $errorMessage) = xnpUploadFile($name, false);
        if ($fileID == false) {
            $errorHTML = '<font color=\'#ff0000\'>'.htmlspecialchars($errorMessage).'</font><br />';

            return array(
                'name' => 'Attachment',
                'value' => $errorHTML,
            );
        } else {
            $sql = "t_file.file_id = $fileID";
        }
    } else {
        $attachmentFileID = $formdata->getValue('post', $name.'FileID', 'i', false);
        if ($attachmentFileID == 0) {
            // no files should be attached
            $sql = ' 0 ';
        } else {
            $sql = "t_file.file_id = $attachmentFileID";
        }
    }

    $files = xnpGetFileInfo('t_file.file_id, t_file.original_file_name, t_file.file_size', "t_file_type.name='$name' and $sql ", $item_id);

    if (count($files) == 0) {
        $html = "<input type='hidden' name='${name}FileID' value=''>";
    } else {
        // todo: to be downloadable
        list(list($fileID, $fileName, $fileSize)) = $files;
        $imageFileName = XOOPS_URL."/modules/xoonips/image.php?file_id=$fileID";
        $html = "<input type='hidden' name='${name}FileID' value='$fileID'><img src='$imageFileName'>";
    }

    // generate html
    return array(
        'name' => 'Attachment',
        'value' => $html,
    );
}

/**
 * @param $item_id
 *
 * @return mixed|string|void
 */
function xnpurlGetConfirmBlock($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $preview = xnpGetPreviewConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $url_banner_file = xnpurlGetUrlBannerFileConfirmBlock($item_id, 'url_banner_file');
    // retrieve detail information
    $url = $formdata->getValue('post', 'url', 's', false);
    if (isset($url)) {
        $detail = array(
            'url' => array(
                'value' => $url,
            ),
        );
        xnpConfirmHtml($detail, 'xnpurl_item_detail', array_keys($detail), _CHARSET);
    } else {
        $detail = array();
    }

    // trim strings
    if (xnpHasWithout($basic) || xnpHasWithout($preview) || xnpHasWithout($detail)) {
        global $system_message;
        $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('url_banner_file', $url_banner_file);
    $tpl->assign('detail', $detail);
    // return HTML content
    return $tpl->fetch('db:xnpurl_confirm_block.tpl');
}

/** make sure that enterd detail information is correctly or not.
 * called from register confirmation and edit confirmation.
 *
 * @param $message
 *
 * @return bool
 */
function xnpurlCheckRegisterParameters($message)
{
    $formdata = xoonips_getUtility('formdata');
    $messages = array();
    $url = $formdata->getValue('post', 'url', 's', false);
    if (empty($url)) {
        $messages[] = 'url required.';
    }
    if (count($messages) == 0) {
        return true;
    }
    $message = "<br />\n".implode("<br />\n", $messages);

    return false;
}

/**
 * make sure that enterd detail information is correctly or not.
 *
 * @param $message
 *
 * @return bool
 */
function xnpurlCheckEditParameters($message)
{
    return xnpurlCheckRegisterParameters($message);
}

/**
 * @param $item_id
 *
 * @return bool
 */
function xnpurlInsertItem($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    global $xoopsDB;
    $xnpsid = $_SESSION['XNPSID'];

    // retister BasicInformation, Index and Attachment
    $item_id = 0;
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'url_banner_file');
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

    // limit num of chracters
    $ar = array(
        'url' => preg_replace('/javascript:/i', '', preg_replace('/[\\x00-\\x20\\x22\\x27]/', '', $formdata->getValue('post', 'url', 's', false))),
    );
    xnpTrimColumn($ar, 'xnpurl_item_detail', array_keys($ar), _CHARSET);

    // register detail information
    $sql = 'insert into '.$xoopsDB->prefix('xnpurl_item_detail')." ( url_id, url ) values ( $item_id, '".addslashes($ar['url']).'\' ) ';
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
function xnpurlUpdateItem($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    global $xoopsDB;
    $xnpsid = $_SESSION['XNPSID'];

    // modify BasicInformation, Index, Preview and Attachment.
    $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'url_banner_file');
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

    // trim strings
    $ar = array(
        'url' => preg_replace('/javascript:/i', '',
                              preg_replace('/[\\x00-\\x20\\x22\\x27]/', '', $formdata->getValue('post', 'url', 's', false, ''))),
    );
    xnpTrimColumn($ar, 'xnpurl_item_detail', array_keys($ar), _CHARSET);

    // register detail information
    $sql = implode(',', array('url'.'=\''.addslashes($ar['url']).'\''));
    $result = $xoopsDB->queryF('update '.$xoopsDB->prefix('xnpurl_item_detail')." set $sql where url_id = $item_id ");
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
function xnpurlGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $join = '';

    return true;
}

/**
 * @param $where
 * @param $join
 */
function xnpurlGetAdvancedSearchQuery(&$where, &$join)
{
    $formdata = xoonips_getUtility('formdata');
    global $xoopsDB;
    $url_table = $xoopsDB->prefix('xnpurl_item_detail');

    $wheres = array();
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpurl');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($url_table.'.url', 'xnpurl_url');
    if ($w) {
        $wheres[] = $w;
    }
    $where = implode(' AND ', $wheres);
    $join = '';
}

/**
 * @param $search_var
 *
 * @return mixed|string|void
 */
function xnpurlGetAdvancedSearchBlock($search_var)
{
    // retrieve blocks of BasicInformation / Preview / IndexKeywords
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpurl', $search_var);
    $search_var[] = 'xnpurl_url';
    $search_var[] = 'xnpurl_url_banner_file';

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpurl');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return HTML content
    return $tpl->fetch('db:xnpurl_search_block.tpl');
}

/**
 * @param $iids
 *
 * @return float|sum
 */
function xnpurlGetDetailInformationTotalSize($iids)
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
function xnpurlExportItem($export_path, $fhdl, $item_id, $attachment)
{
    global $xoopsDB;

    if (!$fhdl) {
        return false;
    }

    // retrieve detail information
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpurl_item_detail')." where url_id=$item_id");
    if (!$result) {
        return false;
    }
    $detail = $xoopsDB->fetchArray($result);
    if (!fwrite($fhdl, "<detail id=\"${item_id}\">\n".'<url>'.htmlspecialchars($detail['url'], ENT_QUOTES)."</url>\n")) {
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
function xnpurlGetModifiedFields($item_id)
{
    $formdata = xoonips_getUtility('formdata');
    $ret = array();
    $detail = xnpurlGetDetailInformation($item_id);
    if ($detail) {
        foreach (array('url' => _MD_XNPURL_URL_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }
        // is modified banner files ?
        if (xnpIsAttachmentModified('url_banner_file', $item_id)) {
            array_push($ret, _MD_XNPURL_URL_BANNER_FILE_LABEL);
        }
    }

    return $ret;
}

/**
 * @param $itemtype
 *
 * @return mixed|string|void
 */
function xnpurlGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_url.gif', _MD_XNPURL_EXPLANATION, false, false);
}

/**
 * @param $metadataPrefix
 * @param $item_id
 *
 * @return bool
 */
function xnpurlSupportMetadataFormat($metadataPrefix, $item_id)
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
function xnpurlGetMetadata($prefix, $item_id)
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
    $files = $fileHandler->getFilesInfo($item_id, 'url_banner_file');
    $detail['banner_url'] = !empty($files) ? $files[0]['image_url'] : '';
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
    $tpl->assign('related_tos', $related_tos);
    $tpl->assign('repository', $repository);
    $xml = $tpl->fetch('db:'.$mydirname.'_oaipmh_'.$prefix.'.xml');

    return $xml;
}
