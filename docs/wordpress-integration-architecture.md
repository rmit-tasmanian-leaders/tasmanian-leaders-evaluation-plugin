
# WordPress Plugin Architecture and Integration Approach

## 1. Purpose

This document investigates the technical architecture and integration approach for the Tasmanian Leaders Evaluation Plugin.

The purpose of this investigation is to determine how the self-contained WordPress evaluation reporting plugin should interact with WordPress, evaluation data, and future reporting functionality.

The investigation also considers different WordPress integration approaches and identifies the recommended approach for the project.

---

## 2. Current Plugin Architecture

The existing Tasmanian Leaders Evaluation Plugin was reviewed before developing the proposed integration architecture.

The current plugin structure includes:

```text
tasmanian-leaders-evaluation-plugin/
|
|-- admin/
|   `-- pdf-export.php
|
|-- assets/
|   |-- css/
|   `-- js/
|
|-- includes/
|
|-- templates/
|
|-- composer.json
|-- composer.lock
|-- README.md
`-- tasmanian-leaders-evaluation.php
```

The main plugin entry file is:

```text
tasmanian-leaders-evaluation.php
```

This file contains the WordPress plugin metadata, prevents direct access outside WordPress, and loads the required plugin functionality.

The existing evaluation report proof of concept is primarily implemented in:

```text
admin/pdf-export.php
```

The current implementation provides:

- A WordPress Admin Evaluation Report page.
- Sample evaluation data.
- Evaluation report preview functionality.
- ELF report sections including Insight, Influence, and Impact.
- PDF report generation using Dompdf.
- PDF export functionality.

The existing implementation demonstrates that the plugin can successfully generate and export ELF evaluation reports.

However, the current proof of concept places several responsibilities, including sample data, interface rendering, styling, JavaScript, and PDF generation, within the same reporting component.

For future development, these responsibilities should be separated so that the reporting functionality is easier to maintain and can support a front-end website interface.

---

## 3. Integration Approaches Investigated

Three possible WordPress integration approaches were investigated.

### 3.1 Approach 1 - WordPress Admin Interface

The existing proof of concept uses the WordPress Admin interface.

The plugin currently registers an Evaluation Report page through the WordPress `admin_menu` hook.

The current flow is:

```text
WordPress Admin
      |
      v
Evaluation Report
      |
      v
Report Preview
      |
      v
PDF Export
```

### Advantages

- Already implemented and working.
- Provides access through the existing WordPress administration environment.
- WordPress capabilities can be used to control access.
- Suitable for administrative configuration and management functionality.

### Limitations

- Staff must work inside the WordPress Admin interface.
- The reporting experience is separated from the normal Tasmanian Leaders website.
- The approach is not the preferred user experience for the intended reporting interface.

For these reasons, the WordPress Admin-only approach is not recommended as the primary interface for the future evaluation dashboard.

---

### 3.2 Approach 2 - Front-End WordPress Shortcode

A front-end shortcode integration was investigated and implemented as a proof of concept.

The shortcode created for the prototype is:

```text
[tasmanian_leaders_evaluation_dashboard]
```

The shortcode allows the Tasmanian Leaders Evaluation Plugin to render dashboard content inside a normal WordPress website page.

The tested integration flow is:

```text
Tasmanian Leaders WordPress Website
              |
              v
Protected WordPress Page
              |
              v
Plugin Shortcode
              |
              v
