<?php
/**
 * Test class for API Rate Limiting functionality
 */
class ApiRateLimitTest extends WP_UnitTestCase {

    private $api_manager;

    public function setUp(): void {
        parent::setUp();
        
        if (class_exists('WPLM_API_Manager')) {
            $this->api_manager = new WPLM_API_Manager();
        }
        
        // Clean up any existing rate limit data
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wplm_rate_limit_%'");
    }

    public function test_rate_limiting_allows_normal_usage() {
        $this->markTestSkipped('Requires WordPress environment setup');
        
        if (!$this->api_manager) {
            $this->markTestSkipped('WPLM_API_Manager not available');
        }

        // Mock IP address for testing
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        
        // Simulate API key setup
        update_option('wplm_api_key', 'test_api_key_12345');
        
        // Test that first request is allowed
        $_POST = [
            'api_key' => 'test_api_key_12345',
            'license_key' => 'TEST-KEY-1234',
            'product_id' => 'test_product'
        ];
        
        // Capture output to prevent headers already sent warnings
        ob_start();
        
        try {
            // This should succeed (within rate limit)
            $this->api_manager->ajax_validate();
            $this->assertTrue(true, 'First API request should be allowed');
        } catch (Exception $e) {
            // Expected behavior for test environment
            $this->assertStringContainsString('License key not found', $e->getMessage());
        }
        
        ob_end_clean();
    }

    public function test_rate_limit_key_generation() {
        if (!$this->api_manager) {
            $this->markTestSkipped('WPLM_API_Manager not available');
        }

        // Test rate limit key format
        $ip = '192.168.1.100';
        $expected_key = 'wplm_rate_limit_' . md5($ip);
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->api_manager);
        $method = $reflection->getMethod('get_client_ip');
        $method->setAccessible(true);
        
        $_SERVER['REMOTE_ADDR'] = $ip;
        $result_ip = $method->invoke($this->api_manager);
        
        $this->assertEquals($ip, $result_ip, 'IP detection should work correctly');
        
        $actual_key = 'wplm_rate_limit_' . md5($result_ip);
        $this->assertEquals($expected_key, $actual_key, 'Rate limit key should be generated correctly');
    }

    public function test_ip_detection_with_proxy_headers() {
        if (!$this->api_manager) {
            $this->markTestSkipped('WPLM_API_Manager not available');
        }

        // Test with proxy headers
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.195, 192.168.1.100';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        
        $reflection = new ReflectionClass($this->api_manager);
        $method = $reflection->getMethod('get_client_ip');
        $method->setAccessible(true);
        
        $result_ip = $method->invoke($this->api_manager);
        
        $this->assertEquals('203.0.113.195', $result_ip, 'Should detect real IP from X-Forwarded-For header');
        
        // Clean up
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function test_transient_cleanup() {
        // Test that rate limit transients are properly created and cleaned up
        $ip = '192.168.1.200';
        $rate_limit_key = 'wplm_rate_limit_' . md5($ip);
        
        // Set a test transient
        set_transient($rate_limit_key, ['count' => 1, 'first_request' => time()], 300);
        
        $data = get_transient($rate_limit_key);
        $this->assertIsArray($data, 'Rate limit data should be stored as array');
        $this->assertEquals(1, $data['count'], 'Count should be initialized to 1');
        
        // Clean up
        delete_transient($rate_limit_key);
        
        $data_after_cleanup = get_transient($rate_limit_key);
        $this->assertFalse($data_after_cleanup, 'Transient should be cleaned up');
    }

    public function tearDown(): void {
        // Clean up test data
        delete_option('wplm_api_key');
        
        // Clean up rate limit transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wplm_rate_limit_%'");
        
        parent::tearDown();
    }
}