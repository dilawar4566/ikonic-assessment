  <?php
  // include script and style

  function project_theme_scripts()

  {
    wp_enqueue_style('style', get_stylesheet_uri());
    wp_enqueue_script('main-js', get_template_directory_uri() . "/assets/js/main.js", array('jquery'), 1.1, true);
    wp_localize_script('main-js', 'ajax_object', array(
      'ajax_url' => admin_url('admin-ajax.php')
    ));
  }

  add_action('wp_enqueue_scripts', 'project_theme_scripts');

  // Function to Redirect Users Based on if their IP address starts with 77.29

  function redirect_users_by_ip()
  {
    $user_ip = $_SERVER['REMOTE_ADDR'];

    if (strpos($user_ip, '77.29.') === 0) {
      wp_redirect('https://www.google.com/');
      exit;
    }
  }
  add_action('init', 'redirect_users_by_ip');


  // Register Custom Post Type and Taxonomy:

  function register_custom_post_type()
  {
    $projects_labels = array(
      'name' => _x('Projects', 'taxonomy general name'),
      'singular_name' => _x('Project', 'taxonomy singular name'),
      'search_items' =>  __('Search Projects'),
      'all_items' => __('All Projects'),
      'parent_item' => __('Parent Project'),
      'parent_item_colon' => __('Parent Project:'),
      'edit_item' => __('Edit Project'),
      'update_item' => __('Update Project'),
      'add_new_item' => __('Add New Project'),
      'new_item_name' => __('New Project Name'),
      'menu_name' => __('Projects'),
    );

    register_post_type(
      'projects',
      array(
        'labels' => $projects_labels,
        'public' => true,
        'menu_icon' => 'dashicons-book',
        'has_archive' => true,
        'rewrite' => array('slug' => 'projects'),
        'show_in_rest' => true,
        'supports'            => array('title', 'editor'),

      )
    );
  }
  add_action('init', 'register_custom_post_type');

  function register_project_taxonomy()
  {

    $project_taxonomy_labels = array(
      'name' => _x('Projects Type', 'taxonomy general name'),
      'singular_name' => _x('Project Type', 'taxonomy singular name'),
      'search_items' =>  __('Search Projects Type'),
      'all_items' => __('All Projects Type'),
      'parent_item' => __('Parent Project Type'),
      'parent_item_colon' => __('Parent Project Type:'),
      'edit_item' => __('Edit Project'),
      'update_item' => __('Update Project Type'),
      'add_new_item' => __('Add New Project Type'),
      'new_item_name' => __('New Project Type Name'),
      'menu_name' => __('Projects Type'),
    );

    register_taxonomy('project_type', array('projects'), array(
      'hierarchical' => true,
      'labels' => $project_taxonomy_labels,
      'show_ui' => true,
      'show_in_rest' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => array('slug' => 'project_type'),
    ));
  }

  add_action('init', 'register_project_taxonomy');


  // shortcode for archive page with pagination

  function projects_shortcode_function($atts)
  {

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $args = array(
      'post_type' => 'projects',
      'posts_per_page' => 6,
      'paged' => $paged,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        the_title('<h2>', '</h2>');
        the_content();
      }
      echo '<div class="ik_pagination">';
      echo paginate_links(array(
        'total' => $query->max_num_pages,
        'current' => $paged,
      ));
      echo '</div>';
    } else {
      echo 'No projects found.';
    }

    wp_reset_postdata();
  }
  add_shortcode('projects_archive', 'projects_shortcode_function');


  // Ajax Endpoint for Project Retrieval if user is login it will return last 6 and if not it will return last 3 project:

  add_action("wp_ajax_ajax_get_projects", "ajax_get_projects");
  add_action("wp_ajax_nopriv_ajax_get_projects", "ajax_get_projects");

  function ajax_get_projects()
  {
    if (is_user_logged_in()) {
      $projects = get_posts([
        'post_type' => 'projects',
        'posts_per_page' => 6,
        'tax_query' => [
          [
            'taxonomy' => 'project_type',
            'field' => 'slug',
            'terms' => 'architecture',
          ],
        ],
      ]);
    } else {
      $projects = get_posts([
        'post_type' => 'projects',
        'posts_per_page' => 3,
        'tax_query' => [
          [
            'taxonomy' => 'project_type',
            'field' => 'slug',
            'terms' => 'architecture',
          ],
        ],
      ]);
    }

    $formatted_projects = [];
    foreach ($projects as $project) {
      $formatted_projects[] = [
        'id' => $project->ID,
        'title' => $project->post_title,
        'link' => get_permalink($project->ID),
      ];
    }

    $response = [
      'success' => true,
      'data' => $formatted_projects,  
    ];

    wp_send_json($response);
  }

  // Function to Retrieve Coffee Link using random coffee API:

  function coffee_link_shortcode_function()
  {
    $response = wp_remote_get('https://coffee.alexflipnote.dev/random.json');
    if (is_array($response)) {
      $coffee_data = json_decode($response['body'], true);
      $coffee_link = $coffee_data['file'];
      $html = '';
      $html .= '<img src="' . $coffee_link . '"  width="50%" height="50%">  ';
      return $html;
    }
    return '';
  }
  add_shortcode('coffee_link', 'coffee_link_shortcode_function');

  // By using this API https://api.kanye.rest/ and show 5 quotes on a page. 

  function quotes_shortcode_function()
  {
    $quotes = array();

    for ($i = 0; $i < 5; $i++) {
      $response = wp_remote_get('https://api.kanye.rest/quotes/');
      if (is_array($response) && !is_wp_error($response)) {
        $quote = json_decode($response['body'], true);
        if (isset($quote['quote'])) {
          $quotes[] = $quote['quote'];
        }
      }
    }

    if (!empty($quotes)) {
      foreach ($quotes as $quote) {
        echo '<p>' . $quote . '</p>';
      }
    } else {
      echo 'Unable to fetch quotes.';
    }
  }

  add_shortcode('quotes', 'quotes_shortcode_function');
