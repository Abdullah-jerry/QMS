# Company Video Setup

## Video Location
Place your company promotional video in this directory with one of these names:
- `company.mp4` (recommended)
- `company.webm` (alternative format)

## Video Specifications

### Recommended Settings:
- **Format:** MP4 (H.264 codec)
- **Resolution:** 1920x1080 (Full HD) or 1280x720 (HD)
- **Aspect Ratio:** 16:9
- **Duration:** 30 seconds to 2 minutes (loops automatically)
- **File Size:** Keep under 50MB for best performance

### Video Content Ideas:
- Company overview and services
- Customer testimonials
- Awards and achievements
- Facilities tour
- Product/service highlights
- Safety information
- Promotional offers

## How It Works
The video will:
- Play automatically on loop
- Be muted by default (won't interfere with audio announcements)
- Display on the left side of the screen
- Resize to fit the available space

## Fallback
If no video file is found, a placeholder message will be shown instead.

## Multiple Videos (Optional)
If you want to display multiple videos in rotation, you can modify the video element in:
`resources/views/display/index.blade.php`

To create a playlist, you would need custom JavaScript to switch between videos.
