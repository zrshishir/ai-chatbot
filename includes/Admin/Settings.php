<?php

namespace AiChatbot\Admin;

use AiChatbot\Models\Setting;

class Settings
{
  /**
   * Initialize the settings page.
   *
   * @return void
   */
  public function init()
  {
    add_action('admin_menu', [$this, 'add_menu_page']);
    add_action('admin_init', [$this, 'register_settings']);
  }

  /**
   * Add the settings page to the admin menu.
   *
   * @return void
   */
  public function add_menu_page()
  {
    add_options_page(
      'AI Chatbot Settings',
      'AI Chatbot',
      'manage_options',
      'ai-chatbot-settings',
      [$this, 'render_settings_page']
    );
  }

  /**
   * Register the settings.
   *
   * @return void
   */
  public function register_settings()
  {
    register_setting('ai_chatbot_settings', 'ai_chatbot_provider');
    register_setting('ai_chatbot_settings', 'ai_chatbot_api_key');
    register_setting('ai_chatbot_settings', 'ai_chatbot_pages');
  }

  /**
   * Render the settings page.
   *
   * @return void
   */
  public function render_settings_page()
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    $provider = get_option('ai_chatbot_provider', 'openai');
    $api_key = get_option('ai_chatbot_api_key', '');
    $pages = get_option('ai_chatbot_pages', []);
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form action="options.php" method="post">
        <?php
        settings_fields('ai_chatbot_settings');
        do_settings_sections('ai_chatbot_settings');
        ?>
        <table class="form-table">
          <tr>
            <th scope="row">
              <label for="ai_chatbot_provider">AI Provider</label>
            </th>
            <td>
              <select name="ai_chatbot_provider" id="ai_chatbot_provider">
                <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI</option>
                <option value="claude" <?php selected($provider, 'claude'); ?>>Claude</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="ai_chatbot_api_key">API Key</label>
            </th>
            <td>
              <input type="password" name="ai_chatbot_api_key" id="ai_chatbot_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
            </td>
          </tr>
          <tr>
            <th scope="row">Pages to Index</th>
            <td>
              <?php
              $posts = get_posts([
                'post_type' => 'page',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
              ]);

              foreach ($posts as $post) {
              ?>
                <label>
                  <input type="checkbox" name="ai_chatbot_pages[]" value="<?php echo esc_attr($post->ID); ?>" <?php checked(in_array($post->ID, $pages)); ?>>
                  <?php echo esc_html($post->post_title); ?>
                </label><br>
              <?php
              }
              ?>
            </td>
          </tr>
        </table>
        <?php submit_button('Save Settings'); ?>
      </form>
    </div>
<?php
  }
}
