// Opera Shalem Worker: serves the static site from dist/ and handles the
// contact form at POST /api/contact, delivering enquiries by email via Resend.

interface Env {
  ASSETS: Fetcher;
  RESEND_API_KEY: string;
  CONTACT_TO: string;
  CONTACT_FROM: string;
  // Only set for local testing against a mock; defaults to Resend's API
  RESEND_API_URL?: string;
}

const INTERESTS: Record<string, string> = {
  founding: 'Founding Support',
  connections: 'Strategic Connections',
  technology: 'Technology Collaboration',
  general: 'General Inquiry',
};

const SUCCESS = 'Thank you! We will be in touch soon.';
const FAILURE = 'Failed to send message. Please email us directly at info@operashalem.com';

export default {
  async fetch(request, env): Promise<Response> {
    const url = new URL(request.url);
    if (url.pathname === '/api/contact') {
      if (request.method !== 'POST') {
        return new Response('Method not allowed', { status: 405, headers: { Allow: 'POST' } });
      }
      return handleContact(request, env);
    }
    return env.ASSETS.fetch(request);
  },
} satisfies ExportedHandler<Env>;

async function handleContact(request: Request, env: Env): Promise<Response> {
  // fetch() from the page asks for JSON; a plain form post (no JavaScript) gets a redirect
  const wantsJson = (request.headers.get('Accept') ?? '').includes('application/json');
  const respond = (status: number, success: boolean, message: string) => {
    if (wantsJson) {
      return Response.json({ success, message }, { status, headers: { 'Cache-Control': 'no-store' } });
    }
    const page = success ? '/message-sent' : '/message-not-sent';
    return Response.redirect(new URL(page, request.url).toString(), 303);
  };

  let form: FormData;
  try {
    form = await request.formData();
  } catch {
    return respond(400, false, 'Please fill in all fields.');
  }

  // Honeypot: real visitors never see or fill this field
  if (field(form, 'website')) {
    return respond(200, true, SUCCESS);
  }

  // Single-line fields lose line breaks so they can't spill into other email fields
  const name = field(form, 'name').replace(/[\r\n\t]+/g, ' ');
  const email = field(form, 'email');
  const interest = field(form, 'interest');
  const message = field(form, 'message');

  if (!name || !email || !interest || !message) {
    return respond(400, false, 'Please fill in all fields.');
  }
  if (!(interest in INTERESTS)) {
    return respond(400, false, 'Please choose an area of interest.');
  }
  if (!isEmail(email)) {
    return respond(400, false, 'Please enter a valid email address.');
  }
  if (name.length > 200 || message.length > 5000) {
    return respond(400, false, 'Your message is too long.');
  }

  const text = [
    'New contact form submission from operashalem.com',
    '',
    `Name: ${name}`,
    `Email: ${email}`,
    `Area of Interest: ${INTERESTS[interest]}`,
    'Message:',
    message,
    '',
    '---',
    `Submitted: ${new Date().toISOString()}`,
  ].join('\n');

  try {
    const response = await fetch(env.RESEND_API_URL ?? 'https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${env.RESEND_API_KEY}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from: env.CONTACT_FROM,
        to: [env.CONTACT_TO],
        reply_to: email,
        subject: 'New Contact Form Submission - Opera Shalem',
        text,
      }),
    });
    if (!response.ok) {
      console.error('Resend rejected the email', response.status, await response.text());
      return respond(502, false, FAILURE);
    }
  } catch (error) {
    console.error('Could not reach Resend', error);
    return respond(502, false, FAILURE);
  }

  return respond(200, true, SUCCESS);
}

function field(form: FormData, key: string): string {
  const value = form.get(key);
  return typeof value === 'string' ? value.trim() : '';
}

function isEmail(value: string): boolean {
  // One @, no spaces, and a dot in the domain: rejects "a@b" but keeps it permissive
  return value.length <= 254 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}