Evaluation Dashboard
```

The proof of concept successfully demonstrated that:

- The plugin dashboard can be embedded inside a normal WordPress page.
- The dashboard does not need to operate exclusively through WordPress Admin.
- Logged-in WordPress users can access the prototype dashboard.
- Logged-out users are prevented from viewing the evaluation data.
- The existing WordPress Admin Evaluation Report continues to operate.
- The existing PDF export functionality continues to operate.

### Advantages

- Can be embedded directly into a normal WordPress page.
- Keeps the reporting functionality within the self-contained plugin.
- Does not require modification of the active WordPress theme.
- Provides flexibility over where the dashboard appears.
- Can be placed on a protected staff page.
- Provides a simple integration method for the initial implementation.
- Allows the plugin to control the reporting functionality while WordPress controls the surrounding website layout.

This is the recommended integration approach for the current project direction.

---

### 3.3 Approach 3 - Custom Gutenberg Block

A custom Gutenberg block was also considered as a possible future integration approach.

A Gutenberg block could allow WordPress editors to insert the evaluation dashboard directly through the WordPress block editor.

### Advantages

- Provides a native WordPress editor experience.
- Allows flexible placement within WordPress pages.
- Could provide additional dashboard configuration through the block editor.

### Limitations

- Requires additional development compared with a shortcode.
- Introduces additional JavaScript and WordPress block-development requirements.
- Is not required to demonstrate the current integration architecture.

A custom Gutenberg block could be investigated in future development if the client requires greater control through the WordPress editor.

---

## 4. Recommended Architecture

The recommended architecture uses a protected WordPress website page containing a shortcode provided by the Tasmanian Leaders Evaluation Plugin.

The proposed architecture is:

```text
Tasmanian Leaders WordPress Website
              |
              v
Protected Staff Page
              |
              v
Evaluation Dashboard Shortcode
              |
              v
Front-End Dashboard Template
              |
              v
Evaluation Data Service
              |
              v
Reporting Service
          /         \
         v           v
Web Dashboard     PDF Export
```

Future evaluation data sources would connect through the evaluation data service:

```text
Gravity Forms ------------------\
                                 \
                                  > Evaluation Data Service
                                 /
Harmonised Historical Data -----/
                                  |
                                  v
                          Normalised Data
                                  |
                                  v
                           Reporting Service
                            /             \
                           v               v
                    Web Dashboard      PDF Export
```

This architecture separates the website presentation from the source of the evaluation data.

The dashboard should not need to know whether evaluation information originally came from Gravity Forms, harmonised historical data, or another future data source.

This separation will make it easier to maintain the plugin and support additional data sources in the future.

---

## 5. Main Plugin Components

### 5.1 Main Plugin Bootstrap

File:

```text
tasmanian-leaders-evaluation.php
```

Responsibilities:

- Defines the WordPress plugin.
- Contains the plugin metadata.
- Prevents direct access to plugin code.
- Loads the required plugin components.
- Acts as the main entry point for the plugin.

### 5.2 Dashboard Shortcode

File:

```text
includes/class-dashboard-shortcode.php
```

Responsibilities:

- Registers the front-end dashboard shortcode.
- Checks whether the user is authenticated.
- Loads the required front-end dashboard stylesheet.
- Loads the dashboard template.
- Returns the generated dashboard content to WordPress.

The shortcode used by the prototype is:

```text
[tasmanian_leaders_evaluation_dashboard]
```

### 5.3 Dashboard Template

File:

```text
templates/evaluation-dashboard.php
```

Responsibilities:

- Provides the front-end dashboard presentation.
- Displays report configuration information.
- Displays prototype evaluation results.
- Uses escaped output when displaying evaluation information.
- Keeps presentation separate from the shortcode integration logic.

The current template uses prototype evaluation data only.

### 5.4 Dashboard Styles

File:

```text
assets/css/dashboard.css
```

Responsibilities:

- Provides front-end dashboard styling.
- Provides responsive layout behaviour.
- Keeps styling separate from PHP logic.
- Uses plugin-specific `tle-` CSS classes to reduce conflicts with WordPress themes.
- Allows the dashboard to operate without depending on WordPress Admin styling.

### 5.5 Evaluation Data Service - Future Component

A future evaluation data service should be responsible for retrieving and normalising evaluation information.

Potential data sources include:

- Gravity Forms.
- Harmonised historical evaluation data.
- Potential future evaluation data sources.

The data service should provide a consistent data structure to the reporting layer.

This means that the dashboard and reporting functionality should not need to know exactly where the original evaluation data was stored.

### 5.6 Reporting Service - Future Component

A future reporting service should contain reusable evaluation reporting logic.

Both the website dashboard and PDF export should use the same reporting logic where possible.

This will reduce duplication and help ensure that the website dashboard and exported PDF use consistent evaluation information.

### 5.7 PDF Export

The existing PDF export functionality is currently implemented in:

```text
admin/pdf-export.php
```

The existing PDF export proof of concept is working and was tested successfully.

Future development should consider separating reusable PDF and reporting logic from the WordPress Admin-specific interface.

This would allow both the website dashboard and PDF export functionality to use shared reporting and evaluation data services.

---

## 6. Data Integration Approach

The recommended data architecture introduces an evaluation data service between the reporting interface and the underlying evaluation data sources.

Instead of allowing dashboard templates or PDF code to directly depend on a specific data source, the evaluation data service should retrieve and transform evaluation information into a consistent format.

The intended data flow is:

```text
Data Source
    |
    v
