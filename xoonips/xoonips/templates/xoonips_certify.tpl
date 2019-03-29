<{*
template for display to certify items


$items[] array of item information to be certified
         array( item_id=>id of item,
                item_body=>HTML to display item's summary to certify,
                indexex=array( array( 'id'=>index_id, 'path' => path string of index ), ... )...

*}>
<{if !empty( $pankuzu )}>
<p><{$pankuzu}></p>
<{/if}>

<h3><{$smarty.const._MD_XOONIPS_ITEM_CERTIFY_ITEM_TITLE}></h3>

<table >
 <tr class="head">
  <th><{$smarty.const._MD_XOONIPS_HEADER_CERTIFY_ITEMS}></th>
 </tr>
<{if !empty( $items ) }>
 <{foreach item=i from=$items}>
 <tr>
  <td class="<{cycle values="odd,even"}>">
   <table>
    <tr>
     <td class="head" width="80"><{$smarty.const._MD_XOONIPS_ITEM_ITEM_LABEL}></td>
     <td><{$i.item_body}></td>
    </tr>
    <tr>
     <td class="head" width="80"><{$smarty.const._MD_XOONIPS_ITEM_INDEX_LABEL}></td>
     <td>
      <form name='xoonips_certify<{$i.item_id}>' action="certify.php<{if !empty($menu_id)}>?menu_id=<{$menu_id}><{/if}>" method="post">
      <{foreach item=j from=$i.indexes}>
        <input type='checkbox' name='index_ids[]' value='<{$j.id}>' />
      <a href="listitem.php?index_id=<{$j.id}>"><{$j.path}></a><br>
      <{/foreach}>
       <img src="images/arrow_ltr.png" alt="arrow" />
       With selected: 
       <input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ITEM_CERTIFY_BUTTON_LABEL}>" onclick="this.form.op.value='certify'; submit();" />
       <input class="formButton" type="button" value="<{$smarty.const._MD_XOONIPS_ITEM_UNCERTIFY_BUTTON_LABEL}>" onclick="this.form.op.value='uncertify'; submit();" />
       <input type='hidden' name='op' value='' />
       <input type='hidden' name='item_id' value='<{$i.item_id}>' />
       <{$token_ticket}>
      </form>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <{/foreach}>
<{else}>
 <tr>
  <td class="even" style="text-align: center;"><span style="font-weight: bold; color: red;"><{$smarty.const._MD_XOONIPS_ITEM_NO_CERTIFY_ITEMS}></span></td>
 </tr>
<{/if}>
</table>
