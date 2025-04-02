<?php

namespace AiChatbot\Services;

/**
 * Class EmbeddingGenerator
 * 
 * Handles embedding generation and storage
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
   * Generate embeddings for text
   *
   * @param string $text Text to generate embeddings for
   * @return array|WP_Error Embeddings array or WP_Error on failure
   */
  public function generate_embeddings($text)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'aicb_page_embeddings';

    // First check if we already have embeddings for this text
    $existing_embedding = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table_name WHERE content = %s",
      $text
    ));

    if ($existing_embedding) {
      return json_decode($existing_embedding->embedding, true);
    }

    // If no existing embedding, generate new one
    $api_key = get_option('ai_chatbot_api_key', '');
    if (empty($api_key)) {
      return new \WP_Error('no_api_key', 'API key not configured');
    }

    $provider = get_option('ai_chatbot_provider', 'openai');
    switch ($provider) {
      case 'openai':
        return $this->generate_openai_embeddings($text, $api_key);
      case 'claude':
        return $this->generate_claude_embeddings($text, $api_key);
      default:
        return new \WP_Error('invalid_provider', 'Invalid AI provider');
    }
  }

  /**
   * Generate embeddings using OpenAI
   *
   * @param string $text Text to generate embeddings for
   * @param string $api_key API key
   * @return array|WP_Error Embeddings array or WP_Error on failure
   */
  private function generate_openai_embeddings($text, $api_key)
  {
    $args = [
      'timeout' => 30,
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'text-embedding-ada-002',
        'input' => $text
      ]),
    ];

    $response = wp_remote_post('https://api.openai.com/v1/embeddings', $args);

    if (is_wp_error($response)) {
      error_log('OpenAI Embeddings API Error: ' . $response->get_error_message());
      return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
      $body = json_decode(wp_remote_retrieve_body($response), true);
      $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown error';
      error_log('OpenAI Embeddings API Error: ' . $error_message);
      return new \WP_Error('api_error', $error_message);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['data'][0]['embedding'])) {
      error_log('OpenAI Embeddings API Error: Unexpected response format');
      return new \WP_Error('invalid_response', 'Unexpected response from OpenAI API');
    }

    // Store the embedding in the database
    $this->store_embedding($text, $body['data'][0]['embedding']);

    return $body['data'][0]['embedding'];
  }

  /**
   * Generate embeddings using Claude
   *
   * @param string $text Text to generate embeddings for
   * @param string $api_key API key
   * @return array|WP_Error Embeddings array or WP_Error on failure
   */
  private function generate_claude_embeddings($text, $api_key)
  {
    $args = [
      'timeout' => 30,
      'headers' => [
        'x-api-key' => $api_key,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'claude-3-sonnet-20240229',
        'input' => $text,
        'embedding_type' => 'text'
      ]),
    ];

    $response = wp_remote_post('https://api.anthropic.com/v1/embeddings', $args);

    if (is_wp_error($response)) {
      error_log('Claude Embeddings API Error: ' . $response->get_error_message());
      return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
      $body = json_decode(wp_remote_retrieve_body($response), true);
      $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown error';
      error_log('Claude Embeddings API Error: ' . $error_message);
      return new \WP_Error('api_error', $error_message);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['embedding'])) {
      error_log('Claude Embeddings API Error: Unexpected response format');
      return new \WP_Error('invalid_response', 'Unexpected response from Claude API');
    }

    // Store the embedding in the database
    $this->store_embedding($text, $body['embedding']);

    return $body['embedding'];
  }

  /**
   * Store embedding in the database
   *
   * @param string $content Original text content
   * @param array $embedding Embedding array
   * @return bool Success status
   */
  private function store_embedding($content, $embedding)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'aicb_page_embeddings';

    return $wpdb->insert(
      $table_name,
      [
        'content' => $content,
        'embedding' => json_encode($embedding)
      ],
      ['%s', '%s']
    );
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
