<script type="text/javascript" src="js/prototype.js">
</script>
<script type="text/javascript" src="js/xoonips_treelib.js">
</script>
<script type="text/javascript" src="js/xoonips_itemedit.js">
</script>
<script type="text/javascript">
<!--

var xoonips_formchecker = new XooNIpsItemEditJS.formChecker();
<{*
xoonips_formchecker.initStyle( 'error', 'color', 'black' );
xoonips_formchecker.initStyle( 'error', 'background', '#FFFFCC' );
*}>

function getCheckedIndexes( form1 ){
	xoonipsGetDocTreeView2();
	xoonipsSaveTreeState2(xoonipsDocTreeView2);
	form1.xoonipsCheckedXID.value = xoonipsCheckState2;
	return true;
}
function xnpSubmitForm(){
    <{* push submit button -> call onSubmit in form, form.submit(); -> not call onSubmit in form *}>
	var form1 = document.getElementById('xoonips_registerform');
	getCheckedIndexes( form1 );
	form1.submit();
}
function xnpSubmitFileUpload( form, name ){
	form.mode.value = 'Upload';
	eval( 'var fileobj = form.' + name + ';' );
	if ( fileobj.value == null || fileobj.value == "" ){
		window.alert( 'error: no file specified.' );
		return false;
	}
	getCheckedIndexes( form );
	saveScrollPosition();
	form.action = '';
	form.submit();
}
function xnpSubmitFileDelete( form, name, fileID ){
	form.mode.value = 'Delete';
	form.fileID.value = fileID;
	getCheckedIndexes( form );
	saveScrollPosition();
	form.action = '';
	form.submit();
}

// return true if all required parameters are filled
function onSubmitItem( form ) {
  var ret = true;
  var focus_el = null;
  if ( ! xoonips_formchecker.isExtIdInputText( form.doi, '<{$smarty.const.XNP_CONFIG_DOI_FIELD_PARAM_NAME}>', <{$smarty.const.XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN}>, '<{$smarty.const.XNP_CONFIG_DOI_FIELD_PARAM_PATTERN}>' ) ) {
    alert( "<{$invalid_doi_message}>" );
    focus_el = form.doi;
    ret = false;
  }
  if ( ! xoonips_formchecker.isFilledInputText( form.title ) ) {
    alert( "<{$smarty.const._MD_XOONIPS_ITEM_TITLE_REQUIRED}>" );
    if ( focus_el == null ) {
      focus_el = form.title;
    }
    ret = false;
  }
  if ( form.preview != null && form.preview.value != null && form.preview.value != '' ) {
    var msg = "<{$smarty.const._MD_XOONIPS_ITEM_UPLOAD_PREVIEW}>".replace( "/%s/", form.preview.value );
    alert( 'error: preview file "' + form.preview.value + '" will not be uploaded. \npush Upload button first.' );
    ret = false;
  }
  if ( focus_el != null ) {
    focus_el.focus();
  }
  return ret;
}

function xnpOpenTextFileInputWindow( name, itemID ){
	window.open( '<{$smarty.const.XOOPS_URL}>/modules/xoonips/input_text_file.php?name=' + name, 'inputTextFile', 
		"dependent,menubar=no,location=no,personalbar=no,directories=no,toolbar=no,resizable=yes,scrollbars=no,innerHeight=220,innerWidth=480");
	return false;
}

function onSelectChange( sel ){
	var index = sel.selectedIndex;
	if ( 0 <= index && index < sel.length ){
		var item_type_id = sel.options[index].value;
		window.location = "<{$this_url}>?item_type_id=" + item_type_id + "&dummy=<{$xnpsid}>";
	}
}

function saveScrollPosition(){
	var x = 0, y = 0;
	if ( window.pageXOffset || window.pageYOffset ){
		x = window.pageXOffset;
		y = window.pageYOffset;
	}
	else if ( document.documentElement && 
	    ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ){
		x = document.documentElement.scrollLeft;
		y = document.documentElement.scrollTop;
	}
	else if ( document.body ){
		x = document.body.scrollLeft;
		y = document.body.scrollTop;
	}
	else
		return;
	
	//window.alert( "saveScrollPosition:" + x + "/" + y );
	var form1 = document.getElementById('xoonips_registerform');
	form1.scrollX.value = x;
	form1.scrollY.value = y;
}

