<?php
defined('MOODLE_INTERNAL') || die();

class block_monlaututoria extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_monlaututoria');
    }

    public function get_content() {
        global $USER, $PAGE;

        $PAGE->requires->css(new moodle_url('/blocks/monlaututoria/styles.css'));

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
        $hastutoraccess = has_any_capability([
            'local/monlaututoria:viewownstudents',
            'local/monlaututoria:viewallassignments',
        ], $context);
        $hascoordinationaccess = has_any_capability([
            'local/monlaututoria:viewcoordinationdashboard',
            'local/monlaututoria:viewallassignments',
        ], $context);
        $hasreferralaccess = has_capability('local/monlaututoria:managereferrals', $context);
        $hasscopemanagement = has_capability('local/monlaututoria:managecoordinationscopes', $context);

        if (!$hastutoraccess && !$hascoordinationaccess && !$hasreferralaccess) {
            $this->content->text = get_string('block_unavailable', 'block_monlaututoria');
            return $this->content;
        }

        $html = '';
        if ($hastutoraccess) {
            $dashboard = (new \local_monlaututoria\service\dashboard_service())
                ->get_active_tutor_dashboard((int) $USER->id);

            if ($dashboard !== null) {
                $summary = $dashboard->summary;
                $coveragepercent = min(100.0, max(0.0, (float) $summary->coveragepercent));

                $html .= html_writer::tag(
                    'h5',
                    get_string('block_section_tutor', 'block_monlaututoria'),
                    ['class' => 'mb-2']
                );

                $assignedtile = html_writer::div(
                    html_writer::span((string) $summary->assignedcount, 'block-monlaututoria-stat__value')
                    . html_writer::span(get_string('dashboard_summary_assigned', 'local_monlaututoria'), 'block-monlaututoria-stat__label'),
                    'block-monlaututoria-stat'
                );

                $coveragetile = html_writer::div(
                    html_writer::span(format_float($coveragepercent, 2) . ' %', 'block-monlaututoria-stat__value')
                    . html_writer::span(get_string('dashboard_summary_coverage', 'local_monlaututoria'), 'block-monlaututoria-stat__label')
                    . html_writer::div(
                        html_writer::div('', 'block-monlaututoria-stat__meter-fill', ['style' => 'width: ' . $coveragepercent . '%;']),
                        'block-monlaututoria-stat__meter'
                    ),
                    'block-monlaututoria-stat'
                );

                $html .= html_writer::div($assignedtile . $coveragetile, 'block-monlaututoria-stats');

                if ($summary->prioritystudentcount > 0) {
                    $html .= html_writer::div(
                        html_writer::link(
                            new moodle_url('/local/monlaututoria/dashboard.php', ['studentfilter' => 'priority']),
                            get_string('block_open_priority', 'block_monlaututoria')
                        )
                    );
                }
            } else {
                $html .= html_writer::tag('p', get_string('block_noactiveyear', 'block_monlaututoria'));
            }

            $html .= html_writer::div(
                html_writer::link(
                    new moodle_url('/local/monlaututoria/dashboard.php'),
                    get_string('block_open_dashboard', 'block_monlaututoria')
                )
            );
            $html .= html_writer::div(
                html_writer::link(
                    new moodle_url('/local/monlaututoria/assignments/index.php'),
                    get_string('block_open_assignments', 'block_monlaututoria')
                )
            );
        }

        if ($hascoordinationaccess || $hasscopemanagement) {
            if ($html !== '') {
                $html .= html_writer::empty_tag('hr');
            }

            $html .= html_writer::tag(
                'h5',
                get_string('block_section_coordination', 'block_monlaututoria'),
                ['class' => 'mb-2']
            );

            if ($hascoordinationaccess) {
                $html .= html_writer::div(
                    html_writer::link(
                        new moodle_url('/local/monlaututoria/coordination.php'),
                        get_string('block_open_coordination', 'block_monlaututoria')
                    )
                );
            }

            if ($hasscopemanagement) {
                $html .= html_writer::div(
                    html_writer::link(
                        new moodle_url('/local/monlaututoria/coordination_scopes.php'),
                        get_string('block_open_coordination_scopes', 'block_monlaututoria')
                    )
                );
            }
        }

        if ($hasreferralaccess) {
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
