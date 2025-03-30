<?php

namespace AiChatbot\Core;

class Plugin
{
  /**
   * Get the plugin URL.
   *
   * @return string
   */
  public static function get_plugin_url()
  {
    return plugin_dir_url(dirname(dirname(__FILE__)));
  }

  /**
   * Get the plugin version.
   *
   * @return string
   */
  public static function get_version()
  {
    return '1.0.0';
  }

  /**
   * Get the plugin path.
   *
   * @return string
   */
  public static function get_plugin_path()
  {
    return plugin_dir_path(dirname(dirname(__FILE__)));
  }

  /**
   * Get the plugin basename.
   *
   * @return string
   */
  public static function get_basename()
  {
    return plugin_basename(dirname(dirname(__FILE__)));
  }
}
