<?php
/**
 * In-app user guide for content editors. Plain-language help, no code required.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;
$canPublish = cms_user_can('reviewer');
$isAdmin = cms_user_can('admin');

admin_header('User guide');
?>
<style>
    .guide-hero {
        background:linear-gradient(135deg, #0b1220 0%, #10233a 60%, #123a4e 100%);
        color:#eaf2f8;
        border-radius:var(--radius-lg);
        padding:1.8rem 1.9rem;
        margin-bottom:1.4rem;
        box-shadow:var(--shadow);
    }
    .guide-hero h1 { color:#fff; margin:0 0 .35rem; }
    .guide-hero p { margin:0; color:#b9cad9; max-width:65ch; font-size:.95rem; }
    .guide-layout { display:grid; grid-template-columns:230px minmax(0,1fr); gap:1.4rem; align-items:start; }
    .guide-toc { position:sticky; top:84px; }
    .guide-toc .card { padding:1rem 1.05rem; }
    .guide-toc-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--ink-faint); margin:0 0 .5rem; }
    .guide-toc a { display:block; padding:.32rem .1rem; font-size:.86rem; font-weight:600; color:var(--ink-soft); }
    .guide-toc a:hover { color:var(--ah-strong); text-decoration:none; }
    .guide-section { scroll-margin-top:90px; }
    .guide-section + .guide-section { margin-top:1.15rem; }
    .guide-section h2 { font-size:1.12rem; margin:0 0 .2rem; color:var(--ink); }
    .guide-section .lead { color:var(--ink-soft); margin:0 0 .9rem; font-size:.92rem; }
    .guide-steps { list-style:none; counter-reset:step; padding:0; margin:0; display:grid; gap:.7rem; }
    .guide-steps li { counter-increment:step; position:relative; padding:.75rem .9rem .75rem 3rem; border:1px solid var(--line); border-radius:var(--radius-sm); background:#fff; font-size:.9rem; }
    .guide-steps li::before { content:counter(step); position:absolute; left:.8rem; top:.7rem; width:1.55rem; height:1.55rem; border-radius:50%; background:var(--ah-soft); color:var(--ah-strong); font-weight:800; font-size:.82rem; display:flex; align-items:center; justify-content:center; }
    .guide-steps li strong { display:block; margin-bottom:.1rem; color:var(--ink); }
    .guide-def { display:grid; gap:.6rem; }
    .guide-def .def-row { display:flex; gap:.75rem; align-items:flex-start; padding:.7rem .8rem; border:1px solid var(--line); border-radius:var(--radius-sm); background:#fff; }
    .guide-def .def-row .def-body { font-size:.88rem; color:var(--ink-soft); }
    .guide-def .def-row .def-body strong { color:var(--ink); }
    .guide-callout { border:1px solid #cfe8e2; background:#eef8f5; border-radius:var(--radius-sm); padding:.75rem .9rem; font-size:.88rem; color:#1f5d52; margin-top:.85rem; }
    .guide-callout.warn { border-color:#f0e0b6; background:#fdf6e6; color:#7d5b18; }
    .guide-tiles { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:.7rem; margin-top:.3rem; }
    .guide-tile { border:1px solid var(--line); border-radius:var(--radius-sm); padding:.8rem .85rem; background:#fff; }
    .guide-tile .t-name { font-weight:700; font-size:.9rem; color:var(--ink); }
    .guide-tile .t-desc { font-size:.82rem; color:var(--ink-soft); margin-top:.2rem; }
    @media (max-width: 900px) {
        .guide-layout { grid-template-columns:1fr; }
        .guide-toc { position:static; }
    }
</style>

<div class="guide-hero">
    <h1>Content editor guide</h1>
    <p>Everything you need to create, edit, and publish pages &mdash; in plain language, no coding required. Follow the steps below or jump to a topic on the left.</p>
</div>

<div class="guide-layout">
    <aside class="guide-toc">
        <div class="card">
            <p class="guide-toc-title">On this page</p>
            <a href="#start">Getting started</a>
            <a href="#editor">The page editor</a>
            <a href="#blocks">Building page sections</a>
            <a href="#modes">Simple vs. Advanced</a>
            <a href="#status">Statuses &amp; publishing</a>
            <a href="#checklist">Readiness &amp; risk</a>
            <a href="#revisions">Undo &amp; history</a>
            <?php if ($isAdmin): ?>
                <a href="#admin">Admin tools</a>
            <?php endif; ?>
            <a href="#tips">Tips &amp; FAQ</a>
        </div>
    </aside>

    <div>
        <section id="start" class="guide-section card">
            <h2>Getting started</h2>
            <p class="lead">Create a new page or edit an existing one in a few clicks.</p>
            <ol class="guide-steps">
                <li><strong>Open your pages</strong> Choose <em>All pages</em> in the left sidebar to see everything on the site.</li>
                <li><strong>Create or edit</strong> Click <em>New page</em> to start fresh, or click any page title to edit it.</li>
                <li><strong>Fill in the sections</strong> Work top to bottom through Page basics, Headline &amp; button, and Page sections.</li>
                <li><strong>Save your work</strong> Click <em>Save draft</em> at the bottom. Your page is saved but not yet public.</li>
                <li><strong>Go live</strong> <?= $canPublish ? 'Set the status to <em>Published</em> and save.' : 'Click <em>Submit for review</em> so a reviewer can publish it.' ?></li>
            </ol>
            <div class="guide-callout">Your changes autosave to this browser as you type. If you close the tab by accident, you&rsquo;ll be offered to restore your draft next time.</div>
        </section>

        <section id="editor" class="guide-section card">
            <h2>The page editor</h2>
            <p class="lead">Each card in the editor covers one part of the page.</p>
            <div class="guide-def">
                <div class="def-row"><div class="def-body"><strong>Page basics</strong> &mdash; the page name (title) and who can see it (status). In Advanced mode you can also set the web address (URL).</div></div>
                <div class="def-row"><div class="def-body"><strong>Headline &amp; button</strong> &mdash; the big message at the top of the page and the main action button (its text and where it links).</div></div>
                <div class="def-row"><div class="def-body"><strong>Page sections</strong> &mdash; the stackable content blocks that make up the body of the page.</div></div>
                <div class="def-row"><div class="def-body"><strong>Ready-made sections</strong> &mdash; one-click, pre-approved section packs you can drop in for speed and consistency.</div></div>
                <div class="def-row"><div class="def-body"><strong>Search &amp; sharing</strong> (Advanced) &mdash; the short description shown in search results and the image used when the page is shared.</div></div>
            </div>
            <div class="guide-callout">Not sure what a field does? Look for the small grey hint text under it &mdash; it explains what to enter and the recommended length.</div>
        </section>

        <section id="blocks" class="guide-section card">
            <h2>Building page sections</h2>
            <p class="lead">Sections are the building blocks of your page. Add as many as you need and arrange them in any order.</p>
            <div class="guide-tiles">
                <div class="guide-tile"><div class="t-name">Text</div><div class="t-desc">A block of formatted words &mdash; headings, paragraphs, and lists.</div></div>
                <div class="guide-tile"><div class="t-name">Prebuilt section</div><div class="t-desc">A trusted, ready-designed section reused from the main site.</div></div>
                <div class="guide-tile"><div class="t-name">Call-to-action</div><div class="t-desc">A highlighted banner with a heading and a button.</div></div>
                <div class="guide-tile"><div class="t-name">FAQ</div><div class="t-desc">A list of questions and answers.</div></div>
            </div>
            <ol class="guide-steps" style="margin-top:.9rem;">
                <li><strong>Add a section</strong> Under <em>Page sections</em>, click a button like <em>+ Text</em> or <em>+ FAQ</em>.</li>
                <li><strong>Fill it in</strong> Type directly into the fields that appear inside the block.</li>
                <li><strong>Reorder</strong> Drag a block by the handle (&#9776;) to move it, or use the up/down arrows.</li>
                <li><strong>Remove</strong> Click <em>Remove</em> on a block you no longer need.</li>
            </ol>
            <div class="guide-callout">Prefer a head start? Use <em>Ready-made sections</em> to insert a complete, pre-approved stack, then tweak the wording.</div>
        </section>

        <section id="modes" class="guide-section card">
            <h2>Simple vs. Advanced</h2>
            <p class="lead">The toggle at the top of the editor controls how much you see.</p>
            <div class="guide-def">
                <div class="def-row"><div class="def-body"><strong>Simple</strong> &mdash; the default. Shows only what you need for everyday content edits and hides technical controls.</div></div>
                <div class="def-row"><div class="def-body"><strong>Advanced</strong> &mdash; reveals extra controls like the web address, template, theme, raw HTML blocks, and search settings. Use it only when you need them.</div></div>
            </div>
            <div class="guide-callout warn">Advanced blocks (like raw or legacy HTML) can change the page layout. Edit them carefully, and when in doubt, stay in Simple mode.</div>
        </section>

        <section id="status" class="guide-section card">
            <h2>Statuses &amp; publishing</h2>
            <p class="lead">Every page has a status that controls whether it&rsquo;s public.</p>
            <div class="guide-def">
                <div class="def-row"><span class="badge badge-draft">Draft</span><div class="def-body">A work in progress. Only visible to editors, never to the public.</div></div>
                <div class="def-row"><span class="badge badge-review">Review</span><div class="def-body">Submitted and waiting for a reviewer to check and publish it.</div></div>
                <div class="def-row"><span class="badge badge-published">Published</span><div class="def-body">Live and visible to everyone on the website.</div></div>
                <div class="def-row"><span class="badge badge-archived">Archived</span><div class="def-body">Retired from the site but kept for reference.</div></div>
            </div>
            <?php if ($canPublish): ?>
                <div class="guide-callout">You have reviewer access: you can set a page to <strong>Published</strong> directly. When you change a status, add a short reviewer decision note explaining why.</div>
            <?php else: ?>
                <div class="guide-callout">Publishing is done by reviewers. Save your draft, then click <strong>Submit for review</strong> and add a handoff note describing what changed.</div>
            <?php endif; ?>
        </section>

        <section id="checklist" class="guide-section card">
            <h2>Readiness &amp; risk</h2>
            <p class="lead">The editor helps you catch problems before a page goes live.</p>
            <div class="guide-def">
                <div class="def-row"><div class="def-body"><strong>Publish checklist</strong> &mdash; the panel on the right of the editor lists required and recommended items. Green means done; anything marked <em>Needs attention</em> must be fixed before publishing.</div></div>
                <div class="def-row"><div class="def-body"><strong>Risk badge</strong> &mdash; on the pages list, each page shows a <span class="badge badge-risk-low">Low</span>, <span class="badge badge-risk-medium">Medium</span>, or <span class="badge badge-risk-high">High</span> badge that flags likely issues (missing hero, no content, and so on) so you know what to review first.</div></div>
            </div>
        </section>

        <section id="revisions" class="guide-section card">
            <h2>Undo &amp; history</h2>
            <p class="lead">Every save is recorded, so you can always go back.</p>
            <ol class="guide-steps">
                <li><strong>Open history</strong> While editing a page, click <em>Revision history</em>.</li>
                <li><strong>Compare</strong> View what changed between versions, side by side.</li>
                <li><strong>Roll back</strong> Restore an earlier version. You&rsquo;ll be asked for a short reason, which is recorded for the record.</li>
            </ol>
        </section>

        <?php if ($isAdmin): ?>
        <section id="admin" class="guide-section card">
            <h2>Admin tools</h2>
            <p class="lead">Available to administrators only.</p>
            <div class="guide-def">
                <div class="def-row"><div class="def-body"><strong>Redirects</strong> &mdash; send an old web address to a new one so links never break. Renaming a page&rsquo;s URL creates one automatically.</div></div>
                <div class="def-row"><div class="def-body"><strong>Section library</strong> &mdash; manage the <em>Ready-made sections</em> everyone can insert: add, edit, reorder, or remove approved packs.</div></div>
                <div class="def-row"><div class="def-body"><strong>Activity &amp; performance</strong> &mdash; on the pages list, review recent saves, reviews, and publishes, filter by time window, and export a CSV.</div></div>
            </div>
        </section>
        <?php endif; ?>

        <section id="tips" class="guide-section card">
            <h2>Tips &amp; FAQ</h2>
            <p class="lead">Quick answers to common questions.</p>
            <div class="guide-def">
                <div class="def-row"><div class="def-body"><strong>Why can&rsquo;t I publish?</strong> Either a required checklist item is unfinished, or publishing needs reviewer access. Fix any <em>Needs attention</em> items, or submit for review.</div></div>
                <div class="def-row"><div class="def-body"><strong>My page isn&rsquo;t showing publicly.</strong> Confirm its status is <span class="badge badge-published">Published</span> and that you saved after changing it.</div></div>
                <div class="def-row"><div class="def-body"><strong>I lost my changes.</strong> Reopen the page &mdash; if a local autosaved draft exists, you&rsquo;ll be offered to restore it. Otherwise use Revision history.</div></div>
                <div class="def-row"><div class="def-body"><strong>What should the button link to?</strong> Use a path that starts with <code>/</code> (for example <code>/multi-quote/</code>) or a full <code>https://</code> address.</div></div>
                <div class="def-row"><div class="def-body"><strong>How long should text be?</strong> Follow the grey hint under each field &mdash; it shows the recommended length and counts characters as you type.</div></div>
            </div>
            <div class="guide-callout">Ready to try it? <a href="<?= cms_e($base) ?>/edit.php">Create a new page</a> or <a href="<?= cms_e($base) ?>/">browse existing pages</a>.</div>
        </section>
    </div>
</div>
<?php
admin_footer();
