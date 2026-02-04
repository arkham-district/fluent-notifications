# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-02-04

### Added
- Fluent notification builder with chainable API
- Support for `success()`, `error()`, `warning()`, and `info()` notification types
- Toast channel (session flash for frontend)
- Alert channel (session flash persistent)
- Full compatibility with Laravel's native `mail`, `database`, and `broadcast` channels
- `Notifiable` trait that extends Laravel's built-in trait with fluent API support
- Translation support with automatic `__()` helper integration
- Queue configuration support
- Configurable session keys for toast and alert channels
- Full backward compatibility with standard Laravel notifications
