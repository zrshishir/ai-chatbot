<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
  <h1>AI Chatbot</h1>

  <div class="card">
    <h2>Welcome to AI Chatbot</h2>
    <p>This plugin helps you create an AI-powered chatbot that can answer questions based on your website content.</p>

    <h3>Quick Links</h3>
    <ul>
      <li><a href="<?php echo admin_url('admin.php?page=ai-chatbot-settings'); ?>">Settings</a> - Configure your AI provider and API keys</li>
      <li><a href="<?php echo admin_url('admin.php?page=ai-chatbot-content-test'); ?>">Content Test</a> - Test content extraction from your website</li>
    </ul>
  </div>

  <div class="card">
    <h2>Getting Started</h2>
    <ol>
      <li>Go to the <a href="<?php echo admin_url('admin.php?page=ai-chatbot-settings'); ?>">Settings</a> page</li>
      <li>Choose your AI provider (OpenAI or Claude)</li>
      <li>Enter your API key</li>
      <4>Select the pages you want to index</li>
        <5>Save your settings</li>
          <6>Use the shortcode <code>[ai_chatbot]</code> to add the chatbot to any page</li>
    </ol>
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

  .card h2 {
    margin-top: 0;
    color: #23282d;
  }

  .card h3 {
    color: #23282d;
  }

  .card ul,
  .card ol {
    margin-left: 20px;
  }

  .card code {
    background: #f0f0f0;
    padding: 2px 5px;
    border-radius: 3px;
  }
</style>