Evaluation Data Service
    |
    v
Normalised Evaluation Data
    |
    v
Reporting Service
    |
    +------> Website Dashboard
    |
    `------> PDF Export
```

Potential data sources include:

- Gravity Forms.
- Harmonised Historical Dataset.

The evaluation data service would provide a consistent interface between these data sources and the reporting functionality.

This approach reduces tight coupling between the dashboard and a specific data source.

It also provides flexibility if additional evaluation data sources are introduced later.

Real Gravity Forms and harmonised historical data integration are outside the scope of the current proof of concept and require further investigation and development.

---

## 7. WordPress Integration Requirements

The proposed architecture requires the following WordPress integration functionality:

- Standard WordPress plugin bootstrap.
- WordPress shortcode registration.
- Front-end WordPress page embedding.
- Front-end CSS loading.
- Future front-end JavaScript loading where required.
- WordPress user authentication.
- Future WordPress role and capability checks.
- Secure handling of user input.
- Escaping of displayed evaluation data.
- WordPress nonces for state-changing actions.
- Protection against direct access to PHP files.
- Integration with future evaluation data sources.
- PDF generation and export.
- Plugin-controlled templates.
- Composer dependency management for Dompdf.

The plugin should remain self-contained and should not require changes to WordPress core.

The front-end dashboard should also avoid depending directly on a specific WordPress theme so that the dashboard can continue functioning if the website theme changes.

---

## 8. Access Control and Security

Evaluation information should not be publicly accessible.

The current proof of concept uses a basic WordPress authentication check.

The current prototype behaviour is:

```text
Logged-in WordPress User
          |
          v
Dashboard Displayed
```

For logged-out users:

```text
Logged-out Visitor
          |
          v
Evaluation Data Hidden
          |
          v
Login-Required Message Displayed
```

This behaviour was tested successfully in the local WordPress development environment.

When a logged-in user accessed the page, the evaluation dashboard and prototype evaluation information were displayed.

When the same page was opened in a private browser session without being logged into WordPress, the evaluation information was hidden and the following message was displayed:

```text
Please log in to access the evaluation dashboard.
```

The final implementation should use WordPress roles and capabilities appropriate to authorised Tasmanian Leaders staff.

The exact access roles and permissions should be confirmed with the client before production implementation.

Future security requirements should include:

- Authentication.
- Role and capability authorisation.
- Input sanitisation.
- Output escaping.
- WordPress nonces where appropriate.
- Protection against direct PHP file access.
- Appropriate handling of evaluation information.
- Prevention of unnecessary public exposure of evaluation data.

---

## 9. Proof-of-Concept Implementation

The front-end shortcode approach was implemented and tested in a local WordPress development environment.

The prototype added the following components:

```text
includes/class-dashboard-shortcode.php
templates/evaluation-dashboard.php
assets/css/dashboard.css
```

The main plugin bootstrap file was also updated to load the new shortcode component.

The prototype uses sample evaluation data to demonstrate the website-embedded architecture.

The prototype displays:

- Program.
- Cohort.
- Evaluation point.
- Comparison.
- ELF Insight capability results.
- Pre-program scores.
- Completion scores.
- 3-Month Delay scores.
- Change values.

