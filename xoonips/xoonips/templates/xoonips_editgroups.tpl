<{include file="db:xoonips_breadcrumbs.inc.tpl"}>
<script type="text/javascript">
<!--//
function xoonips_group_edit_check() {
  var form = document.getElementById( 'xoonips_group_edit_form' );
  if ( form.gname.value == '' ) {
    window.alert( '<{$smarty.const._MD_XOONIPS_MSG_GROUP_ENTER_NAME}>' );
    form.gname.style.backgroundColor = '#FFFFCC';
    form.gname.focus();
    return false;
  }
  return true;
}
function xoonips_group_delete_confirm( gid ){
  if ( ! confirm( '<{$smarty.const._MD_XOONIPS_MSG_GROUP_DELETE_CONFIRM}>' ) ) {
    return false;
  }
  var form = document.getElementById( 'xoonips_group_delete_form' );
  form.gid.value=gid;
  return form.submit();
}
//--></script>

<h3><{$smarty.const._MD_XOONIPS_TITLE_GROUP_EDIT}></h3>
<{if $groups_show}>
<table class="outer" cellspacing="1" cellpadding="4">
  <tr>
    <th><{$smarty.const._MD_XOONIPS_LABEL_GROUP_NAME}></th>
    <th><{$smarty.const._MD_XOONIPS_LABEL_GROUP_DESCRIPTION}></th>
    <th><{$smarty.const._MD_XOONIPS_LABEL_GROUP_ADMINISTRATORS}></th>
    <th style="text-align: center;"><{$smarty.const._MD_XOONIPS_LABEL_ACTION}></th>
  </tr>
<{if empty( $groups ) }>
  <tr>
    <td class="odd" colspan="4"><{$smarty.const._MD_XOONIPS_MSG_GROUP_EMPTY}></td>
  </tr>
<{else}>
<{foreach from=$groups item=group}>
  <tr>
    <td class="head"><{$group.gname}></td>
    <td class="<{cycle name="row1" values="even,odd"}>">
      <{$group.gdesc}>
      <{if $group.locked}>
        <br><span style="color: red;"><{$msg_locked}></span>
      <{/if}>
    </td>
    <td class="<{cycle name="row2" values="even,odd"}>">
      <{foreach name=gadmin from=$group.gadmins item=gadmin}>
        <{if ! $smarty.foreach.gadmin.first}> / <{/if}>
        <a href="showusers.php?uid=<{$gadmin.uid}>"><{$gadmin.uname}></a>
      <{/foreach}>
    </td>
    <td class="<{cycle name="row3" values="even,odd"}>" style="text-align:center;">
      <{if $group.locked}>
        <{if $group.is_admin}>
        &nbsp;
        <a href="groupadmin.php?op=edit&amp;gid=<{$group.gid}>"><img src="images/icon_userlist.png" title="<{$smarty.const._MD_XOONIPS_LABEL_GROUP_MEMBERS}>" alt="<{$smarty.const._MD_XOONIPS_LABEL_GROUP_MEMBERS}>"/></a>
        <{/if}>
      <{else}>
        <a href="editgroups.php?op=edit&amp;gid=<{$group.gid}>"><img src="images/icon_modify.png" title="<{$smarty.const._MD_XOONIPS_LABEL_MODIFY}>" alt="<{$smarty.const._MD_XOONIPS_LABEL_MODIFY}>"/></a>
        <{if $group.is_admin}>
        &nbsp;
        <a href="groupadmin.php?op=edit&amp;gid=<{$group.gid}>"><img src="images/icon_userlist.png" title="<{$smarty.const._MD_XOONIPS_LABEL_GROUP_MEMBERS}>" alt="<{$smarty.const._MD_XOONIPS_LABEL_GROUP_MEMBERS}>"/></a>
        <{/if}>
        &nbsp;
      <a href="javascript:void(0);" onclick="return xoonips_group_delete_confirm( <{$group.gid}> )"><img src="images/icon_delete.png"
                                                                                                         title="<{$smarty.const._MD_XOONIPS_LABEL_DELETE}>"
                                                                                                         alt="<{$smarty.const._MD_XOONIPS_LABEL_DELETE}>"/></a>
      <{/if}>
    </td>
  </tr>
