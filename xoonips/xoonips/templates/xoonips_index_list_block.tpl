<{* xoonips index list block *}>
<table>
 <tr>
  <td style='vertical-align:middle; text-align:center; width: 65px;'>
   <a href="<{$xoops_url}>/modules/xoonips/listitem.php?index_id=<{$index.index_id}>"><img src="<{$smarty.const.XOOPS_URL}>/modules/xoonips/images/icon_folder.gif" alt="<{$index.title}>"/></a>
  </td> 
  <td>
   <a href="<{$xoops_url}>/modules/xoonips/listitem.php?index_id=<{$index.index_id}>"><{$index.title}></a><br>
   <{$index.child_index_num}> Indexes / <{$index.child_item_num}> Items
  </td>
 </tr>
</table>
