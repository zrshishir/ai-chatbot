<?php

namespace AiChatbot\Services;

/**
 * Class ChatController
 * 
 * Handles AI response generation and chat logic
 */
class ChatController
{
  /**
   * Embedding generator service
   *
   * @var EmbeddingGenerator
   */
  private $embedding_generator;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->embedding_generator = new EmbeddingGenerator();
  }

  /**
   * Generate AI response for a message
   *
   * @param string $message User message
   * @return string AI response
   */
  public function generate_response($message)
  {
    try {
      // Generate embedding for the user message
      $message_embedding = $this->embedding_generator->generate_embeddings($message);

      if (is_wp_error($message_embedding)) {
        throw new \Exception($message_embedding->get_error_message());
      }

      // Find relevant content using similarity search
      $relevant_content = $this->find_relevant_content($message_embedding);

      if (empty($relevant_content)) {
        return "I apologize, but I couldn't find any relevant information to answer your question. Could you please try rephrasing your question?";
      }

      // Generate response using the AI service
      return $this->generate_ai_response($message, $relevant_content);
    } catch (\Exception $e) {
      error_log('AI Chatbot Error: ' . $e->getMessage());
      return "I apologize, but I encountered an error while processing your request. Please try again in a moment.";
    }
  }

  /**
   * Find relevant content using similarity search
   *
   * @param array $query_embedding Query embedding
   * @return array Relevant content
   */
  private function find_relevant_content($query_embedding)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'aicb_page_embeddings';

    // Get all stored embeddings
    $stored_embeddings = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($stored_embeddings)) {
      return [];
    }

    $relevant_content = [];
    foreach ($stored_embeddings as $stored) {
      $stored_embedding = json_decode($stored->embedding, true);
      $similarity = $this->calculate_similarity($query_embedding, $stored_embedding);

      // If similarity is above threshold, include the content
      if ($similarity > 0.7) {
        $relevant_content[] = [
          'content' => $stored->content,
          'similarity' => $similarity
        ];
      }
    }

    // Sort by similarity and take top 3
    usort($relevant_content, function ($a, $b) {
      return $b['similarity'] <=> $a['similarity'];
    });

    return array_slice($relevant_content, 0, 3);
  }

  /**
   * Calculate cosine similarity between two embeddings
   *
   * @param array $embedding1 First embedding
   * @param array $embedding2 Second embedding
   * @return float Similarity score
   */
  private function calculate_similarity($embedding1, $embedding2)
  {
    $dot_product = 0;
    $norm1 = 0;
    $norm2 = 0;

    foreach ($embedding1 as $i => $value1) {
      $value2 = $embedding2[$i];
      $dot_product += $value1 * $value2;
      $norm1 += $value1 * $value1;
      $norm2 += $value2 * $value2;
    }

    $norm1 = sqrt($norm1);
    $norm2 = sqrt($norm2);

    if ($norm1 == 0 || $norm2 == 0) {
      return 0;
    }

    return $dot_product / ($norm1 * $norm2);
  }

  /**
   * Generate AI response using the relevant content
   *
   * @param string $message User message
   * @param array $relevant_content Relevant content
   * @return string AI response
   */
  private function generate_ai_response($message, $relevant_content)
  {
    $provider = get_option('ai_chatbot_provider', 'openai');
    $api_key = get_option('ai_chatbot_api_key', '');

    if (empty($api_key)) {
      throw new \Exception('API key not configured');
    }

    // Prepare context from relevant content
    $context = implode("\n\n", array_map(function ($item) {
      return $item['content'];
    }, $relevant_content));

    // Prepare prompt
    $prompt = "Based on the following context, please answer the user's question. If the context doesn't contain enough information to answer the question, please say so.\n\n";
    $prompt .= "Context:\n" . $context . "\n\n";
    $prompt .= "Question: " . $message . "\n\n";
    $prompt .= "Answer:";

    switch ($provider) {
      case 'openai':
        return $this->generate_openai_response($prompt, $api_key);
      case 'claude':
        return $this->generate_claude_response($prompt, $api_key);
      default:
        throw new \Exception('Invalid AI provider');
    }
  }

  /**
   * Generate response using OpenAI
   *
   * @param string $prompt Prompt
   * @param string $api_key API key
   * @return string Response
   */
  private function generate_openai_response($prompt, $api_key)
  {
    $args = [
      'timeout' => 30, // Increase timeout to 30 seconds
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
          [
            'role' => 'system',
            'content' => 'You are a helpful assistant that answers questions based on the provided context.'
          ],
          [
            'role' => 'user',
            'content' => $prompt
          ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 500
      ]),
    ];

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);

    if (is_wp_error($response)) {
      error_log('OpenAI API Error: ' . $response->get_error_message());
      throw new \Exception('Failed to connect to OpenAI API: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
      $body = json_decode(wp_remote_retrieve_body($response), true);
      $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown error';
      error_log('OpenAI API Error: ' . $error_message);
      throw new \Exception('OpenAI API error: ' . $error_message);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['choices'][0]['message']['content'])) {
      error_log('OpenAI API Error: Unexpected response format');
      throw new \Exception('Unexpected response from OpenAI API');
    }

    return $body['choices'][0]['message']['content'];
  }

  /**
   * Generate response using Claude
   *
   * @param string $prompt Prompt
   * @param string $api_key API key
   * @return string Response
   */
  private function generate_claude_response($prompt, $api_key)
  {
    $args = [
      'timeout' => 30, // Increase timeout to 30 seconds
      'headers' => [
        'x-api-key' => $api_key,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'model' => 'claude-3-sonnet-20240229',
        'max_tokens' => 500,
        'messages' => [
          [
            'role' => 'user',
            'content' => $prompt
          ]
        ]
      ]),
    ];

    $response = wp_remote_post('https://api.anthropic.com/v1/messages', $args);

    if (is_wp_error($response)) {
      error_log('Claude API Error: ' . $response->get_error_message());
      throw new \Exception('Failed to connect to Claude API: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
      $body = json_decode(wp_remote_retrieve_body($response), true);
      $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown error';
      error_log('Claude API Error: ' . $error_message);
      throw new \Exception('Claude API error: ' . $error_message);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['content'][0]['text'])) {
      error_log('Claude API Error: Unexpected response format');
      throw new \Exception('Unexpected response from Claude API');
    }

    return $body['content'][0]['text'];
  }
}
