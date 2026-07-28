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
        $this->assertSame('Monlau Tutoría', get_string('pluginname', 'block_monlaututoria'));
    }

    public function test_get_content_shows_only_assigned_and_coverage_stats(): void {
        $this->resetAfterTest();

        $tutor = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();

        $academicyearrepo = new \local_monlaututoria\repository\academic_year_repository();
        $yearid = $academicyearrepo->create((object) [
            'name' => '2026-2027', 'shortname' => 'block-' . uniqid(),
            'startdate' => strtotime('2026-09-01'), 'enddate' => strtotime('2027-06-30'),
            'createdby' => get_admin()->id,
        ]);
        $academicyearrepo->set_active_flag($yearid, true, get_admin()->id);

        (new \local_monlaututoria\repository\assignment_repository())->create((object) [
            'studentid' => $student->id, 'tutorid' => $tutor->id, 'academicyearid' => $yearid,
            'assignmenttype' => 'primary', 'isprimary' => 1, 'status' => 'active',
            'timestart' => time() - DAYSECS, 'timeend' => null, 'createdby' => get_admin()->id,
        ]);

        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('local/monlaututoria:viewownstudents', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $tutor->id, \context_system::instance()->id);

        $this->setUser($tutor);

        $block = new \block_monlaututoria();
        $content = $block->get_content();

        $this->assertStringContainsString('block-monlaututoria-stat', $content->text);
        $this->assertStringContainsString(get_string('dashboard_summary_assigned', 'local_monlaututoria'), $content->text);
        $this->assertStringContainsString(get_string('dashboard_summary_coverage', 'local_monlaututoria'), $content->text);
        $this->assertStringNotContainsString(get_string('dashboard_summary_followupsoverdue', 'local_monlaututoria'), $content->text);
        $this->assertStringNotContainsString(get_string('dashboard_summary_agreementspending', 'local_monlaututoria'), $content->text);
        $this->assertStringNotContainsString(get_string('dashboard_summary_referrals', 'local_monlaututoria'), $content->text);
        $this->assertStringNotContainsString(get_string('dashboard_summary_priority', 'local_monlaututoria'), $content->text);
    }
}