function restoreScrollPosition(){
    var x =
<
    {
        $scrollX
    }
>
    var y =
<
    {
        $scrollY
    }
>
    //window.alert( "restoreScrollPosition:" + x + "/" + y );
	if ( x || y )
		setTimeout('window.scrollTo('+x+', '+y+')',1);
}

var old = window.onload;
window.onload = function(){ 
	if ( typeof(old) == "function" )
		old();
	
	restoreScrollPosition();
}

//-->
</script>

<div id="PrivateIndexCheckedHandler" style="display: none;"></div>
<script type="text/javascript">
<!--
	// add onclick event handler of checkbox of private index
	var c = window.top.document.getElementById("PrivateIndexCheckedHandler");  
	if( c != null ){
		c.onCheckPrivate = 
function(b){
	var l = window.top.document.getElementById("message_label_top");  
	if( l != null ) l.style.display = b ? 'none' : 'inline';

	var l = window.top.document.getElementById("message_label_bottom");  
	if( l != null ) l.style.display = b ? 'none' : 'inline';
};
	}else{
		alert( 'c is null' );
	}
//-->
</script>

<h3><{$smarty.const._MD_XOONIPS_ITEM_REGISTER_ITEM_TITLE}></h3>

<{*
template for display to edit items


$body:                     HTML of item registeration form
$select_item_type[]:       array of selectable itemtypes(type name=>item_type_id)
$next_url:                 access url to push next button
$item_type_id:             itemtype to registered item
$index_checked_id:         id of selected index (comma separated)
$system_message:           messages or errors for system
$num_of_items_current:     number of items register now (not public)
$num_of_items_max:         maximum number of items that can register.
$storage_of_items_current: total file size of all attachment files in registered item (not public)
$storage_of_items_max:     maximum file size of attchment files in item that can register.
*}>
<form id="xoonips_registerform" name="xoonips_registerform" action="<{$next_url}>" method="post" onsubmit="return onSubmitItemType(this) &amp;&amp; onSubmitItem(this) &amp;&amp; getCheckedIndexes(this)" enctype="multipart/form-data"<{$accept_charset}>>

<div style="text-align: center;">
<input type='hidden' name='scrollX' value='0'/>
<input type='hidden' name='scrollY' value='0'/>
<input type='hidden' name='mode' value='' />
<input type='hidden' name='fileID' value='' />
<{if !empty( $select_item_type )}>
<{$smarty.const._MD_XOONIPS_ITEM_SELECT_ITEM_TYPE_LABEL}>
<select name="item_type_id" onchange="onSelectChange(this);">
<{foreach key=name item=id from=$select_item_type}>
 <option value="<{$id}>"<{if $id==$item_type_id}> selected="selected"<{/if}>><{$name}></option>
<{/foreach}>
</select>
<{/if}>
</div>

<{if !empty( $body ) }>
<div style="text-align: center; margin: 10px;">
 <span id='message_label_top' style='display: inline; color: red;'>
  <{$smarty.const._MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX}>
 </span>
</div>
<{/if}>

<{if !empty( $system_message )}>
<p>
<{$system_message}>
</p>
<{/if}>

<{if isset( $num_of_items_current ) && isset( $num_of_items_max ) }>
<{$smarty.const._MD_XOONIPS_ITEM_NUM_OF_ITEM}>: <{$num_of_items_current}> / <{$num_of_items_max}><br>
<{/if}>
<{if isset( $storage_of_items_current ) && isset( $storage_of_items_max ) }>
<{$smarty.const._MD_XOONIPS_ITEM_STORAGE_OF_ITEM}>: <{$storage_of_items_current}>MB / <{$storage_of_items_max}>MB<br>
<{/if}>
<{if !empty( $body ) }>
<{$body}>
<{/if}>

 <input type='hidden' name='xoonipsCheckedXID' value='<{$index_checked_id}>' />

<{if !empty( $body ) }>
<div style="text-align: center; margin: 10px;">
 <span id='message_label_bottom' style='display: inline; color: red;'>
  <{$smarty.const._MD_XOONIPS_ITEM_SELECT_PRIVATE_INDEX}>
 </span>
</div>
<{/if}>

<{if !empty( $body ) || !empty( $select_item_type )}>
<div style="text-align: center;">
 <input class="formButton" type="submit" id="nextButton"
 value="<{$smarty.const._MD_XOONIPS_ITEM_NEXT_BUTTON_LABEL}>"/>
</div>
<{/if}>
</form>
<!-- end of register.tpl -->
