<?php disallow_direct_load( 'sidebar.php' ); ?>

<?php if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'Sidebar' ) ): ?>
<?php endif; ?>
