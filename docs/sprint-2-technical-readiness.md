# Sprint 2 Technical Feasibility and Development Readiness Review

## 1. Purpose

This document records the technical feasibility review and development environment checks completed in preparation for Sprint 2 of the Tasmanian Leaders Evaluation Dashboard project.

The review builds on the existing WordPress plugin architecture, front-end dashboard integration, PDF export prototype, and the proposed Gravity Forms data integration approach.

The purpose is to confirm that the current technical approach is feasible, identify major implementation risks, verify the development environment, identify required dependencies, and document the technical prerequisites that should be addressed during Sprint 2.

---

## 2. Technical Architecture Review

The current WordPress plugin architecture was reviewed for suitability for continued Sprint 2 development.

The current implementation separates several major responsibilities:

- Main WordPress plugin bootstrap.
- WordPress Admin report and PDF export.
- Front-end dashboard shortcode.
- Front-end dashboard template.
- Dashboard styling.
- Composer-managed PDF dependencies.

The current front-end integration uses the shortcode:

[tasmanian_leaders_evaluation_dashboard]

This allows the evaluation dashboard to be embedded inside a normal WordPress page while keeping the reporting functionality within the plugin.

The shortcode integration has already demonstrated that:

- The dashboard can render on a normal WordPress page.
- Logged-in users can access the prototype dashboard.
- Logged-out users are prevented from viewing evaluation data.
- The existing WordPress Admin report continues to work.
- Existing PDF export functionality continues to work.

### Feasibility Result

The current WordPress plugin architecture is technically feasible for continued Sprint 2 development.

A redesign of the front-end integration is not currently required.

The main architectural requirement for Sprint 2 is the introduction of a shared evaluation data integration and normalisation layer between Gravity Forms and the dashboard/reporting components.

The proposed future architecture is:

Gravity Forms
    |
    v
GFAPI
    |
    v
Mapping / Normalisation Layer
    |
    v
Reporting / Data Service
    |
    +----> Front-End Dashboard
    |
    +----> PDF Export

This approach keeps Gravity Forms-specific data access separate from presentation and reporting logic.

---

## 3. Development Environment Verification

The local WordPress development environment was tested before Sprint 2 development.

### Environment

- WordPress local development environment
- PHP 8.5.9
- Composer 2.10.2
- Dompdf 3.1.6

### PHP Validation

PHP syntax validation was performed on:

- tasmanian-leaders-evaluation.php
- admin/pdf-export.php
- includes/class-dashboard-shortcode.php
- templates/evaluation-dashboard.php

All files passed PHP syntax validation without errors.

### Runtime Verification

The WordPress development environment was also tested manually.

The following functionality was confirmed:

- Tasmanian Leaders Evaluation Plugin activates successfully.
- Front-end evaluation dashboard loads for a logged-in user.
- Logged-out users cannot view evaluation data.
- WordPress Admin Evaluation Report loads successfully.
- PDF export continues to generate an accessible PDF.

### Result

The existing local development environment is operational and suitable for continued development of the current plugin architecture.

---

## 4. Required Dependencies

The current project uses Composer for PHP dependency management.

The current composer.json declares:

- dompdf/dompdf ^3.1

The installed Dompdf version verified during this review was:

- Dompdf 3.1.6

Dompdf provides the existing PDF report generation functionality.

The current project does not contain a separate Gravity Forms Composer dependency.

Future Gravity Forms integration is expected to use the Gravity Forms WordPress plugin and its GFAPI interface from within WordPress.

### Sprint 2 Environment Requirements

The expected technical environment for Gravity Forms integration includes:

- WordPress
- PHP
- Composer
- Dompdf
- Gravity Forms
- Representative evaluation forms and test data

The development team will require access to suitable Gravity Forms data before the complete production integration can be implemented and validated.

---

## 5. Current Gravity Forms Integration Status

A source-code search was completed for:

- GFAPI
- Gravity Forms
- gravityforms

Gravity Forms references currently exist only in the project architecture documentation.

No production Gravity Forms integration or GFAPI implementation currently exists in the plugin.

The current dashboard and PDF prototypes continue to use representative/sample evaluation data.

