<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
  <h1>Content Extraction Test</h1>

  <div class="card">
    <h2>Statistics</h2>
    <table class="widefat">
      <tr>
        <th>Total Pages</th>
        <td><?php echo esc_html($stats['total_pages']); ?></td>
      </tr>
      <tr>
        <th>Total Characters</th>
        <td><?php echo esc_html($stats['total_characters']); ?></td>
      </tr>
      <tr>
        <th>Content Types</th>
        <td>
          <?php
          foreach ($stats['content_types'] as $type => $count) {
            echo esc_html(ucfirst($type) . ': ' . $count . '<br>');
          }
          ?>
        </td>
      </tr>
      <tr>
        <th>Date Range</th>
        <td>
          Oldest: <?php echo esc_html($stats['date_range']['oldest']); ?><br>
          Newest: <?php echo esc_html($stats['date_range']['newest']); ?>
        </td>
      </tr>
    </table>
  </div>

  <div class="card">
    <h2>Extracted Content</h2>
    <?php foreach ($content as $item): ?>
      <div class="content-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
        <h3><?php echo esc_html($item['title']); ?></h3>
        <p><strong>Type:</strong> <?php echo esc_html($item['type']); ?></p>
        <p><strong>URL:</strong> <a href="<?php echo esc_url($item['url']); ?>" target="_blank"><?php echo esc_html($item['url']); ?></a></p>
        <p><strong>Date:</strong> <?php echo esc_html($item['date']); ?></p>
        <p><strong>Excerpt:</strong> <?php echo esc_html($item['excerpt']); ?></p>
        <p><strong>Content:</strong></p>
        <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
          <?php echo esc_html($item['content']); ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
  .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    margin-top: 20px;
    padding: 20px;
  }

  .widefat {
    border-collapse: collapse;
    width: 100%;
  }

  .widefat th {
    text-align: left;
    padding: 8px;
    background: #f5f5f5;
  }

  .widefat td {
    padding: 8px;
    border-bottom: 1px solid #f0f0f0;
  }

  .content-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
    padding: 15px;
  }

  .content-item h3 {
    margin-top: 0;
    color: #23282d;
  }
</style>