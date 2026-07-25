<?php
namespace block_monlaututoria;

defined('MOODLE_INTERNAL') || die();

final class plugin_test extends \advanced_testcase {

    public function test_plugin_is_registered(): void {
        $this->resetAfterTest();

        $plugin = \core_plugin_manager::instance()->get_plugin_info('block_monlaututoria');

        $this->assertNotNull($plugin);
        $this->assertSame('block_monlaututoria', $plugin->component);
    }

    public function test_pluginname_string_exists(): void {
        $this->assertSame('Monlau Tutoria', get_string('pluginname', 'block_monlaututoria'));
    }
}
