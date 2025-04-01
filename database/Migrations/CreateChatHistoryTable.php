<?php

namespace AiChatbot\Database\Migrations;

use AiChatbot\Interfaces\Migration;
use wpdb;

class CreateChatHistoryTable implements Migration
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

    // First create the table without foreign key
    $sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}aicb_chat_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            message text NOT NULL,
            type enum('user','bot') NOT NULL,
            metadata json DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Then add the foreign key constraint
    $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}aicb_chat_history 
        ADD CONSTRAINT fk_chat_history_user 
        FOREIGN KEY (user_id) 
        REFERENCES {$this->wpdb->users}(ID) 
        ON DELETE CASCADE");
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $sql = "DROP TABLE IF EXISTS {$this->wpdb->prefix}aicb_chat_history;";
    $this->wpdb->query($sql);
  }
}
