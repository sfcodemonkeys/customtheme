<?php

function custom_theme_enqueue_styles() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style('custom-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'custom_theme_enqueue_styles');

// Menu

function custom_theme_setup() {
    register_nav_menus([
        'primary' => __('Primary Menu', 'custom-theme')
    ]);
}
add_action('after_setup_theme', 'custom_theme_setup');

class Bootstrap_Nav_Pills_Walker extends Walker_Nav_Menu {
    function start_lvl(&$output, $depth = 0, $args = null) {}
    function end_lvl(&$output, $depth = 0, $args = null) {}

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $active = in_array('current-menu-item', $item->classes) ? ' active' : '';
        $output .= '<li class="nav-item">';
        $output .= '<a class="nav-link' . $active . '" href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}



//Register CPT

function register_custom_post_types() {
    // Game CPT
    register_post_type('game', [
        'labels' => ['name' => 'Games', 'singular_name' => 'Game'],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'games'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'taxonomies' => ['category', 'post_tag'],
        'show_in_rest' => true
    ]);

    // Casino CPT
    register_post_type('casino', [
        'labels' => ['name' => 'Casino', 'singular_name' => 'Casino'],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'casino'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'taxonomies' => ['category', 'post_tag'],
        'show_in_rest' => true
    ]);
}
add_action('init', 'register_custom_post_types');

add_theme_support('post-thumbnails', ['casino']);


// Add custom fields

function casino_meta_boxes() {
    add_meta_box('casino_meta', 'Casino Details', 'render_casino_meta_box', 'casino', 'normal', 'high');
}
add_action('add_meta_boxes', 'casino_meta_boxes');

function render_casino_meta_box($post) {
    $fields = [
        'official_site' => 'Επίσημο site',
        'year_established' => 'Έτος ίδρυσης',
        'email' => 'Email επικοινωνίας',
        'loyalty' => 'Loyalty',
        'live_casino' => 'Live Casino',
        'mobile_casino' => 'Mobile Casino'
    ];

    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        echo "<label><strong>{$label}</strong><br />";
        echo "<input type='text' name='{$key}' value='" . esc_attr($value) . "' style='width: 100%;'/></label><br><br>";
    }

    // Ratings
    $rating_fields = ['games', 'live', 'payout', 'license', 'payments', 'withdrawal_speed', 'support', 'offers', 'mobile', 'website'];
    echo "<h4>Βαθμολογία</h4>";
    foreach ($rating_fields as $field) {
        $val = get_post_meta($post->ID, "rating_{$field}", true);
        echo "<div class='col-md-4 mb-3'><label>" . ucfirst($field) . ": <input type='number' step='0.01' min='1' max='10' name='rating_{$field}' value='{$val}' /></label></div>";
    }

    // Παιχνίδια (games)
    $games = get_posts(['post_type' => 'game', 'numberposts' => -1]);
    $selected_games = get_post_meta($post->ID, 'casino_games', true) ?: [];
    echo "<h4>Παιχνίδια</h4>";
    foreach ($games as $game) {
        $checked = in_array($game->ID, $selected_games) ? 'checked' : '';
        echo "<label><input type='checkbox' name='casino_games[]' value='{$game->ID}' $checked /> {$game->post_title}</label><br>";
    }
}

	function save_casino_meta($post_id) {
		$fields = ['official_site', 'year_established', 'email', 'loyalty', 'live_casino', 'mobile_casino'];
		foreach ($fields as $field) {
			update_post_meta($post_id, $field, sanitize_text_field($_POST[$field] ?? ''));
		}

		$rating_fields = ['games', 'live', 'payout', 'license', 'payments', 'withdrawal_speed', 'support', 'offers', 'mobile', 'website'];
		$total = 0;
		foreach ($rating_fields as $field) {
			$value = floatval($_POST["rating_{$field}"] ?? 0);
			update_post_meta($post_id, "rating_{$field}", $value);
			$total += $value;
		}
		$average = $total / count($rating_fields);
		update_post_meta($post_id, 'total_rating', round($average, 1));

		update_post_meta($post_id, 'casino_games', $_POST['casino_games'] ?? []);
	}
	add_action('save_post_casino', 'save_casino_meta');

	function game_meta_boxes() {
    add_meta_box('game_meta', 'Σχετικά Καζίνο', 'render_game_meta_box', 'game', 'normal', 'high');
	}
	add_action('add_meta_boxes', 'game_meta_boxes');

	function render_game_meta_box($post) {
		$casinos = get_posts(['post_type' => 'casino', 'numberposts' => -1]);
		foreach ($casinos as $casino) {
			$games = get_post_meta($casino->ID, 'casino_games', true) ?: [];
			$checked = in_array($post->ID, $games) ? 'checked' : '';
			echo "<label><input type='checkbox' name='game_casinos[]' value='{$casino->ID}' $checked> {$casino->post_title}</label><br>";
		}
	}

	add_action('save_post_casino', 'update_final_score_meta', 10, 30);

function update_final_score_meta($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'casino') return;
    $rating_fields = ['games', 'live', 'payout', 'license', 'payments', 'withdrawal_speed', 'support', 'offers', 'mobile', 'website'];
    $scores = [];

    foreach ($rating_fields as $field) {
        $val = get_post_meta($post_id, 'rating_'.$field, true);
        if (is_numeric($val)) {
            $scores[] = floatval($val);
        }
    }

    if (count($scores) > 0) {
        $average = array_sum($scores) / count($scores);
        update_post_meta($post_id, 'final_score', round($average, 2));
    } else {
        delete_post_meta($post_id, 'final_score');
    }



}
	function save_game_meta($post_id) {
		if (isset($_POST['game_casinos'])) {
			$selected = $_POST['game_casinos'];
			foreach (get_posts(['post_type' => 'casino', 'numberposts' => -1]) as $casino) {
				$games = get_post_meta($casino->ID, 'casino_games', true) ?: [];
				if (in_array($casino->ID, $selected)) {
					if (!in_array($post_id, $games)) {
						$games[] = $post_id;
					}
				} else {
					$games = array_diff($games, [$post_id]);
				}
				update_post_meta($casino->ID, 'casino_games', $games);
			}
		}
	}
	add_action('save_post_game', 'save_game_meta');


	// [casino_table] Shortcode
	function render_casino_table($atts) {

		$atts = shortcode_atts([
			'title' => 'Best Casino',
			'template' => '1',
			'second_col' => 'loyalty',
		], $atts, 'casino_table');

		$casinos = get_posts([
			'post_type' => 'casino',
			'posts_per_page' => -1,
			'meta_key' => 'final_score',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		]);


		$output = '<div class="casino-table-template">';

		// Template 2: Add dropdown
		if ($atts['template'] == '2') {
            if (isset($_GET['second_col'])) {
                $atts['second_col'] = sanitize_text_field($_GET['second_col']);
            }
			$options = [
				'loyalty' => 'Loyalty',
				'live_casino' => 'Live Casino',
				'mobile_casino' => 'Mobile Casino',
				'year' => 'Έτος ίδρυσης',
				'email' => 'Email επικοινωνίας',
				'games' => 'Παιχνίδια'
			];
			$output .= '<div class="d-flex justify-content-between align-items-center mb-3">';
			$output .= '<h3>' . esc_html($atts['title']) . '</h3>';
			$output .= '<select class="form-select w-auto casino-select-col" onchange="casinoTableColumnChange(this)">';
			foreach ($options as $key => $label) {
				$output .= '<option value="' . esc_attr($key) . '"' . selected($atts['second_col'], $key, false) . '>' . esc_html($label) . '</option>';
			}
			$output .= '</select></div>';
		} else {
			$output .= '<h3>' . esc_html($atts['title']) . '</h3>';
		}

		$output .= '<table class="table table-bordered">';
		$output .= '<thead><tr><th>Καζίνο</th><th class="second-col">';

		$field_map = [
			'loyalty' => 'Loyalty',
			'live_casino' => 'Live Casino',
			'mobile_casino' => 'Mobile Casino',
			'year' => 'Έτος ίδρυσης',
			'email' => 'Email επικοινωνίας',
			'games' => 'Παιχνίδια'
		];

		$output .= esc_html($field_map[$atts['second_col']]) . '</th><th>Αξιολόγηση</th></tr></thead><tbody>';

		foreach ($casinos as $casino) {
			$site = get_post_meta($casino->ID, 'official_site', true);
			$final_score = get_post_meta($casino->ID, 'final_score', true);
			$second_value = '';

			switch ($atts['second_col']) {
				case 'loyalty':
				case 'live_casino':
				case 'mobile_casino':
					$second_value = get_post_meta($casino->ID, $atts['second_col'], true) ? 'ΝΑΙ' : 'ΟΧΙ';
					break;
				case 'year':
					$second_value = get_post_meta($casino->ID, 'year', true);
					break;
				case 'email':
					$second_value = get_post_meta($casino->ID, 'email', true);
					break;
				case 'games':
					$game_ids = get_post_meta($casino->ID, 'casino_games', true);
					if (is_array($game_ids)) {
						$second_value = '<ul>';
						foreach ($game_ids as $game_id) {
							$second_value .= '<li><a href="' . get_permalink($game_id) . '">' . get_the_title($game_id) . '</a></li>';
						}
						$second_value .= '</ul>';
					}
					break;
			}

			$output .= '<tr><td>';
			if (has_post_thumbnail($casino->ID)) {
				$img = get_the_post_thumbnail($casino->ID, 'thumbnail');
				$output .= $site ? '<a href="' . esc_url($site) . '" target="_blank">' . $img . '</a>' : $img;
			}
           
            $output .= casino_display_stars($final_score);
			// '<br>' . esc_html($final_score) . '</td>';
			$output .= '<td class="second-col">' . $second_value . '</td>';
			$output .= '<td>';
			$output .= $site ? '<a href="' . esc_url($site) . '" class="btn btn-primary" target="_blank">Αξιολόγηση</a>' : 'Αξιολόγηση';
			$output .= '</td></tr>';
		}

		$output .= '</tbody></table></div>';

		// JavaScript to dynamically switch column in template 2
		if ($atts['template'] == '2') {
			$output .= "<script>
			function casinoTableColumnChange(select) {
				const col = select.value;
				const url = new URL(window.location.href);
				url.searchParams.set('second_col', col);
				window.location.href = url.toString();
			}
			</script>";
		}

		return $output;
	}


    function casino_display_stars($score) {
        $stars = round($score / 2); 
        $output = '<div class="casino-stars">';
    
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $stars) {
                $output .= '<span class="star full">★</span>';
            } else {
                $output .= '<span class="star empty">★</span>';
            }
        }
    
        $output .= '</div>';
        return $output;
    }


	add_shortcode('casino_table', 'render_casino_table');

	function custom_widgets_init() {
    register_sidebar([
        'name'          => __('Footer Widget Area 1', 'custom-theme'),
        'id'            => 'footer-1',
        'description'   => __('Περιοχή widgets για το footer.', 'custom-theme'),
        'before_widget' => '<div class="footer-widget mb-3">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title">',
        'after_title'   => '</h5>',
	    ]);

	 register_sidebar([
        'name'          => __('Footer Widget Area 2 ', 'custom-theme'),
        'id'            => 'footer-2',
        'description'   => __('Περιοχή widgets για το footer.', 'custom-theme'),
        'before_widget' => '<div class="footer-widget mb-3">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title">',
        'after_title'   => '</h5>',
            ]);

	 register_sidebar([
        'name'          => __('Footer Widget Area 3', 'custom-theme'),
        'id'            => 'footer-3',
        'description'   => __('Περιοχή widgets για το footer.', 'custom-theme'),
        'before_widget' => '<div class="footer-widget mb-3">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title">',
        'after_title'   => '</h5>',
            ]);

	 register_sidebar([
        'name'          => __('Footer Widget Area 4', 'custom-theme'),
        'id'            => 'footer-4',
        'description'   => __('Περιοχή widgets για το footer.', 'custom-theme'),
        'before_widget' => '<div class="footer-widget mb-3">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title">',
        'after_title'   => '</h5>',
            ]);
	    register_sidebar([
        'name'          => __('Main Sidebar', 'custom-theme'),
        'id'            => 'main-sidebar',
        'description'   => __('Η βασική πλευρική στήλη του θέματος.', 'custom-theme'),
        'before_widget' => '<div class="widget mb-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widget-title mb-2">',
        'after_title'   => '</h5>',
    	]);
	}

	add_action('widgets_init', 'custom_widgets_init');	

	//sidebar

	class Category_Tabs_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'category_tabs_widget',
            __('Κατηγορίες σε Tabs', 'custom-theme'),
            ['description' => __('Εμφανίζει άρθρα από δύο κατηγορίες σε tabs.', 'custom-theme')]
        );
    }

    public function widget($args, $instance) {
        $cat1 = !empty($instance['cat1']) ? intval($instance['cat1']) : 1;
        $cat2 = !empty($instance['cat2']) ? intval($instance['cat2']) : 2;

        echo $args['before_widget'];
        ?>

        <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab">
                    <?php echo get_cat_name($cat1); ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab">
                    <?php echo get_cat_name($cat2); ?>
                </button>
            </li>
        </ul>
        <div class="tab-content mt-2" id="categoryTabsContent">
            <div class="tab-pane fade show active" id="tab1" role="tabpanel">
                <?php $this->render_posts($cat1); ?>
            </div>
            <div class="tab-pane fade" id="tab2" role="tabpanel">
                <?php $this->render_posts($cat2); ?>
            </div>
        </div>

        <?php
        echo $args['after_widget'];
    }

	private function render_posts($cat_id) {
    $query = new WP_Query([
        'cat' => $cat_id,
        'posts_per_page' => 3,
    ]);

    echo '<ul class="list-unstyled">';
    while ($query->have_posts()) {
        $query->the_post();
        echo '<li class="d-flex mb-3 align-items-start">';
        
        if (has_post_thumbnail()) {
            echo '<a href="' . get_permalink() . '" class="me-3" style="width: 60px; flex-shrink: 0;">';
            the_post_thumbnail('thumbnail', ['class' => 'img-fluid']);
            echo '</a>';
        }

        echo '<div>';
        echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
        echo '</div>';

        echo '</li>';
    }
    echo '</ul>';
    wp_reset_postdata();
}

	public function form($instance) {
    $cat1 = !empty($instance['cat1']) ? intval($instance['cat1']) : '';
    $cat2 = !empty($instance['cat2']) ? intval($instance['cat2']) : '';

    $categories = get_categories(['hide_empty' => false]);
    ?>
    <p>
        <label for="<?php echo $this->get_field_id('cat1'); ?>"><?php _e('Κατηγορία 1:'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('cat1'); ?>" name="<?php echo $this->get_field_name('cat1'); ?>">
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($cat1, $cat->term_id); ?>>
                    <?php echo esc_html($cat->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('cat2'); ?>"><?php _e('Κατηγορία 2:'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('cat2'); ?>" name="<?php echo $this->get_field_name('cat2'); ?>">
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($cat2, $cat->term_id); ?>>
                    <?php echo esc_html($cat->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['cat1'] = (!empty($new_instance['cat1'])) ? strip_tags($new_instance['cat1']) : '';
        $instance['cat2'] = (!empty($new_instance['cat2'])) ? strip_tags($new_instance['cat2']) : '';
        return $instance;
    }
}

function register_category_tabs_widget() {
    register_widget('Category_Tabs_Widget');
}
add_action('widgets_init', 'register_category_tabs_widget');