Therefore, Gravity Forms integration remains a Sprint 2 implementation requirement.

---

## 6. Major Implementation Risks

### 6.1 Gravity Forms Integration

The proposed architecture depends on Gravity Forms, but GFAPI integration has not yet been implemented.

Risk:

Development may be blocked or delayed if the team cannot access representative Gravity Forms forms and evaluation data.

Mitigation:

Confirm Gravity Forms availability and obtain representative test data before implementing the production data integration layer.

### 6.2 Different Form Structures and Field IDs

Evaluation stages may be stored in separate Gravity Forms forms with different field IDs and structures.

Risk:

Hard-coding Gravity Forms field IDs directly into dashboard or PDF code would create a fragile implementation.

Mitigation:

Use a dedicated mapping and normalisation layer between GFAPI and the reporting components.

### 6.3 Participant Matching

Evaluation results from different stages must be associated with the correct participant.

Risk:

An unreliable participant identifier could cause evaluation records belonging to different people or stages to be matched incorrectly.

Mitigation:

Confirm the participant identifier and matching rules before implementing cross-form comparisons.

### 6.4 Missing Evaluation Stages

Some participants may not have completed every evaluation stage.

Risk:

Reports could contain incomplete comparisons or produce errors if missing evaluation stages are not handled correctly.

Mitigation:

The normalisation and reporting layers should explicitly support missing evaluation stages and only display valid comparisons.

### 6.5 Access Control

The current front-end prototype uses is_user_logged_in().

Risk:

Any authenticated WordPress user can currently access the prototype dashboard.

Mitigation:

Confirm the required Tasmanian Leaders staff roles and WordPress capabilities with the client and implement capability-based access control before production use.

### 6.6 Dashboard and PDF Consistency

The dashboard and PDF export currently originate from prototype reporting functionality.

Risk:

Separate calculations or data-processing logic could cause the dashboard and exported PDF to display inconsistent results.

Mitigation:

Both interfaces should consume the same normalised reporting data and shared calculation logic.

### 6.7 Environment Compatibility

The current local environment uses PHP 8.5.9.

Risk:

Production WordPress, Gravity Forms, or another team member's environment may use different PHP or dependency versions.

Mitigation:

Confirm the supported PHP and WordPress versions for the target environment before relying on version-specific functionality.

---

## 7. Sprint 2 Technical Prerequisites

Before full Sprint 2 Gravity Forms/reporting implementation can be completed, the following technical prerequisites should be addressed:

1. Confirm access to the Gravity Forms environment.
2. Obtain representative evaluation forms and test data.
3. Confirm the production Gravity Forms form IDs and relevant field mappings.
4. Confirm the participant identifier used to associate evaluation stages.
5. Define the normalised evaluation data structure.
6. Define how missing evaluation stages will be represented.
7. Implement a shared Gravity Forms data-access and mapping layer.
8. Ensure the dashboard and PDF export consume the same normalised reporting data.
9. Confirm final WordPress user roles/capabilities for dashboard access.
10. Confirm compatibility between the development and target WordPress/PHP environments.

---

## 8. Sprint 2 Readiness Assessment

The existing WordPress plugin foundation is technically suitable for continued Sprint 2 development.

The following components have already been demonstrated successfully:

- WordPress plugin loading and activation.
- Front-end dashboard integration.
- Basic authentication protection.
- WordPress Admin reporting.
- PDF generation through Dompdf.
- Separation of dashboard template and styling.
- Local development environment.

The primary outstanding technical work is the implementation of the real evaluation data integration layer.

The most important dependencies for this work are access to representative Gravity Forms data, confirmed field mappings, and a reliable participant identifier.

---

## 9. Conclusion

The technical feasibility review found no major architectural blocker preventing Sprint 2 development.

The existing WordPress plugin, front-end shortcode integration and PDF export provide a suitable foundation for the next development stage.

Sprint 2 should focus on connecting the existing reporting interfaces to a shared, normalised evaluation data source rather than rebuilding the existing front-end integration.

The development environment has been verified as operational, the current dependencies have been identified, and the major technical risks and prerequisites for Sprint 2 have been documented.
