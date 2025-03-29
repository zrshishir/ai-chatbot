<?php
/**
 * AiChatbot Routes
 *
 * Defines and registers custom API routes for the AiChatbot using the Haruncpi\WpApi library.
 *
 * @package AiChatbot\Routes
 */

namespace AiChatbot\Routes;

use AiChatbot\Libs\API\Route;

Route::prefix(
	AICB_ROUTE_PREFIX,
	function ( Route $route ) {

		// Define accounts API routes.

		$route->post( '/accounts/create', '\AiChatbot\Controllers\Accounts\Actions@create' );
		$route->get( '/accounts/get', '\AiChatbot\Controllers\Accounts\Actions@get' );
		$route->post( '/accounts/delete', '\AiChatbot\Controllers\Accounts\Actions@delete' );
		$route->post( '/accounts/update', '\AiChatbot\Controllers\Accounts\Actions@update' );

		// Posts routes.
		$route->get( '/posts/get', '\AiChatbot\Controllers\Posts\Actions@get_all_posts' );
		$route->get( '/posts/get/{id}', '\AiChatbot\Controllers\Posts\Actions@get_post' );
		// Allow hooks to add more custom API routes.
		do_action( 'aicb_api', $route );
	}
);
