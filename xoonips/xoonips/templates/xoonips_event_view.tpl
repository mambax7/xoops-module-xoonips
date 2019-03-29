<{* page for download event logs *}>
<script type="text/javascript">
<!--//
function xoonips_eventview_pagenavi( xnp_page )
{
  var form = document.getElementById( 'xoonips_eventview_form' );
  form.page.value = xnp_page;
  form.submit();
  return false;
}
//--></script>
<{include file="db:xoonips_breadcrumbs.inc.tpl"}>

<form id="xoonips_eventview_form" name="xoonips_eventview_form" action='' method='get'>
<input type="hidden" name="mode" value="list" />
<{if $is_users}>
  <input type="hidden" name="log_type_id" value="20" />
  <h3><{$smarty.const._MD_XOONIPS_EVENTLOG_VIEW_USER_TITLE}></h3>
<{else}>
  <input type="hidden" name="log_type_id" value="21" />
  <h3><{$smarty.const._MD_XOONIPS_EVENTLOG_VIEW_ITEM_TITLE}></h3>
<{/if}>
<input type="hidden" name="page" value="1" />

<div style="text-align: right;">
  <select name="limit" onchange="submit();">
  <{foreach item=limit from=$navi_limits }>
    <option value="<{$limit}>" <{if $limit==$navi.limit }> selected="selected" <{/if}> ><{$limit}></option>
  <{/foreach}>
  </select>
  <{if $is_users}>users<{else}>items<{/if}> per page.
  <br>
  <span style="font-weight: bold;">
  <{$navi.start}> - <{$navi.end}> of <{$navi.total}> <{if $is_users}>Users<{else}>Items<{/if}>
  </span>
</div>

<{if $navi.maxpage != 1 }>
<div style="text-align: center; white-space: nowrap;">
    <{if $navi.prev }><a href="#" onclick="return xoonips_eventview_pagenavi(<{$navi.prev}>)">PREV</a><{else}>PREV<{/if}>
    <{foreach item=page from=$navi.navi}>
    &nbsp;<{if $page == $navi.page}><{$page}><{else}><a href="#" onclick="return xoonips_eventview_pagenavi(<{$page}>)"><{$page}></a><{/if}>
  <{/foreach}>
  &nbsp;
    <{if $navi.next }><a href="#" onclick="return xoonips_eventview_pagenavi(<{$navi.next}>)">NEXT</a><{else}>NEXT<{/if}>
</div>
<{/if}>

<table style='margin: 0;' border="0">
<{if $is_users}>
  <tr>
    <th>Name</th>
    <th>Company</th>
    <th>Division</th>
    <th>E-Mail</th>
  </tr>
  <{foreach item=user from=$users}>
  <tr>
    <td class="<{cycle name="row1" values="odd,even"}>"><{$user.uname}></td>
    <td class="<{cycle name="row2" values="odd,even"}>"><{$user.company_name}></td>
    <td class="<{cycle name="row3" values="odd,even"}>"><{$user.division}></td>
    <td class="<{cycle name="row4" values="odd,even"}>"><{$user.email}></td>
  </tr>
  <{/foreach}>
<{else}>
  <tr>
    <th>Item ID</th>
    <th style='width: 70%; margin: auto;'>Title</th>
    <th>Item Type</th>
    <th>Contributer</th>
  </tr>
  <{if empty( $items ) }>
  <tr>
    <td colspan="4" class="odd" style="font-weight: bold; color: red;">No Items Found.</td>
  </hr>
  <{else}>
  <{foreach item=item from=$items}>
  <tr>
    <td class="<{cycle name="row1" values="odd,even"}>"><{$item.item_id}></td>
    <td class="<{cycle name="row2" values="odd,even"}>"><{$item.title|nl2br}></td>
    <td class="<{cycle name="row3" values="odd,even"}>"><{$item.display_name}></td>
    <td class="<{cycle name="row4" values="odd,even"}>"><{$item.uname}></td>
  </tr>
  <{/foreach}>
  <{/if}>
<{/if}>
</table>

<{if $navi.maxpage != 1 }>
<div style="text-align: center; white-space: nowrap;">
    <{if $navi.prev }><a href="#" onclick="return xoonips_eventview_pagenavi(<{$navi.prev}>)">PREV</a><{else}>PREV<{/if}>
    <{foreach item=page from=$navi.navi}>
    &nbsp;<{if $page == $navi.page}><{$page}><{else}><a href="#" onclick="return xoonips_eventview_pagenavi(<{$page}>)"><{$page}></a><{/if}>
  <{/foreach}>
  &nbsp;
    <{if $navi.next }><a href="#" onclick="return xoonips_eventview_pagenavi(<{$navi.next}>)">NEXT</a><{else}>NEXT<{/if}>
</div>
<{/if}>

</form>
