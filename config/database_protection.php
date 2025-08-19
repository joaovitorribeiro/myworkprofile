<?php

return array (
  'enabled' => true,
  'production_protection' => true,
  'blocked_commands' => 
  array (
    0 => 'migrate:fresh',
    1 => 'migrate:reset',
    2 => 'db:wipe',
    3 => 'migrate:rollback',
  ),
  'require_confirmation' => true,
  'auto_backup' => true,
  'backup_retention_days' => 30,
);
