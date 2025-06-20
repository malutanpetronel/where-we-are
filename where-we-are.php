<?php
// WordPress este încărcat
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/buy-endpoints.php';

define( 'PPWWA_URL', plugin_dir_url( __FILE__ ) );

// 1. Adăugăm meniul pentru plugin în panoul de administrare
add_action( 'admin_menu', 'ppwwa_plugin_menu' );
function ppwwa_plugin_menu() {
	add_menu_page(
		'Where we are',        // Titlu pagină
		'We on map',           // Titlu meniu
		'manage_options',      // Permisiune necesară
		'where-we-are',        // Slug pentru pagină
		'ppwwa_admin_page',    // 'petro_plugin_page',   // Funcția care afișează conținutul paginii
		'dashicons-hammer',    // Iconița meniului
		6                      // Poziția în meniu
	);
}

// Shortcode pentru afișarea hărții și funcționalității pluginului
function ppwwa_where_we_are_shortcode() {
	// Domeniul curent
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
        $current_domain = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
    } else {
        $current_domain = ''; // Default value if HTTP_HOST is not set
    }

	// delete_transient('ppwwa_map_validation_' . md5($current_domain));

	// Verifică dacă există deja un transient pentru acest domeniu
	$cache_key     = 'ppwwa_map_validation_' . md5( $current_domain );
	$cached_result = get_transient( $cache_key );

	if ( $cached_result === false ) {
		// Nu există cache, interoghează endpoint-ul
		$endpoint_url = 'https://shop.webnou.ro/wp-json/petroPlugins/v1/check-order';
		$response     = wp_remote_post(
			$endpoint_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json', // Specificăm că trimitem JSON
				),
				'body'    => wp_json_encode(
					array( // Codificăm array-ul în JSON
						'domain' => $current_domain,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			// error_log('ERROR ' . $response->get_error_message());
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );
		}

		if ( is_wp_error( $response ) ) {
			ob_start();
			?>
			<p>Eroare la validarea domeniului. Contactați suportul tehnic.</p>
			<?php
			return ob_get_clean();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['status'] ) && $data['status'] === 'valid' ) {
			// Salvează rezultatul în cache pentru o săptămână
			set_transient( $cache_key, 'valid', WEEK_IN_SECONDS );
			$cached_result = 'valid';
		} else {
			// Salvează rezultatul în cache pentru o săptămână
			set_transient( $cache_key, 'invalid', WEEK_IN_SECONDS );
			$cached_result = 'invalid';
		}
	}

	if ( $cached_result === 'valid' ) {
		update_option( 'where_we_are_paid', 1 );
		// Afișează containerul hărții
		ob_start();
		?>
		<div id="mapDiv" style="width: 100%; height: 500px;"></div>
		<?php
		return ob_get_clean();
	} else {
		ob_start();
		?>
<!--		<p>Harta nu are attribution removed si nu este disponibilă pentru acest domeniu. Verificați achiziția pluginului.</p>-->
        <div id="mapDiv" style="width: 100%; height: 500px;"></div>
        <?php
		return ob_get_clean();
	}
}
add_shortcode( 'where_we_are', 'ppwwa_where_we_are_shortcode' );


