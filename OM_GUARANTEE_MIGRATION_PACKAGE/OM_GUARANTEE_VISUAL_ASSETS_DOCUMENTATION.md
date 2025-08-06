# OM Guarantee for WooCommerce - Visual Assets & UI Documentation

**Version:** 3.0.8  
**Last Updated:** August 2025  
**Visual Design Documentation for Migration to Cursor**

---

## Brand Assets

### Official OM Guarantee Logos

#### 1. Primary Logo (Horizontal)
**File:** `newOMGincLogo2022HorizontalWEB.jpg`  
**Dimensions:** 800x300px  
**Format:** JPG  
**Usage:** Main branding, headers, admin interface  
**Background:** Transparent/White  

#### 2. Primary Logo (PNG Version)
**File:** `newOMGincLogo2022Horizontal.png`  
**Dimensions:** 800x300px  
**Format:** PNG with transparency  
**Usage:** When transparency needed  

### Certification Badges

#### 1. Main Certification Badge
**File:** `OMGcertificate2022.png`  
**Dimensions:** 366x366px  
**Format:** PNG with transparency  
**Usage:** Shortcodes, certification displays  
**Design:** Circular badge with OM Guarantee branding  

#### 2. Inverted Certification Badge
**File:** `OMGcertificate2022INVERTED-366.png`  
**Dimensions:** 366x366px  
**Format:** PNG with transparency  
**Usage:** Dark backgrounds, alternative styling  

#### 3. White Full Certification Badge
**File:** `OMGcertificate2022WhiteFULL.png`  
**Dimensions:** 500x500px  
**Format:** PNG with transparency  
**Usage:** Large displays, hero sections  

#### 4. Alternative Badge Design
**File:** `OMGbadge.png`  
**Dimensions:** 300x300px  
**Format:** PNG with transparency  
**Usage:** Compact displays, sidebar widgets  

---

## UI Screenshots & Development Progress

### Admin Interface Screenshots

#### 1. Main Dashboard (Current State)
**File:** `Dash.png`  
**Description:** Main admin dashboard showing settings, API status, and shortcode instructions  
**Issues Documented:** Logo display, padding inconsistencies  
**Status:** Needs design improvements  

#### 2. Registration Page Issues
**Files:** `reggg.png`, `qwq.png`, `okk.png`  
**Description:** Screenshots showing registration page layout problems  
**Issues:** Missing headers, poor padding, logo distortion  
**Resolution:** Fixed in v3.0.8  

#### 3. Settings Page Problems
**Files:** `issuesss.png`, `zxcc.png`, `sets.png`  
**Description:** Settings page layout and logo display issues  
**Issues:** Missing logo in header, inconsistent spacing  
**Resolution:** Addressed with CSS fixes  

#### 4. Impact Report Page
**File:** `asdsda.png`  
**Description:** Impact reporting interface  
**Features:** Metrics display, blockchain verification links  
**Status:** Functional, needs visual polish  

### Frontend Display Screenshots

#### 1. Shortcode Display Issues
**Files:** `nooo.png`, `whte.png`, `rep.png`  
**Description:** Screenshots showing shortcode rendering problems  
**Issues:** Oversized certification badge, poor layout  
**Priority:** High - needs complete redesign  

#### 2. Website Integration Examples
**Files:** `OMREG.png`, `heads.png`  
**Description:** Examples of plugin integration on live websites  
**Reference:** Shows how shortcodes appear in real-world usage  

### Design Evolution Screenshots

#### 1. Layout Improvements
**Files:** `better.png`, `new.png`, `lier.png`  
**Description:** Progressive improvements to admin interface  
**Shows:** Evolution of design fixes and enhancements  

#### 2. Problem Documentation
**Files:** `boxes.png`, `terr.png`, `boooo.png`, `yes.png`  
**Description:** Screenshots documenting specific UI problems  
**Purpose:** Reference for what NOT to do in redesign  

---

## Brand Guidelines

### Color Palette
```css
/* Primary Colors */
--omg-primary-blue: #3A8CCB;
--omg-secondary-blue: #2E7AB8;
--omg-accent-blue: #4A9CD9;

/* Supporting Colors */
--omg-success-green: #28a745;
--omg-warning-orange: #ffc107;
--omg-danger-red: #dc3545;

/* Neutral Colors */
--omg-dark-gray: #333333;
--omg-medium-gray: #666666;
--omg-light-gray: #f8f9fa;
--omg-border-gray: #dee2e6;

/* Background Colors */
--omg-white: #ffffff;
--omg-off-white: #f8f9fa;
--omg-blue-gradient: linear-gradient(135deg, #3A8CCB, #28a745);
```

### Typography Guidelines
```css
/* Font Families */
--omg-primary-font: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
--omg-secondary-font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
--omg-monospace-font: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;

/* Font Sizes */
--omg-font-xs: 12px;
--omg-font-sm: 14px;
--omg-font-base: 16px;
--omg-font-lg: 18px;
--omg-font-xl: 20px;
--omg-font-2xl: 24px;
--omg-font-3xl: 30px;

/* Font Weights */
--omg-font-light: 300;
--omg-font-normal: 400;
--omg-font-medium: 500;
--omg-font-semibold: 600;
--omg-font-bold: 700;
```

