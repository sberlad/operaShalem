// @ts-check
import { defineConfig } from 'astro/config';

// Static site. The Cloudflare Worker in worker/ serves dist/ and handles the
// contact form at /api/contact.
export default defineConfig({
  site: 'https://operashalem.com',
  trailingSlash: 'never',
  build: {
    // blog/what-is-opera.html, served at /blog/what-is-opera
    format: 'file',
  },
});
