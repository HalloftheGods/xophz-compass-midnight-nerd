<?php

class Xophz_Compass_Midnight_Nerd_API {

	public function register_routes() {
		register_rest_route( 'midnight-nerd/v1', '/submit-ticket', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'submit_ticket' ),
			'permission_callback' => array( $this, 'check_permission' )
		) );
	}

	public function check_permission() {
		// Basic auth check for logged-in users. We can tighten this based on roles later.
		return current_user_can( 'read' ); 
	}

	public function submit_ticket( WP_REST_Request $request ) {
		$params = $request->get_json_params();
		$ticket_data = isset($params['ticket']) ? $params['ticket'] : array();
		
		$subject = sanitize_text_field( $ticket_data['subject'] ?? 'Untitled Ticket' );
		$message = sanitize_textarea_field( $ticket_data['message'] ?? '' );
		$urgency = sanitize_text_field( $ticket_data['urgency'] ?? 'Normal' );
		
		$post_id = wp_insert_post( array(
			'post_title'   => $subject,
			'post_content' => $message,
			'post_status'  => 'publish',
			'post_type'    => 'midnight_ticket',
			'comment_status' => 'open'
		) );

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'create_failed', 'Failed to create ticket', array( 'status' => 500 ) );
		}

		// Save meta data for tracking
		update_post_meta( $post_id, '_mn_urgency', $urgency );
		update_post_meta( $post_id, '_mn_system_data', json_encode(array(
			'wp_version' => $params['wp_version'] ?? '',
			'php_version' => $params['php_version'] ?? '',
			'active_plugins' => $params['active_plugins'] ?? array(),
		)) );
		update_post_meta( $post_id, '_mn_status', 'open' );

		// Dispatch webhooks to external tracking domains
		$this->dispatch_webhooks( $post_id, $params );

		return rest_ensure_response( array(
			'success' => true,
			'ticket_id' => $post_id,
			'message' => 'Ticket created and transmitted successfully.'
		) );
	}

	private function dispatch_webhooks( $post_id, $params ) {
		// Prepare the payload for the external APIs
		$payload = array(
			'source_domain' => site_url(),
			'local_ticket_id' => $post_id,
			'ticket_data' => $params
		);

		$request_args = array(
			'body'        => wp_json_encode( $payload ),
			'headers'     => array(
				'Content-Type' => 'application/json',
				// 'Authorization' => 'Bearer YOUR_API_KEY' // Can be implemented later for security
			),
			'timeout'     => 15,
			'blocking'    => false, // Don't block the local response waiting for external servers
		);

		// 1. Ping the Central Brain (MidnightNerd.com)
		// This acts as the main CRM where all tickets across all client sites aggregate.
		$midnight_nerd_endpoint = 'https://api.midnightnerd.com/v1/uplink/receive';
		wp_remote_post( $midnight_nerd_endpoint, $request_args );

		// 2. Ping the Analytics Tracker (YouMeOS.com)
		// This tracks platform health and aggregate stats.
		$youmeos_endpoint = 'https://api.youmeos.com/v1/telemetry/ticket-logged';
		wp_remote_post( $youmeos_endpoint, $request_args );

		// 3. (Optional) Direct Email Notification
		// We could use wp_mail() here to immediately email the DP if it's a 'CRITICAL' urgency.
		$urgency = sanitize_text_field( $params['ticket']['urgency'] ?? 'Normal' );
		if ( strpos( strtoupper($urgency), 'CRITICAL' ) !== false ) {
			$admin_email = get_option( 'admin_email' );
			$subject = "CRITICAL Midnight Nerd Ticket from " . site_url();
			$message = "A critical ticket was just submitted.\n\nSubject: " . $params['ticket']['subject'];
			wp_mail( $admin_email, $subject, $message );
		}
	}
}