### Spacing System
```css
/* Spacing Scale */
--omg-space-xs: 4px;
--omg-space-sm: 8px;
--omg-space-md: 16px;
--omg-space-lg: 24px;
--omg-space-xl: 32px;
--omg-space-2xl: 48px;
--omg-space-3xl: 64px;

/* Border Radius */
--omg-radius-sm: 4px;
--omg-radius-md: 8px;
--omg-radius-lg: 12px;
--omg-radius-xl: 16px;
--omg-radius-full: 50%;

/* Shadows */
--omg-shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
--omg-shadow-md: 0 4px 12px rgba(0,0,0,0.1);
--omg-shadow-lg: 0 8px 24px rgba(0,0,0,0.1);
--omg-shadow-xl: 0 16px 48px rgba(0,0,0,0.15);
```

---

## UI Component Specifications

### Admin Interface Components

#### 1. Header Component
**Requirements:**
- OM Guarantee logo (60px height)
- Centered layout
- Blue gradient background (#3A8CCB to #2E7AB8)
- White text and logo
- Consistent across all admin pages

#### 2. Card Component
**Requirements:**
- White background
- Border radius: 12px
- Padding: 24px
- Box shadow: 0 4px 12px rgba(0,0,0,0.1)
- Border: 1px solid #dee2e6

#### 3. Button Components
```css
/* Primary Button */
.omg-button-primary {
    background: #3A8CCB;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.omg-button-primary:hover {
    background: #2E7AB8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(58, 140, 203, 0.3);
}

/* Secondary Button */
.omg-button-secondary {
    background: transparent;
    color: #3A8CCB;
    border: 2px solid #3A8CCB;
    padding: 10px 22px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.omg-button-secondary:hover {
    background: #3A8CCB;
    color: white;
}
```

### Frontend Shortcode Components

#### 1. Certification Badge (Compact)
**Design Requirements:**
- Maximum width: 400px
- Logo size: 60px (same as Gopals)
- Horizontal layout (logo + text)
- Professional, trustworthy appearance
- Subtle border and shadow
- Mobile responsive

**Reference:** Gopals Health Foods footer badge

#### 2. Impact Dashboard (Professional)
**Design Requirements:**
- Maximum width: 600px
- Clean metric cards with color-coded borders
- Professional typography hierarchy
- Responsive grid layout
- Clear visual separation of sections

#### 3. Impact Summary (Compact)
**Design Requirements:**
- Maximum width: 400px
- Side-by-side metrics layout
- Clean, scannable format
- Appropriate for sidebars

---

## Design Problems to Solve

### Critical Issues (High Priority)

#### 1. Shortcode Oversizing
**Problem:** Certification badge and dashboard are too large
**Current:** Badge takes full width, overwhelming
**Solution Needed:** Compact, professional sizing like Gopals
**Reference Files:** `nooo.png`, `whte.png`

#### 2. Logo Display Issues
**Problem:** Logo appears as white circle or distorted
**Current:** CSS filter issues, aspect ratio problems
**Solution Needed:** Proper logo display with correct proportions
**Reference Files:** `reggg.png`, `issuesss.png`

#### 3. Inconsistent Padding
**Problem:** Uneven spacing throughout admin interface
**Current:** Content too close to edges, inconsistent gaps
**Solution Needed:** Standardized spacing system
**Reference Files:** `qwq.png`, `okk.png`

### Medium Priority Issues

#### 4. Mobile Responsiveness
**Problem:** Admin interface not optimized for tablets
**Solution Needed:** Responsive design improvements

#### 5. Visual Hierarchy
**Problem:** Poor information organization
**Solution Needed:** Clear typography hierarchy, better spacing

#### 6. Theme Compatibility
**Problem:** Plugin styles may conflict with WordPress themes
**Solution Needed:** More specific CSS selectors, !important declarations

---

## Canva Design Assets (To Be Created)

### Recommended Canva Templates Needed

#### 1. Shortcode Mockups
- Certification badge variations (3 sizes)
- Impact dashboard layouts (2 variations)
- Impact summary designs (compact format)
- Donation counter designs (eye-catching)
- Charity list layouts (professional)

#### 2. Admin Interface Mockups
- Header design (consistent across pages)
- Card component designs
- Form layouts and styling
- Button variations and states

#### 3. Brand Guidelines Document
- Logo usage guidelines
- Color palette with hex codes
- Typography specifications
- Spacing and layout guidelines

#### 4. Reference Comparisons
- Gopals Health Foods badge analysis
- Professional certification badge examples
- WordPress admin interface best practices

---

## Asset Organization for Migration

### Required File Structure for Cursor
```
visual-assets/
├── brand/
│   ├── logos/
│   │   ├── omg-logo-horizontal.jpg
│   │   ├── omg-logo-horizontal.png
│   │   └── omg-logo-variations/
│   ├── badges/
│   │   ├── certification-badge-main.png
│   │   ├── certification-badge-inverted.png
│   │   └── certification-badge-white.png
│   └── guidelines/
│       ├── brand-guidelines.pdf
│       └── color-palette.css
├── ui-screenshots/
│   ├── admin-interface/
│   │   ├── current-dashboard.png
│   │   ├── settings-page.png
│   │   └── registration-page.png
│   ├── frontend-displays/
│   │   ├── shortcode-examples.png
│   │   └── website-integration.png
│   └── issues-documentation/
│       ├── layout-problems.png
│       └── design-issues.png
├── mockups/ (from Canva)
│   ├── shortcode-designs/
│   ├── admin-interface-designs/
│   └── component-specifications/
└── reference/
    ├── gopals-badge-reference.png
    └── competitor-analysis/
```

*This visual documentation provides the complete picture of current assets, design problems, and requirements for the Cursor development team to create professional, production-ready designs.*

