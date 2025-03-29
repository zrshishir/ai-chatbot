<?php

namespace AiChatbot\Core;

use AiChatbot\Traits\Base;
use AiChatbot\Libs\API\Config;

/**
 * Class API
 *
 * Initializes and configures the API for the AiChatbot.
 *
 * @package AiChatbot\Core
 */
class API {

	use Base;

	/**
	 * Initializes the API for the AiChatbot.
	 *
	 * @return void
	 */
	public function init() {
		Config::set_route_file( AICB_DIR . '/includes/Routes/Api.php' )
			->set_namespace( 'AiChatbot\Api' )
			->init();
	}
}
