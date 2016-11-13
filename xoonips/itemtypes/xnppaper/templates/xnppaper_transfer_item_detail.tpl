<{* xnppaper detail block *}>
<{* $Revision: 1.1.2.4 $ *}>
<table class="outer">
<{include file="db:xoonips_transfer_detail_doi.tpl"}>
<{include file="db:xoonips_transfer_detail_lang.tpl"}>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PUBMED_ID_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.pubmed_link}></td>
  </tr>
<{include file="db:xoonips_transfer_detail_titles.tpl"}>
<{include file="db:xoonips_transfer_detail_keywords.tpl"}>
<{include file="db:xoonips_transfer_detail_description.tpl"}>
<{include file="db:xoonips_transfer_detail_update_date.tpl"}>
<{include file="db:xoonips_transfer_detail_creation_date.tpl"}>
<{include file="db:xoonips_transfer_detail_contributor.tpl"}>
<{include file="db:xoonips_transfer_detail_item_type.tpl"}>
<{include file="db:xoonips_transfer_detail_changelogs.tpl"}>

  <!-- Paper Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_AUTHOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{include file='db:xoonips_confirm_multiple_field.tpl' vars=$xoonips_item.xnppaper_author}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_JOURNAL_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.journal}></td>
  </tr>
<{include file="db:xoonips_transfer_detail_publication_y.tpl" name=$smarty.const._MD_XNPPAPER_YEAR_OF_PUBLICATION_LABEL}>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_VOLUME_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.volume}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_NUMBER_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.number}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PAGE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.page}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_ABSTRACT_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.abstract|nl2br}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PDF_REPRINT_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.paper_pdf_reprint )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.paper_pdf_reprint}>
<{/if}>
    </td>
  </tr>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
</table>
