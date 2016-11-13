<{* $Revision: 1.1.2.2 $ *}>
<table class="outer">
  <!-- Basic Information -->
<{include file="db:xoonips_transfer_detail_doi.tpl"}>
<{include file="db:xoonips_transfer_detail_titles.tpl"}>
<{include file="db:xoonips_transfer_detail_keywords.tpl"}>
<{include file="db:xoonips_transfer_detail_description.tpl"}>
<{include file="db:xoonips_transfer_detail_update_date.tpl"}>
<{include file="db:xoonips_transfer_detail_creation_date.tpl"}>
<{include file="db:xoonips_transfer_detail_contributor.tpl"}>
<{include file="db:xoonips_transfer_detail_item_type.tpl"}>
<{include file="db:xoonips_transfer_detail_changelogs.tpl"}>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
</table>

<!-- Binder Item Detail Information -->
<div class="xoonips_page_title">
<{$smarty.const._MD_XNPBINDER_REGISTERED_ITEMS_TITLE}>
</div>
<table>
<{foreach item=item from=$xoonips_item.detail.child_items}>
<tr>
  <td class="<{cycle name="oddeven"}>">
<{include file=$item.filename xoonips_item=$item.var}>
  </td>
</tr>
<{/foreach}>

</table>
