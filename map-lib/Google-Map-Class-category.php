<?php
class GMP_Google_Map_Category {

    public function __construct() {
    }

    public function run( $height, $id, $category = 0 ) {
		$this->element_id = $id;
		$this->display_map( $height );
        $this->category = $category;
        add_action( 'wp_footer', array( $this, 'javascript_include' ) );
    }

    public function display_map( $height='650', $id='map_canvas' ) {
		echo '<div id="'.esc_attr( $this->element_id ).'" style="height:' .absint( $height ) .'px;"></div>';
    }

    public function javascript_include() {
		global $map_included;

		//load plugin options
		$options_arr = get_option( 'gmp_params' );

		//get map type setting
		$display_type = ( $options_arr["post_gmp_map_type"] ) ? $options_arr["post_gmp_map_type"] : 'ROADMAP';

		$javascript = '';
		$js_footer = '';

		$javascript = $this->build_marker_javascript($this->category);

		if ( ! $map_included ) {
			$js_footer .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
		}

		if ( $this->element_id == 'map_canvas' ) {
			$js_footer .= '<script type="text/javascript">';
			$js_footer .= 'function wds_map_markers_initialize() {';
			$js_footer .= ' var coords = new google.maps.LatLng( \'0\', \'0\' );';
			$js_footer .= '	var mapOptions = {';
			$js_footer .= '	  zoom: 10,';
			$js_footer .= '	  center: coords,';
			$js_footer .= '	  mapTypeId: google.maps.MapTypeId.' .esc_js( $display_type );
			$js_footer .= '	};';
			$js_footer .= '    var map = new google.maps.Map( document.getElementById( "map_canvas" ), mapOptions );';
			$js_footer .= '    var bounds = new google.maps.LatLngBounds();';
			$js_footer .= '    var infowindow = new google.maps.InfoWindow();';
			$js_footer .= $javascript;
			$js_footer .= '}';
			$js_footer .= 'setTimeout( "wds_map_markers_initialize()", 10 );';
			$js_footer .= '</script>';
		} elseif ( $this->element_id == 'map_canvas_shortcode_'.$this->category ) {
			$js_footer .= '<script type="text/javascript">'."\r\n";
			$js_footer .= 'function wds_map_markers_initialize_shortcode_'.$this->category.'() {'."\r\n";
			$js_footer .= '    var coords_'.$this->category.' = new google.maps.LatLng( \'0\', \'0\' );'."\r\n";
			$js_footer .= '	var mapOptions_'.$this->category.' = {'."\r\n";
			$js_footer .= '	  zoom: 10,'."\r\n";
			$js_footer .= '	  center: coords_'.$this->category.','."\r\n";
			$js_footer .= '	  mapTypeId: google.maps.MapTypeId.' .esc_js( $display_type )."\r\n";
			$js_footer .= '	};'."\r\n";
			$js_footer .= '    var map_'.$this->category.'= new google.maps.Map( document.getElementById( "map_canvas_shortcode_'.$this->category.'" ), mapOptions_'.$this->category.' );'."\r\n";
			$js_footer .= '    var bounds_'.$this->category.' = new google.maps.LatLngBounds();'."\r\n";
			$js_footer .= '    var infowindow_'.$this->category.' = new google.maps.InfoWindow();'."\r\n";
			$js_footer .= $javascript."\r\n";
			$js_footer .= '}'."\r\n";
			$js_footer .= 'setTimeout( "wds_map_markers_initialize_shortcode_'.$this->category.'()", 10 );'."\r\n";
			$js_footer .= '</script>'."\r\n";
		}

		$map_included = true;

		echo $js_footer;

    }

