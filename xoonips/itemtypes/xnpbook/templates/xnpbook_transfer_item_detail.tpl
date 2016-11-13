<table class="outer">
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
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_AUTHOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" >
<{include file='db:xoonips_confirm_multiple_field.tpl' vars=$xoonips_item.xnpbook_author}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_EDITOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{$xoonips_item.detail.editor}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_PUBLISHER_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{$xoonips_item.detail.publisher}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_YEAR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{$xoonips_item.basic.publication_year}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_URL_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{if $xoonips_item.detail.url != ""}><a title="<{$xoonips_item_detail.url}>" href="<{$xoonips_item.detail.url}>" target="_blank"><{$xoonips_item.detail.url|truncate:60}></a><{/if}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_ISBN_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" ><{$xoonips_item.detail.isbn}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPBOOK_PDF_LABEL}></td>
    <td class="<{cycle name="oddeven"}>" >
<{if isset( $xoonips_item.detail.book_pdf )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.book_pdf}>
<{/if}>
    </td>
  </tr>
<!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
<!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
