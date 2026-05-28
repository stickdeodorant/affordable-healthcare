# Loading Modal (drop-in)

This is a reusable version of the loading modal + progress animation currently used in `p/signup.php`.

## Files
- `loading-modal.html` – Bootstrap modal markup
- `loading-modal.css` – styles for the progress circle + status text
- `loading-modal.js` – small API (`OHPLoadingModal`) to show/hide and run the progress sequence

## Dependencies (same assumptions as the current site)
- jQuery
- Bootstrap 3 modal plugin
- CircleProgress (optional but expected for the circular progress UI)

## Install (copy/paste into another site)
1) Copy the `shared/loading-modal/` folder into your site (or copy the three files).
2) Include the markup somewhere near the bottom of `<body>`:

```html
<!-- paste from loading-modal.html -->
```

3) Include CSS:

```html
<link rel="stylesheet" href="/path/to/loading-modal.css">
```

4) Include JS (after jQuery + bootstrap.js):

```html
<script src="/path/to/loading-modal.js"></script>
```

5) Ensure CircleProgress is available globally as `window.CircleProgress`.

If you want to keep the same CDN used in `p/signup.php`, you can try:

```html
<script src="https://cdn.jsdelivr.net/gh/tigrr/circle-progress@v0.2.4/dist/circle-progress.min.js" type="module"></script>
```

Note: depending on the browser/module behavior, that script may or may not expose `CircleProgress` on `window`.

## Usage
### Show/hide
```js
OHPLoadingModal.show();
OHPLoadingModal.hide();
```

### Run the progress sequence (3s)
```js
OHPLoadingModal.show();
OHPLoadingModal.startProgress();
```

### Run progress then do something
```js
OHPLoadingModal.show();
OHPLoadingModal.startProgress({
  onDone: function () {
    console.log('progress finished');
  }
});
```

### Update status text manually
```js
OHPLoadingModal.show();
OHPLoadingModal.setText('Submitting…');
OHPLoadingModal.setProgress(42);
```
