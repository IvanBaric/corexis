# Doma Guide Project Profile

This document defines rules specific to the Doma Guide host application.

## Product Boundary

- Doma Guide is a separate Laravel application. Product billing, guest-guide analytics, AI translation orchestration and the destination catalog stay in the host application.
- Reusable content, tenancy, media, public-site rendering, billing lifecycle and QR generation must use the IvanBaric packages instead of being copied from Niva.
- Onboarding is not part of the product. Registration creates the user's first property, home page and starter guide sections immediately.

## Public-First Editing

- An authenticated owner lands on the real guide and manages content there. A separate administration shell must not become the primary workflow.
- Desktop editing uses a left flyout while the guide remains visible on the right. Mobile editing may use the full viewport.
- Content changes are visible immediately. Guide content does not have draft or editorial publication statuses.
- All 24 configured section types use the Pages registry and app-owned English definitions. Niva-specific section definitions and labels must not leak into Doma Guide.

## Billing And Public Access

- Billing belongs to a property, not globally to a user. One user may own multiple independently billed properties.
- The annual plan costs EUR 79 per property.
- Owners may edit and preview before payment. Anonymous public access and QR download require confirmed billing access.
- A browser success URL is never proof of payment. Stripe webhook confirmation activates access.

## Language And Sensitive Data

- English is the primary product and UI language. Croatian, German and Italian are supported content locales planned for the product.
- This profile explicitly overrides the Niva-only Croatian admin UI rule for the Doma Guide host.
- Public sections must never expose door, lockbox, alarm or other property access codes. Owners must share sensitive access data through a private channel.
