
# Tasmanian Leaders Evaluation Plugin

Evaluation reporting WordPress plugin developed for Tasmanian Leaders as part of RMIT Programming Project 1 - Team 55.

## Project Overview

The Tasmanian Leaders Evaluation Plugin is being developed as a self-contained WordPress plugin to support evaluation reporting for Tasmanian Leaders.

The system is intended to provide authorised Tasmanian Leaders staff with a structured way to work with evaluation data and generate meaningful evaluation reports.

## Current Development Status

**Sprint 1 - Design and Bootstrap**

Current development work focuses on:

- Establishing the Git repository
- Creating the initial WordPress plugin structure
- Verifying that WordPress recognises the plugin
- Establishing the team development workflow
- Documenting the local development and testing process

Functional dashboard and reporting features will be implemented in later development work.

## Initial Plugin Structure

The initial WordPress plugin structure is:

```text
tasmanian-leaders-evaluation-plugin/
|
|-- admin/
|
|-- assets/
|   |-- css/
|   `-- js/
|
|-- includes/
|
|-- templates/
|
|-- tasmanian-leaders-evaluation.php
`-- README.md
```

The directories provide the initial foundation for the plugin and allow functionality to be separated as the project develops.

## Development Requirements

Developers will require:

- Git
- GitHub account with appropriate repository permissions for contributing
- A local WordPress development environment
- PHP version supported by the project's WordPress development environment
- A code editor such as Visual Studio Code

Local is currently used for local WordPress development and testing.

Additional technical requirements will be documented as the project progresses.

## Getting Started

### 1. Clone the Repository

Clone the project repository:

```bash
git clone https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin.git
```

### 2. Enter the Project Directory

```bash
cd tasmanian-leaders-evaluation-plugin
```

### 3. Check Repository Status

```bash
git status
```

A successful setup should show that the local repository is connected to the project and has a clean working tree before development begins.

## Local WordPress Setup

The plugin should be tested in a local WordPress development environment before changes are submitted for review.

Local can be used to create and run the WordPress development environment.

### 1. Create a Local WordPress Site

Create a new WordPress site in Local.

For example:

```text
tasmanian-leaders-test
```

Start the local WordPress site before installing or testing the plugin.

### 2. Locate the WordPress Plugins Directory

The WordPress plugins directory is located inside:

```text
wp-content/plugins/
```

When using Local, the path will typically be located under:

```text
app/public/wp-content/plugins/
```

### 3. Install the Plugin

Place the project folder inside the WordPress plugins directory.

The resulting structure should look similar to:

```text
wp-content/
`-- plugins/
    `-- tasmanian-leaders-evaluation-plugin/
        |-- admin/
        |-- assets/
        |   |-- css/
        |   `-- js/
        |-- includes/
        |-- templates/
        |-- README.md
        `-- tasmanian-leaders-evaluation.php
```

Only the plugin project is maintained in this Git repository.

The complete local WordPress installation should not be committed to the project repository.

### 4. Verify WordPress Recognises the Plugin

Open WordPress Admin and navigate to:

```text
Plugins > Installed Plugins
```

Confirm that the following plugin appears:

```text
Tasmanian Leaders Evaluation Plugin
```

The initial plugin metadata should display:

```text
Plugin Name: Tasmanian Leaders Evaluation Plugin
Version: 0.1.0
Author: Team 55
```

### 5. Activate the Plugin

Select:

```text
Activate
```

The plugin should activate successfully without producing an error.

This confirms that the initial plugin structure is recognised and can be loaded by WordPress.

## Development Workflow

Development should not normally be performed directly on the `main` branch.

Developers should create separate branches for their work so changes can be tested and reviewed before being merged.

### 1. Update Main

Before starting new development work:

```bash
git checkout main
git pull origin main
```

### 2. Create a Feature Branch

Create a separate branch for the task being completed:

```bash
git checkout -b feature/your-feature-name
```

Example:

```bash
git checkout -b feature/plugin-structure
```

### 3. Make Changes

Complete the required development work on the feature branch.

Check the repository regularly with:

```bash
git status
```

Changes should be tested locally before being submitted for review.

### 4. Review Changes

Before committing, developers can review their changes using:

```bash
git diff
```

This helps confirm that only the intended files and changes are being committed.

### 5. Stage Changes

Stage the required changes:

```bash
git add .
```

Check the staged files:

```bash
git status
```

### 6. Commit Changes

Use clear and descriptive commit messages.

Example:

```bash
git commit -m "chore: create initial WordPress plugin structure"
```

Example commit messages include:

```text
chore: create initial WordPress plugin structure
docs: update development workflow
feat: add evaluation dashboard component
fix: resolve plugin activation issue
```

### 7. Push the Feature Branch

For the first push of a new branch:

```bash
git push -u origin feature/your-feature-name
```

After the upstream branch has been configured:

```bash
git push
```

### 8. Review and Test

Once development work is complete:

1. Push the completed feature branch to GitHub.
2. Create a pull request when the work is ready for integration.
3. Have another team member review and test the changes.
4. Address any identified issues.
5. Merge approved changes into `main` according to the team's agreed workflow.

## Branching Convention

The project uses separate branches for development work.

```text
main
feature/*
fix/*
docs/*
```

### Main Branch

```text
main
```

Contains the stable project code.

### Feature Branches

```text
feature/*
```

Used when developing new functionality or project components.

Example:

```text
feature/plugin-structure
```

### Fix Branches

```text
fix/*
```

Used for correcting identified problems.

### Documentation Branches

```text
docs/*
```

Can be used for documentation-only changes when appropriate.

## WordPress Plugin Entry File

The main WordPress plugin entry file is:

```text
tasmanian-leaders-evaluation.php
```

The initial file contains the WordPress plugin metadata required for WordPress to recognise the project as a plugin.

Current metadata identifies the plugin as:

```text
Tasmanian Leaders Evaluation Plugin
```

The initial plugin structure provides the foundation for future development.

## Sprint 1 Verification

The initial Sprint 1 development setup has been tested against the following requirements.

### Git Repository

The project repository has been created and successfully cloned into a local development environment.

A feature branch has also been successfully created and pushed to the remote repository.

### WordPress Plugin Structure

The initial WordPress plugin directory structure has been established.

### WordPress Recognition

The plugin has been installed in a local WordPress development environment and successfully appears under:

```text
Plugins > Installed Plugins
```

as:

```text
Tasmanian Leaders Evaluation Plugin
```

### Plugin Activation

The plugin has been successfully activated in the local WordPress environment without producing an activation error.

### Development Workflow

The Git branching, commit, push, review, and testing workflow is documented in this README.

### Team Repository Access

The repository has been configured for team development.

Team members should confirm that they can successfully access and clone the repository before this verification item is considered complete.

## Important Development Notes

- Do not commit the complete local WordPress installation to this repository.
- Only project/plugin files should be version controlled.
- Do not develop directly on `main` unless specifically agreed by the team.
- Create a feature branch for development work.
- Test changes locally before requesting review.
- Pull the latest `main` branch before beginning new work.
- Do not commit passwords, credentials, API keys, or other sensitive information.

## Future Development

The current Sprint 1 implementation establishes the technical foundation of the WordPress plugin.

Functional features such as evaluation data integration, dashboard functionality, filtering, comparisons, visualisations, and report generation will be developed as the project progresses.

## Team

**Project:** Evaluation Dashboard Project - Tasmanian Leaders

**Client:** Tasmanian Leaders

**Course:** RMIT Programming Project 1

**Team:** Team 55

## Development Phase

**Sprint 1 - Design and Bootstrap**
