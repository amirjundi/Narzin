# Narzin E-Commerce Design System (DESIGN.md)

This document establishes the official visual identity, color palette, design tokens, and components for the Narzin Mobile Applications. It is inspired by the modern, premium aesthetic of the Narzin branding and curated traditional/modern Kurdish and Yazidi fashion targeting users in Germany and Europe.

---

## 1. Brand Identity & Theme

Narzin is a luxury, heritage-inspired fashion e-commerce platform. The design balances cultural richness (traditional embroidery, vibrant details) with a modern European layout (minimalist, clean margins, high-fidelity imagery, elegant typography).

### Color Palette

The colors are derived from the official logo concept (Concept 3), incorporating high-contrast slate-navy bases with warm sandy-beige gold accents.

| Token | HSL / Hex | Usage | Role |
| :--- | :--- | :--- | :--- |
| `primaryDark` | `Hex #141923` | Top bars, prominent buttons, headings, heavy text | Primary Brand Color |
| `accentSand` | `Hex #C5A880` | Selected tabs, special buttons, star ratings, borders | Accent Highlight |
| `accentGold` | `Hex #D4AF37` | Luxury tags, highlight embroidery details | Subtle Luxury Accent |
| `bgLight` | `Hex #F7F9FB` | Screen background, card backdrops | Background Light |
| `surfaceWhite` | `Hex #FFFFFF` | Main product cards, sheet containers | Surface |
| `neutralGray` | `Hex #718096` | Subtitles, disabled states, unselected icons | Muted/Body Text |
| `errorRed` | `Hex #E53E3E` | Out of stock, error alerts, payment failures | System Alert |

---

## 2. Typography

*   **Global App Font**: *Tajawal* (Arabic & Latin geometric font, premium luxury style)
*   **Header Font**: *Tajawal Bold* or *Outfit*
*   **Body Font**: *Tajawal Medium/Regular* or *Inter*

### Text Styles

*   **Display / Large Title**: `Size: 26sp`, `Weight: Bold`, `Color: primaryDark`
*   **Section Title**: `Size: 20sp`, `Weight: SemiBold`, `Color: primaryDark`
*   **Product Name (Grid)**: `Size: 14sp`, `Weight: Medium`, `Color: primaryDark`
*   **Category Label**: `Size: 12sp`, `Weight: Medium`, `Color: neutralGray`
*   **Price Tag**: `Size: 16sp`, `Weight: SemiBold`, `Color: primaryDark` (displays both EUR and IQD e.g., "€149.00 / 215,000 IQD")

---

## 3. Core Features & Business Logic

### 3.1 Dual Currency Display (EUR & IQD)
To serve Yazidi and Kurdish customers in Germany/Europe while syncing with Iraqi vendor product listings:
*   **Source Currency**: Products are created by vendors with base prices in Iraqi Dinars (IQD).
*   **Exchange Rate**: A daily exchange rate configuration converts IQD to EUR (e.g., `1 EUR = 1450 IQD` or dynamic from backend).
*   **Customer Display**: Customers in Germany view **both currencies** clearly separated (e.g. `€299.00 / 430,000 IQD` or `€299.00 (430,000 IQD)`).
*   **Formatters**:
    *   EUR prices formatted as `€#,##0.00`
    *   IQD prices formatted as `#,##0 IQD`

---

## 4. UI Component Specifications

### 4.1 Buttons
*   **Primary Button**: Height `50dp`, Border Radius `25dp` (fully rounded capsule), Color `primaryDark` (`#141923`), Text color `white`, `Size: 16sp`, `Weight: SemiBold`.
*   **Secondary Button**: Height `50dp`, Border Radius `25dp`, Outline with `accentSand` (`#C5A880`), Background `transparent`, Text color `primaryDark`.

### 4.2 Product Cards
*   **Container**: Border Radius `16dp`, Background `surfaceWhite`, minimal soft shadow (elevation `1` or `2`), or a simple 1dp border (`#E2E8F0`) to emphasize a clean minimalist look.
*   **Image Aspect Ratio**: `3:4` portrait ratio to show garments fully.
*   **Layout**: Portrait image on top with rounded corners, followed by category name (small/muted), product title (medium bold), and row containing dual price and wishlist icon (in `accentSand` / outline).

### 4.3 Category Items
*   **Shape**: Circular avatar `65dp` with a thin gold border, or square with rounded corners `12dp`.
*   **Content**: High-quality lifestyle photo of the category theme.
*   **Label**: Below the avatar, centered text.

---

## 5. Screen Layout Rules

1.  **Welcome / Splash Screen**: Deep `primaryDark` (`#141923`) background, centered large logo in off-white with the `accentSand` diagonal stroke on the Z. Clean onboarding text at the bottom.
2.  **Home Hub**:
    *   Top Bar: Clean row with profile image, delivery location dropdown (e.g., "Munich, Germany" in `primaryDark`), and a notification badge icon.
    *   Curated banner slider with a `16:9` ratio, showing models in stunning Kurdish/Yazidi apparel.
    *   Horizontal scroll categories slider.
    *   Product grid (`2 columns` on mobile) using the `3:4` aspect ratio cards showing dual currency.
3.  **Product Details Screen**:
    *   Large swipeable image gallery taking `50%` of screen height.
    *   Details pane: title, rating, dual currency price display (`€349.00 / 506,000 IQD`), and size/color selection chips.
    *   Floating bottom action bar containing price in EUR and "Add to Bag" capsule button.
    *   Clean expandable tiles for "Heritage & Story" (cultural background of the dress), "Sizing Guide", and "Shipping Info".
4.  **Categories & Search Screen**:
    *   Top search field + filter chips (Size, Color, Price limits).
    *   Grid view showing filtered products with dual currency pricing.
5.  **Shopping Cart / Bag**:
    *   Itemized rows displaying chosen size, color, quantity, and dual currency subtotal.
    *   Order summary showing subtotal, shipping, and total in both EUR and IQD.
6.  **Checkout Screen**:
    *   Address selection, payment method options (Nass Pay, Cards, Cash on Delivery).
    *   Final authorization displaying total amounts in both currencies.

