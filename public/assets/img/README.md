# Image Assets Directory

This directory contains static images used throughout the application.

## Required Placeholder Images

To complete the visual design, add the following placeholder images:

1. **logo.png** - Laboratory logo (transparent background, 200x60px recommended)
2. **esi-logo.png** - ESI University logo (transparent background)
3. **news-placeholder.jpg** - Default image for news/events (800x400px)
4. **event-placeholder.jpg** - Default image for events (800x400px)
5. **project-placeholder.jpg** - Default image for projects (400x300px)
6. **user-placeholder.jpg** - Default avatar for users (200x200px)

## Directory Structure

```
/img
├── logo.png                # Lab logo
├── esi-logo.png           # University logo
├── news-placeholder.jpg   # News default image
├── event-placeholder.jpg  # Event default image
├── project-placeholder.jpg # Project default image
└── user-placeholder.jpg   # User avatar default
```

## Image Guidelines

- **Format**: PNG for logos (transparency), JPG for photos
- **Size**: Optimize for web (keep under 200KB per image)
- **Aspect Ratio**: Maintain consistent ratios per category
- **Quality**: Balance between quality and file size

## Note

The application includes fallback behavior if images are missing:
- Missing logos will show text alternative
- Missing photos will show colored initials circles
- Broken images use `onerror` handler to gracefully degrade
