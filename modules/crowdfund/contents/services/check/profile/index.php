<?php

$userInfo = control_user_complete_information(null, MOD_CROWDFUND_USER_INSERT_IDEA_FIELD, true);

echo (is_array($userInfo) ? ffCommon_jsonenc($userInfo, true) : null);
exit;

?>