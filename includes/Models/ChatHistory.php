<?php

namespace AiChatbot\Models;

/**
 * Class ChatHistory
 * 
 * Handles chat session and message storage
 */
class ChatHistory
{
  /**
   * Add a message to a chat session
   *
   * @param string $session_id Session ID
   * @param string $role Message role (user/assistant)
   * @param string $content Message content
   * @return bool Success status
   */
  public function add_message($session_id, $role, $content)
  {
    global $wpdb;

    $sessions_table = $wpdb->prefix . 'aicb_chat_sessions';
    $messages_table = $wpdb->prefix . 'aicb_chat_messages';

    // Create session if it doesn't exist
    $session = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $sessions_table WHERE session_id = %s",
      $session_id
    ));

    if (!$session) {
      $wpdb->insert(
        $sessions_table,
        ['session_id' => $session_id],
        ['%s']
      );
    }

    // Add message
    return $wpdb->insert(
      $messages_table,
      [
        'session_id' => $session_id,
        'role' => $role,
        'content' => $content
      ],
      ['%s', '%s', '%s']
    );
  }

  /**
   * Get chat history for a session
   *
   * @param string $session_id Session ID
   * @return array Messages
   */
  public function get_history($session_id)
  {
    global $wpdb;

    $messages_table = $wpdb->prefix . 'aicb_chat_messages';

    return $wpdb->get_results($wpdb->prepare(
      "SELECT role, content FROM $messages_table WHERE session_id = %s ORDER BY created_at ASC",
      $session_id
    ));
  }

  /**
   * Get recent chat sessions
   *
   * @param int $limit Number of sessions to return
   * @return array Sessions
   */
  public function get_recent_sessions($limit = 5)
  {
    global $wpdb;

    $sessions_table = $wpdb->prefix . 'aicb_chat_sessions';
    $messages_table = $wpdb->prefix . 'aicb_chat_messages';

    return $wpdb->get_results($wpdb->prepare(
      "SELECT s.*, COUNT(m.id) as message_count 
       FROM $sessions_table s 
       LEFT JOIN $messages_table m ON s.session_id = m.session_id 
       GROUP BY s.id 
       ORDER BY s.updated_at DESC 
       LIMIT %d",
      $limit
    ));
  }
}
