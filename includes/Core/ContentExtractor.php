<?php

namespace AiChatbot\Core;

use AiChatbot\Models\PageEmbedding;
use WP_Post;

class ContentExtractor
{
  /**
   * Extract content from a WordPress post.
   *
   * @param WP_Post $post
   * @return array
   */
  public function extractFromPost(WP_Post $post)
  {
    $content = $this->cleanContent($post->post_content);
    $title = $this->cleanContent($post->post_title);

    return [
      'title' => $title,
      'content' => $content,
      'url' => get_permalink($post->ID),
      'post_id' => $post->ID
    ];
  }

  /**
   * Clean the content by removing HTML and extra whitespace.
   *
   * @param string $content
   * @return string
   */
  private function cleanContent($content)
  {
    // Remove HTML tags
    $content = wp_strip_all_tags($content);

    // Remove extra whitespace
    $content = preg_replace('/\s+/', ' ', $content);

    // Trim whitespace
    $content = trim($content);

    return $content;
  }

  /**
   * Extract content from multiple posts.
   *
   * @param array $post_ids
   * @return array
   */
  public function extractFromPosts($post_ids)
  {
    $extracted = [];

    foreach ($post_ids as $post_id) {
      $post = get_post($post_id);
      if ($post instanceof WP_Post) {
        $extracted[] = $this->extractFromPost($post);
      }
    }

    return $extracted;
  }

  /**
   * Save extracted content with embeddings.
   *
   * @param array $content
   * @param array $embedding
   * @return PageEmbedding
   */
  public function saveWithEmbedding($content, $embedding)
  {
    return PageEmbedding::create([
      'post_id' => $content['post_id'],
      'content' => $content['content'],
      'embedding' => $embedding,
      'metadata' => [
        'title' => $content['title'],
        'url' => $content['url']
      ]
    ]);
  }
}
