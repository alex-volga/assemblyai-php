# Changelog

All notable changes to `alex-volga/assemblyai-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-11-06

### Added
- Migration to AssemblyAI's LLM-powered Speech Understanding architecture for Summarization and Auto Chapters.
- Added `SummaryEffort` enum (`low`, `medium`).
- Added `TranscriptParams::withSpeechUnderstanding()` for direct Speech Understanding configuration.
- Added `TranscriptParams::withSummarization(..., effort: ...)` with `effort` parameter support.
- Added `$speechUnderstanding` property to `Transcript` value object.
- Added `$text` property to `AutoChapter` value object and made `$gist` optional with fallback to `$headline`.

### Changed
- `TranscriptParams::withAutoChapters()` and `withSummarization()` now seamlessly output `speech_understanding.request.summarization` without deprecated top-level flags.
- Legacy `auto_chapters` and `summarization` parameters passed via `extra` are automatically migrated and removed from top-level payload.
- `Transcript::fromArray()` parses topic-based summaries from `speech_understanding.response.summarization` into `$transcript->chapters` and generates combined `$transcript->summary`.
- Deprecated `SummaryModel` enum and legacy `SummaryType` cases (`headline`, `gist`, `bullets_verbose`), automatically mapping them to modern types (`bullets` or `paragraph`).

## [1.1.0] - 2026-11-06

### Added
- Support for `speech_models` parameter with fallback list configuration via `TranscriptParams::withSpeechModels()`.
- Added `SpeechModel::Universal35Pro` (`universal-3-5-pro`) and `SpeechModel::Universal36ProPreview` (`universal-3-6-pro-preview`) enum cases.
- Added `speechModelUsed` and `speechModels` properties to `Transcript` value object.

### Changed
- Migrated API request serialization to use `speech_models` instead of deprecated `speech_model`.
- Preserved backward compatibility for `TranscriptParams::withSpeechModel()`, automatically mapping to the `speech_models` array.
- Automatic migration of legacy `speech_model` key in `extra` parameters.
- Updated `examples/transcribe.php` and `README.md` to use current models (`Universal35Pro` with `Universal2` fallback).

### Removed
- Removed deprecated Conformer models `SpeechModel::Best` (`best`) and `SpeechModel::Nano` (`nano`) which are no longer supported by the AssemblyAI API.

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
