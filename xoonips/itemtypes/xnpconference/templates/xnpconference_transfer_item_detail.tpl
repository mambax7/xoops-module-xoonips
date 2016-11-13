  <table class="outer">
<{include file="db:xoonips_transfer_detail_doi.tpl"}>
<{include file="db:xoonips_transfer_detail_lang.tpl"}>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_CONFERENCE_TITLE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.conference_title}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_PLACE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.place}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_DATE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.conference_date}></td>
  </tr>
<{include file="db:xoonips_transfer_detail_update_date.tpl"}>
<{include file="db:xoonips_transfer_detail_creation_date.tpl"}>
<{include file="db:xoonips_transfer_detail_contributor.tpl"}>
<{include file="db:xoonips_transfer_detail_item_type.tpl"}>
<{include file="db:xoonips_transfer_detail_changelogs.tpl"}>
<{include file="db:xoonips_transfer_detail_titles.tpl"}>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_AUTHOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{include file='db:xoonips_confirm_multiple_field.tpl' vars=$xoonips_item.xnpconference_author}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_ABSTRACT_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.abstract|nl2br}></td>
  </tr>  

  <!-- file -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_PRESENTATION_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.conference_file )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.conference_file}>
<{/if}>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_PRESENTATION_TYPE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><{$xoonips_item.detail.presentation_type}></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPCONFERENCE_CONFERENCE_PAPER_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
<{if isset( $xoonips_item.detail.conference_paper )}>
<{include file="db:xoonips_transfer_detail_file.tpl" fileinfo=$xoonips_item.detail.conference_paper}>
<{/if}>
    </td>
  </tr>
  <!-- index -->
<{include file="db:xoonips_transfer_detail_indexes.tpl"}>
  <!-- related_to -->
<{include file="db:xoonips_transfer_detail_related_tos.tpl"}>
  </table>
