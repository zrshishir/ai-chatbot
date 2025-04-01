<?php

/**
 * AiChatbot Routes
 *
 * Defines and registers custom API routes for the AiChatbot using the Haruncpi\WpApi library.
 *
 * @package AiChatbot\Routes
 */

namespace AiChatbot\Routes;

use AiChatbot\Core\AIService;
use AiChatbot\Models\ChatHistory;
use Haruncpi\WpApi\ApiRoute;
use AiChatbot\Models\Setting;
use AiChatbot\AiChatbot;

ApiRoute::prefix(
	AICB_ROUTE_PREFIX,
	function (ApiRoute $route) {

		// Define accounts API routes.

		$route->post('/accounts/create', '\AiChatbot\Controllers\Accounts\Actions@create');
		$route->get('/accounts/get', '\AiChatbot\Controllers\Accounts\Actions@get');
		$route->post('/accounts/delete', '\AiChatbot\Controllers\Accounts\Actions@delete');
		$route->post('/accounts/update', '\AiChatbot\Controllers\Accounts\Actions@update');

		// Posts routes.
		$route->get('/posts/get', '\AiChatbot\Controllers\Posts\Actions@get_all_posts');
		$route->get('/posts/get/{id}', '\AiChatbot\Controllers\Posts\Actions@get_post');
		// Allow hooks to add more custom API routes.
		do_action('aicb_api', $route);
	}
);

class Api
{
	/**
	 * Initialize the API routes.
	 *
	 * @return void
	 */
	public function init()
	{
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	/**
	 * Register the API routes.
	 *
	 * @return void
	 */
	public function register_routes()
	{
		register_rest_route('ai-chatbot/v1', '/chat', [
			'methods' => 'POST',
			'callback' => [$this, 'handle_chat'],
			'permission_callback' => [$this, 'check_permission'],
			'args' => [
				'message' => [
					'required' => true,
					'type' => 'string',
				],
			],
		]);

		register_rest_route(AICB_ROUTE_PREFIX, '/test-extraction', [
			'methods' => 'GET',
			'callback' => [$this, 'test_content_extraction'],
			'permission_callback' => function () {
				return current_user_can('manage_options');
			}
		]);
	}

	/**
	 * Check if the user has permission to use the chat.
	 *
	 * @return bool
	 */
	public function check_permission()
	{
		return is_user_logged_in();
	}

	/**
	 * Handle chat messages.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_chat($request)
	{
		try {
			$message = $request->get_param('message');
			$user_id = get_current_user_id();

			// Save user message
			ChatHistory::create([
				'user_id' => $user_id,
				'message' => $message,
				'type' => 'user'
			]);

			// Get AI service instance
			$ai_service = new AIService();

			// Find relevant content
			$relevant_content = $ai_service->findRelevantContent($message);

			// Generate response
			$response = $ai_service->generateResponse($message, $relevant_content);

			// Save bot response
			ChatHistory::create([
				'user_id' => $user_id,
				'message' => $response,
				'type' => 'bot'
			]);

			return rest_ensure_response([
				'success' => true,
				'response' => $response
			]);
		} catch (\Exception $e) {
			return new \WP_Error(
				'chat_error',
				$e->getMessage(),
				['status' => 500]
			);
		}
	}

	/**
	 * Test content extraction endpoint
	 *
	 * @return \WP_REST_Response
	 */
	public function test_content_extraction()
	{
		try {
			$ai_chatbot = new AiChatbot();
			$content_extractor = $ai_chatbot->get_content_extractor();

			// Extract content
			$content = $content_extractor->extract_content();

			// Get statistics
			$stats = $content_extractor->get_statistics();

			return new \WP_REST_Response(array(
				'success' => true,
				'data' => array(
					'content' => $content,
					'statistics' => $stats
				)
			), 200);
		} catch (\Exception $e) {
			return new \WP_REST_Response(array(
				'success' => false,
				'message' => $e->getMessage()
			), 500);
		}
	}
}
