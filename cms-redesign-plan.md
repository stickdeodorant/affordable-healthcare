# CMS Redesign Plan (Non-Technical First)

## Goal
Rebuild the CMS authoring experience so a non-technical marketer can build, edit, and reorder legacy and modern sections confidently, without touching raw HTML.

## Product Principles
- Default-safe: users should not need to understand HTML or code.
- Task-first flow: Create -> Compose -> Review -> Publish.
- Reuse over reinvention: prebuilt section blocks and snippet packs.
- Guided quality: publishing gates, inline guidance, and clarity on impact.
- Premium UX: high-end visual design, strong hierarchy, and calm interactions.

## Phase 1 - Legacy Section Block Foundation
Status: Completed

Scope:
- Add a secure `legacy_section` block type backed by a whitelist of pre-CMS includes.
- Catalog legacy reusable sections and expose them in editor controls.
- Ensure blocks can be added, edited, and reordered in the page editor.
- Add reusable snippet pack representing pre-CMS home sections stack.

Deliverables:
- `cms/lib/legacy-sections.php` catalog + safe renderer.
- Renderer support in `cms/lib/render-blocks.php`.
- Editor support in `admin/edit.php` sanitizer + UI block card.
- Snippet compatibility in `admin/snippets.php` and seed snippet data.

## Phase 2 - CMS Shell Redesign (Premium, Task-Oriented)
Status: Completed (shell foundation)

Scope:
- Replace current admin chrome with a redesigned workspace shell.
- Introduce clear IA for non-technical users:
  - Pages
  - Composer
  - Revisions
  - Snippets
- Improve readability, action clarity, and visual hierarchy.
- Maintain responsive behavior for laptop and tablet usage.

Deliverables:
- New global admin layout and visual system in `admin/_layout.php`.
- Improved component consistency for cards, tables, forms, and controls.
- Sidebar/workspace shell with task-first IA for non-technical users.

## Phase 3 - Composer UX Rebuild
Status: In progress

Scope:
- Rebuild `admin/edit.php` into guided composer sections:
  - Page basics
  - Hero + CTA
  - Section stack
  - SEO + social
  - Publish readiness
- Add section templates and one-click stack insertion.
- Enhance block cards with clearer labels, preview hints, and drag-order affordances.

Delivered in current pass:
- Guided step-based composer layout in `admin/edit.php`.
- Sticky action bar and dedicated sidebar readiness panel.
- Drag-and-drop reordering for content blocks (in addition to arrow controls).
- Role-specific reviewer handoff cues and note requirements for submit-for-review.
- Block stack empty-state onboarding and drag affordance hints.

## Phase 4 - Review + Governance Experience
Status: In progress

Scope:
- Deepen revisions compare with semantic changes and summary badges.
- Add stronger review workflows for marketer -> reviewer handoff.
- Expand activity reporting with role-focused views.

Delivered in current pass:
- Revisions compare summary badges for field/body change counts.
- Rollback governance requires a rollback reason note (server-validated) and stores it in revision history + audit details.
- Review submission telemetry now emits `page_submit_review` audit events.
- Activity dashboard and CSV export now include review-submission actions.
- Reviewer decision notes are now required when reviewers/admins change a page status, with both client and server validation.
- Submit/save actions now generate and log a concise automatic change summary in audit metadata for reviewer context.
- Pages list now includes a publish-risk signal (low/medium/high) with reason hints for faster triage.

Visual refinement update:
- Admin color tokens and shell surfaces were upgraded to a higher-end brass + deep-ocean palette while preserving contrast and responsive behavior.

## Phase 5 - Non-Technical UX Rework + Premium Design System
Status: Completed (current pass)

Scope:
- Make the CMS understandable without training for non-technical editors.
- Apply a best-practice, high-end visual system across all admin screens.
- Provide built-in, role-aware help.

Delivered in current pass:
- New premium design system in `admin/_layout.php` (refined teal/ink tokens, hairline borders, layered shadows, icon + grouped sidebar with brand mark, user avatar, and primary "New page" action; reusable `stat-card`, `section-head`, and `segmented` components).
- Composer de-jargoned in `admin/edit.php`: numbered "Step N" cards replaced with plain-language sections + icons; Simple/Advanced segmented toggle; friendlier field labels and add-block buttons (JS hooks preserved).
- Pages dashboard upgraded to premium stat cards in `admin/index.php`.
- New in-app user guide at `admin/guide.php` with a sticky table of contents and role-aware sections, linked from the sidebar Help group and from a contextual help link in the composer.

## Execution Notes
- Build each phase behind existing page routes to avoid migration disruption.
- Preserve current data model and backward compatibility for existing blocks.
- Validate each phase with PHP lint and quick manual QA on key admin pages.
