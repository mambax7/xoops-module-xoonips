<{* page for user that forgets ones password or inputs miss password *}>

<fieldset style="padding: 10px;">
  <legend style="font-weight: bold;"><{$smarty.const._LOGIN}></legend>
  <form action="user.php" method="post">
    <{$smarty.const._USERNAME}> <input type="text" name="uname" size="26" maxlength="25" value="<{$usercookie}>"/><br>
    <{$smarty.const._PASSWORD}> <input type="password" name="pass" size="21" maxlength="32"/><br>
    <input type="hidden" name="op" value="login"/>
    <input type="hidden" name="xoops_redirect" value="<{$redirect_page}>"/>
    <input class="formButton" type="submit" value="<{$smarty.const._LOGIN}>"/>
  </form>
  <a name="lost"></a>
  <div><{$smarty.const._MD_XOONIPS_ACCOUNT_NOTREGISTERED1}>
    <{if $smarty.const._MD_XOONIPS_ACCOUNT_NOTREGISTERED2 != ""}>
    <a href="registeruser.php"><{$smarty.const._MD_XOONIPS_ACCOUNT_NOTREGISTERED2}></a><{/if}><br></div>
</fieldset>

<br>

<fieldset style="padding: 10px;">
  <legend style="font-weight: bold;"><{$smarty.const._US_LOSTPASSWORD}></legend>
  <div><br><{$smarty.const._US_NOPROBLEM}></div>
  <form action="lostpass.php" method="post">
    <{$smarty.const._US_YOUREMAIL}> <input type="text" name="email" size="26" maxlength="60"/>&nbsp;&nbsp;
    <input type="hidden" name="op" value="mailpasswd"/><input class="formButton" type="submit" value="<{$smarty.const._US_SENDPASSWORD}>"/>
  </form>
</fieldset>
