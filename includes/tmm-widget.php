<?php
/**
 * Adds Tmm_offer_widget widget.
 */
class Tmm_offer_widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  public function __construct() {
    parent::__construct(
      'tmm_offer_widget', // Base ID
      __('Timimas Supermarket Offers', 'tmm_offer_widget'),
      array( 'description' => __( 'Τελευταίες προσφορές απο το timimas.gr', 'tmm_offer_widget' ), ) // Args
    );

    add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $before_widget;
    if ( ! empty( $title ) )
      echo $before_title . $title . $after_title;

    $url      = 'http://www.timimas.gr/api/foo/offers_widget';
    $request  =  wp_remote_get($url);

    if ( is_wp_error( $request ) )
    {
      $output = $request->get_error_message();
    }
    else
    {
      $response = wp_remote_retrieve_body( $request );
    }

    if( $response != '' )
    {
      $offers = json_decode($response, true);

      $output = '<!-- Tmm Offer Widget -->'."\n".'<div class="tmm-offer-widget-wrapper"><div class="tmm-offer-widget-inner">';
      $output .= '<div class="tmm-offer-widget-header">';
      $output .= '<a href="#" class="tmm-offer-widget-nav tmm-offer-widget-next"></a><a href="#" class="tmm-offer-widget-nav tmm-offer-widget-prev"></a>';
      $output .= '<p>'.__('Το φθηνότερο καλάθι στο…', 'tmm_offer_widget').' <a href="'.esc_url( '//timimas.gr' ).'" target="_blank"><img src="'.plugins_url('timimas-supermarket-offers/images/tmmlogo.png').'" /></a></p></div>';
      $output .= '<div class="tmm-offer-widget">';
      foreach ( $offers as $offer )
      {
        $output .= '<div class="tmm-offer-widget-item">';

        if ( $offer['badge'] == 'plusone' )
          $output .= '<p class="tmm-offer-widget-desc">'.__('Προσφορά', 'tmm_offer_widget').'<span>1+1</span></p>';
        else
          $output .= '<p class="tmm-offer-widget-desc">'.__('Έκπτωση ', 'tmm_offer_widget').'<span> -'.esc_attr($offer['discount']).'&#37;</span></p>';

        $output .= '<a class="tmm-offer-widget-item-link" href="'.esc_url($offer['url']).'" target="_blank"><img src="'.esc_url($offer['img']).'" alt="'.esc_attr($offer['title']).'"/></a>';
        $output .= '<p class="tmm-product-price"><span>'.esc_attr($offer['offer_price']).'</span>&euro;</p>';
        $output .= '<br class="clear"/></div>';
      }
      $output .= '</div>';
      $output .= '<div class="tmm-offer-widget-footer"><a href="'.esc_url('http://timimas.gr').'" target="_blank">'.__('www.timimas.gr', 'tmm_offer_widget').'</a></div></div></div>'."\n".'<!-- Tmm Offer Widget end -->';
    }
    else
    {
      $output = null;
    }

    echo $output;

    echo $after_widget;
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = strip_tags( $new_instance['title'] );

    return $instance;
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = false;
    }
    ?>
    <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
  }

  public function load_scripts_styles() {
    wp_register_style('tmm-offer-widget', plugins_url('timimas-supermarket-offers/css/style.css'), null, null, 'screen');
    wp_enqueue_style('tmm-offer-widget');

    wp_enqueue_script('tmm-offer-widget', plugins_url('timimas-supermarket-offers/js/script.js'), array('jquery'), null, true);
  }

} // class Tmm_offer_widget

function tmm_register_offer_widget() {
  register_widget( 'Tmm_offer_widget' );
}
add_action( 'widgets_init', 'tmm_register_offer_widget' );