function ppwwa_admin_page() {
    $settings_tab_url = wp_nonce_url( '?page=where-we-are&tab=settings', 'where_we_are_tab_navigation', 'tab_nonce' );
    $map_tab_url = wp_nonce_url( '?page=where-we-are&tab=map', 'where_we_are_tab_navigation', 'tab_nonce' );
    $unlock_tab_url = wp_nonce_url( '?page=where-we-are&tab=unlock', 'where_we_are_tab_navigation', 'tab_nonce' );
    $about_tab_url = wp_nonce_url( '?page=where-we-are&tab=about', 'where_we_are_tab_navigation', 'tab_nonce' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Where We Are Settings', 'where-we-are' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( $settings_tab_url ); ?>" class="nav-tab <?php echo esc_attr( ppwwa_get_active_tab( 'settings' ) ); ?>"><?php echo esc_html__( 'Settings', 'where-we-are' ); ?></a>
            <a href="<?php echo esc_url( $map_tab_url ); ?>" class="nav-tab <?php echo esc_attr( ppwwa_get_active_tab( 'map' ) ); ?>"><?php echo esc_html__( 'Map', 'where-we-are' ); ?></a>
            <a href="<?php echo esc_url( $unlock_tab_url ); ?>" class="nav-tab <?php echo esc_attr( ppwwa_get_active_tab( 'unlock' ) ); ?>"><?php echo esc_html__( 'Unlock', 'where-we-are' ); ?></a>
            <a href="<?php echo esc_url( $about_tab_url ); ?>" class="nav-tab <?php echo esc_attr( ppwwa_get_active_tab( 'about' ) ); ?>"><?php echo esc_html__( 'About', 'where-we-are' ); ?></a>
        </h2>
        <div class="tab-content">
            <?php
            $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';
            if ( $current_tab === 'settings' ||
                ( isset( $_GET['tab_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['tab_nonce'] ) ), 'where_we_are_tab_navigation' ) ) ) {
                switch ( $current_tab ) {
                    case 'map':
                        ppwwa_render_map_tab();
                        break;
                    case 'unlock':
                        ppwwa_render_unlock_tab();
                        break;
                    case 'about':
                        ppwwa_render_about_tab();
                        break;
                    case 'settings':
                    default:
                        ppwwa_render_settings_tab();
                        break;
                }
            } else {
                echo '<div class="error"><p>' . esc_html__( 'Invalid navigation request.', 'where-we-are' ) . '</p></div>';
            }
            ?>
        </div>
    </div>
    <?php
}

// Helper pentru activarea taburilor
function ppwwa_get_active_tab( $tab_name ) {
    // Setează "settings" ca tab implicit
    $default_tab = 'settings';

    // Dacă tabul nu este specificat în URL, folosește tabul implicit
    if ( ! isset( $_GET['tab'] ) ) {
        return $tab_name === $default_tab ? 'nav-tab-active' : '';
    }

    // Asigură-te că $_GET['tab_nonce'] și $_GET['tab'] sunt "unslashed" și sanitizate
    $tab_nonce = isset( $_GET['tab_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['tab_nonce'] ) ) : '';
    $tab_value = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

    // Validarea nonce-ului pentru securitate
    if ( wp_verify_nonce( $tab_nonce, 'where_we_are_tab_navigation' ) ) {
        return $tab_value === $tab_name ? 'nav-tab-active' : '';
    }

    return $tab_name === $default_tab ? 'nav-tab-active' : '';
}


function ppwwa_render_settings_tab() {
    if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        // Check if nonce is valid
        if ( isset( $_POST['where_we_are_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['where_we_are_nonce'] ) ), 'where_we_are_save_settings' ) ) {
            if ( isset( $_POST['company'] ) ) {
                update_option( 'where_we_are_company', sanitize_text_field( wp_unslash( $_POST['company'] ) ) );
            }
            if ( isset( $_POST['address'] ) ) {
                update_option( 'where_we_are_address', sanitize_text_field( wp_unslash( $_POST['address'] ) ) );
            }
            if ( isset( $_POST['latitude'] ) ) {
                update_option( 'where_we_are_latitude', sanitize_text_field( wp_unslash( $_POST['latitude'] ) ) );
            }
            if ( isset( $_POST['longitude'] ) ) {
                update_option( 'where_we_are_longitude', sanitize_text_field( wp_unslash( $_POST['longitude'] ) ) );
            }
            if ( isset( $_POST['zoom'] ) ) {
                update_option( 'where_we_are_zoom', (int) sanitize_text_field( wp_unslash( $_POST['zoom'] ) ));
            }
            if ( isset( $_POST['slogan'] ) ) {
                update_option( 'where_we_are_slogan', sanitize_text_field( wp_unslash( $_POST['slogan'] ) ) );
            }
            echo '<div class="updated"><p>' . esc_html__( 'Settings saved!', 'where-we-are' ) . '</p></div>';
        } else {
            // Nonce verification failed
            wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'where-we-are' ) );
        }
	}

	list($company, $address, $latitude, $longitude, $zoom, $slogan) = ppwa_defaultOptions();

	$default_args = array(
		'company'   => $company,
		'address'   => $address,
		'latitude'  => $latitude,
		'longitude' => $longitude,
		'zoom'      => $zoom,
		'slogan'    => $slogan,
	);
	$args         = wp_parse_args( array(), $default_args ); // Asigură valori implicite

	$template_path = plugin_dir_path( __FILE__ ) . 'templates/tabs/settings.php';
	if ( file_exists( $template_path ) ) {
		extract( $args ); // Creează variabile din array
		include $template_path;
	} else {
		echo '<!-- Template not found: settings.php -->';
	}
}

function ppwwa_render_unlock_tab() {
	// Verificăm dacă plata a fost efectuată
	$remove_attribution = get_option( 'where_we_are_paid', 0 );

	$default_args = array(
		'remove_attribution' => $remove_attribution,
	);
	$args         = wp_parse_args( array(), $default_args ); // Asigură valori implicite

	$template_path = plugin_dir_path( __FILE__ ) . 'templates/tabs/unlock.php';
	if ( file_exists( $template_path ) ) {
		extract( $args ); // Creează variabile din array
		include $template_path;
	} else {
		echo '<!-- Template not found: settings.php -->';
	}
}

function ppwwa_render_about_tab() {
    // Instanțiază clasa DefaultArgs
    $default_args_instance = new \includes\DefaultArgs();
    $default_args = $default_args_instance->get_args();

	$args = wp_parse_args( array(), $default_args ); // Asigură valori implicite

	$template_path = plugin_dir_path( __FILE__ ) . 'templates/tabs/about.php';
	if ( file_exists( $template_path ) ) {
		extract( $args ); // Creează variabile din array
		include $template_path;
	} else {
		echo esc_html__( '<!-- Template not found: about.php -->', 'where-we-are' );
	}
}

function ppwwa_render_map_tab( $args = array() ) {
	$default_args = array(
		'width'  => '100%',
		'height' => '500px',
		'id'     => 'mapDiv',
	);
	$args         = wp_parse_args( $args, $default_args );

	$template_path = plugin_dir_path( __FILE__ ) . 'templates/tabs/map.php';
	if ( file_exists( $template_path ) ) {
		extract( $args ); // Extrage variabilele pentru utilizare în șablon
		include $template_path;
	} else {
		echo esc_html__( '<!-- Template not found: about.php -->', 'where-we-are' );
	}
}

// unused
// Include un șablon din directorul /templates
//function ppwwa_load_plugin_template( $template_name, $args = array() ) {
//	$template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
//
//	if ( file_exists( $template_path ) ) {
//		// Extrage variabilele pentru utilizare în șablon
//		extract( $args );
//
//		// Include fișierul de șablon
//		include $template_path;
//	}
//}

function ppwwa_admin_styles( $hook = '' ) {
	// Verificăm dacă suntem în zona de administrare sau dacă pagina curentă conține shortcode-ul
	if ( is_admin() ) {
		// În zona de administrare, încarcă doar dacă suntem pe pagina pluginului
		if ( $hook != 'toplevel_page_where-we-are' ) {
			return;
		}
	} elseif ( ! ( is_singular() && has_shortcode( get_post()->post_content, 'where_we_are' ) ) ) {
		// În zona publică, încarcă doar dacă există shortcode-ul în conținut
		return;
	}

    $default_args_instance = new \includes\DefaultArgs();
    $default_args = $default_args_instance->get_args();

    list($company, $address, $latitude, $longitude, $zoom, $slogan) = ppwa_defaultOptions();

	// Încarcă stilurile și scripturile necesare
	wp_enqueue_style( 'leaflet_css', plugin_dir_url( __FILE__ ) . 'assets/vendor/leaflet/leaflet.css', array(), $default_args['version'] );
	wp_enqueue_script( 'leaflet_js', plugin_dir_url( __FILE__ ) . 'assets/vendor/leaflet/leaflet.js', array(), $default_args['version'], true );
	wp_enqueue_script( 'turf_js', plugin_dir_url( __FILE__ ) . 'assets/vendor/turf/turf.min.js', array(), $default_args['version'], true );

	wp_enqueue_style( 'admin-style_css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', array(), $default_args['version'] );

	// Încarcă scriptul principal al pluginului
	wp_enqueue_script( 'petro_plugin_js', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array( 'leaflet_js', 'turf_js' ), $default_args['version'], true );

	// Transmite date si către JavaScript
	$map_data = array(
		'company'   => $company,
        'slogan' => $slogan,
		'address'   => $address,
		'latitude'  => $latitude,
		'longitude' => $longitude,
		'zoom'      => $zoom,
		'paid'      => get_option( 'where_we_are_paid', false ),
	);
	wp_localize_script( 'petro_plugin_js', 'mapData', $map_data );

	// Localizează datele pentru iconiță
	wp_localize_script(
		'petro_plugin_js',
		'pluginPetroData',
		array(
			'pluginUrl' => plugin_dir_url( __FILE__ ) . 'assets/',
		)
	);
}

/**
 * @return array
 */
function ppwa_defaultOptions(): array {
	$company   = esc_html( get_option( 'where_we_are_company', 'AQUIS grana impex SRL' ) );
	$address   = esc_html( get_option( 'where_we_are_address', '405200, Piata 16 Februarie nr. 2, Dej, Cluj, Romania' ) );
	$latitude  = esc_html( get_option( 'where_we_are_latitude', 47.1445245 ) );
	$longitude = esc_html( get_option( 'where_we_are_longitude', 23.8759876 ) );

	$zoom   = esc_html( get_option( 'where_we_are_zoom', 22 ) );
	$slogan = esc_html( get_option( 'where_we_are_slogan', 'Alaturi de tine, cu bun simt si ganduri bune!' ) );
	return array( $company, $address, $latitude, $longitude, $zoom, $slogan );
}

add_action( 'admin_enqueue_scripts', 'ppwwa_admin_styles' );
add_action( 'wp_enqueue_scripts', 'ppwwa_admin_styles' );


add_filter( 'script_loader_tag', 'ppwwa_add_charset_to_turf_script', 10, 2 );
function ppwwa_add_charset_to_turf_script( $tag, $handle ) {
	// Verifică dacă este handle-ul pentru turf_js
	if ( 'turf_js' === $handle ) {
		// Adaugă atributul charset la tag-ul scriptului
		return str_replace( '></script>', ' charset="utf-8"></script>', $tag );
	}
	return $tag;
}

// Funcție pentru ștergerea bazei de date atunci când pluginul este dezactivat
add_action( 'ppwwa_disable', 'ppwwa_delete_db', 10, 1 );
function ppwwa_delete_db()
{
//    global $wpdb;
//
//    $table_name = esc_sql( $wpdb->prefix . 'where_we_are' ); // Sanitize the table name
//    $sql = "DROP TABLE IF EXISTS `$table_name`"; // Directly construct the query with sanitized table name
//    $wpdb->query( $sql );

    // Șterge opțiunile salvate de plugin
    delete_option('where_we_are_company');
    delete_option('where_we_are_address');
    delete_option('where_we_are_latitude');
    delete_option('where_we_are_longitude');
    delete_option('where_we_are_zoom');
    delete_option('where_we_are_slogan');
    delete_option('where_we_are_paid');
}

// Funcția de activare a pluginului
//register_activation_hook( __FILE__, 'petro_plugin_activate' );
//function petro_plugin_activate() {
//	global $wpdb;
//	$table_name      = $wpdb->prefix . 'where_we_are';
//	$charset_collate = $wpdb->get_charset_collate();
//	$sql             = "CREATE TABLE $table_name (
//        id mediumint(9) NOT NULL AUTO_INCREMENT,
//        name tinytext NOT NULL,
//        description text NOT NULL,
//        PRIMARY KEY  (id)
//    ) $charset_collate;";
//	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
//	dbDelta( $sql );
//}

// Funcția de dezactivare a pluginului
register_deactivation_hook( __FILE__, 'ppwwa_deactivate' );
function ppwwa_deactivate() {
	do_action( 'ppwwa_disable' );
}

// Functia pentru TRADUCERI
function ppwwa_load_textdomain_conditionally() {
    global $wp_version;

    // Load textdomain only for WordPress versions < 4.6
    if ( version_compare( $wp_version, '4.6', '<' ) ) {
        load_plugin_textdomain( 'where-we-are', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
}
add_action( 'plugins_loaded', 'ppwwa_load_textdomain_conditionally' );