The following functionality was successfully tested:

- Existing plugin activation.
- Existing WordPress Admin Evaluation Report.
- Existing PDF export.
- Front-end shortcode registration.
- Dashboard rendering on a normal WordPress website page.
- Display of prototype evaluation information.
- Logged-in access to the dashboard.
- Prevention of evaluation-data display to logged-out visitors.

The existing WordPress Admin Evaluation Report and PDF export functionality continued to work after the front-end integration was introduced.

The proof of concept therefore demonstrates that the plugin can provide evaluation reporting functionality through the WordPress website rather than requiring the reporting interface to remain exclusively inside WordPress Admin.

---

## 10. Prototype Scope and Limitations

The current implementation is an architecture and integration proof of concept.

It does not represent the completed production evaluation dashboard.

The prototype does not currently implement:

- Production Gravity Forms integration.
- Harmonised historical dataset integration.
- Final client-defined WordPress roles and capabilities.
- Complete dashboard filtering.
- Complete comparison functionality.
- Production evaluation data processing.
- Final Tasmanian Leaders website branding.
- Final client-approved user interface.
- Complete reporting workflow.

These components should be implemented during future development after the relevant requirements, data structures, and client expectations have been confirmed.

---

## 11. Integration Approach Recommendation

Based on the investigation and proof-of-concept testing, the front-end WordPress shortcode approach is recommended for the current project direction.

The recommended approach allows the evaluation dashboard to be embedded into a normal WordPress website page while keeping the reporting functionality within the self-contained Tasmanian Leaders Evaluation Plugin.

The shortcode approach also allows the team to:

- Keep the plugin independent from the active WordPress theme.
- Protect evaluation information from unauthorised visitors.
- Reuse WordPress authentication.
- Continue using the existing PDF export functionality.
- Introduce future evaluation data services without redesigning the website integration.
- Replace prototype data with real evaluation data when the data integration work is completed.

The existing WordPress Admin interface can remain available for administrative or development functionality where required, but it is not recommended as the primary reporting interface.

A custom Gutenberg block may be considered in the future if additional WordPress editor integration is required.

---

## 12. Recommended Next Steps

The recommended next steps are:

1. Review the proposed architecture with the development team.
2. Demonstrate the front-end embedded dashboard proof of concept to the Project Manager.
3. Demonstrate the website-embedded approach to the client.
4. Confirm whether the shortcode-based website integration meets the client's expected workflow.
5. Confirm the final WordPress user roles and access requirements.
6. Complete the investigation of Gravity Forms and the harmonised historical dataset.
7. Define the normalised evaluation data structure.
8. Introduce the evaluation data service.
9. Introduce reusable reporting logic where required.
10. Connect the dashboard and PDF export to the shared reporting and data architecture.
11. Replace prototype evaluation data with integrated evaluation data when the required data sources are available.
12. Apply the final client-approved interface and branding.

---

## 13. Development Team Review

Architecture review status:

Pending development team review.

Review comments:

To be completed after the proposed architecture and front-end integration proof of concept have been reviewed by the development team.

Client review status:

Pending client demonstration and feedback.

Client feedback:

To be recorded after the website-embedded dashboard approach has been demonstrated to the client.

---

## 14. Conclusion

The architecture investigation confirmed that the Tasmanian Leaders Evaluation Plugin can support a front-end website-embedded evaluation dashboard while remaining a self-contained WordPress plugin.

The existing WordPress Admin reporting and PDF export proof of concept remains functional, while the new shortcode proof of concept demonstrates that evaluation reporting content can also be presented through a normal WordPress website page.

The recommended architecture separates the front-end presentation, evaluation data access, reporting logic, and PDF generation responsibilities.

This provides a foundation for future Gravity Forms integration, harmonised historical data integration, dashboard functionality, evaluation comparisons, and report generation.

The front-end shortcode approach is therefore recommended as the current WordPress integration approach, subject to development team review and client feedback.
