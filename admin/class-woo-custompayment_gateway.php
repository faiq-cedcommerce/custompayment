<?php

class WC_Gateway_Offline extends WC_Payment_Gateway {
    public function __construct() {

        $this->id                 = 'cedcoss_offline_gateway';      //id of the payment gateway
        $this->icon               = '';                             //apply_filters('woocommerce_offline_icon', '');
        $this->has_fields         = false;                          //if offline then set it to false otherwise set it to the true
        $this->method_title       = 'CEDCOSS Payment Gateway';      // Title to be shown on the Admin page    
        $this->method_description = 'CEDCOSS Payment Gateway allows offline payment.'; //Description of the payment gateway
      
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
      
        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
      
        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
      
        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }
    //Initialize Gateway Settings Form Fields
    public function init_form_fields() {
	  
        $this->form_fields = apply_filters( 'wc_offline_form_fields', array(
      
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable CEDCOSS Offline Payment',
                'default' => 'yes'
            ),
            
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This is Offline Payment Gateway Title to be Shown on the frontend',
                'default'     => 'CEDCOSS Offline Payment (COD)',
                'desc_tip'    => true,
            ),
            
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This is Offline Payment Gateway description to be shown on the frontend',
                'default'     => 'Pay with cash upon delivery.',
                'desc_tip'    => true,
            ),
            
            'instructions' => array(
                'title'       => 'Instructions',
                'type'        => 'textarea',
                'description' => 'Instructions to be shown on emails and thankyou page',
                'default'     => 'Pay with cash upon delivery.',
                'desc_tip'    => true,
            ),
        ) );
    }

    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }

    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
        if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
    }

    public function process_payment( $order_id ) {
	
        $order = wc_get_order( $order_id );
        $order->update_status( 'on-hold', 'Awaiting offline payment');
        $order->reduce_order_stock();        
        WC()->cart->empty_cart();   
        return array(
            'result' 	=> 'success',
            'redirect'	=> $this->get_return_url( $order )
        );
    }
} 