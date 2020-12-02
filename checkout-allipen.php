<?php
/**
 * Plugin Name:       Checkout Form Allipen Ferreterías
 * Plugin URI:        http://softem.cl/
 * Description:       Plugin Personalizado para Allipen Ferreterías
 * Version:           1.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sergio Roa
 * Author URI:        http://softem.cl/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */

// Filtro que agrega campos a Billing
add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');


//accion que agrega campos customizados

add_action('woocommerce_before_checkout_billing_form', 'customise_form');

//Acción que guarda la funcion nueva agregada
add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );

//Accion que muestra lo agregado
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );


//Acción que agrega  contenido al footer
add_action( 'wp_footer', 'conditional_billing_form_ajax' );

/**
 * Re ordena los Campos y edita los existentes
 */
//add_action('woocommerce_before_order_notes', 'customise_checkout_field');
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

//Acciones que agregan contenido en la vista de la orden (Cliente)
add_action( 'woocommerce_thankyou', 'misha_view_order_and_thankyou_page', 20 );
add_action( 'woocommerce_view_order', 'misha_view_order_and_thankyou_page', 20 );

//Filtro que limpia los campos guardados

add_filter('woocommerce_checkout_get_value','__return_empty_string',10);

 
function misha_view_order_and_thankyou_page( $order_id ){  

	echo '<p><strong>'.__('¿Boleta o Factura? ').':</strong> ' . get_post_meta( $order_id, 'boleta-factura', true ) . '</p>';

	echo '<style>
	#order_data > div.order_data_column_container > div:nth-child(2) > div.address > p:nth-child(1) > #text{
		display: none;
	}
	</style>';
	
	if(strcmp(get_post_meta( $order_id, 'boleta-factura', true ), 'Factura') == 0){

		
	echo '<p><strong>'.__('Nombre de la empresa').':</strong> ' . get_post_meta( $order_id, 'nombre-empresa', true ) . '</p>';

	
}
}

function custom_woocommerce_billing_fields($fields)
{

    $fields['entrega'] = array(
	'type' => 'select',
	'label' => __('Métodos de entrega') ,
	'class' => array(
	'form-row-first'
	) ,
	'options'	=> array( // options for <select> or <input type="radio" />
		'Tienda'	=> 'Retiro en tienda', // 'value'=>'Name'
		'Despacho'	=> 'Despacho a domicilio'
		)
	,
	'required' => true
) ;

    $fields['retirar'] = array(
	'type' => 'select',
	'label' => __('Retiro en tienda') ,
	'class' => array(
	'form-row-last'
	) ,
	'options'	=> array( // options for <select> or <input type="radio" />
		'Victoria'	=> 'Victoria', // 'value'=>'Name'
		'Traiguen'	=> 'Traiguen'
		)
	,
	'required' => true
) ;

$fields['localidad'] = array(
	'type' => 'select',
	'label' => __('Envio a localidad') ,
	'class' => array(
	'form-row-first'
	) ,
	'options'	=> array( 
		'Collipulli'	=> 'Collipulli', // 'value'=>'Name'
		'Angol'	=> 'Angol'
		)
	,
	'required' => true
) ;

$fields['fecha'] = array(
	'id' => 'fecha',
	'type' => 'text',
	'label' => __('Fecha de despacho') ,
	'class' => array(
	'form-row-last'
	)
	,
	'required' => true
) ;

return $fields;
   

}


 
function conditional_billing_form_ajax(){
	
 
	echo "<script>
	jQuery( function( $ ) {

		geturl = window.location.href;
		
		if (geturl.endsWith('checkout/')) {
		var boleta = $('input:radio[name=boleta-factura]');
		var entrega = $('select[name=entrega]');

		document.querySelector('#billing_address_1_field > label > span').innerHTML = '*'; // Elimina etiqueta opcional


			
		boleta.change(function(){ //when the rating changes
			var value=this.value;						
			
			if (value == 'Boleta'){
				$('.woocommerce_billing_factura').addClass('esconder'); //show feedback_bad				
			}
			else if (value == 'Factura'){
				$('.woocommerce_billing_factura').removeClass('esconder');		
			}
		});
		
		entrega.change(function(){ //when the rating changes
			var value=this.value;						
			
			if (value == 'Tienda'){
				$('#billing_address_1_field').addClass('esconder'); //show feedback_bad
				$('#billing_address_1').prop('required',false);
				$('#billing_address_1_field').removeClass('validate-required');
				$('#billing_address_1_field > label > abbr').removeClass('required');
				$('#billing_address_1_field').removeClass('woocommerce-validated');
				$('#retirar > label > Retiro en tienda').removeClass('requered');
				$('#retirar').removeClass('esconder');
				$('.woocommerce_billing_despacho').addClass('esconder'); //show feedback_bad
				
				
				
							
			}
			else if (value == 'Despacho'){
				$('#billing_address_1_field').removeClass('esconder');
				$('#billing_address_1').prop('required',true);
				$('#billing_address_1_field').addClass('validate-required');
				$('#billing_address_1_field > label > abbr').addClass('required');
				$('#retirar > label > Retiro en tienda').addClass('requered');
				$('#retirar').addClass('esconder');
				$('.woocommerce_billing_despacho').removeClass('esconder');

			}
		});

		$(document).ready(function(){
			$(function(){
				$('#fecha').datepicker({
					format: 'dd-mm-yyyy',
                    weekStart: 0,
                    startDate: '+2d',
                    endDate: '+30d',
                    clearBtn: true,
                    language: 'es',
                    multidate: false,
                    daysOfWeekDisabled: '0,6',
                    autoclose: true,
                    todayHighlight: true
				});
			});
		});

	}
	});

	
	</script>";

	echo '<style>
	.esconder, #customer_details > div > div.woocommerce-billing-fields > h3:nth-child(1){
		display: none;
	}
	
	#customer_details > div:nth-child(1) > div > div:nth-child(8)> #billing_first_name_field.form-row{
		display : none;
	} 
	</style>';
 
} 


