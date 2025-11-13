<?php 
// Main Theme
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  
<header id="site-logo" class="sticky-top">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
    <div class="container">
      <a class="navbar-brand" href="<?php echo home_url(); ?>">
        <?php bloginfo('name'); ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <?php
        wp_nav_menu(array(
          'theme_location' => 'main_menu',
          'container' => 'div',
          'container_class' => 'collapse navbar-collapse',
          'container_id' => 'main-menu', // ঠিক করা
          'fallback_cb' => '__return_false', // ঠিক করা
          'menu_class' => 'navbar-nav ms-auto mb-2 mb-lg-0',
          'depth' => 2,
          'walker' => new Bootstrap5_WP_Navwalker(),
        ));
      ?>
    </div> <!-- container শেষ -->
  </nav>
</header>

<?php wp_footer(); ?>
</body>
</html>