    public function build_marker_javascript( $category_name, $class = '' ) {
        $valid_posts = array();

        $category_id = get_cat_ID($category_name);
        
        if(!empty($category_id)){
            $query = new WP_Query("cat=".$category_id);
        }else{
            $query = new WP_Query();
        }

        $pageposts = $query->posts;

        if(empty($pageposts)){
            return;
        }

        for ( $row = 0; $row < count( $pageposts ); $row++ ) {
            $gmp_arr = array();

            $gmp_arr = get_post_meta( $pageposts[$row]->ID, 'gmp_arr', true );
            
            if(!empty($gmp_arr["gmp_title"]))
                $title  = $gmp_arr["gmp_title"];
            else
                $title  = $pageposts[$row]->post_title;

            if(!empty($gmp_arr["gmp_description"]))
                $desc   = $gmp_arr["gmp_description"];
            else
                $desc   = apply_filters('get_the_excerpt', $pageposts[$row]->post_excerpt);

            $lat        = $gmp_arr["gmp_lat"];
            $lng        = $gmp_arr["gmp_long"];
            $address    = $gmp_arr["gmp_address1"];

            $address = $gmp_arr['gmp_address1'];
            if ( !empty( $gmp_arr['gmp_address2'] ) )
                $address .= '<br/>' . $gmp_arr['gmp_address2'];
            if ( !empty( $gmp_arr['gmp_zip']))
                $address .= ' ' . $gmp_arr['gmp_zip'] ;
            if ( !empty( $gmp_arr[ $markers ]['gmp_zip'] ))
                $address .=  ' ' . $gmp_arr['gmp_city'];

            $location_id    = $pageposts[$row]->ID;
            $featimg        = $this->get_listing_thumbnail( NULL, $pageposts[$row]->ID );
            $entry_url      = get_permalink( $pageposts[$row]->ID );
            $post_type      = get_post_type( $pageposts[$row] );
            $html           = $pageposts[$row]->post_content;

            if ( $lat && $lng ) {

                $args[$row]=array(
                    'post_id'	=> $pageposts[$row]->ID,
                    'post_type' => get_post_type( $pageposts[$row] ),
                    'address'	=> $address,
                    'lat'		=> $lat,
                    'lng'		=> $lng,
                    'url'		=> $entry_url,
                    'img'		=> $featimg,
                    'title'		=> htmlentities( $title, ENT_QUOTES ),
                    'html'		=> $html,
                    'desc'      => $desc,
                    'class'		=> $class
                );

            }
        }

        return $this->EL_wds_map_load_markers( $args,'200px','100%','no', $this->category );
    }

    public function get_listing_thumbnail( $listing_post_type='', $post_id ) {
		//future feature
		$feat_image = '';

        return $feat_image;
    }

    public function EL_wds_map_load_markers( $args_arr=array(), $map_height="400px", $map_width="100%", $echo="yes" , $category) {
        global $gmp_display;

		$markers = 0;
		$return = '';

        if ( empty( $args_arr ) ) {
            return $return;
        }
        //extract our post meta early, so that we actually get ALL meta fields. Before we kept getting just first one.
        //Don't ask me how we were getting multiple markers for the different addresses.
        $id = $args_arr[0]['post_id'];
        $gmp_arr = get_post_meta( $id, 'gmp_arr', false );

        foreach ( $args_arr as $args ) {

            extract( $args, EXTR_OVERWRITE );
			//$gmp_arr = get_post_meta( $post_id, 'gmp_arr', true );
			$gmp_marker = ( !empty( $gmp_arr[ $markers ]['gmp_marker'] ) ) ? $gmp_arr[ $markers ]["gmp_marker"] : 'blue-dot.png';
            
            $address = esc_js( $args['address'] );

            //printy($address);
			$return .= 'var icon = new google.maps.MarkerImage( "' . plugins_url( '/markers/' . $gmp_marker, dirname( __FILE__ ) ) . '");';

            $content = $img . $title;
            $id = absint( $post_id ) . '_' . $markers;
            $return .=
                'var myLatLng = new google.maps.LatLng('.esc_js( $lat ).','.esc_js( $lng ).');
                bounds_'.$category.'.extend(myLatLng);
                var marker' . $id . ' = new google.maps.Marker({
                    map: map_'.$category.', icon: icon, position:
                    new google.maps.LatLng('.esc_js( $lat ).','.esc_js( $lng ).')
                });

                var contentString' . $id . ' = "<div><p><strong>'.esc_js($title).'</strong>, '.esc_js($desc).'<br />' . esc_js($address) . '</p></div>";
                var infowindow' . $id . ' = new google.maps.InfoWindow({
                    content: contentString' . $id . '
                });
                google.maps.event.addListener(marker' . $id . ', "click", function() {
                    infowindow' . $id . '.open(map_'.$category.', marker' . $id . ');
                });';
            $markers++;
        }

        if ( $markers == 1 ) {
        	$return .= 'map_'.$category.'.setCenter(bounds_'.$category.'.getCenter());'; // Set center and zoom out/in from here.
        } else {
        	// If more than one marker we want to fit all markers in a bound area. No Zoom.
        	$return .= 'map_'.$category.'.fitBounds(bounds_'.$category.');';
		}

		return $return;

    }

    public static function htmlentitiesCallback( &$string, $key = null ) {
        $string = htmlentities( $string );
    }
}