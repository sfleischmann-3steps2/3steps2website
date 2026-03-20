# 3steps2 — Design System

## Farben

### Primärfarben
| Name         | Hex       | Verwendung                                      |
|--------------|-----------|------------------------------------------------|
| Brand Pink   | `#ee1a6c` | Logo "3steps", CTA-Buttons, Akzente, Zitate    |
| Brand Blue   | `#1da6ef` | Logo "2", Schritt-Überschriften, Links, Icons   |

### Neutralfarben (Light Mode)
| Name         | Hex / Tailwind   | Verwendung                          |
|--------------|------------------|-------------------------------------|
| Weiß         | `#ffffff`         | Hintergrund Hauptsektionen          |
| Grau 50      | `#f9fafb`         | Karten-Hintergrund (Problem-Cards)  |
| Grau 100     | `#f3f4f6`         | Sektions-Hintergrund (Legal)        |
| Grau 200     | `#e5e7eb`         | Bild-Platzhalter, Borders           |
| Grau 600     | `#4b5563`         | Fließtext                           |
| Grau 700     | `#374151`         | Fließtext (betont)                  |
| Grau 800     | `#1f2937`         | Footer-Hintergrund                  |
| Grau 900     | `#111827`         | Headlines                           |

### Dark Mode
| Element              | Light → Dark                          |
|----------------------|---------------------------------------|
| Body Background      | weiß → `gray-900`                     |
| Karten               | `gray-50` → `gray-800`               |
| Headlines            | `gray-900` → weiß                     |
| Fließtext            | `gray-600/700` → `gray-400`          |
| Footer               | `gray-800` → schwarz                  |
| Sektionen (Legal)    | `gray-100` → `gray-800`              |
| Hero/Solution Gradient| `gray-100→200` → `gray-800→900`     |

## Typografie

| Element           | Größe (Desktop)         | Gewicht     | Farbe         |
|-------------------|------------------------|-------------|---------------|
| H1 (Hero)         | `text-4xl` → `text-6xl` | extrabold   | gray-900      |
| H2 (Sektionen)    | `text-3xl` → `text-4xl` | bold        | gray-900      |
| H3 (Cards/Steps)  | `text-xl` — `text-2xl`  | semibold    | gray-900 / Brand Blue |
| Body              | `text-lg`               | normal      | gray-600      |
| Hero Subline       | `text-xl` → `text-2xl`  | normal      | gray-700      |
| Blockquote         | `text-xl`               | italic      | gray-700      |
| Footer             | `text-sm` / `text-xs`   | normal      | gray-400      |
| Font-Family        | System Sans-Serif (Tailwind Default: Inter, Helvetica, Arial) |

## Logo
- Text-Logo: `3steps` in Brand Pink + `2` in Brand Blue
- Größe: `text-2xl font-bold`
- Kein Bild-Logo verwendet

## Layout & Spacing

### Container
- Max-Width: Tailwind `container mx-auto`
- Horizontal Padding: `px-6`

### Sektionen
- Vertikal: `py-16 md:py-24` (Hero: `py-24 md:py-32`)
- Abstände zwischen Elementen: `mb-6` (Headlines), `mb-16` (nach Sublines), `gap-8`/`gap-10` (Grids)

### Grid-Struktur
| Sektion     | Spalten                          |
|-------------|----------------------------------|
| Problem     | 1 → 2 (sm) → 4 (lg)            |
| 3 Schritte  | 1 → 3 (md)                      |
| Gründerin   | 1 (mobile) → 1/3 + 2/3 (lg)    |

## Komponenten

### Cards (Problem-Sektion)
- Hintergrund: `gray-50`
- Padding: `p-6`
- Border-Radius: `rounded-xl`
- Shadow: `shadow-lg`
- Border: `border border-gray-200`
- Hover: `scale-105` Transform
- Icon oben zentriert: `h-12 w-12 text-brand-blue`

### Step-Cards (Lösung)
- Hintergrund: weiß
- Padding: `p-8`
- Border-Radius: `rounded-xl`
- Shadow: `shadow-xl`
- Border: transparent → `border-brand-blue` on hover
- Bild oben: `h-40` Container mit `h-32` Bild
- Titel: Brand Blue, `text-2xl font-semibold`

### CTA-Banner (Workshop)
- Hintergrund: Brand Blue
- Text: weiß
- Padding: `p-10 md:p-16`
- Border-Radius: `rounded-xl`
- Dekorative Kreise: weiße Kreise mit 10% Opacity

### Buttons
- Primary (CTA): `bg-brand-pink text-white font-bold rounded-lg shadow-md`
- Hover: `hover:bg-opacity-90 hover:-translate-y-0.5`
- Theme Toggle: `bg-gray-200 rounded-lg p-2 text-sm`

### Modals
- Backdrop: `bg-black bg-opacity-70`
- Content: `rounded-xl shadow-2xl max-w-md/2xl`
- Animation: scale 0.9→1 + opacity 0→1
- Header: sticky mit Border unten

## Animationen

| Animation       | Effekt                                    | Trigger              |
|-----------------|-------------------------------------------|----------------------|
| fade-in-up      | opacity 0→1, translateY 30px→0            | Intersection Observer (15%) |
| fade-in-right   | opacity 0→1, translateX -30px→0           | Intersection Observer |
| fade-in-left    | opacity 0→1, translateX 30px→0            | Intersection Observer |
| stagger-delay   | 0.1s / 0.2s / 0.3s / 0.4s Verzögerung    | Kaskade              |
| Counter          | 1→40 hochzählen in 2s                     | Hero 50% sichtbar    |
| Typewriter       | Phrases tippen + löschen                  | DOMContentLoaded     |
| Theme Transition | 0.3s ease auf bg, color, border           | Toggle-Klick         |
| Modal            | scale + opacity, 0.3s ease                | Open/Close           |

### Typewriter-Phrases
1. "Redefine success with AI agents."
2. "Potenziale entfesseln mit KI-Agenten."
3. "Prozesse optimieren. Erfolg steigern."
4. "KI-Revolution für Ihr Unternehmen."

## Seitenstruktur

1. **Header** — Sticky, Logo + Typewriter + Theme-Toggle
2. **Hero** — Hintergrundbild, Counter-Headline, Subline
3. **Problem** — 4 Hürden-Cards mit Icons
4. **Lösung** — 3-Schritte-Cards mit Bildern
5. **Gründerin** — Foto (rund, pink Border) + Text + Blockquote
6. **Rechtssicherheit** — Partnerin-Karte
7. **Workshop CTA** — Blauer Banner
8. **Footer** — Kontaktdaten, Impressum/Datenschutz (Modals), Copyright

## Assets (benötigt)

| Datei              | Verwendung                    | Format |
|--------------------|-------------------------------|--------|
| `bg.png`           | Hero Hintergrundbild          | PNG    |
| `1.png`            | Schritt 1 — Beratung          | PNG    |
| `2.png`            | Schritt 2 — Implementierung   | PNG    |
| `3.png`            | Schritt 3 — Betreuung         | PNG    |
| `N1_sf_5.png`      | Gründerin-Foto                | PNG    |
| Favicons           | Browser-Tab                   | PNG/ICO |

## SEO / Meta (aktuell fehlend, empfohlen)
- `<meta name="description">` — Firmenbeschreibung
- Open Graph Tags (og:title, og:description, og:image)
- Canonical URL
- Structured Data (LocalBusiness)
