<?php

namespace AiChatbot\Services;

/**
 * Class EmbeddingGenerator
 * 
 * Handles generation of embeddings using AI services
 */
class EmbeddingGenerator
{
  /**
   * OpenAI API endpoint
   *
   * @var string
   */
  private $openai_endpoint = 'https://api.openai.com/v1/embeddings';

  /**
   * Claude API endpoint
   *
   * @var string
   */
  private $claude_endpoint = 'https://api.anthropic.com/v1/embeddings';

  /**
   * Current AI provider
   *
   * @var string
   */
  private $provider;

  /**
   * API key
   *
   * @var string
   */
  private $api_key;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->provider = get_option('ai_chatbot_provider', 'openai');
    $this->api_key = get_option('ai_chatbot_api_key', '');
  }

  /**
   * Generate embeddings for content
   *
   * @param string $content Content to generate embeddings for
   * @return array|WP_Error Embeddings or error
   */
  public function generate_embeddings($content)
  {
    if (empty($this->api_key)) {
      return new \WP_Error('no_api_key', 'API key not configured');
    }

    switch ($this->provider) {
      case 'openai':
        return $this->generate_openai_embeddings($content);
      case 'claude':
        return $this->generate_claude_embeddings($content);
      default:
        return new \WP_Error('invalid_provider', 'Invalid AI provider');
    }
  }

  /**
   * Generate embeddings using OpenAI
   *
   * @param string $content Content to generate embeddings for
   * @return array|WP_Error Embeddings or error
   */
  private function generate_openai_embeddings($content)
  {
    $response = wp_remote_post($this->openai_endpoint, [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'text-embedding-ada-002',
        'input' => $content,
      ]),
    ]);

    if (is_wp_error($response)) {
      return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['error'])) {
      return new \WP_Error('openai_error', $body['error']['message']);
    }

    return $body['data'][0]['embedding'];
  }

  /**
   * Generate embeddings using Claude
   *
   * @param string $content Content to generate embeddings for
   * @return array|WP_Error Embeddings or error
   */
  private function generate_claude_embeddings($content)
  {
    $response = wp_remote_post($this->claude_endpoint, [
      'headers' => [
        'x-api-key' => $this->api_key,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'claude-3-sonnet-20240229',
        'input' => $content,
      ]),
    ]);

    if (is_wp_error($response)) {
      return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['error'])) {
      return new \WP_Error('claude_error', $body['error']['message']);
    }

    return $body['embedding'];
  }

  /**
   * Save embeddings to database
   *
   * @param int $post_id Post ID
   * @param string $content Content
   * @param array $embeddings Embeddings array
   * @return bool|WP_Error Success or error
   */
  public function save_embeddings($post_id, $content, $embeddings)
  {
    global $wpdb;

    if (is_wp_error($embeddings)) {
      return $embeddings;
    }

    $table_name = $wpdb->prefix . 'aicb_page_embeddings';

    $data = [
      'post_id' => $post_id,
      'content' => $content,
      'embedding' => json_encode($embeddings),
      'metadata' => json_encode([
        'provider' => $this->provider,
        'model' => $this->provider === 'openai' ? 'text-embedding-ada-002' : 'claude-3-sonnet-20240229'
      ])
    ];

    $format = [
      '%d', // post_id
      '%s', // content
      '%s', // embedding
      '%s'  // metadata
    ];

    $result = $wpdb->insert($table_name, $data, $format);

    if ($result === false) {
      return new \WP_Error('db_error', $wpdb->last_error);
    }

    return true;
  }

  /**
   * Get embeddings for a post
   *
   * @param int $post_id Post ID
   * @return array|WP_Error Embeddings or error
   */
  public function get_embeddings($post_id)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'aicb_page_embeddings';

    $embeddings = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d ORDER BY created_at DESC LIMIT 1",
        $post_id
      )
    );

    if (!$embeddings) {
      return new \WP_Error('not_found', 'No embeddings found for this post');
    }

    $metadata = json_decode($embeddings->metadata, true);

    return [
      'embeddings' => json_decode($embeddings->embedding, true),
      'content' => $embeddings->content,
      'provider' => $metadata['provider'],
      'model' => $metadata['model'],
      'created_at' => $embeddings->created_at
    ];
  }

  /**
   * Set AI provider
   *
   * @param string $provider Provider name
   * @return void
   */
  public function set_provider($provider)
  {
    $this->provider = $provider;
  }

  /**
   * Set API key
   *
   * @param string $api_key API key
   * @return void
   */
  public function set_api_key($api_key)
  {
    $this->api_key = $api_key;
  }
}
