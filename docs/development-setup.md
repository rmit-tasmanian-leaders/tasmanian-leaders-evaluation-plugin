# Development Setup

## Overview

This document explains how to set up and run the Tasmanian Leaders Evaluation Plugin locally for development and testing.

The plugin is developed as a standalone WordPress plugin and is tested using a local WordPress environment.

## Requirements

Developers need:

- Git
- GitHub access to the project repository
- LocalWP or another local WordPress environment
- PHP supported by the local WordPress environment
- Composer
- A code editor such as Visual Studio Code

Composer is required because the plugin uses Dompdf for PDF generation.

## Repository

Repository:

https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin

Clone the repository:

```bash
git clone https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin.git
```

Enter the project directory:

```bash
cd tasmanian-leaders-evaluation-plugin
```

Install the PHP dependencies:

```bash
composer install
```

The `vendor` directory is generated locally by Composer and is not committed to Git.

## Repository Structure

```text
tasmanian-leaders-evaluation-plugin/
├── admin/
│   └── pdf-export.php
├── assets/
│   ├── css/
│   │   └── dashboard.css
│   └── js/
├── docs/
│   ├── development-setup.md
│   └── wordpress-integration-architecture.md
├── includes/
│   └── class-dashboard-shortcode.php
├── templates/
│   └── evaluation-dashboard.php
├── composer.json
├── composer.lock
├── tasmanian-leaders-evaluation.php
└── README.md
```

## Main Components

### tasmanian-leaders-evaluation.php

The main WordPress plugin bootstrap file. It loads the plugin functionality.

### admin/pdf-export.php

Contains the current WordPress Admin evaluation report and PDF export proof of concept.

### includes/class-dashboard-shortcode.php

Registers the front-end dashboard shortcode and handles the current login protection.

### templates/evaluation-dashboard.php

Contains the front-end evaluation dashboard template.

### assets/css/dashboard.css

Contains the styling for the front-end dashboard.

### docs/wordpress-integration-architecture.md

Documents the current WordPress integration architecture and future data integration approach.

## Local WordPress Setup

The current team development environment uses LocalWP.

Create or start a local WordPress site.

The Sprint 1 development site used for testing was:

```text
tasmanian-leaders-test
```

A LocalWP WordPress plugins directory is normally located under:

```text
app/public/wp-content/plugins/
```

## Windows Junction Setup

On Windows, the Git repository can be linked directly into the LocalWP plugins directory using a directory junction.

Example repository location:

```text
C:\Users\<username>\RMIT\tasmanian-leaders-evaluation-plugin
```

Example LocalWP plugin location:

```text
C:\Users\<username>\Local Sites\tasmanian-leaders-test\app\public\wp-content\plugins\tasmanian-leaders-evaluation-plugin
```

Example command:

```powershell
cmd /c mklink /J "C:\Users\<username>\Local Sites\tasmanian-leaders-test\app\public\wp-content\plugins\tasmanian-leaders-evaluation-plugin" "C:\Users\<username>\RMIT\tasmanian-leaders-evaluation-plugin"
```

The destination plugin folder should not already contain a separate copy of the plugin before the junction is created.

Using a junction means changes made inside the Git repository are immediately available to the local WordPress site.

## Plugin Activation

Start the LocalWP site.

Open WordPress Admin and go to:

```text
Plugins > Installed Plugins
```

Confirm that the following plugin appears:

```text
Tasmanian Leaders Evaluation Plugin
```

Activate the plugin.

The plugin should activate without PHP or WordPress errors.

## Front-End Dashboard

Create a normal WordPress page and add the shortcode:

```text
[tasmanian_leaders_evaluation_dashboard]
```

When logged into WordPress, the dashboard should display.

When logged out, evaluation data should not be displayed and the user should be asked to log in.

## Admin Report and PDF Export

After activating the plugin, the WordPress Admin area should contain:

```text
Evaluation Report
```

The current proof of concept displays sample evaluation report data and supports PDF export.

PDF generation uses Dompdf, which is installed through Composer.

## Verification Checklist

A successful development setup should confirm:

1. The plugin appears in WordPress.
2. The plugin activates without errors.
3. The front-end dashboard shortcode renders.
4. Logged-out users cannot view evaluation data.
5. The Evaluation Report Admin page loads.
6. PDF export completes successfully.
7. No JavaScript console errors appear during normal dashboard use.

## Development Workflow

Development should be completed on task-specific branches rather than directly on the shared integration branch.

Update the shared integration branch:

```bash
git fetch origin
git switch feature/plugin-structure
git pull origin feature/plugin-structure
```

Create a task-specific branch:

```bash
git switch -c feature/example-task
```

Check changes before committing:

```bash
git status
git diff
```

Stage the required files:

```bash
git add .
```

Commit the changes:

```bash
git commit -m "docs: update development setup"
```

Push the new branch:

```bash
git push -u origin feature/example-task
```

Changes should be tested locally before being submitted through a pull request.

## Sprint 2 Development Direction

The current plugin scaffold supports:

- WordPress plugin loading and activation
- Front-end dashboard rendering
- Basic authenticated access
- WordPress Admin reporting
- PDF export
- Separation of templates, assets and integration code

Sprint 2 development will begin connecting the plugin to real Gravity Forms evaluation data.

Gravity Forms-specific logic should remain separate from dashboard and reporting presentation logic so different form structures can be mapped into a consistent reporting format.
