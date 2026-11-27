# Changelog

## 2.0.0

- Select API v1 for legacy options and API v2 for shared endpoints and new capture options.
- Support v2 synchronous captures, device discovery, and app management.

## 1.32.0

- Add `screenshot_pdf` and `screenshot_pdf_file` for PDF downloads.
- Add `screenshot_video` and `screenshot_video_file` for WebM, MP4, and GIF downloads.
- Use the existing capture parameter arrays for API 1.32 options and native country instance IDs.
- Add offline download tests and live checks for owned captures. Run the suite with PHPUnit 9.6 and provide live credentials through `BROWSHOT_API_KEY`.
- Declare the existing client properties and remove an unused default from the thumbnail file method to avoid PHP 8 deprecation notices.