function my_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['boleta-factura'] || $_POST['entrega'] ) ) {
		update_post_meta( $order_id, 'boleta-factura', sanitize_text_field( $_POST['boleta-factura'] ) );
		update_post_meta( $order_id, 'rut', sanitize_text_field( $_POST['rut'] ) );


		update_post_meta( $order_id, 'nombre-empresa', sanitize_text_field( $_POST['nombre-empresa'] ) );
	

    }
}




function my_custom_checkout_field_display_admin_order_meta($order){
	echo sanitize_text_field($_POST['boleta-factura']);

	echo '<p><strong>'.__('¿Boleta o Factura? ').':</strong> <br>' . get_post_meta( $order->id, 'boleta-factura', true ) . '</p>';
	
	echo '<p><strong>'.__('Rut ').':</strong> <br>' . get_post_meta( $order->id, 'rut', true ) . '</p>';

	echo '<style>
	#order_data > div.order_data_column_container > div:nth-child(2) > div.address > p:nth-child(1) > #text{
		display: none;
	}
	</style>';
	
	if(strcmp(get_post_meta( $order->id, 'boleta-factura', true ), 'Factura') == 0){

		
	echo '<p><strong>'.__('Nombre de la empresa'). get_post_meta( $order->id, 'nombre-empresa', true ) . '</p>';

	
	

	}

	echo '<script>';
	//echo 'console.log('. json_encode( $data ) .')';
	echo 'console.log('. json_encode(get_post_meta( $order->id, 'entrega', true )) .')';
	echo '</script>';
	
	
}
	

function customise_form($checkout)
{

	echo '<h3>DETALLES DE COMPRA</h3>';
	
	echo '<div class="woocommerce-billing-fields__field-wrapper">';
	woocommerce_form_field('boleta-factura', array(
		'type' => 'radio',
		'label' => __('¿Boleta o Factura?') ,
		'class' => array(
		'form-row-first'
		) ,
		'options'	=> array( // options for <select> or <input type="radio" />
			'Boleta'	=> 'Boleta',  // 'value'=>'Name'
			'Factura'	=> 'Factura'
			)
		,
		'default' => 1 ,//This will pre-select the checkbox
		'required' => true,
	) , $checkout->get_value('boleta-factura'));

	woocommerce_form_field('boleta-factura', array(
		'type' => 'text',
		'label' => __('RUT Persona o empresa (Sin puntos ni guion)') ,
		'placeholder' => __('Ej. 86863546'),
		'class' => array(
		'form-row-last'
		) ,
		'default' => 1 ,//This will pre-select the checkbox
		'required' => true,
	) , $checkout->get_value('boleta-factura'));

	woocommerce_form_field('nombre', array(
		'type' => 'text',
		'class' => array(
	    'form-row-first'
		) ,
		'label' => __('Nombre '),
		'placeholder' => __('Ej. Daniel'),
		'required' => true,
	) , $checkout->get_value('nombre'));

	woocommerce_form_field('apellido', array(
		'type' => 'text',
		'class' => array(
	    'form-row-last'
		) ,
		'label' => __('Apellidos '),
		'placeholder' => __('Ej. Espinoza'),
		'required' => true,
	) , $checkout->get_value('apellido'));
	

echo '<div class="woocommerce_billing_factura esconder">';

	woocommerce_form_field('nombre-empresa', array(
		'type' => 'text',
		'class' => array(
		) ,
		'label' => __('Nombre de la empresa'),
		'placeholder' => __('Ej. Aumenta360'),
		'required' => false,
	) , $checkout->get_value('nombre-empresa'));

	echo '</div>';

	echo '<div class="woocommerce_billing_despacho esconder">';

	echo '</div>';

	echo '</div>';


}


