<{* $Revision: 1.1.2.2 $ *}>
<table class="outer">
  <!-- Basic Information -->
<{include file="db:xoonips_transfer_detail_doi.tpl"}>
<{include file="db:xoonips_transfer_detail_lang.tpl"}>
<{include file="db:xoonips_transfer_detail_titles.tpl"}>
<{include file="db:xoonips_transfer_detail_update_date.tpl"}>
<{include file="db:xoonips_transfer_detail_creation_date.tpl"}>
<{include file="db:xoonips_transfer_detail_contributor.tpl"}>
<{include file="db:xoonips_transfer_detail_item_type.tpl"}>
<{include file="db:xoonips_transfer_detail_changelogs.tpl"}>
  <!-- File Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPFILES_DATA_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.files_file )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.files_file}>
<{/if}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPFILES_DATA_FILE_NAME}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.file_name}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPFILES_DATA_FILE_MIMETYPE}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.mime_type}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPFILES_DATA_FILE_FILETYPE}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.file_type}></td>
  </tr>
<{include file="db:xoonips_transfer_detail_keywords.tpl"}>
<{include file="db:xoonips_transfer_detail_description.tpl"}>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
