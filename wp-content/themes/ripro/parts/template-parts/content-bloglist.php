<div class="col-12">
  <article id="post-<?php the_ID(); ?>" <?php post_class( 'post post-list' ); ?>>
    <?php cao_entry_media(); ?>
    <div class="entry-wrapper">
      <?php cao_entry_header( array( 'category' => true ,'author'=>true ) ); ?>
      <div class="entry-excerpt u-text-format"><?php echo _get_excerpt(); ?></div>
      <?php get_template_part( 'parts/entry-footer' ); ?>
    </div>
  </article>
</div>