<nav class="d-flex align-items-center">
    <?php $logoVersion = @filemtime(__DIR__ . '/../../img/logo.svg') ?: time(); ?>
    <div class="container" style="color: #333;">
        <div class="row align-items-center">
            <div class="col-12 text-center">
                <img class="logo" src="../img/logo.svg?v=<?= $logoVersion ?>" alt="<?php echo $sitename; ?> logo">
            </div>
        </div>
    </div>
</nav>