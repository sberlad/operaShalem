# Opera Shalem website

The site for [operashalem.com](https://operashalem.com): an [Astro](https://astro.build) static site served by a Cloudflare Worker, which also handles the contact form.

## Layout

| Path | What it is |
|---|---|
| `src/pages/` | One file per page. `index.astro` is the homepage; `blog/[slug].astro` renders every post |
| `src/content/blog/` | Blog posts, one Markdown file each. Frontmatter holds title, date, category, tags, read time |
| `src/layouts/Base.astro` | Shared `<head>` (meta, canonical, social tags, favicons, fonts), nav and footer |
| `src/components/` | Site nav (with the mobile menu) and footer |
| `src/styles/` | `global.css` (brand tokens, nav, footer, buttons) and `post.css` (blog posts and policy pages) |
| `public/` | Copied as-is: images, favicons, `robots.txt`, `_redirects` (old `.html` URLs), `_headers` (security headers) |
| `worker/index.ts` | Serves the built site and handles `POST /api/contact`, emailing enquiries via [Resend](https://resend.com) |

## Writing a blog post

Add `src/content/blog/my-post-slug.md`:

```markdown
---
title: "Post title"
description: "150-160 characters for search engines"
excerpt: "One-line summary for the blog card and under the title"
date: 2026-11-15
category: news        # vision | news | opera | culture
tags: ["Tag one", "Tag two"]
readTime: 4
---

Post body in Markdown (HTML works too).
```

It appears on `/blog`, at `/blog/my-post-slug`, and in the sitemap automatically.

## Local development

```bash
npm install
npm run dev        # Astro dev server at http://localhost:4321 (the form endpoint is not available here)
npm run preview    # build, then run the real Worker locally at http://localhost:8787
npm run check      # type-check pages and the Worker
```

For `npm run preview`, create `.dev.vars` (git-ignored) with `RESEND_API_KEY=...` to send real test emails.

## Deploying on Cloudflare

One-time setup:

1. **Resend:** create an account, add and verify the domain `operashalem.com` (it gives you DNS records to add in Cloudflare), and create an API key with sending access.
   The sender in `wrangler.jsonc` (`CONTACT_FROM`) must be on that verified domain.
2. **Worker:** in the Cloudflare dashboard, go to Workers & Pages → Create → Import a repository, choose this GitHub repo and branch.
   Build command `npm run build`, deploy command `npx wrangler deploy`. Every push to that branch then redeploys.
3. **Secret:** in the Worker's Settings → Variables and Secrets, add `RESEND_API_KEY` as a secret.
4. **Domain:** in the Worker's Settings → Domains & Routes, add `operashalem.com` and `www.operashalem.com` as custom domains.
   First delete the existing `A`/`AAAA`/`CNAME` records for `operashalem.com` and `www` that point to IONOS.
   **Do not touch the `MX` and SPF `TXT` records**: email stays at IONOS.
5. Send a test message through the form and check it arrives (and isn't in spam).

After the switch, keep the IONOS **email** package; only the web hosting can be cancelled.
