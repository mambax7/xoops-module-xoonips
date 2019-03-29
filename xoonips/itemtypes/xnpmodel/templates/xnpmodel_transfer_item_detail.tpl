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

  <!-- Model Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_MODEL_TYPE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.model_type}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_CREATOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" >
<{include file='db:xoonips_confirm_multiple_field.tpl' vars=$xoonips_item.xnpmodel_creator}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_PREVIEW_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.previews )}>
<{include file="db:xoonips_transfer_detail_previews.tpl" previews=$xoonips_item.detail.previews}>
<{/if}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_MODEL_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.model_data )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.model_data}>
<{/if}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XOONIPS_ITEM_README_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.readme}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XOONIPS_ITEM_RIGHTS_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.rights}></td>
  </tr>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
