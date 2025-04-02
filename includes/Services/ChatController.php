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
   * Predefined responses based on patterns
   *
   * @var array
   */
  private $responses = [
    'greeting' => [
      'patterns' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'],
      'response' => 'Hello! How can I help you today?'
    ],
    'farewell' => [
      'patterns' => ['bye', 'goodbye', 'see you', 'good night'],
      'response' => 'Goodbye! Have a great day!'
    ],
    'help' => [
      'patterns' => ['help', 'support', 'assist', 'guide'],
      'response' => 'I can help you with: 
- General information about our services
- Contact details
- Business hours
- Location information
What would you like to know?'
    ],
    'contact' => [
      'patterns' => ['contact', 'phone', 'email', 'address', 'location'],
      'response' => 'You can reach us at:
Phone: [Your Phone Number]
Email: [Your Email]
Address: [Your Address]'
    ],
    'hours' => [
      'patterns' => ['hours', 'open', 'closed', 'business hours', 'working hours'],
      'response' => 'Our business hours are:
Monday - Friday: 9:00 AM - 6:00 PM
Saturday: 10:00 AM - 4:00 PM
Sunday: Closed'
    ],
    'default' => [
      'response' => 'I apologize, but I\'m not sure how to help with that. Could you please rephrase your question or ask something else?'
    ]
  ];

  /**
   * Generate a response based on the input message
   *
   * @param string $message
   * @return string
   */
  public function generate_response($message)
  {
    $message = strtolower(trim($message));

    foreach ($this->responses as $category => $data) {
      if ($category === 'default') {
        continue;
      }

      foreach ($data['patterns'] as $pattern) {
        if (strpos($message, $pattern) !== false) {
          return $data['response'];
        }
      }
    }

    return $this->responses['default']['response'];
  }
}
