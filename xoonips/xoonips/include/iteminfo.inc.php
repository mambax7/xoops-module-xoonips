<?php

//  ------------------------------------------------------------------------ //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
//  ------------------------------------------------------------------------ //
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
//  ------------------------------------------------------------------------ //

/*
 * common part of iteminfo.php
 */

//
// define orm of compo
$iteminfo['orm'] = [
    ['module' => 'xoonips', 'name' => 'item_basic', 'field' => 'basic', 'foreign_key' => 'item_id', 'multiple' => false],
    ['module' => 'xoonips', 'name' => 'title', 'field' => 'titles', 'foreign_key' => 'item_id', 'multiple' => true, 'required' => true],
    ['module' => 'xoonips', 'name' => 'keyword', 'field' => 'keywords', 'foreign_key' => 'item_id', 'multiple' => true],
    ['module' => 'xoonips', 'name' => 'index_item_link', 'field' => 'indexes', 'foreign_key' => 'item_id', 'multiple' => true],
    ['module' => 'xoonips', 'name' => 'related_to', 'field' => 'related_tos', 'foreign_key' => 'parent_id', 'multiple' => true],
    ['module' => 'xoonips', 'name' => 'changelog', 'field' => 'changelogs', 'foreign_key' => 'item_id', 'multiple' => true],
];

$iteminfo['ormfield']['basic'] = [
    ['name' => 'item_id', 'type' => 'int', 'required' => true, 'multiple' => false],
    ['name' => 'item_type_id', 'type' => 'int', 'required' => true, 'multiple' => false],
    ['name' => 'uid', 'type' => 'int', 'required' => true, 'multiple' => false],
    ['name' => 'title', 'type' => 'string', 'required' => true, 'multiple' => true],
    ['name' => 'keywords', 'type' => 'string', 'required' => false, 'multiple' => true],
    ['name' => 'description', 'type' => 'string', 'required' => false, 'multiple' => false],
    ['name' => 'doi', 'type' => 'string', 'required' => false, 'multiple' => false],
    ['name' => 'last_update_date', 'type' => 'int', 'required' => false, 'multiple' => false],
    ['name' => 'creation_date', 'type' => 'int', 'required' => false, 'multiple' => false],
    ['name' => 'publication_year', 'type' => 'int', 'required' => false, 'multiple' => false],
    ['name' => 'publication_month', 'type' => 'int', 'required' => false, 'multiple' => false],
    ['name' => 'publication_mday', 'type' => 'int', 'required' => false, 'multiple' => false],
    ['name' => 'lang', 'type' => 'string', 'required' => false, 'multiple' => false],
    ['name' => 'index_id', 'type' => 'int', 'required' => true, 'multiple' => true],
    ['name' => 'item_id', 'type' => 'int', 'required' => false, 'multiple' => true],
];

