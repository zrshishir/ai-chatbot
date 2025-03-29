<?php

namespace AiChatbot\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class Setting extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'aicb_settings';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'key',
    'value'
  ];

  /**
   * Get a setting value by key.
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public static function get($key, $default = null)
  {
    $setting = static::where('key', $key)->first();
    return $setting ? $setting->value : $default;
  }

  /**
   * Set a setting value.
   *
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public static function set($key, $value)
  {
    static::updateOrCreate(
      ['key' => $key],
      ['value' => $value]
    );
  }
}
