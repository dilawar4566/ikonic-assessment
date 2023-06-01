<!DOCTYPE html>
<html lang="en">

<head>
  <?php get_header(); ?>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Ikonic</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
</head>

<body>

  <main id="main">
    <?php
    if (have_posts()) {
      while (have_posts()) {
        the_post();
        the_title('<h2>', '</h2>');
        the_content();
      }

      echo paginate_links();
    }
    ?>
  </main>
  <?php get_footer(); ?>