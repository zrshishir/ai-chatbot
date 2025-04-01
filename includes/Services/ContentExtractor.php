<?php

namespace AiChatbot\Services;

/**
 * Class ContentExtractor
 * 
 * Handles content extraction from WordPress pages
 */
class ContentExtractor
{
  /**
   * Maximum number of pages to extract
   *
   * @var int
   */
  private $max_pages = 5;

  /**
   * Content types to extract
   *
   * @var array
   */
  private $content_types = ['page', 'post'];

  /**
   * Extract content from WordPress pages
   *
   * @return array Array of extracted content
   */
  public function extract_content()
  {
    $extracted_content = [];
    $args = array(
      'post_type' => $this->content_types,
      'posts_per_page' => $this->max_pages,
      'post_status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC'
    );

    $query = new \WP_Query($args);

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $content = $this->clean_content(get_the_content());

        if (!empty($content)) {
          $extracted_content[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'content' => $content,
            'url' => get_permalink(),
            'type' => get_post_type(),
            'excerpt' => get_the_excerpt(),
            'date' => get_the_date('Y-m-d H:i:s')
          ];
        }
      }
    }

    wp_reset_postdata();
    return $extracted_content;
  }

  /**
   * Clean and structure the content
   *
   * @param string $content Raw content from WordPress
   * @return string Cleaned content
   */
  private function clean_content($content)
  {
    // Remove HTML tags
    $content = wp_strip_all_tags($content);

    // Remove extra whitespace
    $content = preg_replace('/\s+/', ' ', $content);

    // Remove shortcodes
    $content = strip_shortcodes($content);

    // Remove special characters
    $content = preg_replace('/[^\p{L}\p{N}\s.,!?-]/u', '', $content);

    // Trim whitespace
    $content = trim($content);

    return $content;
  }

  /**
   * Set maximum number of pages to extract
   *
   * @param int $max_pages Maximum number of pages
   * @return void
   */
  public function set_max_pages($max_pages)
  {
    $this->max_pages = (int) $max_pages;
  }

  /**
   * Set content types to extract
   *
   * @param array $content_types Array of content types
   * @return void
   */
  public function set_content_types($content_types)
  {
    $this->content_types = (array) $content_types;
  }

  /**
   * Get content statistics
   *
   * @return array Statistics about extracted content
   */
  public function get_statistics()
  {
    $content = $this->extract_content();

    return [
      'total_pages' => count($content),
      'total_characters' => array_sum(array_map('strlen', array_column($content, 'content'))),
      'content_types' => array_count_values(array_column($content, 'type')),
      'date_range' => [
        'oldest' => min(array_column($content, 'date')),
        'newest' => max(array_column($content, 'date'))
      ]
    ];
  }
}
