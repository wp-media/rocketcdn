<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WPMedia\RocketCDN\RESTSubscriber;

/**
 * @covers \WPMedia\RocketCDN\RESTSubscriber::get_subscribed_events
 * @group RocketCDN
 */
class Test_GetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'rest_api_init' => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
			],
		];

		$this->assertSame(
			$events,
			RESTSubscriber::get_subscribed_events()
		);
	}
}
