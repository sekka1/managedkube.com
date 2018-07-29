<?php
/**
 * DogPaw functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package DogPaw
 */

if ( ! function_exists( 'dogpaw_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function dogpaw_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on DogPaw, use a find and replace
	 * to change 'dogpaw' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'dogpaw', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'menu-1' => esc_html__( 'Primary', 'dogpaw' ),
	) );


	// membership navbar
	function custom_menu_shortcode($atts) {
	    extract(shortcode_atts(array( 'name' => null, ), $atts));
	    return wp_nav_menu( array(
	        'menu' => 'membership-sidebar',
	        'echo' => false,
	        'menu_class' => 'membership-sidebar-menu'
		  ) );
	}
	add_shortcode('menu', 'custom_menu_shortcode');



	if (!is_admin()) add_action("wp_enqueue_scripts", "my_jquery_enqueue", 11);
	function my_jquery_enqueue() {
	   wp_deregister_script('jquery');
	   wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", false, null);
	   wp_enqueue_script('jquery');
	}



	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'dogpaw_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 250,
		'width'       => 250,
		'flex-width'  => true,
		'flex-height' => true,
	) );
}
endif;
add_action( 'after_setup_theme', 'dogpaw_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function dogpaw_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'dogpaw_content_width', 640 );
}
add_action( 'after_setup_theme', 'dogpaw_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function dogpaw_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'dogpaw' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'dogpaw' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'dogpaw_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function dogpaw_scripts() {
	wp_enqueue_style( 'dogpaw-style', get_stylesheet_uri() );

	wp_enqueue_script( 'dogpaw-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'dogpaw-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dogpaw_scripts' );


// add page name to body class
add_filter( 'body_class', 'body_class_for_pages' );

function body_class_for_pages( $classes ) {

	if ( is_singular( 'page' ) ) {
		global $post;
		$classes[] = $post->post_name;
	}

	return $classes;

}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';



/**
 * Adds the custom fields to the registration form and profile editor
 *
 */
function pw_rcp_add_bizname_fields() {

	$bizname = get_user_meta( get_current_user_id(), 'rcp_bizname', true );
	?>

	<p class="p-dark" id="rcp_bizname_wrap">
		<label for="rcp_bizname"><?php _e( 'Name of Business', 'rcp' ); ?></label>
		<input name="rcp_bizname" placeholder="Name of Business" id="rcp_bizname" type="text" value="<?php echo esc_attr( $bizname  ); ?>"/>
	</p>
	<?php
}
add_action( 'rcp_after_password_registration_field', 'pw_rcp_add_bizname_fields' );
// add_action( 'rcp_profile_editor_after', 'pw_rcp_add_address_fields' );


/**
 * Adds the custom fields to the member edit screen
 *
 */
function pw_rcp_add_bizname_edit_fields( $user_id = 0 ) {

	$bizname = get_user_meta( $user_id, 'rcp_bizname', true );

	?>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_bizname"><?php _e( 'Name of Business', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_bizname" placeholder="Name of Business" id="rcp_bizname" type="text" value="<?php echo esc_attr( $bizname ); ?>"/>
		</td>
	</tr>
	<?php
}
add_action( 'rcp_edit_member_after', 'pw_rcp_add_bizname_edit_fields' );


/**
 * Stores the information submitted during registration
 *
 */
function pw_rcp_save_bizname_fields_on_register( $posted, $user_id ) {
	if( ! empty( $posted['rcp_bizname'] ) ) {
		update_user_meta( $user_id, 'rcp_bizname', sanitize_text_field( $posted['rcp_bizname'] ) );
	}
}
add_action( 'rcp_form_processing', 'pw_rcp_save_bizname_fields_on_register', 10, 2 );



/**
 * Stores the information submitted profile update
 *
 */
function pw_rcp_save_bizname_fields_on_profile_save( $user_id ) {
		update_user_meta( $user_id, 'rcp_bizname', sanitize_text_field( $_POST['rcp_bizname'] ) );
}
add_action( 'rcp_user_profile_updated', 'pw_rcp_save_bizname_fields_on_profile_save', 10 );
add_action( 'rcp_edit_member', 'pw_rcp_save_bizname_fields_on_profile_save', 10 );



/**
 * Example for adding a custom select field to the
 * Restrict Content Pro registration form and profile editors.
 */
/**
 * Adds a custom select field to the registration form and profile editor.
 */
function ag_rcp_add_select_field() {
    $referrer = get_user_meta( get_current_user_id(), 'rcp_referrer', true );
    ?>
    <p>
        <label class="dropdown_label" for="rcp_referrer"><?php _e( 'How did you find out about Dogpaw?', 'rcp' ); ?></label>
        <select id="rcp_homepark" name="rcp_referrer">
            <option value="friend" <?php selected( $referrer, 'friend'); ?>><?php _e( 'From a friend', 'rcp' ); ?></option>
            <option value="search" <?php selected( $referrer, 'search'); ?>><?php _e( 'Search engine', 'rcp' ); ?></option>
            <option value="social" <?php selected( $referrer, 'social'); ?>><?php _e( 'Social media', 'rcp' ); ?></option>
            <option value="other" <?php selected( $referrer, 'other'); ?>><?php _e( 'Other', 'rcp' ); ?></option>
        </select>
    </p>

    <?php
}
add_action( 'rcp_after_password_registration_field', 'ag_rcp_add_select_field' );

/**
 * Adds the custom select field to the member edit screen.
 */
function ag_rcp_add_select_member_edit_field( $user_id = 0 ) {
    $referrer = get_user_meta( $user_id, 'rcp_referrer', true );
    ?>
    <tr valign="top">
        <th scope="row" valign="top">
            <label for="rcp_referrer"><?php _e( 'Referred By', 'rcp' ); ?></label>
        </th>
        <td>
            <select id="rcp_referrer" name="rcp_referrer">
                <option value="friend" <?php selected( $referrer, 'friend'); ?>><?php _e( 'From a friend', 'rcp' ); ?></option>
                <option value="search" <?php selected( $referrer, 'search'); ?>><?php _e( 'Search engine', 'rcp' ); ?></option>
                <option value="social" <?php selected( $referrer, 'social'); ?>><?php _e( 'Social media', 'rcp' ); ?></option>
                <option value="other" <?php selected( $referrer, 'other'); ?>><?php _e( 'Other', 'rcp' ); ?></option>
            </select>
        </td>
    </tr>
    <?php
}
add_action( 'rcp_edit_member_after', 'ag_rcp_add_select_member_edit_field' );
/**
 * Determines if there are problems with the registration data submitted.
 */
function ag_rcp_validate_select_on_register( $posted ) {
    if ( is_user_logged_in() ) {
        return;
    }

    // List all the available options that can be selected.
    $available_choices = array(
        'friend',
        'search',
        'social',
        'other'
    );
    // Add an error message if the submitted option isn't one of our valid choices.
    if ( ! in_array( $posted['rcp_referrer'], $available_choices ) ) {
        rcp_errors()->add( 'invalid_referrer', __( 'Please select a valid referrer', 'rcp' ), 'register' );
    }
}
add_action( 'rcp_form_errors', 'ag_rcp_validate_select_on_register', 10 );
/**
 * Stores the information submitted during registration.
 */
function ag_rcp_save_select_field_on_register( $posted, $user_id ) {
    if ( ! empty( $posted['rcp_referrer'] ) ) {
        update_user_meta( $user_id, 'rcp_referrer', sanitize_text_field( $posted['rcp_referrer'] ) );
    }
}
add_action( 'rcp_form_processing', 'ag_rcp_save_select_field_on_register', 10, 2 );
/**
 * Stores the information submitted during profile update.
 */
function ag_rcp_save_select_field_on_profile_save( $user_id ) {

    // List all the available options that can be selected.
    $available_choices = array(
        'friend',
        'search',
        'social',
        'other'
    );

    if ( isset( $_POST['rcp_referrer'] ) && in_array( $_POST['rcp_referrer'], $available_choices ) ) {
        update_user_meta( $user_id, 'rcp_referrer', sanitize_text_field( $_POST['rcp_referrer'] ) );
    }
}

add_action( 'rcp_user_profile_updated', 'ag_rcp_save_select_field_on_profile_save', 10 );
add_action( 'rcp_edit_member', 'ag_rcp_save_select_field_on_profile_save', 10 );


/**
 * Adds the custom fields to the registration form and profile editor
 *
 */
function pw_rcp_add_address_fields() {

	$street = get_user_meta( get_current_user_id(), 'rcp_street', true );
	$city   = get_user_meta( get_current_user_id(), 'rcp_city', true );
	$state   = get_user_meta( get_current_user_id(), 'rcp_state', true );
	$zipcode   = get_user_meta( get_current_user_id(), 'rcp_zipcode', true );

	?>

	<h2 class="h2 rcp_subtitle">Mailing Address</h2>

	<p class="p-dark" id="rcp_user_street_wrap">
		<label for="rcp_street"><?php _e( 'Street Address', 'rcp' ); ?></label>
		<input name="rcp_street" placeholder="Street Address" id="rcp_street" type="text" value="<?php echo esc_attr( $street ); ?>"/>
	</p>

	<p class="p-dark" id="rcp_user_city_wrap">
		<label for="rcp_city"><?php _e( 'City', 'rcp' ); ?></label>
		<input name="rcp_city" placeholder="City" id="rcp_city" type="text" value="<?php echo esc_attr( $city ); ?>"/>
	</p>

	<p class="p-dark" id="rcp_user_state_wrap">
		<label for="rcp_state"><?php _e( 'State', 'rcp' ); ?></label>
		<input maxlength="2" name="rcp_state" placeholder="State" id="rcp_state" type="text" value="<?php echo esc_attr( $state ); ?>"/>
	</p>

	<p class="p-dark" id="rcp_user_zipcode_wrap">
		<label for="rcp_zipcode"><?php _e( 'Zip code', 'rcp' ); ?></label>
		<input name="rcp_zipcode" placeholder="Zip code" id="rcp_zipcode" type="text" value="<?php echo esc_attr( $zipcode ); ?>"/>
	</p>
	<?php
}
add_action( 'rcp_after_password_registration_field', 'pw_rcp_add_address_fields' );
add_action( 'rcp_profile_editor_after', 'pw_rcp_add_address_fields' );


/**
 * Adds the custom fields to the member edit screen
 *
 */
function pw_rcp_add_address_edit_fields( $user_id = 0 ) {

	$street = get_user_meta( $user_id, 'rcp_street', true );
	$city   = get_user_meta( $user_id, 'rcp_city', true );
	$state   = get_user_meta( $user_id, 'rcp_state', true );
	$zipcode   = get_user_meta( $user_id, 'rcp_zipcode', true );

	?>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_street"><?php _e( 'Street Address', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_street" placeholder="Street Address" id="rcp_street" type="text" value="<?php echo esc_attr( $street ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_city"><?php _e( 'City', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_city" placeholder="City" id="rcp_city" type="text" value="<?php echo esc_attr( $city ); ?>"/>
		</td>
	</tr>
		<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_state"><?php _e( 'State', 'rcp' ); ?></label>
		</th>
		<td>
			<input maxlength="2" name="rcp_state" placeholder="State" id="rcp_state" type="text" value="<?php echo esc_attr( $state ); ?>"/>
		</td>
	</tr>
		<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_zipcode"><?php _e( 'Zip code', 'rcp' ); ?></label>
		<td>
			<input name="rcp_zipcode" placeholder="Zip code" id="rcp_zipcode" type="text" value="<?php echo esc_attr( $zipcode ); ?>"/>
		</td>
	</tr>
	<?php
}
add_action( 'rcp_edit_member_after', 'pw_rcp_add_address_edit_fields' );

/**
 * Determines if there are problems with the registration data submitted
 *
 */
function pw_rcp_validate_address_fields_on_register( $posted ) {
	if ( is_user_logged_in() ) {
	   return;
	}
	if( empty( $posted['rcp_street'] ) ) {
		rcp_errors()->add( 'invalid_street', __( 'Please enter your street', 'rcp' ), 'register' );
	}
	if( empty( $posted['rcp_city'] ) ) {
		rcp_errors()->add( 'invalid_city', __( 'Please enter your city', 'rcp' ), 'register' );
	}
	if( empty( $posted['rcp_state'] ) ) {
		rcp_errors()->add( 'invalid_state', __( 'Please enter your state', 'rcp' ), 'register' );
	}
	if( empty( $posted['rcp_zipcode'] ) ) {
		rcp_errors()->add( 'invalid_zipcode', __( 'Please enter your zip code', 'rcp' ), 'register' );
	}
}
add_action( 'rcp_form_errors', 'pw_rcp_validate_address_fields_on_register', 10 );


/**
 * Stores the information submitted during registration
 *
 */
function pw_rcp_save_address_fields_on_register( $posted, $user_id ) {
	if( ! empty( $posted['rcp_street'] ) ) {
		update_user_meta( $user_id, 'rcp_street', sanitize_text_field( $posted['rcp_street'] ) );
	}
	if( ! empty( $posted['rcp_city'] ) ) {
		update_user_meta( $user_id, 'rcp_city', sanitize_text_field( $posted['rcp_city'] ) );
	}
	if( ! empty( $posted['rcp_state'] ) ) {
		update_user_meta( $user_id, 'rcp_state', sanitize_text_field( $posted['rcp_state'] ) );
	}
	if( ! empty( $posted['rcp_zipcode'] ) ) {
		update_user_meta( $user_id, 'rcp_zipcode', sanitize_text_field( $posted['rcp_zipcode'] ) );
	}
}
add_action( 'rcp_form_processing', 'pw_rcp_save_address_fields_on_register', 10, 2 );



/**
 * Stores the information submitted profile update
 *
 */
function pw_rcp_save_address_fields_on_profile_save( $user_id ) {
		update_user_meta( $user_id, 'rcp_street', sanitize_text_field( $_POST['rcp_street'] ) );
		update_user_meta( $user_id, 'rcp_city', sanitize_text_field( $_POST['rcp_city'] ) );
		update_user_meta( $user_id, 'rcp_state', sanitize_text_field( $_POST['rcp_state'] ) );
		update_user_meta( $user_id, 'rcp_zipcode', sanitize_text_field( $_POST['rcp_zipcode'] ) );
}
add_action( 'rcp_user_profile_updated', 'pw_rcp_save_address_fields_on_profile_save', 10 );
add_action( 'rcp_edit_member', 'pw_rcp_save_address_fields_on_profile_save', 10 );




/**
 * Example for adding a custom select field to the
 * Restrict Content Pro registration form and profile editors.
 */
/**
 * Adds a custom select field to the registration form and profile editor.
 */
function ag_rcp_add_homepark_field() {
    $homepark = get_user_meta( get_current_user_id(), 'rcp_homepark', true );
    ?>
    <div class="register-display-none">
        <label class="h2 rcp_subtitle" for="rcp_homepark"><?php _e( 'Where is your homepark?', 'rcp' ); ?></label>
        <p class="p-dark">Homepark is the <span class="dogpaw">dogpaw</span> park you and your dogs visit the most.</p>
        <select id="rcp_homepark" name="rcp_homepark">
	        <option value="" <?php selected( $homepark, ''); ?>><?php _e( '', 'rcp' ); ?></option>
            <option value="Dakota" <?php selected( $homepark, 'Dakota'); ?>><?php _e( 'Dakota', 'rcp' ); ?></option>
            <option value="Ike" <?php selected( $homepark, 'Ike'); ?>><?php _e( 'Ike', 'rcp' ); ?></option>
            <option value="Kane" <?php selected( $homepark, 'Kane'); ?>><?php _e( 'Kane', 'rcp' ); ?></option>
            <option value="Lucky" <?php selected( $homepark, 'Lucky'); ?>><?php _e( 'Lucky', 'rcp' ); ?></option>
        </select>
    </div>

    <?php
}
add_action( 'rcp_after_password_registration_field', 'ag_rcp_add_homepark_field' );
add_action( 'rcp_profile_editor_after', 'ag_rcp_add_homepark_field' );
/**
 * Adds the custom select field to the member edit screen.
 */
function ag_rcp_add_homepark_member_edit_field( $user_id = 0 ) {
    $homepark = get_user_meta( $user_id, 'rcp_homepark', true );
    ?>
    <tr valign="top" class="bar">
        <th scope="row" valign="top">
            <label class="dropdown_label" for="rcp_homepark"><?php _e( 'Homepark: ', 'rcp' ); ?></label>
        </th>
        <td>
            <select id="rcp_homepark" name="rcp_homepark">
            	<option value="" <?php selected( $homepark, ''); ?>><?php _e( '', 'rcp' ); ?></option>
                <option value="Dakota" <?php selected( $homepark, 'Dakota'); ?>><?php _e( 'Dakota', 'rcp' ); ?></option>
                <option value="Ike" <?php selected( $homepark, 'Ike'); ?>><?php _e( 'Ike', 'rcp' ); ?></option>
                <option value="Kane" <?php selected( $homepark, 'Kane'); ?>><?php _e( 'Kane', 'rcp' ); ?></option>
                <option value="Lucky" <?php selected( $homepark, 'Lucky'); ?>><?php _e( 'Lucky', 'rcp' ); ?></option>
            </select>
        </td>
    </tr>
    <?php
}
add_action( 'rcp_edit_member_after', 'ag_rcp_add_homepark_member_edit_field' );
/**
 * Determines if there are problems with the registration data submitted.
 */
function ag_rcp_validate_homepark_on_register( $posted ) {
    if ( rcp_get_subscription_id() ) {
        return;
    }

    // List all the available options that can be selected.
    $available_choices = array(
    	'',
        'Dakota',
        'Ike',
        'Kane',
        'Lucky'
    );
    // Add an error message if the submitted option isn't one of our valid choices.
    if ( ! in_array( $posted['rcp_homepark'], $available_choices ) ) {
        rcp_errors()->add( 'invalid_homepark', __( 'Please select a valid homepark', 'rcp' ), 'register' );
    }
}
add_action( 'rcp_form_errors', 'ag_rcp_validate_homepark_on_register', 10 );
/**
 * Stores the information submitted during registration.
 */
function ag_rcp_save_homepark_field_on_register( $posted, $user_id ) {
    if ( ! empty( $_POST['rcp_homepark'] ) ) {
        update_user_meta( $user_id, 'rcp_homepark', sanitize_text_field( $_POST['rcp_homepark'] ) );
    }
}
add_action( 'rcp_form_processing', 'ag_rcp_save_homepark_field_on_register', 10, 2 );
/**
 * Stores the information submitted during profile update.
 */
function ag_rcp_save_homepark_field_on_profile_save( $user_id ) {

    // List all the available options that can be selected.
    $available_choices = array(
    	'',
        'Dakota',
        'Ike',
        'Kane',
        'Lucky'
    );
    if ( isset( $_POST['rcp_homepark'] ) && in_array( $_POST['rcp_homepark'], $available_choices ) ) {
        update_user_meta( $user_id, 'rcp_homepark', sanitize_text_field( $_POST['rcp_homepark'] ) );
    }
}
add_action( 'rcp_user_profile_updated', 'ag_rcp_save_homepark_field_on_profile_save', 10 );
add_action( 'rcp_edit_member', 'ag_rcp_save_homepark_field_on_profile_save', 10 );







function pw_rcp_dog_info_fields() {

	$dogName1 = get_user_meta( get_current_user_id(), 'rcp_dogname1', true );
	$dogBday1  = get_user_meta( get_current_user_id(), 'rcp_dogbday1', true );
	$licence1 = get_user_meta( get_current_user_id(), 'rcp_licence1', true );

	$dogName2 = get_user_meta( get_current_user_id(), 'rcp_dogname2', true );
	$dogBday2  = get_user_meta( get_current_user_id(), 'rcp_dogbday2', true );
	$licence2 = get_user_meta( get_current_user_id(), 'rcp_licence2', true );

	$dogName3 = get_user_meta( get_current_user_id(), 'rcp_dogname3', true );
	$dogBday3  = get_user_meta( get_current_user_id(), 'rcp_dogbday3', true );
	$licence3 = get_user_meta( get_current_user_id(), 'rcp_licence3', true );

	?>
	<div class="register-display-none">
		<h2 class="h2 rcp_subtitle">Dog Profile</h2>


		<p class="p-dark">
			<label for="rcp_dognam1"><?php _e( 'Name of your dog', 'rcp' ); ?></label>
			<input name="rcp_dogname1" placeholder="Name of your dog (optional)" id="rcp_dogname1" type="text" value="<?php echo esc_attr( $dogName1 ); ?>"/>
		</p>

		<p class="p-dark">
			<label for="rcp_dogbday1"><?php _e( 'Birthday of your dog', 'rcp' ); ?></label>
			<input name="rcp_dogbday1" placeholder="Birthday of your dog" id="rcp_dogbday1" type="text" value="<?php echo esc_attr( $dogBday1 ); ?>"/>
		</p>

		<p class="p-dark" class="rcp_user_licence_wrap">
			<label for="rcp_licence1"><?php _e( 'Dog License', 'rcp' ); ?></label>
			<input name="rcp_licence1" placeholder="Dog License" id="rcp_licence1" type="text" value="<?php echo esc_attr( $licence1 ); ?>"/>
		</p>

		<p class="p-dark add-more"> + Add more dogs</p>
		<br><br>
		<div class="moreDogInfo">
			<p class="p-dark">
				<label for="rcp_dogname2"><?php _e( 'Name of second dog', 'rcp' ); ?></label>
				<input name="rcp_dogname2" placeholder="Name of your dog (optional)" id="rcp_dogname2" type="text" value="<?php echo esc_attr( $dogName2 ); ?>"/>
			</p>
			<p class="p-dark">
				<label for="rcp_dogbday2"><?php _e( 'Birthday of second dog', 'rcp' ); ?></label>
				<input name="rcp_dogbday2" placeholder="Name of your dog (optional)" id="rcp_dogbday2" type="text" value="<?php echo esc_attr( $dogBday2 ); ?>"/>
			</p>
			<p class="p-dark" class="rcp_user_licence_wrap">
				<label for="rcp_licence2"><?php _e( 'Dog License', 'rcp' ); ?></label>
				<input name="rcp_licence2" placeholder="Dog License" id="rcp_licence2" type="text" value="<?php echo esc_attr( $licence2 ); ?>"/>
			</p>
			<br><br>
			<p class="p-dark">
				<label for="rcp_dogname3"><?php _e( 'Name of third dog', 'rcp' ); ?></label>
				<input name="rcp_dogname3" placeholder="Name of your dog (optional)" id="rcp_dogname3" type="text" value="<?php echo esc_attr( $dogName3 ); ?>"/>
			</p>
			<p class="p-dark">
				<label for="rcp_dogbday3"><?php _e( 'Birthday of third dog', 'rcp' ); ?></label>
				<input name="rcp_dogbday3" placeholder="Name of your dog (optional)" id="rcp_dogbday3" type="text" value="<?php echo esc_attr( $dogBday2 ); ?>"/>
			</p>
			<p class="p-dark" class="rcp_user_licence_wrap">
				<label for="rcp_licence3"><?php _e( 'Dog License', 'rcp' ); ?></label>
				<input name="rcp_licence3" placeholder="Dog License" id="rcp_licence3" type="text" value="<?php echo esc_attr( $licence3 ); ?>"/>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'rcp_after_password_registration_field', 'pw_rcp_dog_info_fields' );
add_action( 'rcp_profile_editor_after', 'pw_rcp_dog_info_fields' );


function pw_rcp_dog_info_edit_fields( $user_id = 0 ) {

	$dogName1 = get_user_meta( $user_id, 'rcp_dogname1', true );
	$dogBday1  = get_user_meta( $user_id, 'rcp_dogbday1', true );
	$licence1 = get_user_meta( $user_id, 'rcp_licence1', true );

	$dogName2 = get_user_meta( $user_id, 'rcp_dogname2', true );
	$dogBday2  = get_user_meta( $user_id, 'rcp_dogbday2', true );
	$licence2 = get_user_meta( $user_id, 'rcp_licence2', true );

	$dogName3 = get_user_meta( $user_id, 'rcp_dogname3', true );
	$dogBday3  = get_user_meta( $user_id, 'rcp_dogbday3', true );
	$licence3 = get_user_meta( $user_id, 'rcp_licence3', true );

	?>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogname1"><?php _e( 'Name of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogname1" placeholder="Name of your dog" id="rcp_dogname1" type="text" value="<?php echo esc_attr( $dogName1 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogbday1"><?php _e( 'Birthday of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogbday1" placeholder="Birthday of your dog" id="rcp_dogbday1" type="text" value="<?php echo esc_attr( $dogBday1 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_licence1"><?php _e( 'Dog Licence', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_licence1" placeholder="Dog License" id="rcp_licence1" type="text" value="<?php echo esc_attr( $licence1 ); ?>"/>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogname2"><?php _e( 'Name of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogname2" placeholder="Name of your dog" id="rcp_dogname2" type="text" value="<?php echo esc_attr( $dogName2 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogbday2"><?php _e( 'Birthday of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogbday2" placeholder="Birthday of your dog" id="rcp_dogbday2" type="text" value="<?php echo esc_attr( $dogBday2 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_licence1"><?php _e( 'Dog Licence', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_licence2" placeholder="Dog License" id="rcp_licence2" type="text" value="<?php echo esc_attr( $licence2 ); ?>"/>
		</td>
	</tr>

		<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogname3"><?php _e( 'Name of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogname3" placeholder="Name of your dog" id="rcp_dogname3" type="text" value="<?php echo esc_attr( $dogName3 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_dogbday3"><?php _e( 'Birthday of your dog', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_dogbday3" placeholder="Birthday of your dog" id="rcp_dogbday3" type="text" value="<?php echo esc_attr( $dogBday3 ); ?>"/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="rcp_licence3"><?php _e( 'Dog Licence', 'rcp' ); ?></label>
		</th>
		<td>
			<input name="rcp_licence3" placeholder="Dog License" id="rcp_licence3" type="text" value="<?php echo esc_attr( $licence3 ); ?>"/>
		</td>
	</tr>
	<?php
}
add_action( 'rcp_edit_member_after', 'pw_rcp_dog_info_edit_fields' );


/**
 * Stores the information submitted profile update
 *
 */
function pw_rcp_save_dog_info_fields_on_profile_save( $user_id ) {

		update_user_meta( $user_id, 'rcp_dogname1', sanitize_text_field( $_POST['rcp_dogname1'] ) );
		update_user_meta( $user_id, 'rcp_dogbday1', sanitize_text_field( $_POST['rcp_dogbday1'] ) );
		update_user_meta( $user_id, 'rcp_licence1', sanitize_text_field( $_POST['rcp_licence1'] ) );
		update_user_meta( $user_id, 'rcp_dogname2', sanitize_text_field( $_POST['rcp_dogname2'] ) );
		update_user_meta( $user_id, 'rcp_dogbday2', sanitize_text_field( $_POST['rcp_dogbday2'] ) );
		update_user_meta( $user_id, 'rcp_licence2', sanitize_text_field( $_POST['rcp_licence2'] ) );
		update_user_meta( $user_id, 'rcp_dogname3', sanitize_text_field( $_POST['rcp_dogname3'] ) );
		update_user_meta( $user_id, 'rcp_dogbday3', sanitize_text_field( $_POST['rcp_dogbday3'] ) );
		update_user_meta( $user_id, 'rcp_licence3', sanitize_text_field( $_POST['rcp_licence3'] ) );
}
add_action( 'rcp_user_profile_updated', 'pw_rcp_save_dog_info_fields_on_profile_save', 10 );
add_action( 'rcp_edit_member', 'pw_rcp_save_dog_info_fields_on_profile_save', 10 );



/**
 * Add new export column for "full name" in memebers export.
 * 
 * @param array $columns Default column headers.
 * 
 * @return array
 */
function ag_rcp_members_export_full_name_header( $columns ) {
    $columns['full_name'] = __( 'Full Name' );
	unset($columns['first_name']);
    unset($columns['last_name']);
    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_full_name_header' );


/**
 * Add full name value for each member.
 * 
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 * 
 * @return array
 */
function ag_rcp_members_export_full_name( $row, $member ) {
    $row['full_name'] = $member->first_name . " " . $member->last_name;

    // Or you can add a piece of user meta like this:
    // $row['my_field'] = get_user_meta( $member->ID, 'my_custom_field', true );

    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_full_name', 10, 2 );


/**
 * Add new export column for "full name" in payments export.
 * 
 * @param array $columns Default column headers.
 * 
 * @return array
 */
function ag_rcp_payments_export_full_name_header( $columns ) {
    $columns['full_name'] = __( 'Full Name' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_payments', 'ag_rcp_payments_export_full_name_header' );

/**
 * Add the transaction ID value for each payment.
 * 
 * @param array  $row     Array of data to be exported for the current payment.
 * @param object $payment Payment object from the database.
 * 
 * @return array
 */
function ag_rcp_payments_export_full_name( $row, $payment ) {
	$member = new RCP_Member( $payment->user_id );

    $row['full_name'] = $member->first_name . " " . $member->last_name;
    return $row;
}
add_filter( 'rcp_export_payments_get_data_row', 'ag_rcp_payments_export_full_name', 10, 2 );

/**
 * Add new export column for "full name" in payments export.
 * 
 * @param array $columns Default column headers.
 * 
 * @return array
 */
function ag_rcp_payments_export_bizname_header( $columns ) {
    $columns['bizname'] = __( 'Business Name' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_payments', 'ag_rcp_payments_export_bizname_header' );

/**
 * Add the transaction ID value for each payment.
 * 
 * @param array  $row     Array of data to be exported for the current payment.
 * @param object $payment Payment object from the database.
 * 
 * @return array
 */
function ag_rcp_payments_export_bizname( $row, $payment ) {
	$member = new RCP_Member( $payment->user_id );

    $row['bizname'] = get_user_meta( $member->ID, 'rcp_bizname', true );
    return $row;
}
add_filter( 'rcp_export_payments_get_data_row', 'ag_rcp_payments_export_bizname', 10, 2 );


/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_dogname1_header( $columns ) {
    $columns['dogname1'] = __( 'Dog Name 1' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_dogname1_header' );

/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_dogname1( $row, $member ) {

    $row['dogname1'] = get_user_meta( $member->ID, 'rcp_dogname1', true );
    // $row['dogname'] = get_user_meta( get_current_user_id(), 'rcp_dogname1', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_dogname1', 10, 2 );




/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_bday1_header( $columns ) {
    $columns['dogBday1'] = __( 'Birthday 1' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_bday1_header' );



/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_bday1( $row, $member ) {
    // $row['dogBday1'] = $member->get_rcp_dogbday1();

    // Or you can add a piece of user meta like this:
    $row['dogBday1'] = get_user_meta( $member->ID, 'rcp_dogbday1', true );

    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_bday1', 10, 2 );


// dog2

/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_dogname2_header( $columns ) {
    $columns['dogname2'] = __( 'Dog Name 2' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_dogname2_header' );

/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_dogname2( $row, $member ) {

    $row['dogname2'] = get_user_meta( $member->ID, 'rcp_dogname2', true );
    // $row['dogname'] = get_user_meta( get_current_user_id(), 'rcp_dogname1', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_dogname2', 10, 2 );




/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_bday2_header( $columns ) {
    $columns['dogBday2'] = __( 'Birthday 2' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_bday2_header' );



/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_bday2( $row, $member ) {
    // $row['dogBday1'] = $member->get_rcp_dogbday1();

    // Or you can add a piece of user meta like this:
    $row['dogBday2'] = get_user_meta( $member->ID, 'rcp_dogbday2', true );

    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_bday2', 10, 2 );


// dog3

/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_dogname3_header( $columns ) {
    $columns['dogname3'] = __( 'Dog Name 3' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_dogname3_header' );

/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_dogname3( $row, $member ) {

    $row['dogname3'] = get_user_meta( $member->ID, 'rcp_dogname3', true );
    // $row['dogname'] = get_user_meta( get_current_user_id(), 'rcp_dogname1', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_dogname3', 10, 2 );




/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_bday3_header( $columns ) {
    $columns['dogBday3'] = __( 'Birthday 3' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_bday3_header' );


/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_bday3( $row, $member ) {
    // $row['dogBday1'] = $member->get_rcp_dogbday1();

    // Or you can add a piece of user meta like this:
    $row['dogBday3'] = get_user_meta( $member->ID, 'rcp_dogbday3', true );

    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_bday3', 10, 2 );


// Name of Business

/**
 * Add new export column for "Join Date".
 *
 * @param array $columns Default column headers.
 *
 * @return array
 */
function ag_rcp_members_export_bizname_header( $columns ) {
    $columns['bizname'] = __( 'Name of Business' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_bizname_header' );

/**
 * Add the join date value for each member.
 *
 * @param array      $row    Array of data to be exported for the current member.
 * @param RCP_Member $member Member object.
 *
 * @return array
 */
function ag_rcp_members_export_bizname( $row, $member ) {

    $row['bizname'] = get_user_meta( $member->ID, 'rcp_bizname', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_bizname', 10, 2 );


//adding address as part of the export fields - Jennifer


function ag_rcp_members_export_street_header( $columns ) {
    $columns['street'] = __( 'Street Address' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_street_header' );


function ag_rcp_members_export_street( $row, $member ) {

    $row['street'] = get_user_meta( $member->ID, 'rcp_street', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_street', 10, 2 );



function ag_rcp_members_export_city_header( $columns ) {
    $columns['city'] = __( 'city' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_city_header' );


function ag_rcp_members_export_city( $row, $member ) {

    $row['city'] = get_user_meta( $member->ID, 'rcp_city', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_city', 10, 2 );



function ag_rcp_members_export_state_header( $columns ) {
    $columns['state'] = __( 'state' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_state_header' );


function ag_rcp_members_export_state( $row, $member ) {

    $row['state'] = get_user_meta( $member->ID, 'rcp_state', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_state', 10, 2 );



function ag_rcp_members_export_zipcode_header( $columns ) {
    $columns['zipcode'] = __( 'zipcode' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_zipcode_header' );


function ag_rcp_members_export_zipcode( $row, $member ) {

    $row['zipcode'] = get_user_meta( $member->ID, 'rcp_zipcode', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_zipcode', 10, 2 );



//adding doglicence

function ag_rcp_members_export_licence1_header( $columns ) {
    $columns['licence1'] = __( 'doglicence1' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_licence1_header' );


function ag_rcp_members_export_licence1( $row, $member ) {

    $row['licence1'] = get_user_meta( $member->ID, 'rcp_licence1', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_licence1', 10, 2 );


function ag_rcp_members_export_licence2_header( $columns ) {
    $columns['licence2'] = __( 'doglicence2' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_licence2_header' );


function ag_rcp_members_export_licence2( $row, $member ) {

    $row['licence2'] = get_user_meta( $member->ID, 'rcp_licence2', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_licence2', 10, 2 );


function ag_rcp_members_export_licence3_header( $columns ) {
    $columns['licence3'] = __( 'doglicence3' );

    return $columns;
}
add_filter( 'rcp_export_csv_cols_members', 'ag_rcp_members_export_licence3_header' );


function ag_rcp_members_export_licence3( $row, $member ) {

    $row['licence3'] = get_user_meta( $member->ID, 'rcp_licence3', true );
    return $row;
}
add_filter( 'rcp_export_members_get_data_row', 'ag_rcp_members_export_licence3', 10, 2 );



//adding "address" as part of the import fields - Jennifer

function ag_rcp_import_custom_fields( $user_id, $user_data, $subscription_id, $status, $expiration, $row ) {

    $rcp_dogname1 = $row['rcp_dogname1']; // Replace 'birthday' with your column heading name.

    if ( ! empty( $rcp_dogname1 ) ) {
        // Change 'birthday' to the user meta key you'd like to save the data as.
        update_user_meta( $user_id, 'rcp_dogname1', sanitize_text_field( $rcp_dogname1 ) );
    }

		$rcp_dogname2 = $row['rcp_dogname2']; // Replace 'birthday' with your column heading name.

    if ( ! empty( $rcp_dogname2 ) ) {
        // Change 'birthday' to the user meta key you'd like to save the data as.
        update_user_meta( $user_id, 'rcp_dogname2', sanitize_text_field( $rcp_dogname2 ) );
    }

		$rcp_dogname3 = $row['rcp_dogname3']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_dogname3 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_dogname3', sanitize_text_field( $rcp_dogname3 ) );
		}


		$rcp_dogbday1 = $row['rcp_dogbday1']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_dogbday1 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_dogbday1', sanitize_text_field( $rcp_dogbday1 ) );
		}

		$rcp_dogbday2 = $row['rcp_dogbday2']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_dogbday2 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_dogbday2', sanitize_text_field( $rcp_dogbday2 ) );
		}

		$rcp_dogbday3 = $row['rcp_dogbday3']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_dogbday3 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_dogbday3', sanitize_text_field( $rcp_dogbday3 ) );
		}


		$rcp_licence1 = $row['rcp_licence1']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_licence1 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_licence1', sanitize_text_field( $rcp_licence1 ) );
		}

		$rcp_licence2 = $row['rcp_licence2']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_licence2 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_licence2', sanitize_text_field( $rcp_licence2 ) );
		}

		$rcp_licence3 = $row['rcp_licence3']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_licence3 ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_licence3', sanitize_text_field( $rcp_licence3 ) );
		}

		$rcp_bizname = $row['rcp_bizname']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_bizname ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_bizname', sanitize_text_field( $rcp_bizname ) );
		}

		$rcp_street = $row['rcp_street']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_street ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_street', sanitize_text_field( $rcp_street ) );
		}


		$rcp_city = $row['rcp_city']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_city ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_city', sanitize_text_field( $rcp_city ) );
		}


		$rcp_state = $row['rcp_state']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_state ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_state', sanitize_text_field( $rcp_state ) );
		}


		$rcp_zipcode = $row['rcp_zipcode']; // Replace 'birthday' with your column heading name.

		if ( ! empty( $rcp_zipcode ) ) {
				// Change 'birthday' to the user meta key you'd like to save the data as.
				update_user_meta( $user_id, 'rcp_zipcode', sanitize_text_field( $rcp_zipcode ) );
		}


}

add_action( 'rcp_user_import_user_added', 'ag_rcp_import_custom_fields', 10, 6 );



// echo "<script type='text/javascript'>alert('test');</script>";



// function rcp_filter_restricted_content_customize( $content ) {
// 	global $post, $rcp_options;

// 	$user_id = get_current_user_id();

// 	$member = new RCP_Member( $user_id );

// 	if ( ! $member->can_access( $post->ID ) ) {

// 		$message = ! empty( $rcp_options['free_message'] ) ? $rcp_options['free_message'] : false; // message shown for free content

// 		if ( rcp_is_paid_content( $post->ID ) || in_array( $post->ID, rcp_get_post_ids_assigned_to_restricted_terms() ) ) {
// 			$message = ! empty( $rcp_options['paid_message'] ) ? $rcp_options['paid_message'] : false; // message shown for premium content
// 		}

// 		$links = array();
// 		if ( rcp_get_subscription( get_current_user_id() ) == 'Membership') {
// 			$message = ! empty( $message ) ? $message : __( 'Please renew your account', 'rcp' );

// 								$links[] = apply_filters( 'rcp_subscription_details_action_renew', '<a href="' . esc_url( get_permalink( $rcp_options['registration_page'] ) ) . '" title="' . __( 'Renew your subscription', 'rcp' ) . '" class="rcp_sub_details_renew">' . __( 'Renew your subscription', 'rcp' ) . '</a>', $user_ID );

// 						}

// 						if ( rcp_get_subscription( get_current_user_id() ) !== 'Membership') {
// 							if ( rcp_subscription_upgrade_possible( $user_ID ) ) {
// 								$links[] = apply_filters( 'rcp_subscription_details_action_upgrade', '<a href="/partner-registration/" title="' . __( 'Renew, upgrade or change your subscription', 'rcp' ) . '" class="rcp_sub_details_renew">' . __( 'Renew, upgrade or change your subscription', 'rcp' ) . '</a>', $user_ID );
// 							}
// 						}

// 		return rcp_format_teaser( $message );
// 	}

// 	return $content;
// }
// add_filter( 'the_content', 'rcp_filter_restricted_content_customize' , 100 );


// remove admin login header
function remove_admin_login_header() {
    remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'remove_admin_login_header');





/*function to add async and defer attributes*/
function defer_js_async($tag){

## 2: list of scripts to async. (Edit with your script names)
$scripts_to_async = array('jquery-migrate.min.js', 'wp-fastclick.min.js');
// $scripts_to_async = array('jquery.js', 'jquery-migrate.min.js', 'wp-fastclick.min.js');


#async scripts
foreach($scripts_to_async as $async_script){
	if(true == strpos($tag, $async_script ) )
	return str_replace( ' src', ' async="async" src', $tag );
}
return $tag;
}
add_filter( 'script_loader_tag', 'defer_js_async', 10 );





/*allow donation add_action*/


//hide this field
/*function pw_rcp_add_donation_fields() {


	//add donation custom fields
	$donation = get_user_meta( get_current_user_id(), 'rcp_donation', true );
	?>
  <p id="rcp_donation">

		<input id='donation' name="donate" type="button" value="Add $20 Donation">
		<p id="donation-msg"></p>
  </p>
	<?php
}

add_action( 'rcp_after_password_registration_field', 'pw_rcp_add_donation_fields' );
*/




/*
 * Adds a custom fee to registration
 *
 * This example adds a $20 fee that applies to the first payment only and is not affected by pro-ration
 *
 */
/*function pw_rcp_add_registration_fee( $registration ) {

	$registration->add_fee( 20, __( 'Custom Registration Fee', 'rcp' ), false, false );
}

add_action( 'rcp_registration_init', 'pw_rcp_add_registration_fee', 20 );

<input name="rcp_donation"  type="checkbox" value="25" <?php checked(add_action( 'rcp_registration_init', 'pw_rcp_add_registration_fee', 20 )); ?>/> Donate $25
*/


/**
 * Adds the custom fields to the registration form and profile editor
 *
 */
function pw_rcp_add_donation_fields() {
	
	$donation = get_user_meta( get_current_user_id(), 'rcp_donation', true );
	?>
	<p>
		<label class="h2 rcp_subtitle" style="display: block;" for="rcp_donation"><?php _e( 'Give to sustain DOGPAW! Add a donation below (optional)', 'rcp' ); ?></label>
		<span>$</span> <input style="width: 30%; min-width: 300px;" name="rcp_donation" id="rcp_donation" type="text" value="<?php echo esc_attr( $donation ); ?>"/>
	</p>

	<?php
}
add_action( 'rcp_after_password_registration_field', 'pw_rcp_add_donation_fields' );

/**
 * Determines if there are problems with the registration data submitted
 *
 */

function pw_rcp_validate_user_donation_on_register( $posted ) {
	if ( rcp_get_subscription_id() ) {
	   return;
    	}
	if( ! is_numeric( $posted['rcp_donation'] ) && ! empty( $posted['rcp_donation'] ) ) {
		rcp_errors()->add( 'invalid_donation', __( 'Please only enter the amount of donation (example: 100)', 'rcp' ), 'register' );
	}

}
add_action( 'rcp_form_errors', 'pw_rcp_validate_user_donation_on_register', 10 );

/**
 * Disable email requirment when registering
 *
 */
 
add_action( 'user_profile_update_errors', 'gowp_remove_new_user_email_error', 10, 3 );
function gowp_remove_new_user_email_error( $errors, $update, $user ) {
        unset( $errors->errors['empty_email'] );
}


/**
 * Stores the information submitted during registration
 *
 */
function pw_rcp_save_user_donation_on_register( $posted, $user_id ) {
	if( ! empty( $posted['rcp_donation'] ) ) {
		update_user_meta( $user_id, 'rcp_donation', sanitize_text_field( $posted['rcp_donation'] ) );
	}
}
add_action( 'rcp_form_processing', 'pw_rcp_save_user_donation_on_register', 10, 2 );


function add_conditional_fee($posted) {
	if (empty( $posted['rcp_donation'] )  || is_numeric( $posted['rcp_donation'] ) ) {
		rcp_get_registration()->add_fee((float)$posted['rcp_donation']);
	}
}
//'rcp_form_errors' here is not to check any errors, but to render this funtion after the form is validadated and before it's processed.

add_action('rcp_form_errors', 'add_conditional_fee');

/**
 * Email tags
 *
 */
function ag_rcp_email_template_bizname_tags( $bizname_tags ) {
    $bizname_tags[] = array(
        'tag'         => 'business_name',
        'description' => __( 'The name of the business' ),
        'function'    => 'ag_rcp_bizname_tag_callback_function'
    );
    return $bizname_tags;
}

add_filter( 'rcp_email_template_tags', 'ag_rcp_email_template_bizname_tags' );

function ag_rcp_email_template_address_tags( $address_tags ) {
    $address_tags[] = array(
	        'tag'         => 'address',
	        'description' => __( 'The address of the user' ),
	        'function'    => 'ag_rcp_address_tag_callback_function'
    );

    return $address_tags;
}

add_filter( 'rcp_email_template_tags', 'ag_rcp_email_template_address_tags' );


function ag_rcp_bizname_tag_callback_function( $user_id = 0, $payment_id = 0, $tag ) {
    $bizname = get_user_meta( $user_id, 'rcp_bizname', true );

    return $bizname;
}

function ag_rcp_address_tag_callback_function( $user_id = 0, $payment_id = 0, $tag ) {
    $street = get_user_meta( $user_id, 'rcp_street', true );
	$city   = get_user_meta( $user_id, 'rcp_city', true );
	$state   = get_user_meta( $user_id, 'rcp_state', true );
	$zipcode   = get_user_meta( $user_id, 'rcp_zipcode', true );

	$full_address = $street  . ', ' . $city  . '. ' .  $state  . ', ' .  $zipcode;

    return $full_address;
}

/**
 * Sending emails to business users
 *
 */
function ag_new_subscription_active_email( $message, $user_id, $status ) {
    $member = new RCP_Member( $user_id );
    $subscription_id = $member->get_subscription_id();

    // Change the email contents if they're on level #3.
    if( 6 == $subscription_id ) {
      	 $message = __( 'Welcome to the DOGPAW community! We are so glad you are here! Now that you are a DOGPAW member we have lots of great things we want to give you – newsletters, dog training tips, how to get the best DOGPAW off-leash park experience AND discounts to our DOGPAW partners. So voyage around our website and explore all that DOGPAW has to offer. . . . Oh, and make sure you are connected with us on social media – a lot of the action is there, and you don\'t want to miss it. See you at the parks!', 'rcp' );
    }

    return $message;
}
add_filter( 'rcp_subscription_active_email', 'ag_new_subscription_active_email', 10, 3 );

function ag_new_subscription_active_email_subject( $subject, $user_id, $status ) {
    $member = new RCP_Member( $user_id );
    $subscription_id = $member->get_subscription_id();

    if( 1 == $subscription_id ) {
      	 $subject = __( 'Thank you for becoming a DOGPAW member!', 'rcp' );
    }

    return $subject;
}
add_filter( 'rcp_subscription_active_subject', 'ag_new_subscription_active_email_subject', 10, 3 );


// function ag_rcp_admin_email_active_email( $message, $user_id, $status ) {
//     $member = new RCP_Member( $user_id );
//     $subscription_id = $member->get_subscription_id();

//     if( 1 == $subscription_id ) {
        
//     	$message = '';
//     }

//     return $message;
// }
// add_filter( 'rcp_email_admin_membership_active_message', 'ag_rcp_admin_email_active_email', 10 , 3);

// function ag_rcp_admin_email_active_email_subject( $subject, $user_id, $status ) {
//     $member = new RCP_Member( $user_id );
//     $subscription_id = $member->get_subscription_id();

//     if( 1 == $subscription_id ) {
//       	 $subject = __( 'New DOGPAW Membership Notification', 'rcp' );
//     }

//     return $subject;
// }
// add_filter( 'rcp_email_admin_membership_active_subject', 'ag_rcp_admin_email_active_email_subject', 10 , 3);