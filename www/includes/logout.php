<?php

// clear the session
$db->run('UPDATE `'.DB_USER_TABLE.'` SET `session_id`=? WHERE `id`=?', '', USER_ID);

session_destroy();
$domain = CookieDomain();
setcookie('keep_session', '', time() - 3600, '/', $domain);
setcookie(session_name(), '', time() - 42000, '/');

header('Location: /');
exit();
?>