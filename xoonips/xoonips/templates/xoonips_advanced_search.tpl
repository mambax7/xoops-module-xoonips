<{*
  $Revision: 1.5.4.1.2.2 $
*}>

<h3><{$smarty.const._MD_XOONIPS_ITEM_ADVANCED_SEARCH_TITLE}></h3>
<form action="<{$itemselect_url}>" method="post"<{$accept_charset}>>

<{include file='db:xoonips_advanced_search_inc.tpl'
 search_blocks=$search_blocks
 jumto_url=$jumto_url
 search_var=$search_var}>
<input type='hidden' name='op' value='advancedsearch' />

</form>