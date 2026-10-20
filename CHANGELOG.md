# Changelog

All notable changes to `alex-volga/assemblyai-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-20

### Added
- Modern PHP 8.3+ SDK architecture for AssemblyAI API.
- PSR-7, PSR-17, and PSR-18 compliant HTTP layer with zero mandatory client lock-in (bundled with Guzzle 7).
- Full Transcripts API support (upload, create, poll, get, delete, word search, sentences, paragraphs).
- Export subtitles to SRT and VTT formats.
- Speaker Diarization and Audio Intelligence features (Auto Chapters, Sentiment Analysis, Entity Detection, Summarization, Content Safety).
- PII Redaction & Redacted Audio support.
- LeMUR integration (Summary, Q&A, Action Items, Custom Tasks).
- Real-time temporary WebSocket token generation.
- Complete unit and feature test suite with 100% mocked HTTP isolation.
- Static analysis with PHPStan at level 8.
- GitHub Actions CI workflow for PHP 8.3 and 8.4.
