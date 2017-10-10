<?php
  if(!mod_security_check_session(false) || get_session("UserNID") == MOD_SEC_GUEST_USER_ID) {
	prompt_login();
  }
  
  
?>
