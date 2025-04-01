<?php

namespace AiChatbot\Database\Migrations;

use AiChatbot\Interfaces\Migration;
use wpdb;

class CreateSettingsTable implements Migration
{
  /**
   * @var wpdb
   */
  private $wpdb;

  /**
   * Constructor
   */
  public function __construct()
  {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $charset_collate = $this->wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}aicb_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            value text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY `key` (`key`)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $sql = "DROP TABLE IF EXISTS {$this->wpdb->prefix}aicb_settings;";
    $this->wpdb->query($sql);
  }
}
