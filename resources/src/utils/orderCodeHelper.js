/**
 * orderCodeHelper.js
 * Shared utility for Order Reference barcode, QR code and scanner input handling.
 *
 * Usage example:
 *   import { generateBarcodeProps, generateQrDataUrl, sanitizeScanInput } from '@/utils/orderCodeHelper'
 *
 *   // Barcode: pass spread result directly to <barcode v-bind="..." />
 *   const barcodeProps = generateBarcodeProps('SO-20260220-0001')
 *
 *   // QR code (plain ref):
 *   const dataUrl = await generateQrDataUrl('SO-20260220-0001', 'plain')
 *
 *   // QR code (deep link):
 *   const dataUrl = await generateQrDataUrl('SO-20260220-0001', 'url', 'https://mystore.com')
 *
 *   // Sanitize scanner input:
 *   const clean = sanitizeScanInput(rawScannerOutput)  // strips \r\n, extracts ref from URLs
 */

import QRCode from 'qrcode'

// ─── A) Barcode ──────────────────────────────────────────────────────────────

/**
 * Returns vue-barcode component props for a given order reference.
 * Format is always CODE128.
 *
 * @param {string} ref  - The order reference string (e.g. "SO-20260220-0001")
 * @returns {object}    - Props to v-bind onto <barcode>
 */
export function generateBarcodeProps(ref) {
    return {
        value: String(ref || ''),
        format: 'CODE128',
        width: 1.5,
        height: 40,
        fontSize: 13,
        margin: 4,
        displayValue: true,
        textMargin: 2,
        fontOptions: 'bold',
    }
}

// ─── B) QR Code ──────────────────────────────────────────────────────────────

/**
 * Generate a QR code as a PNG data URL.
 *
 * @param {string} ref      - The order reference string
 * @param {'plain'|'url'} mode - 'plain' = encode ref directly, 'url' = encode deep-link URL
 * @param {string} [baseUrl]  - Required when mode === 'url'. Defaults to current window.location.origin
 * @returns {Promise<string>} - data URL (image/png)
 */
export async function generateQrDataUrl(ref, mode = 'plain', baseUrl = null) {
    let value
    if (mode === 'url') {
        const origin = baseUrl || (typeof window !== 'undefined' ? window.location.origin : '')
        value = `${origin}/orders/ref/${encodeURIComponent(ref)}`
    } else {
        value = String(ref || '')
    }

    return QRCode.toDataURL(value, {
        errorCorrectionLevel: 'M',
        margin: 1,
        width: 200,
        color: {
            dark: '#000000',
            light: '#ffffff',
        },
    })
}

// ─── C) Scanner Input Sanitization ───────────────────────────────────────────

/**
 * Sanitize raw scanner input into a clean order reference string.
 *
 * Steps:
 *   1. Trim leading/trailing whitespace
 *   2. Strip trailing \r and \n characters (common from barcode scanners that append Enter)
 *   3. If the result is a valid URL (QR deep-link), extract the order reference
 *      from the last URL path segment (e.g. /orders/ref/SO-20260220-0001 → SO-20260220-0001)
 *
 * @param {string} raw - The raw string from scanner input
 * @returns {string}   - Cleaned order reference
 */
export function sanitizeScanInput(raw) {
    let s = String(raw || '').trim().replace(/[\r\n]+$/, '').trim()

    try {
        const url = new URL(s)
        // Extract last non-empty path segment as the order reference
        const segments = url.pathname.split('/').filter(Boolean)
        if (segments.length > 0) {
            s = decodeURIComponent(segments[segments.length - 1])
        }
    } catch (_) {
        // Not a URL — use s as-is
    }

    return s
}
