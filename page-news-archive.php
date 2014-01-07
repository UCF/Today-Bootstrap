<?php get_header();?>
    <div id="archives" class="subpage">
        <div class="row">
            <div class="span9 border-right">
                <?=do_shortcode('[sc-archive-articles]')?>
            </div>
            <div class="span3" id="sidebar">
                <?=esi_include('display_events')?>
            </div>
        </div>
    </div>
<?php get_footer();?>