<?php

/**
 * Create chat tables migration
 */

if (!defined('ABSPATH')) {
  exit;
}

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();

// Create chat sessions table
$sessions_table = $wpdb->prefix . 'aicb_chat_sessions';
$sql = "CREATE TABLE IF NOT EXISTS $sessions_table (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  session_id varchar(50) NOT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY session_id (session_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Create chat messages table
$messages_table = $wpdb->prefix . 'aicb_chat_messages';
$sql = "CREATE TABLE IF NOT EXISTS $messages_table (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  session_id varchar(50) NOT NULL,
  role varchar(20) NOT NULL,
  content text NOT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY session_id (session_id),
  FOREIGN KEY (session_id) REFERENCES $sessions_table(session_id) ON DELETE CASCADE
) $charset_collate;";

dbDelta($sql);
