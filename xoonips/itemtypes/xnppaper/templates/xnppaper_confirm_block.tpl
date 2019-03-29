<{* xnppaper confirm block *}>
<table class="outer">
  <tr>
    <td class="head"><{$basic.doi.name}></td>
    <td class="<{cycle name="oddeven" values="odd,even"}>"><{$basic.doi.value}></td>
  </tr>
  <tr>
    <td width="30%" class="head"><{$basic.lang.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.lang.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PUBMED_ID_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.pubmed_id.value}></td>
  </tr>
  <!-- Basic Information -->
  <tr>
    <td width="30%" class="head"><{$basic.title.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.title.value|nl2br}></td>
  </tr>
  <tr>
    <td class="head"><{$basic.keywords.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.keywords.value}></td>
  </tr>
  <tr>
    <td class="head"><{$basic.description.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.description.value|nl2br}></td>
  </tr>
<{if !empty( $basic.last_update_date.value ) }>
  <tr>
    <td class="head"><{$basic.last_update_date.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.last_update_date.value}></td>
  </tr>
<{/if}>
<{if !empty( $basic.creation_date.value ) }>
  <tr>
    <td class="head"><{$basic.creation_date.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.creation_date.value}></td>
  </tr>
<{/if}>
<{if !empty( $basic.contributor.value ) }>
  <tr>
    <td class="head"><{$basic.contributor.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.contributor.value}></td>
  </tr>
<{/if}>
<{if !empty( $basic.item_type.value ) }>
  <tr>
    <td class="head"><{$basic.item_type.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.item_type.value}></td>
  </tr>
<{/if}>
<{if !empty( $basic.change_log.value ) }>
  <tr>
    <td class="head"><{$basic.change_log.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.change_log.value}></td>
  </tr>
<{/if}>
<{if !empty( $basic.change_logs.value ) }>
  <tr>
    <td class="head"><{$basic.change_logs.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.change_logs.value}></td>
  </tr>
<{/if}>
  
  <!-- Paper Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_AUTHOR_LABEL}></td>
   <td class="<{cycle name="oddeven"}>">
<{include file='db:xoonips_confirm_multiple_field.tpl' vars=$xnppaper_author}>
   </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_JOURNAL_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.journal.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_YEAR_OF_PUBLICATION_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.publication_year.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_VOLUME_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.volume.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_NUMBER_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.number.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PAGE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.page.value}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_ABSTRACT_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$detail.abstract.value|nl2br}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPPAPER_PDF_REPRINT_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$paper_pdf_reprint.value}></td>
  </tr>
  <!-- index -->
  <tr>
    <td class="head"><{$index.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$index.value}></td>
  </tr>
  <!-- related_to -->
  <tr>
    <td class="head"><{$basic.related_to.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.related_to.value}></td>
  </tr>
</table>
<input type="hidden" name="journal" value="<{$detail.journal.html_string}>"/>
<input type="hidden" name="volume" value="<{$detail.volume.value}>"/>
<input type="hidden" name="number" value="<{$detail.number.value}>"/>
<input type="hidden" name="page" value="<{$detail.page.html_string}>"/>
<input type="hidden" name="abstract" value="<{$detail.abstract.html_string}>"/>
<input type="hidden" name="pubmed_id" value="<{$detail.pubmed_id.html_string}>"/>
