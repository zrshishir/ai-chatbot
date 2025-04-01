<?php
if (!defined('ABSPATH')) {
  exit;
}

// Get a test page
$test_page = get_posts([
  'post_type' => 'page',
  'posts_per_page' => 1,
  'orderby' => 'ID',
  'order' => 'ASC'
]);

$post_id = $test_page[0]->ID ?? 0;
$page_content = $test_page[0]->post_content ?? '';

// Generate and save embeddings
$embeddings = $embedding_generator->generate_embeddings($page_content);
$save_result = $embedding_generator->save_embeddings($post_id, $page_content, $embeddings);

// Retrieve saved embeddings
$saved_embeddings = $embedding_generator->get_embeddings($post_id);
?>

<div class="wrap">
  <h1>Embedding Generation Test</h1>

  <?php if (is_wp_error($embeddings)): ?>
    <div class="notice notice-error">
      <p>Error generating embeddings: <?php echo esc_html($embeddings->get_error_message()); ?></p>
    </div>
  <?php else: ?>
    <div class="card">
      <h2>Test Results</h2>

      <div class="test-content">
        <h3>Test Content</h3>
        <p>Post ID: <?php echo esc_html($post_id); ?></p>
        <p>Content: <?php echo esc_html(substr($page_content, 0, 200)) . '...'; ?></p>
      </div>

      <div class="embedding-stats">
        <h3>Embedding Statistics</h3>
        <ul>
          <li>Embedding Length: <?php echo count($embeddings); ?> dimensions</li>
          <li>Provider: <?php echo esc_html(get_option('ai_chatbot_provider', 'openai')); ?></li>
        </ul>
      </div>

      <div class="embedding-preview">
        <h3>Generated Embeddings Preview (First 10 dimensions)</h3>
        <pre><?php
              $preview = array_slice($embeddings, 0, 10);
              echo esc_html(json_encode($preview, JSON_PRETTY_PRINT));
              ?></pre>
      </div>

      <?php if (is_wp_error($save_result)): ?>
        <div class="notice notice-error">
          <p>Error saving embeddings: <?php echo esc_html($save_result->get_error_message()); ?></p>
        </div>
      <?php else: ?>
        <div class="notice notice-success">
          <p>Embeddings saved successfully!</p>
        </div>

        <?php if (!is_wp_error($saved_embeddings)): ?>
          <div class="saved-embeddings">
            <h3>Retrieved Saved Embeddings</h3>
            <p>Created at: <?php echo esc_html($saved_embeddings['created_at']); ?></p>
            <p>Provider: <?php echo esc_html($saved_embeddings['provider']); ?></p>
            <p>Model: <?php echo esc_html($saved_embeddings['model']); ?></p>
            <p>Content: <?php echo esc_html(substr($saved_embeddings['content'], 0, 200)) . '...'; ?></p>
            <h4>Saved Embeddings Preview (First 10 dimensions)</h4>
            <pre><?php
                  $saved_preview = array_slice($saved_embeddings['embeddings'], 0, 10);
                  echo esc_html(json_encode($saved_preview, JSON_PRETTY_PRINT));
                  ?></pre>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <style>
      .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        margin-top: 20px;
        padding: 20px;
      }

      .test-content {
        margin-bottom: 20px;
      }

      .embedding-stats {
        margin-bottom: 20px;
      }

      .embedding-stats ul {
        list-style: none;
        padding: 0;
        margin: 0;
      }

      .embedding-stats li {
        margin-bottom: 10px;
      }

      .embedding-preview,
      .saved-embeddings {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-top: 20px;
      }

      .embedding-preview pre,
      .saved-embeddings pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
      }

      .notice {
        margin: 20px 0;
      }
    </style>
  <?php endif; ?>
</div>