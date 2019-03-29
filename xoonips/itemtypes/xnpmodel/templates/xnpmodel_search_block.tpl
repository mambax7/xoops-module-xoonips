<{* xnpmodel register block *}>

<table class="outer">
  <tr>
   <th align="left"><input type="checkbox" name="<{$module_name}>" value="on" onclick="showhide('<{$module_name}>_search_block');"/><{$module_display_name}></th>
  </tr>
</table>
<div id="<{$module_name}>_search_block" class="xoonips_search_block">
<table class="outer">
  <!-- Basic Information -->
  <tr>
    <td class="head" width="30%"><{$basic.title.name}></td>
    <td class="<{cycle name="oddeven" values="odd,even"}>"><{$basic.title.value}></td>
  </tr>
  <tr>
    <td class="head"><{$basic.keywords.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.keywords.value}></td>
  </tr>
  <tr>
    <td class="head"><{$basic.description.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.description.value}></td>
  </tr>
  <tr>
    <td class="head"><{$basic.doi.name}></td>
    <td class="<{cycle name="oddeven"}>"><{$basic.doi.value}></td>
  </tr>

  <!-- Model Item Detail Information -->
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_MODEL_TYPE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>">
     <select name="<{$module_name}>_model_type">
      <option value="">Any</option>
      <{html_options options=$model_type_option}>
     </select>
    </td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_CREATOR_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><input type="text" name="<{$module_name}>_creator" value=""  size="50"/></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_CAPTION_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><input type="text" name="<{$module_name}>_caption" value=""  size="50"/></td>
  </tr>
  <tr>
    <td class="head"><{$smarty.const._MD_XNPMODEL_MODEL_FILE_LABEL}></td>
    <td class="<{cycle name="oddeven"}>"><input type="text" name="<{$module_name}>_model_file" value=""  size="50"/></td>
  </tr>
</table>
</div>