<{/foreach}>
<{/if}>
</table>
<br><br>
<{/if}>

<form id="xoonips_group_edit_form" action="editgroups.php" onsubmit="return xoonips_group_edit_check();" method="post">
<table class="outer" cellspacing="1" cellpadding="4">
 <tr>
  <th colspan="2">
<{if $gid != 0 }>
<{$smarty.const._MD_XOONIPS_TITLE_GROUP_EDIT}>
<input type="hidden" name="op" value="update"/>
<input type="hidden" name="gid" value="<{$gid}>"/>
<{else}>
<{$smarty.const._MD_XOONIPS_TITLE_GROUP_CREATE}>
<input type="hidden" name="op" value="register"/>
<{/if}>
  </th>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_GROUP_NAME}></td>
  <td class="<{cycle name="oddeven" values="odd,even"}>">
  <{if $gname_exists}>
  <span style="color: red;"><{$smarty.const._MD_XOONIPS_MSG_GROUP_NAME_EXISTS}></span><br>
<{/if}>
<{if $gname_forbidden}>
  <span style="color: red;"><{$smarty.const._MD_XOONIPS_MSG_GROUP_NAME_FORBIDDEN}></span><br>
<{/if}>
  <input type="text" name="gname" size="50" value="<{$gname}>"<{if $gname_exists || $gname_forbidden}> style="background-color: #FFFFCC;"<{/if}>/>
 </td>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_GROUP_DESCRIPTION}></td>
  <td class="<{cycle name="oddeven"}>"><textarea name="gdesc" rows="5" cols="50" style="width:400px;"><{$gdesc}></textarea></td>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_GROUP_ADMINISTRATORS}><br></td>
  <td class="<{cycle name="oddeven"}>">
   <{if $gadmins_empty}><span style="color: red;"><{$smarty.const._MD_XOONIPS_MSG_GROUP_ENTER_ADMINS}><br></span><{/if}>
   <select name="gadmins[]" size="10" multiple="multiple"<{if $gadmins_empty}> style="background-color: #FFFFCC;"<{/if}>>
     <{foreach from=$gadmins item=user}>
     <option value="<{$user.uid}>"<{if $user.isadmin eq 1}> selected="selected"<{/if}>><{$user.uname}></option>
     <{/foreach}>
   </select>
  </td>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_ITEM_NUMBER_LIMIT}></td>
  <td class="<{cycle name="oddeven"}>"><input type="text" name="item_number_limit" value="<{$item_number_limit}>"/></td>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_INDEX_NUMBER_LIMIT}></td>
  <td class="<{cycle name="oddeven"}>"><input type="text" name="index_number_limit" value="<{$index_number_limit}>"/></td>
 </tr>
 <tr>
  <td class="head"><{$smarty.const._MD_XOONIPS_LABEL_ITEM_STORAGE_LIMIT}></td>
  <td class="<{cycle name="oddeven"}>"><input type="text" name="item_storage_limit" value="<{$item_storage_limit}>"/></td>
 </tr>
 <tr>
  <td class="head" colspan="2" style="text-align: center;">
   <input class="formButton" type="submit" name="submit" value="<{$smarty.const._MD_XOONIPS_LABEL_SUBMIT}>"/>
   <{$token_ticket}>
  </td>
 </tr>
</table>
</form>
<br><br>

<form id="xoonips_group_delete_form" action="editgroups.php" method="post">
<input type="hidden" name="gid" value="0"/>
<input type="hidden" name="op" value="delete"/>
<{$token_ticket}>
</form>
