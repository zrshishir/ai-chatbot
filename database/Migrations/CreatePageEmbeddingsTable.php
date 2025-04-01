<?php

namespace AiChatbot\Database\Migrations;

use AiChatbot\Interfaces\Migration;
use wpdb;

class CreatePageEmbeddingsTable implements Migration
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
    $sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}aicb_page_embeddings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            content text NOT NULL,
            embedding json NOT NULL,
            metadata json DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Then add the foreign key constraint
    $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}aicb_page_embeddings 
        ADD CONSTRAINT fk_page_embeddings_post 
        FOREIGN KEY (post_id) 
        REFERENCES {$this->wpdb->posts}(ID) 
        ON DELETE CASCADE");
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $sql = "DROP TABLE IF EXISTS {$this->wpdb->prefix}aicb_page_embeddings;";
    $this->wpdb->query($sql);
  }
}
