<h3><{$smarty.const._MD_XOONIPS_ITEM_ADVANCED_SEARCH_TITLE}></h3>
<form action="itemselect_transfer.php" method="post"<{$accept_charset}>>

<{include file='db:xoonips_advanced_search_inc.tpl'
 search_blocks=$search_blocks
 jumto_url=$jumto_url
 search_var=$search_var
 back_form='xoonips_advanced_search_back'}>
<input type='hidden' name='op' value='select_item_advancedsearch' />
<input type='hidden' name='submit_url' value='<{$submit_url}>' />
<{include file="db:xoonips_extra_param.tpl" extra_param=$extra_param}>
<{foreach item=id from=$selected_original}>
<input type="hidden" name="selected_original[]" value="<{$id}>" />
<{/foreach}>

</form>
<form action='<{$submit_url}>' method='post' id='xoonips_advanced_search_back'>
<input type='hidden' name='op' value='select_item_advancedsearch' />
<{foreach item=id from=$selected_original}>
<input type="hidden" name="selected_original[]" value="<{$id}>" />
<{/foreach}>
<{include file="db:xoonips_extra_param.tpl" extra_param=$extra_param}>
</form>
