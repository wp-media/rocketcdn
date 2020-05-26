<?php

namespace WPMedia\RocketCDN\Tests\Integration;

use ReflectionObject;
use WPMedia\RocketCDN\Tests\SettingsTrait;
use WPMedia\RocketCDN\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use CapTrait;
	use SettingsTrait;
	use StubTrait;

	protected static $use_settings_trait = true;
	protected static $transients = [
		'rocketcdn_status' => null,
	];

	protected $config;
	protected $original_wp_filter;
	protected $cdn_names;
	protected $home_url = 'http://example.org';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::hasAdminCapBeforeClass();

		if ( static::$use_settings_trait ) {
			SettingsTrait::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				self::$transients[ $transient ] = get_transient( $transient );
			}
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		CapTrait::resetAdminCap();

		if ( static::$use_settings_trait ) {
			SettingsTrait::resetOriginalSettings();
		}

		foreach ( self::$transients as $transient => $value ) {
			if ( ! empty( $transient ) ) {
				set_transient( $transient, $value );
			} else {
				delete_transient( $transient );
			}
		}
	}

	public function setUp() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$this->stubRocketGetConstant();

		parent::setUp();

		if ( static::$use_settings_trait ) {
			$this->setUpSettings();
		}

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->resetStubProperties();

		if ( static::$use_settings_trait ) {
			$this->tearDownSettings();
		}

		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );
		set_current_screen( 'front' );
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}

	protected function unregisterAllCallbacksExcept( $event_name, $method_name, $priority = 10 ) {
		global $wp_filter;
		$this->original_wp_filter = $wp_filter[ $event_name ]->callbacks;

		foreach ( $this->original_wp_filter[ $priority ] as $key => $config ) {

			// Skip if not this tests callback.
			if ( substr( $key, - strlen( $method_name ) ) !== $method_name ) {
				continue;
			}

			$wp_filter[ $event_name ]->callbacks = [
				$priority => [ $key => $config ],
			];
		}
	}

	protected function restoreWpFilter( $event_name ) {
		global $wp_filter;
		$wp_filter[ $event_name ]->callbacks = $this->original_wp_filter;

	}

	public function home_url_cb() {
		return $this->home_url;
	}

	public function cdn_names_cb() {
		return $this->cdn_names;
	}

	public function return_empty_string() {
		return '';
	}
}