//
// transform orm to array for view
// basic information(don't modifiy)
// $iteminfo['io']['xmlrpc'][<name of rule>] => array( ... );
$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'item_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['item_id'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_ITEM_ID',
        'type' => 'string',
        'multiple' => false,
        'readonly' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'item_type_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['itemtype'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_ITEMTYPE',
        'type' => 'string',
        'multiple' => false,
        'required' => true,
        'readonly' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'uid'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['username'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_USERNAME',
        'type' => 'string',
        'multiple' => false,
        'required' => false,
        'readonly' => true,
    ],
    'eval' => [
        'orm2xmlrpc' => '$user_handler =& xoops_gethandler( "user" ); $user =& $user_handler->get($in_var[0]); if( $user ) $out_var[0] = $user->getVar( "uname", "none" );',
        'xmlrpc2orm' => '$user_handler =& xoops_gethandler( "user" ); $users =& $user_handler->getObjects(new Criteria( "uname", trim($in_var[0]) ) ); if($users) $out_var[0] = $users[0]->getVar( "uid" );',
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'titles', 'field' => 'title'],
            ['orm' => 'titles', 'field' => 'title_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['titles'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_TITLES',
        'type' => 'string',
        'multiple' => true,
        'required' => true,
    ],
    'eval' => [
        'orm2xmlrpc' => '$out_var[0] = $in_var[0];',
        'xmlrpc2orm' => 'if( strlen( trim($in_var[0]) ) > 0 ){ $out_var[0] = trim($in_var[0]); $out_var[1] = $context["position"]; }',
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'keywords', 'field' => 'keyword'],
            ['orm' => 'keywords', 'field' => 'keyword_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['keywords'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_KEYWORDS',
        'type' => 'string',
        'multiple' => true,
    ],
    'eval' => [
        'orm2xmlrpc' => '$out_var[0] = $in_var[0];',
        'xmlrpc2orm' => 'if( strlen( trim($in_var[0]) ) > 0 ){ $out_var[0] = trim($in_var[0]); $out_var[1] = $context["position"]; }',
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'description'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['comment'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_DESCRIPTION',
        'type' => 'string',
        'multiple' => false,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [['orm' => 'basic', 'field' => 'doi']],
    ],
    'xmlrpc' => [
        'field' => ['ext_id'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_EXT_ID',
        'type' => 'string',
        'multiple' => false,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'last_update_date'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['last_modified_date'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LAST_MODIFIED_DATE',
        'type' => 'dateTime.iso8601',
        'readonly' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'creation_date'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['registration_date'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_REGISTRATION_DATE',
        'type' => 'dateTime.iso8601',
        'readonly' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'publication_year'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['creation_year'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_CREATION_YEAR',
        'type' => 'string',
        'multiple' => false,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'publication_month'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['creation_month'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_CREATION_MONTH',
        'type' => 'string',
        'multiple' => false,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'publication_mday'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['creation_mday'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_CREATION_MDAY',
        'type' => 'string',
        'multiple' => false,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'basic', 'field' => 'lang'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['lang'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG',
        'type' => 'string',
        'options' => [
            ['option' => 'jpn', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_JPN'],
            ['option' => 'eng', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_ENG'],
            ['option' => 'fra', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_FRA'],
            ['option' => 'deu', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_DEU'],
            ['option' => 'esl', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_ESL'],
            ['option' => 'ita', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_ITA'],
            ['option' => 'dut', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_DUT'],
            ['option' => 'sve', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_SVE'],
            ['option' => 'nor', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_NOR'],
            ['option' => 'dan', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_DAN'],
            ['option' => 'fin', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_FIN'],
            ['option' => 'por', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_POR'],
            ['option' => 'chi', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_CHI'],
            ['option' => 'kor', 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_LANG_KOR'],
        ],
        'multiple' => false,
        'required' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => ['field' => [['orm' => 'basic', 'field' => 'item_id']]],
    'xmlrpc' => ['field' => ['url'], 'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_URL', 'type' => 'string', 'readonly' => true],
    'eval' => ['orm2xmlrpc' => '$out_var[0] = XOOPS_URL . "/modules/xoonips/detail.php?item_id=" . $in_var[0];', 'xmlrpc2orm' => ';'],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'indexes', 'field' => 'index_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['indexes'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_INDEXES',
        'type' => 'string',
        'multiple' => true,
        'required' => true,
    ],
];

$iteminfo['io']['xmlrpc']['item'][] = [
    'orm' => [
        'field' => [
            ['orm' => 'related_tos', 'field' => 'item_id'],
        ],
    ],
    'xmlrpc' => [
        'field' => ['related_to'],
        'display_name' => '_MD_XOONIPS_XMLRPC_DISPLAY_NAME_RELATED_TO',
        'type' => 'string',
        'multiple' => true,
    ],
    'eval' => [
        'orm2xmlrpc' => '$out_var[0] = $in_var[0];',
        'xmlrpc2orm' => 'if( is_numeric( trim($in_var[0]) ) && ctype_digit( trim($in_var[0]) ) ){ $out_var[0] = trim($in_var[0]); $out_var[1] = $context["position"]; }',
    ],
];

/*
 * create Criteria object for file type
 * @param string $file_type_name
 */
if (!function_exists('iteminfo_file_criteria')) {
    function iteminfo_file_criteria($file_type_name)
    {
        $criteria = new CriteriaCompo(new Criteria('name', $file_type_name));
        $criteria->add(new Criteria('is_deleted', 0));

        return $criteria;
    }
}
