<?php
/** Administrator setup; reporting UI and PDF layout remain owned by the frontend. */
if (!defined('ABSPATH')) { exit; }
class TLE_Form_Setup
{
    public static function menu() {
        add_options_page('Evaluation Forms', 'Evaluation Forms', 'manage_options', 'tle-evaluation-forms', [self::class, 'render']);
    }
    public static function render() {
        if (!current_user_can('manage_options')) { return; }
        $id = absint($_GET['form_id'] ?? 0);
        echo '<div class="wrap"><h1>Evaluation Forms</h1>';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('tle_save_form_profile');
            $id = absint($_POST['form_id'] ?? 0);
            $profile = TLE_Form_Registry::profile($id);
            $input = $_POST['profile_json'] ?? '';
            $config = is_string($input) ? json_decode(wp_unslash($input), true) : null;
            $result = is_wp_error($profile)
                ? $profile
                : TLE_Form_Registry::save(
                    $id,
                    $config
                );

            if (!is_wp_error($result)) {
                /*
                 * Program/cohort availability may have changed.
                 * Force the dashboard selector to rebuild.
                 */
                delete_transient(
                    'tle_comparison_options_v1'
                );
            }

            echo '<p>' .
                esc_html(
                    is_wp_error($result)
                        ? $result->get_error_message()
                        : 'Profile saved.'
                ) .
                '</p>';
        }
        $forms = TLE_Form_Registry::discover();
        if (is_wp_error($forms)) { echo '<p>' . esc_html($forms->get_error_message()) . '</p></div>'; return; }
        echo '<p>Forms are discovered from the configured remote Gravity Forms REST API. Select a form to map participant, program, program group and cohort fields. New forms start disabled.</p><ul>';
        foreach ($forms as $form) {
            $url = add_query_arg(['page' => 'tle-evaluation-forms', 'form_id' => $form['form_id']], admin_url('options-general.php'));
            echo '<li><a href="' . esc_url($url) . '">' . esc_html($form['title'] . ' (ID ' . $form['form_id'] . ')') . '</a></li>';
        }
        echo '</ul>';
        if ($id) {
            $profile = TLE_Form_Registry::profile($id);
            if (is_wp_error($profile)) { echo '<p>' . esc_html($profile->get_error_message()) . '</p></div>'; return; }
            echo '<h2>' . esc_html($profile['title']) . '</h2><p>Exact question wording is matched against the supplied ELF document. Use question_overrides for changed wording, ambiguous matches or explicit scoring rules. Empty item_id excludes a field. Confirm the scale and averaging policy before marking scoring_confirmed true.</p>';
            echo '<form method="post">'; wp_nonce_field('tle_save_form_profile');
            echo '<input type="hidden" name="form_id" value="' . esc_attr($id) . '"><label for="profile_json">Form profile (JSON)</label><br><textarea id="profile_json" name="profile_json" rows="20" class="large-text code">' . esc_textarea(wp_json_encode(TLE_Form_Registry::configuration($id), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</textarea>';
            submit_button('Save form profile'); echo '</form>';
            echo '<h3>Field IDs and labels</h3><pre>' . esc_html(wp_json_encode($profile['available_fields'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            echo '<h3>Matched questions, scoring and coverage</h3><pre>' . esc_html(wp_json_encode(['questions' => $profile['questions'], 'missing_items' => $profile['missing_items'], 'ambiguous_fields' => $profile['ambiguous_fields'], 'issues' => $profile['issues']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            echo '<h3>Document catalog (item IDs for overrides)</h3><pre>' . esc_html(wp_json_encode(TLE_Form_Registry::catalog(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        }
        echo '</div>';
    }
}
add_action('admin_menu', ['TLE_Form_Setup', 'menu']);
