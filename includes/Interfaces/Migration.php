<?php

namespace AiChatbot\Interfaces;

/**
 * Interface Migration
 *
 * Defines the contract for database migration operations.
 *
 * @package AiChatbot\Interfaces
 */
interface Migration
{

	/**
	 * Perform actions when migrating up.
	 *
	 * @return void
	 */
	public function up();

	/**
	 * Perform actions when migrating down.
	 *
	 * @return void
	 */
	public function down();
}
