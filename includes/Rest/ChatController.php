<?php

namespace AiChatbot\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use AiChatbot\Services\ChatController;
use AiChatbot\Services\EmbeddingGenerator;

class ChatEndpoint extends WP_REST_Controller
{
  /**
   * Chat controller instance
   *
   * @var ChatController
   */
  private $chat_controller;

  /**
   * Embedding generator instance
   *
   * @var EmbeddingGenerator
   */
  private $embedding_generator;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->namespace = 'ai-chatbot/v1';
    $this->rest_base = 'chat';
    $this->chat_controller = new ChatController();
    $this->embedding_generator = new EmbeddingGenerator();
  }

  /**
   * Register routes
   */
  public function register_routes()
  {
    register_rest_route($this->namespace, '/' . $this->rest_base, [
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'handle_message'],
        'permission_callback' => [$this, 'check_permission'],
        'args' => [
          'message' => [
            'required' => true,
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
          ],
          'session_id' => [
            'required' => false,
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
          ],
        ],
      ],
    ]);
  }

  /**
   * Check if user has permission to use the endpoint
   *
   * @return bool|WP_Error
   */
  public function check_permission()
  {
    if (!is_user_logged_in() && !get_option('ai_chatbot_public_access', false)) {
      return new WP_Error(
        'rest_forbidden',
        __('You must be logged in to use the chatbot.', 'ai-chatbot'),
        ['status' => 401]
      );
    }
    return true;
  }

  /**
   * Handle chat message
   *
   * @param WP_REST_Request $request
   * @return WP_REST_Response|WP_Error
   */
  public function handle_message($request)
  {
    $message = $request->get_param('message');
    $session_id = $request->get_param('session_id');

    try {
      // Generate embeddings for the message
      $message_embedding = $this->embedding_generator->generate_embeddings($message);

      if (is_wp_error($message_embedding)) {
        throw new \Exception($message_embedding->get_error_message());
      }

      // Get AI response
      $response = $this->chat_controller->generate_response($message, $message_embedding);

      if (is_wp_error($response)) {
        throw new \Exception($response->get_error_message());
      }

      // Save to chat history if session ID provided
      if ($session_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aicb_chat_messages';

        // Save user message
        $wpdb->insert(
          $table_name,
          [
            'session_id' => $session_id,
            'role' => 'user',
            'content' => $message,
            'created_at' => current_time('mysql')
          ],
          ['%s', '%s', '%s', '%s']
        );

        // Save AI response
        $wpdb->insert(
          $table_name,
          [
            'session_id' => $session_id,
            'role' => 'assistant',
            'content' => $response,
            'created_at' => current_time('mysql')
          ],
          ['%s', '%s', '%s', '%s']
        );
      }

      return new WP_REST_Response([
        'success' => true,
        'response' => $response,
        'session_id' => $session_id
      ], 200);
    } catch (\Exception $e) {
      return new WP_REST_Response([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
