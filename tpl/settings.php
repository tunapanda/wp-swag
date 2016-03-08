<div class="wrap">
    <h2>Tunapanda Swag</h2>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $tabKey=>$tabLabel) { ?>
            <a href="<?php echo $adminUrl ?>&tab=<?php echo $tabKey; ?>"
                <?php if ($tab==$tabKey) { ?>
                    class="nav-tab nav-tab-active"
                <?php } else { ?>
                    class="nav-tab"
                <?php } ?>
            >
                <?php echo $tabLabel; ?>
            </a>
        <?php } ?>
    </h2>

    <?php echo $content; ?>
</div>