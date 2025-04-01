<?php

/**
 * Database configuration using Eloquent ORM.
 *
 * @package AiChatbot
 * @subpackage Database
 * @since 1.0.0
 */

namespace AiChatbot\Database\Migrations;

use AiChatbot\Interfaces\Migration;
use wpdb;

/**
 * Class Accounts
 *
 * Represents the migration for creating the 'accounts' table.
 *
 * @package AiChatbot\Database\Migrations
 */
class Accounts implements Migration
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

		$sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}aicb_accounts (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				user_id varchar(255) NOT NULL,
				host varchar(255) NOT NULL,
				port int NOT NULL,
				first_name varchar(255) NOT NULL,
				last_name varchar(255) NOT NULL,
				email varchar(255) NOT NULL,
				name varchar(255) NOT NULL,
				password varchar(255) NOT NULL,
				created_at datetime DEFAULT NULL,
				updated_at datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				UNIQUE KEY user_id (user_id)
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	/**
	 * Reverse the migrations (if needed)
	 *
	 * @return void
	 */
	public function down()
	{
		$this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}aicb_accounts");
	}
}
