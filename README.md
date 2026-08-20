# Tasmanian Leaders Evaluation Plugin

Evaluation reporting WordPress plugin developed for Tasmanian Leaders as part of RMIT Programming Project 1 - Team 55.

## Project Overview

The Tasmanian Leaders Evaluation Plugin is being developed as a self-contained WordPress plugin to support evaluation reporting for Tasmanian Leaders.

The system is intended to help authorised staff work with evaluation data and generate meaningful evaluation reports.

## Current Development Status

Sprint 1 - Design and Bootstrap

Current development work focuses on establishing the project repository, initial WordPress plugin structure, and team development workflow.

Functional dashboard and reporting features will be developed in later sprints.

## Initial Plugin Structure

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

## Requirements

Developers will require:

* Git
* GitHub access
* A local WordPress development environment
* PHP compatible with the agreed WordPress environment
* A code editor such as Visual Studio Code

Additional project requirements will be documented as development progresses.

## Getting Started

Clone the repository:

```bash
git clone https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin.git
```

Enter the project directory:

```bash
cd tasmanian-leaders-evaluation-plugin
```

Check the repository status:

```bash
git status
```

## Development Workflow

Development should not be performed directly on the `main` branch.

### 1. Update Main

Before beginning new work:

```bash
git checkout main
git pull origin main
```

### 2. Create a Feature Branch

Create a separate branch for your work:

```bash
git checkout -b feature/your-feature-name
```

Example:

```bash
git checkout -b feature/plugin-structure
```

### 3. Make and Test Changes

Complete development work locally and test changes before committing them.

Check changed files with:

```bash
git status
```

### 4. Stage Changes

```bash
git add .
```

### 5. Commit Changes

Use a clear commit message:

```bash
git commit -m "chore: describe your change"
```

Examples:

```text
chore: create initial WordPress plugin structure
docs: update development workflow
feat: add evaluation dashboard component
fix: resolve plugin activation issue
```

### 6. Push the Feature Branch

For the first push:

```bash
git push -u origin feature/your-feature-name
```

After the upstream branch has been configured:

```bash
git push
```

### 7. Review and Merge

Once development and testing are complete:

1. Push the completed feature branch.
2. Create a pull request.
3. Have another team member review and test the changes.
4. Address any identified issues.
5. Merge approved changes into `main` according to the team's agreed workflow.

## Branching Convention

```text
main
feature/*
fix/*
docs/*
```

`main` contains the stable project code.

`feature/*` is used for new development work.

`fix/*` is used for fixes.

`docs/*` is used for documentation-only changes.

## WordPress Plugin

The main plugin entry file is:

```text
tasmanian-leaders-evaluation.php
```

The initial plugin metadata identifies the project to WordPress as:

```text
Tasmanian Leaders Evaluation Plugin
```

The initial Sprint 1 structure provides the foundation for future development. Dashboard functionality, data integration, filtering, comparisons, visualisations, and report generation will be implemented as the project progresses.

## Team

Project: Evaluation Dashboard Project - Tasmanian Leaders

Organisation: Tasmanian Leaders

RMIT Programming Project 1

Team: Team 55

## Development Phase

Sprint 1 - Design and Bootstrap
