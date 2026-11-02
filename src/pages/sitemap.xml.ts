import type { APIRoute } from 'astro';
import { getCollection } from 'astro:content';

const isoDate = (d: Date) => d.toISOString().slice(0, 10);

export const GET: APIRoute = async ({ site }) => {
  const posts = await getCollection('blog');
  const latest = posts.reduce((max, p) => Math.max(max, (p.data.updated ?? p.data.date).valueOf()), 0);

  const pages = [
    { path: '/', lastmod: isoDate(new Date(latest)) },
    { path: '/blog', lastmod: isoDate(new Date(latest)) },
    ...posts.map((p) => ({ path: `/blog/${p.id}`, lastmod: isoDate(p.data.updated ?? p.data.date) })),
    { path: '/terms-conditions' },
  ];

  const urls = pages
    .map(({ path, lastmod }) =>
      [`  <url>`, `    <loc>${new URL(path, site)}</loc>`, lastmod && `    <lastmod>${lastmod}</lastmod>`, `  </url>`]
        .filter(Boolean)
        .join('\n'),
    )
    .join('\n');

  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`,
    { headers: { 'Content-Type': 'application/xml' } },
  );
};
