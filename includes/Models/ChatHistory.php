<?php

namespace AiChatbot\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class ChatHistory extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'aicb_chat_history';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id',
    'message',
    'type',
    'metadata'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'metadata' => 'array'
  ];

  /**
   * Get the user that owns the chat history.
   */
  public function user()
  {
    return $this->belongsTo('WP_User', 'user_id');
  }
}
