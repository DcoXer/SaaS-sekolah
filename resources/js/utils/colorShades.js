/**
 * Generate Tailwind-style color shades (50–900) from a single hex color,
 * then apply them as CSS custom properties on :root.
 *
 * Tailwind `primary-*` classes read these vars:
 *   --p-50, --p-100, ..., --p-900  (each as "R G B" triplet)
 */

function hexToRgb(hex) {
    const h = hex.replace('#', '');
    return [
        parseInt(h.slice(0, 2), 16),
        parseInt(h.slice(2, 4), 16),
        parseInt(h.slice(4, 6), 16),
    ];
}

function rgbToHsl(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h = 0, s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
            case g: h = ((b - r) / d + 2) / 6; break;
            case b: h = ((r - g) / d + 4) / 6; break;
        }
    }
    return [h * 360, s * 100, l * 100];
}

function hslToRgbTriplet(h, s, l) {
    h /= 360; s /= 100; l /= 100;
    let r, g, b;
    if (s === 0) {
        r = g = b = l;
    } else {
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        const hue2 = (t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };
        r = hue2(h + 1 / 3);
        g = hue2(h);
        b = hue2(h - 1 / 3);
    }
    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

/**
 * Apply primary color CSS variables to document root.
 * @param {string} hex - e.g. "#10b981"
 */
export function applyPrimaryColor(hex) {
    if (!hex || !/^#[0-9a-fA-F]{6}$/.test(hex)) return;

    const [r, g, b] = hexToRgb(hex);
    const [h, s, l] = rgbToHsl(r, g, b);

    const shades = {
        50:  hslToRgbTriplet(h, Math.min(s * 0.15, 100), 97),
        100: hslToRgbTriplet(h, Math.min(s * 0.25, 100), 94),
        200: hslToRgbTriplet(h, Math.min(s * 0.45, 100), 88),
        300: hslToRgbTriplet(h, Math.min(s * 0.65, 100), 78),
        400: hslToRgbTriplet(h, Math.min(s * 0.85, 100), 65),
        500: [r, g, b],
        600: hslToRgbTriplet(h, s, Math.max(l - 8,  5)),
        700: hslToRgbTriplet(h, s, Math.max(l - 18, 5)),
        800: hslToRgbTriplet(h, s, Math.max(l - 28, 5)),
        900: hslToRgbTriplet(h, s, Math.max(l - 38, 5)),
        950: hslToRgbTriplet(h, s, Math.max(l - 44, 3)),
    };

    const root = document.documentElement;
    for (const [shade, [rv, gv, bv]] of Object.entries(shades)) {
        root.style.setProperty(`--p-${shade}`, `${rv} ${gv} ${bv}`);
    }
}
