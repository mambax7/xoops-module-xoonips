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

  <!-- Url Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPURL_URL_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><a href="<{$xoonips_item.detail.url}>"><{$xoonips_item.detail.url}></a></td>
  </tr>
  <!-- file -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPURL_URL_BANNER_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if !empty($xoonips_item.detail.banner_image_url)}>
      <a target="_blank" href="<{$xoonips_item.detail.url}>">
        <img src="<{$xoonips_item.detail.banner_image_url}>"
             alt="<{$xoonips_item.detail.url}>"/>
      </a>
<{/if}>
    </td>
  </tr>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
