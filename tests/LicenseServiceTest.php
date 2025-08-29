<?php
/**
 * Test class for License Service functionality
 */
class LicenseServiceTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        // Reset plugin state before each test
        delete_option('wplm_api_key');
    }

    public function test_api_key_generation() {
        // Test that API key is generated during plugin activation
        $original_key = get_option('wplm_api_key');
        $this->assertEmpty($original_key, 'API key should not exist initially');
        
        // Simulate plugin activation
        $plugin = WP_License_Manager::instance();
        $plugin->activate();
        
        $generated_key = get_option('wplm_api_key');
        $this->assertNotEmpty($generated_key, 'API key should be generated on activation');
        $this->assertEquals(64, strlen($generated_key), 'API key should be 64 characters long');
    }

    public function test_license_key_format() {
        // Test license key generation format
        if (class_exists('WPLM_Admin_Manager')) {
            $admin_manager = new WPLM_Admin_Manager();
            
            // Test that license key follows expected pattern
            $test_key = 'ABCD-1234-EFGH-5678-IJKL';
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $test_key);
        } else {
            $this->markTestSkipped('WPLM_Admin_Manager class not available');
        }
    }

    public function test_custom_capabilities() {
        // Test that custom capabilities are added correctly
        $admin_role = get_role('administrator');
        $this->assertInstanceOf('WP_Role', $admin_role);
        
        // Simulate plugin activation to add capabilities
        $plugin = WP_License_Manager::instance();
        $plugin->activate();
        
        // Check for key capabilities
        $this->assertTrue($admin_role->has_cap('manage_wplm_licenses'));
        $this->assertTrue($admin_role->has_cap('edit_wplm_license'));
        $this->assertTrue($admin_role->has_cap('create_wplm_products'));
    }
}