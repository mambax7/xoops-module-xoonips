<{* $Revision: 1.1.2.4 $ *}>
<table class="outer">
  <!-- Basic Information -->
<{include file="db:xoonips_transfer_detail_doi.tpl"}>
<{include file="db:xoonips_transfer_detail_lang.tpl"}>
<{include file="db:xoonips_transfer_detail_titles.tpl"}>
<{include file="db:xoonips_transfer_detail_keywords.tpl"}>
<{include file="db:xoonips_transfer_detail_description.tpl"}>
<{include file="db:xoonips_transfer_detail_update_date.tpl"}>
<{include file="db:xoonips_transfer_detail_creation_date.tpl"}>
<{include file="db:xoonips_transfer_detail_contributor.tpl"}>
<{include file="db:xoonips_transfer_detail_item_type.tpl"}>
<{include file="db:xoonips_transfer_detail_changelogs.tpl"}>

  <!-- Memo Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMEMO_ITEM_LINK_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{if $xoonips_item.detail.item_link != ""}><a href="<{$xoonips_item.detail.item_link}>" target="_blank"><{$xoonips_item.detail.item_link}></a><{/if}></td>  </tr>
  <!-- file -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMEMO_MEMO_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" >
<{if isset( $xoonips_item.detail.memo_file )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.memo_file}>
<{/if}>
    </td>
  </tr>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
