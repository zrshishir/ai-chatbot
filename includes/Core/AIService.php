<?php

namespace AiChatbot\Core;

use AiChatbot\Models\Setting;
use AiChatbot\Models\PageEmbedding;

class AIService
{
  /**
   * The AI provider being used.
   *
   * @var string
   */
  private $provider;

  /**
   * The API key for the AI service.
   *
   * @var string
   */
  private $api_key;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->provider = Setting::get('ai_provider', 'openai');
    $this->api_key = Setting::get('ai_api_key');
  }

  /**
   * Generate embeddings for text.
   *
   * @param string $text
   * @return array
   */
  public function generateEmbedding($text)
  {
    if ($this->provider === 'openai') {
      return $this->generateOpenAIEmbedding($text);
    } else {
      return $this->generateClaudeEmbedding($text);
    }
  }

  /**
   * Generate embeddings using OpenAI.
   *
   * @param string $text
   * @return array
   */
  private function generateOpenAIEmbedding($text)
  {
    $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json'
      ],
      'body' => json_encode([
        'model' => 'text-embedding-ada-002',
        'input' => $text
      ])
    ]);

    if (is_wp_error($response)) {
      throw new \Exception($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['data'][0]['embedding'];
  }

  /**
   * Generate embeddings using Claude.
   *
   * @param string $text
   * @return array
   */
  private function generateClaudeEmbedding($text)
  {
    $response = wp_remote_post('https://api.anthropic.com/v1/embeddings', [
      'headers' => [
        'x-api-key' => $this->api_key,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json'
      ],
      'body' => json_encode([
        'model' => 'claude-3-sonnet-20240229',
        'input' => $text
      ])
    ]);

    if (is_wp_error($response)) {
      throw new \Exception($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['embedding'];
  }

  /**
   * Find relevant content based on a query.
   *
   * @param string $query
   * @param int $limit
   * @return array
   */
  public function findRelevantContent($query, $limit = 5)
  {
    $query_embedding = $this->generateEmbedding($query);
    $embeddings = PageEmbedding::all();

    $scores = [];
    foreach ($embeddings as $embedding) {
      $score = $this->cosineSimilarity($query_embedding, $embedding->embedding);
      $scores[] = [
        'score' => $score,
        'content' => $embedding
      ];
    }

    // Sort by score in descending order
    usort($scores, function ($a, $b) {
      return $b['score'] <=> $a['score'];
    });

    // Return top N results
    return array_slice($scores, 0, $limit);
  }

  /**
   * Calculate cosine similarity between two vectors.
   *
   * @param array $vector1
   * @param array $vector2
   * @return float
   */
  private function cosineSimilarity($vector1, $vector2)
  {
    $dot_product = 0;
    $norm1 = 0;
    $norm2 = 0;

    foreach ($vector1 as $i => $value) {
      $dot_product += $value * $vector2[$i];
      $norm1 += $value * $value;
      $norm2 += $vector2[$i] * $vector2[$i];
    }

    $norm1 = sqrt($norm1);
    $norm2 = sqrt($norm2);

    return $dot_product / ($norm1 * $norm2);
  }

  /**
   * Generate a response using the AI service.
   *
   * @param string $query
   * @param array $context
   * @return string
   */
  public function generateResponse($query, $context)
  {
    if ($this->provider === 'openai') {
      return $this->generateOpenAIResponse($query, $context);
    } else {
      return $this->generateClaudeResponse($query, $context);
    }
  }

  /**
   * Generate a response using OpenAI.
   *
   * @param string $query
   * @param array $context
   * @return string
   */
  private function generateOpenAIResponse($query, $context)
  {
    $prompt = $this->buildPrompt($query, $context);

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json'
      ],
      'body' => json_encode([
        'model' => 'gpt-4-turbo-preview',
        'messages' => [
          [
            'role' => 'system',
            'content' => 'You are a helpful AI assistant that answers questions based on the provided context. Always cite your sources when possible.'
          ],
          [
            'role' => 'user',
            'content' => $prompt
          ]
        ]
      ])
    ]);

    if (is_wp_error($response)) {
      throw new \Exception($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['choices'][0]['message']['content'];
  }

  /**
   * Generate a response using Claude.
   *
   * @param string $query
   * @param array $context
   * @return string
   */
  private function generateClaudeResponse($query, $context)
  {
    $prompt = $this->buildPrompt($query, $context);

    $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
      'headers' => [
        'x-api-key' => $this->api_key,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json'
      ],
      'body' => json_encode([
        'model' => 'claude-3-sonnet-20240229',
        'max_tokens' => 1000,
        'messages' => [
          [
            'role' => 'user',
            'content' => $prompt
          ]
        ]
      ])
    ]);

    if (is_wp_error($response)) {
      throw new \Exception($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['content'][0]['text'];
  }

  /**
   * Build the prompt for the AI service.
   *
   * @param string $query
   * @param array $context
   * @return string
   */
  private function buildPrompt($query, $context)
  {
    $context_text = '';
    foreach ($context as $item) {
      $context_text .= "Source: {$item['content']['metadata']['title']}\n";
      $context_text .= "URL: {$item['content']['metadata']['url']}\n";
      $context_text .= "Content: {$item['content']['content']}\n\n";
    }

    return "Based on the following context, please answer the question. If you cannot find the answer in the context, please say so.\n\nContext:\n{$context_text}\nQuestion: {$query}";
  }
}
