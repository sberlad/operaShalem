import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

const blog = defineCollection({
  loader: glob({ pattern: '*.md', base: './src/content/blog' }),
  schema: z.object({
    title: z.string(),
    // Meta description for search engines
    description: z.string(),
    // Short summary shown on blog cards and under the post title
    excerpt: z.string(),
    date: z.coerce.date(),
    updated: z.coerce.date().optional(),
    category: z.enum(['vision', 'news', 'opera', 'culture']),
    tags: z.array(z.string()).default([]),
    readTime: z.number().int().positive(),
    featured: z.boolean().default(false),
  }),
});

export const collections = { blog };
