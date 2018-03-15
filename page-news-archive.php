<?php get_header();?>
<?php echo gen_alerts_html(); ?>
    <div id="archives" class="subpage">
        <div class="row">
            <div class="span9 border-right">
                <?=do_shortcode('[sc-archive-articles]')?>
            </div>
            <div class="span3" id="sidebar">
                <?=esi_include('do_shortcode', '[events]', true)?>
            </div>
        </div>
    </div>
<?php get_footer();?>
