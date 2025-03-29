<?php

namespace AiChatbot\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class PageEmbedding extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'aicb_page_embeddings';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'post_id',
    'content',
    'embedding',
    'metadata'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'embedding' => 'array',
    'metadata' => 'array'
  ];

  /**
   * Get the post that owns the embedding.
   */
  public function post()
  {
    return $this->belongsTo('WP_Post', 'post_id');
  }
}