function customise_checkout_field($checkout)
{
  echo '<div id="customise_checkout_field"><h2>' . __('Heading') . '</h2>';
  woocommerce_form_field('customised_field_name', array(
    'type' => 'text',
    'class' => array(
      'my-field-class form-row-wide'
    ) ,
    'label' => __('Campo Adicional') ,
    'placeholder' => __('Guidence') ,
    'required' => true,
  ) , $checkout->get_value('customised_field_name'));
  echo '</div>';

  
}

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {

	
		unset ($fields['billing']['billing_company'] );
		unset ($fields['billing']['billing_country'] );
		unset ($fields['billing']['billing_address_2'] );
		unset ($fields['billing']['billing_postcode'] );
		unset ($fields['billing']['billing_state'] );
		unset ($fields['billing']['billing_first_name'] );
		unset ($fields['billing']['billing_last_name'] );
		//form-row-wide

		
		$fields['billing']['billing_address_1']['label'] = 'Detalle Dirección ';
		$fields['billing']['billing_address_1']['required'] = false;
		$fields['billing']['billing_city']['label'] = 'Direccion de la calle ';
		//$fields['billing']['billing_first_name']['label'] = 'Nombres ';
		//$fields['billing']['billing_first_name']['placeholder'] = 'Ej. Daniel ';
		//$fields['billing']['billing_last_name']['placeholder'] = 'Ej. Espinoza ';
		$fields['billing']['billing_city']['placeholder'] = 'Ej. San Martin ';
		$fields['billing']['billing_phone']['placeholder'] = 'Ej.989542873 ';
		$fields['billing']['billing_email']['placeholder'] = 'Ej. contacto@aumenta360.cl ';
		
		$fields['billing']['billing_city']['class'][0] = 'form-row-first';
		$fields['billing']['billing_phone']['class'][0] = 'form-row-first';
		$fields['billing']['billing_email']['class'][0] = 'form-row-last';
		$fields['billing']['billing_address_1']['class'][2] = 'form-row-wide esconder';
	

		$fields['billing']['entrega']['priority'] = 40; 
		$fields['billing']['retirar']['priority'] = 50; 
		$fields['billing']['fecha']['priority'] = 55; 
		$fields['billing']['billing_address_1']['priority'] = 60;
		$fields['billing']['billing_city']['priority'] = 70;
		
	
		//form-row-wide
  //comentario de prueba
	 
	 echo '<script>';
  //echo 'console.log('. json_encode( $data ) .')';
  echo 'console.log('. json_encode( $fields ) .')';
  echo '</script>';

  echo '<script>';
  //echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';

     return $fields;
}

//Validación

function claserama_validate_select_field(){

	$Total      = WC()->cart->cart_contents_total;

    //Verificar que el campo haya sido seleccionado, si no mostrar un error
    if ( empty( $_POST['boleta-factura'] ) ){
		wc_add_notice( '<STRONG>¿Boleta o Factura?</STRONG> es un campo requerido', 'error' );
	}

	if( strlen($_POST['rut']) < 5  ){
		wc_add_notice( '<STRONG>Facturación RUT</STRONG> No tiene la longitud necesaria', 'error' );
	}

	//Validaciones hechas a campos de facturación

	if(strcmp($_POST['boleta-factura'], 'Factura') == 0){

		if ( empty( $_POST['T-empresa'] ) ){
			wc_add_notice( '<STRONG>Rellene todos los campos de Facturación</STRONG>', 'error' );
		}
			
	} 

	if(strcmp($_POST['entrega'], 'Despacho') == 0 &&  empty($_POST['billing_address_1'])){
		wc_add_notice( '<STRONG>Detalle Dirección </STRONG>  es un campo requerido', 'error' );
	}
	
}

add_action('woocommerce_checkout_process','claserama_validate_select_field');


//calentadio datepicker
function js_calendarios(){
	
	wp_enqueue_style('calendario-css', get_template_directory_uri().'/css/jquery-ui.css', array(), '1.0');
	wp_enqueue_style('calendario-css-2', get_template_directory_uri().'/css/jquery-ui.theme.css', array(), '1.0');
	wp_enqueue_style('calendario-css-3', get_template_directory_uri().'/css/jquery-ui.structure.css', array(), '1.0');

   //  console.log(get_template_directory_uri().'/js/jquery-ui.js');
	wp_enqueue_script('calendario-js', get_template_directory_uri().'/js/jquery-ui.js', array('jquery'), '1.0');
	
 }

 add_action('wp_enqueue_scripts', 'js_calendarios');


//Envio a localidad
 function prefix_add_discount_line( $cart ) {

	$discount = $cart->subtotal * 0.1;
  
	$cart->add_fee( __( 'Descuento', 'yourtext-domain' ) , -$discount );
 //print_r($cart);
  
  }


 // add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line' );





  // Add tax for Swiss country
add_action( 'woocommerce_cart_calculate_fees','custom_tax_surcharge_for_swiss', 10, 1 );

function custom_tax_surcharge_for_swiss( $cart ) {
    if ( is_admin() && ! defined('DOING_AJAX') ) return;

    // Only for Swiss country (if not we exit)
    if ( 'Angol' != WC()->customer->get_shipping_country() ) return;

    $percent = 8;
    # $taxes = array_sum( $cart->taxes ); // <=== This is not used in your function

    // Calculation
    $surcharge = ( $cart->cart_contents_total + $cart->shipping_total ) * $percent / 100;

    // Add the fee (tax third argument disabled: false)
    $cart->add_fee( __( 'TAX', 'woocommerce')." ($percent%)", $surcharge, false );
}
