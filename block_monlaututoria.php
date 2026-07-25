<?php
defined('MOODLE_INTERNAL') || die();

class block_monlaututoria extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_monlaututoria');
    }

    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $context = context_system::instance();
        if (!has_any_capability([
            'local/monlaututoria:viewownstudents',
            'local/monlaututoria:viewallassignments',
        ], $context)) {
            $this->content->text = get_string('block_unavailable', 'block_monlaututoria');
            return $this->content;
        }

        $dashboard = (new \local_monlaututoria\service\dashboard_service())
            ->get_active_tutor_dashboard((int) $USER->id);
        if ($dashboard === null) {
            $this->content->text = get_string('block_noactiveyear', 'block_monlaututoria');
            return $this->content;
        }

        $summary = $dashboard->summary;
        $items = [
            get_string('dashboard_summary_assigned', 'local_monlaututoria') . ': ' . $summary->assignedcount,
            get_string('dashboard_summary_coverage', 'local_monlaututoria') . ': ' . format_float($summary->coveragepercent, 2) . ' %',
            get_string('dashboard_summary_followupsoverdue', 'local_monlaututoria') . ': ' . $summary->overduefollowupcount,
            get_string('dashboard_summary_agreementspending', 'local_monlaututoria') . ': ' . ($summary->pendingagreementcount + $summary->overdueagreementcount),
            get_string('dashboard_summary_referrals', 'local_monlaututoria') . ': ' . $summary->openreferralcount,
            get_string('dashboard_summary_priority', 'local_monlaututoria') . ': ' . $summary->prioritystudentcount,
        ];

        $html = html_writer::tag('ul', implode('', array_map(static fn(string $item): string => html_writer::tag('li', s($item)), $items)));
        $html .= html_writer::div(
            html_writer::link(
                new moodle_url('/local/monlaututoria/dashboard.php'),
                get_string('block_open_dashboard', 'block_monlaututoria')
            )
        );
        if ($summary->prioritystudentcount > 0) {
            $html .= html_writer::div(
                html_writer::link(
                    new moodle_url('/local/monlaututoria/dashboard.php', ['studentfilter' => 'priority']),
                    get_string('block_open_priority', 'block_monlaututoria')
                )
            );
        }
        if (has_capability('local/monlaututoria:managereferrals', $context) && $summary->openreferralcount > 0) {
            $html .= html_writer::div(
                html_writer::link(
                    new moodle_url('/local/monlaututoria/referrals/index.php'),
                    get_string('block_open_referrals', 'block_monlaututoria')
                )
            );
        }

        $this->content->text = $html;
        return $this->content;
    }

    public function applicable_formats() {
        return [
            'all' => true,
        ];
    }
}
