<?php
/**
 * Class Users
 *
 * Represents the Users model for AiChatbot.
 *
 * @package AiChatbot\Models
 */

namespace AiChatbot\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

/**
 * Class Users
 *
 * Represents the Users model for AiChatbot.
 *
 * @package AiChatbot\Models
 */
class Users extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array( 'user_login' );
}
