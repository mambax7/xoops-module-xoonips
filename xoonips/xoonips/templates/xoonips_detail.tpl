<h3><{$smarty.const._MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE}></h3>

<{*
template for display item's detail information


$item_id: id of item
$body: HTML of detail page
$delete_button_visible 1:display delete button
$modify_button_visible 1:display modify buttom
$print_button_visible  1:display print buttom
$export_enabled it is defined when item in this page can export.
$locked_message show lock type in message
*}>

<script type="text/javascript">
//<![CDATA[
function delete_confirm(){
    if(confirm("<{$smarty.const._MD_XOONIPS_ITEM_DELETE_CONFIRMATION_MESSAGE}>"))
        document.delete_item.submit();
}
function xoonips_certify_confirm(xids, item_id, op){
    var message;
    if ( op == 'accept_certify' )
        message = "<{$smarty.const._MD_XOONIPS_ITEM_ACCEPT_CERTIFY_CONFIRMATION_MESSAGE}>";
    else if ( op == 'reject_certify' )
        message = "<{$smarty.const._MD_XOONIPS_ITEM_CANCEL_CERTIFY_CONFIRMATION_MESSAGE}>";
    else if ( op == 'withdraw' )
        message = "<{$smarty.const._MD_XOONIPS_ITEM_WITHDRAW_CONFIRMATION_MESSAGE}>";
    else
        return;
    var form = document.getElementById('xoonips_certify_form');
    for ( var i = 0; i < xids.length; i++ ){
        var element = document.createElement( 'INPUT' );
        element.type = 'hidden';
        element.name = 'index_ids[]';
        element.value = xids[i];
        form.appendChild(element);
    }
    form.item_id.value=item_id;
    form.op.value=op;
    if(confirm(message))
        form.submit();
}

function add_input( form, name, value ){
  var node = document.createElement('INPUT');
  node.type = 'hidden'; node.name = name; node.value = value;
  form.appendChild(node);
  return node;
}

function submit_edit_form(){
  var form = document.getElementById('xoonips_edit_form');
  var sel = document.getElementById('add_to_index_id_sel');
  if ( form == null || sel == null )
    return;
  
  if ( !form.add_to_index_id ){ /* avoid doubling add_input() */
    form.action = 'confirm_edit.php';
    form.method = 'post';
    var node = add_input( form, 'add_to_index_id', '' );
    add_input( form, 'op', 'add_to_public' );
      add_input(form, 'item_id', < {$item_id} >
  )
      if ( !form.add_to_index_id )
      form.add_to_index_id = node;
  }
  form.add_to_index_id.value = sel.options[sel.selectedIndex].value;
  
  form.submit();
}

function xoonips_download_button_change( type, file_id ) {
  var img = document.getElementById( 'xoonips_download_button_' + file_id );
  var mode = 'none';
  // initilize
  if ( typeof img.attrib_init == 'undefined' ) {
     img.attrib_focus = 0;
     img.attrib_down = 0;
     img.attrib_init = 1;
  }
  switch ( type ) {
  case 'focus':
    if ( img.attrib_down == 0 ) {
      img.attrib_focus = 1;
      mode = 'focus';
    }
    img.attrib_down = 0;
    break;
  case 'blur':
    img.attrib_focus = 0;
    img.attrib_down = 0;
    mode = 'normal';
    break;
  case 'down':
    img.attrib_down = 1;
    img.attrib_focus = 1;
    mode = 'down';
    break;
  case 'out':
    if ( img.attrib_focus == 0 ) {
      mode = 'normal';
    } else {
      mode = 'focus';
    }
    img.attrib_down = 0;
    break;
  case 'over':
    mode = 'over';
    break;
  case 'init':
    img.attrib_down = 0;
    img.attrib_focus = 0;
    mode = 'normal';
    break;
  }
  if ( mode != 'none' ) {
    img.src = 'images/icon_button.php?label=download&' + 'mode=' + mode;
  }
  return false;
}

function xoonips_download_button_click( file_id ) {
  xoonips_download_button_change( 'init', file_id );
  xoonips_download_confirmation( file_id );
  return false;
}

//]]>
</script>

<br>

<{if $modify_button_visible==1}>
<form name='modify' action="edit.php" method="get">
<input type="hidden" name="item_id" value="<{$item_id}>" />
</form>
<{/if}>

<{if $delete_button_visible==1}>
<form name='delete_item' action="detail.php" method="post">
<input type="hidden" name="item_id" value="<{$item_id}>" />
<input type="hidden" name="op" value="delete" />
</form>
<{/if}>

<{if $print_button_visible==1}>
<form name='print' action="detail.php" method="post" target="_blank">
<input type="hidden" name="item_id" value="<{$item_id}>" />
<input type="hidden" name="op" value="print" />
<{if $doi != ''}>
<input type="hidden" name="<{$doi_column_name}>" value="<{$doi}>" />
<{/if}>
</form>
<{/if}>

<form id='xoonips_certify_form' action="detail.php" method="post">
<input type="hidden" name="index_id" />
<input type="hidden" name="item_id" />
<input type="hidden" name="op" value="" />
</form>

<form action="detail.php" name="detail_form" method="post">
<{if $modify_button_visible==1}>
<input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ITEM_MODIFY_BUTTON_LABEL}>" onclick="document.modify.submit();" />
<{/if}>
<{if $delete_button_visible==1}>
<input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ITEM_DELETE_BUTTON_LABEL}>" onclick="delete_confirm()" />
<{/if}>
<{if $print_button_visible==1}>
<input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ITEM_PRINT_FRIENDLY_BUTTON_LABEL}>" onclick="document.print.submit();" />
<{/if}>
<{if $export_enabled==1}>
<input class="formButton" type="button"
value="<{$smarty.const._MD_XOONIPS_EXPORT_BUTTON_LABEL}>"
onclick="form.action='export.php'; op.value='config'; submit();" />
<input type="hidden" name="op" value=""/>
<input type="hidden" name="ids[]" value="<{$item_id}>"/>
<{/if}>
</form>


<br>
<{if $locked_message}>
<span style='color: red;'><{$locked_message}></span><br>
<{/if}>

<div align="right"><{$smarty.const._MD_XOONIPS_ITEM_VIEWED_COUNT_LABEL}>:<{$viewed_count}></div> 
<{$body}>
<{if $modify_button_visible==1}>
 <{$smarty.const._MD_XOONIPS_ADD_TO_PUBLIC_EXPLANATION}>
 <select id="add_to_index_id_sel">
 <{foreach item=to from=$index_tree}>
  <option value="<{$to.item_id}>"><{$to.indent_html}><{$to.select_label}></option>
 <{/foreach}>
 </select>
 <input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ADD_TO_PUBLIC_LABEL}>" onclick="submit_edit_form();" />
<{/if}>

<br><br>


<{foreach key=k item=v from=$http_vars}>
 <input type="hidden" name="<{$k}>" value="<{$v}>" />
<{/foreach}>

 <input type="hidden" name="item_id" value="<{$item_id}>" />

<{$download_confirmation}>

<{* Comment out this line if you wont to use comment function. *}>
<{* <{d3forum_comment dirname=$dir_name forum_id=$forum_id itemname=item_id subject=$xoops_pagetitle}> *}>
  
