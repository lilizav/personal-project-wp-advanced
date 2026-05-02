# Travel Bucket List Theme

A clean, visual, and interactive WordPress theme for managing travel bucket lists.

## Features

- **Country Cards**: Display destinations with hover effects
- **Visited vs Dream Toggle**: Filter between visited and dream destinations
- **Map Section**: Static world map display (placeholder for interactive map)
- **Gallery-based Layout**: Grid layout for visual browsing
- **Travel Journal Posts**: Detailed posts for travel experiences

## Installation

1. Download or clone this theme into your `wp-content/themes/` directory
2. Activate the theme in the WordPress admin panel
3. Create posts for destinations, assign categories "Visited" or "Dream"
4. Add featured images to posts for the gallery
5. Use the home page to view the gallery and toggle filters

## Adding Photos to Your Theme

### 1. Featured Images for Country Cards (Main Gallery)
These are the most important! Featured images show on the gallery homepage.

**How to add:**
1. In WordPress admin, go to **Posts → Add New**
2. Create a post for a destination (e.g., "Paris")
3. On the right side, find **Featured Image** section
4. Click "Set featured image"
5. Upload or select a photo of the destination
6. Assign category "Visited" or "Dream"
7. Publish!

**Photo specs:**
- Recommended size: 800x600px or larger
- Format: JPG, PNG
- File size: Under 500KB for best performance

### 2. Gallery/Content Images in Journal Posts
Add multiple photos within the post content itself.

**How to add:**
1. While editing a post, click the **Add block** button
2. Search for **Image** or **Gallery** block
3. Upload or select photos
4. Arrange them as needed in the post

### 3. World Map (Static Asset)
For the map section on the homepage.

**How to add:**
1. Create or download a world map image
2. Name it `world-map.png`
3. Place it in the theme's `/images/` folder:
   - Path: `wp-content/themes/personal-project-wp-advanced/images/world-map.png`
4. The theme will automatically display it

**Where to get a world map:**
- Free options: Unsplash, Pexels, Pixabay
- SVG maps: Find at wikimedia.org or create your own

## Usage

- Create new posts for each destination
- Assign category "Visited" for places you've been, "Dream" for places you want to go
- Upload featured images for visual appeal in the gallery
- Write detailed journal entries in the post content
- Add additional images to posts for rich storytelling

## Customization

- Modify `style.css` for visual changes
- Edit `functions.php` to add custom functionality
- Update templates in the theme root for layout changes
- Edit `js/script.js` to add new interactive features

## Templates

- `index.php`: Main gallery page with country cards
- `single.php`: Individual travel journal post
- `header.php`: Site header and navigation
- `footer.php`: Site footer

## JavaScript

Interactive features are handled in `js/script.js`, including:
- "Visited vs Dream" toggle filtering
- Country card hover effects

## File Structure

```
personal-project-wp-advanced/
├── style.css           # Main theme styles
├── functions.php       # Theme functions and setup
├── header.php          # Header template
├── footer.php          # Footer template
├── index.php           # Gallery/home page
├── single.php          # Individual post template
├── js/
│   └── script.js       # JavaScript functionality
├── images/             # Theme static images
│   └── world-map.png   # World map placeholder
└── README.md           # This file
```

## Tips for Best Results

1. **Consistent Photos**: Use destination photos with consistent quality and style
2. **Descriptive Titles**: Name your posts with destination names (e.g., "Paris, France")
3. **Rich Descriptions**: Write detailed journal entries to complement the photos
4. **Regular Updates**: Keep adding new destinations to keep the gallery fresh
5. **Image Optimization**: Optimize images before uploading for faster page load

## Development

This theme is built with standard WordPress practices and can be extended with additional plugins for enhanced functionality like:
- Advanced Custom Fields (for additional post metadata)
- Elementor (for custom page layouts)
- Leaflet Map (for interactive maps)