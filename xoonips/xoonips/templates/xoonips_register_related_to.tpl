<{* form to input link related to items (a part of register item page) *}>
<{* 

$related_to: default value
 
*}>
<!-- begin of xoonips_edit_related_to.tpl -->
<p>
<{$smarty.const._MD_XOONIPS_ITEM_RELATED_TO_ADD_ID}>
<br>
<textarea cols="20" rows="5" name="related_to" style="width:160px;">
<{if !empty( $related_to )}>
<{$related_to}>
<{/if}>
</textarea>
<input class="formButton" type="button"
    value="<{$smarty.const._MD_XOONIPS_ITEM_RELATED_TO_ADD_ID_BUTTON_LABEL}>"
    onclick="window.open('related_to_subwin.php');"/>
</p>
<!-- end of xoonips_edit_related_to.tpl -->
