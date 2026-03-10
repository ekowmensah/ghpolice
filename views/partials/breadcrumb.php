<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $pageTitle ?? $title ?? 'Page' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?= url($crumb['url']) ?>"><?= sanitize($crumb['title']) ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= sanitize($crumb['title']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</div>
