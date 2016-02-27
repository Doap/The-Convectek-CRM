<?php

class Appointments_Appointment {
	public $ID = '';
	public $created = '';
	public $user = '';
	public $name = '';
	public $email = '';
	public $phone = '';
	public $address = '';
	public $city = '';
	public $location = '';
	public $service = '';
	public $worker = '';
	public $price = '';
	public $status = '';
	public $start = '';
	public $end = '';
	public $sent = '';
	public $sent_worker = '';
	public $note = '';
	public $gcal_ID = '';
	public $gcal_updated = '';

	public function __construct( $appointment ) {
		foreach ( get_object_vars( $appointment ) as $key => $value ) {
			$this->$key = $this->_sanitize_field( $key, $value );
		}
	}

	private function _sanitize_field( $field, $value ) {
		// @TODO Sanitize
		//$int_fields = array( 'ID', 'user', 'service', 'location', 'worker' );

//		if ( in_array( $field, $int_fields ) )
//			return absint( $value );
//		else
			return $value;
	}

	public function get_service() {
		return appointments_get_service( $this->service );
	}

	public function get_service_id() {
		return $this->service;
	}

	public function get_worker() {
		return appointments_get_worker( $this->worker );
	}

	public function get_worker_id() {
		return $this->worker;
	}

	public function get_sent_worker_hours() {
		if ( ! $this->sent_worker ) {
			return array();
		}

		$sent_worker_hours = trim( $this->sent_worker, ':' );
		return explode( ':', $sent_worker_hours );
	}

	public function get_sent_user_hours() {
		if ( ! $this->sent ) {
			return array();
		}

		$sent_hours = trim( $this->sent, ':' );
		return explode( ':', $sent_hours );
	}


}

/**
 * Get a single Appointment data
 *
 * @param int|object $app_id Appointment ID or an object with an Appointment fields
 *
 * @return bool|Appointments_Appointment
 */
function appointments_get_appointment( $app_id ) {
	global $wpdb;

	if ( is_a( $app_id, 'Appointments_Appointment' ) ) {
		return $app_id;
	}
	elseif ( is_object( $app_id ) ) {
		wp_cache_add( $app_id->ID, $app_id, 'app_appointments' );
		return new Appointments_Appointment( $app_id );
	}

	$table = appointments_get_table( 'appointments' );

	$app = wp_cache_get( $app_id, 'app_appointments' );

	if ( ! $app ) {
		$app = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE ID = %d",
				$app_id
			)
		);

		wp_cache_add( $app_id, $app, 'app_appointments' );
	}


	if ( $app ) {
		return new Appointments_Appointment( $app );
	}

	return false;
}

/**
 * Insert a new Appointment
 *
 * @param array $args
 * @param bool $send_confirmation
 *
 * @return bool|int
 */
function appointments_insert_appointment( $args ) {
	global $wpdb, $appointments;

	$defaults = array(
		'user' => 0,
		'email' => '',
		'name' => '',
		'phone' => '',
		'address' => '',
		'city' => '',
		'service' => '',
		'worker' => '',
		'price' => '',
		'date' => '',
		'time' => '',
		'created' => current_time( 'mysql' ),
		'note' => '',
		'status' => 'pending',
		'location' => '',
		'gcal_updated' => '',
		'gcal_ID' => ''
	);

	$args = wp_parse_args( $args, $defaults );

	$insert = array();
	$insert_wildcards = array();

	$insert['created'] = $args['created'];
	$insert_wildcards[] = '%s';

	if ( $user = get_userdata( $args['user'] ) ) {
		$insert['user'] = $args['user'];
		$insert_wildcards[] = '%d';
	}
	else {
		$insert['user'] = 0;
		$insert_wildcards[] = '%d';
	}

	if ( is_email( $args['email'] ) ) {
		$insert['email'] = $args['email'];
		$insert_wildcards[] = '%s';
	}

	$insert['name'] = $args['name'];
	$insert_wildcards[] = '%s';

	$insert['phone'] = $args['phone'];
	$insert_wildcards[] = '%s';

	$insert['address'] = $args['address'];
	$insert_wildcards[] = '%s';

	$insert['city'] = $args['city'];
	$insert_wildcards[] = '%s';


	$service = appointments_get_service( $args['service'] );
	if ( ! $service ) {
		return false;
	}

	$insert['service'] = $service->ID;
	$insert_wildcards[] = '%d';

	$worker = appointments_get_worker( $args['worker'] );
	if ( $worker ) {
		$insert['worker'] = $worker->ID;
		$insert_wildcards[] = '%d';
	}
	else {
		$insert['worker'] = 0;
		$insert_wildcards[] = '%d';
	}



	$price = preg_replace( "/[^0-9,.]/", "", $args['price'] );
	if ( $price !== '' ) {
		if ( ! $price ) {
			$price = '';
		}
	}
	$insert['price'] = $price;
	$insert_wildcards[] = '%s';

	$time = date( 'H:i', strtotime( $args['time'] ) );
	if ( is_numeric( $args['date'] ) ) {
		// It's a timestamp
		$datetime = $args['date'];
		$insert['start'] = date( 'Y-m-d H:i:s', $datetime );
		$insert_wildcards[] = '%s';
	}
	else {
		$datetime = strtotime( str_replace(',', '', $appointments->to_us( $args['date'] ) ) . " " . $time );
		$insert['start'] = date( 'Y-m-d H:i:s', $datetime );
		$insert_wildcards[] = '%s';
	}

	$insert['end'] = date( 'Y-m-d H:i:s', $datetime + ( $service->duration * 60 ) );
	$insert_wildcards[] = '%s';

	$insert['note'] = $args['note'];
	$insert_wildcards[] = '%s';

	$insert['gcal_updated'] = $args['gcal_updated'];
	$insert_wildcards[] = '%s';

	$insert['gcal_ID'] = $args['gcal_ID'];
	$insert_wildcards[] = '%s';

	$allowed_status = appointments_get_statuses();
	if ( ! array_key_exists( $args['status'], $allowed_status ) ) {
		$args['status'] = 'pending';
	}
	$insert['status'] = $args['status'];
	$insert_wildcards[] = '%s';

	$location_id = ! empty( $args['location'] ) ? absint( $args['location'] ) : 0;
	$insert['location'] = $location_id;
	$insert_wildcards[] = '%d';

	$table = appointments_get_table( 'appointments' );
	$result = $wpdb->insert( $table, $insert, $insert_wildcards );

	if ( ! $result )
		return false;

	$app_id = $wpdb->insert_id;
	appointments_clear_appointment_cache( $app_id );

	appointments_update_user_appointment_data( $app_id );

	do_action( 'app_new_appointment', $app_id );
	do_action( 'wpmudev_appointments_insert_appointment', $app_id );

	return $app_id;
}

/**
 * Send a confirmation email fro this appointment
 *
 * @param $app_id
 */
function appointments_send_confirmation( $app_id ) {
	global $appointments;
	$appointments->send_confirmation( $app_id );
}

/**
 * Send an email when an appointment has been removed
 *
 * @param $app_id
 */
function appointments_send_removal_notification( $app_id ) {
	global $appointments;
	$appointments->send_removal_notification( $app_id );
}


/**
 * Legacy: Update a user address, city, name...
 *
 * @param $app_id
 */
function appointments_update_user_appointment_data( $app_id ) {

	if ( defined('APP_USE_LEGACY_USERDATA_OVERWRITING') && APP_USE_LEGACY_USERDATA_OVERWRITING ) {
		$app = appointments_get_appointment( $app_id );
		if ( ! $app )
			return;

		$user = get_userdata( $app->user );
		if ( ! $user )
			return;

		if ( $app->name )
			update_user_meta( $app->user, 'app_name',  $app->name );
		if (  $app->email )
			update_user_meta( $app->user, 'app_email', $app->email );
		if ( $app->phone )
			update_user_meta( $app->user, 'app_phone', $app->phone );
		if ( $app->address )
			update_user_meta( $app->user, 'app_address', $app->address );
		if ( $app->city )
			update_user_meta( $app->user, 'app_city', $app->city );

		do_action( 'app_save_user_meta', $app->user, (array)$app );
	}

}

/**
 * Update an appointment data
 *
 * @param $app_id
 * @param $args
 * @param bool $resend
 *
 * @return bool True in case of success
 */
function appointments_update_appointment( $app_id, $args ) {
	global $wpdb, $appointments;

	$old_appointment = appointments_get_appointment( $app_id );
	if ( ! $old_appointment )
		return false;

	$fields = array(
		'user' => '%d',
		'email' => '%s',
		'name' => '%s',
		'phone' => '%s',
		'address' => '%s',
		'city' => '%s',
		'service' => '%d', // Add it manually
		'worker' => '%d',
		'price' => '%s',
		'note' => '%s',
		'status' => false, // We'll not update in the same query execution
		'location' => '%d',
		'date' => false, // There are no fields like these in the table but they can be passed to the function
		'time' => false,
		'gcal_updated' => '%s',
		'gcal_ID' => '%s',
		'sent_worker' => '%s',
		'sent' => '%s'
	);

	$update = array();
	$update_wildcards = array();
	foreach ( $fields as $field => $wildcard ) {
		if ( isset( $args[ $field ] ) && $wildcard ) {
			$update[ $field ] = $args[ $field ];
			$update_wildcards[] = $wildcard;
		}
	}

	if ( isset( $update['service'] ) ) {
		$service = appointments_get_service( $update['service'] );
		if ( ! $service ) {
			return false;
		}
	}

	if ( isset( $update['worker'] ) ) {
		$worker = appointments_get_worker( $update['worker'] );
		if ( ! $worker ) {
			$update['worker'] = 0;
		}
	}

	if ( isset( $update['price'] ) ) {
		$update['price'] = preg_replace( "/[^0-9,.]/", "", $update['price'] );
	}

	if ( isset( $update['user'] ) ) {
		$user = get_userdata( $update['user'] );
		if ( ! $user ) {
			$update['user'] = 0;
		}
	}

	if ( isset( $update['sent_worker'] ) && is_array( $update['sent_worker'] ) ) {
		// Let's convert the array to a string
		$update['sent_worker'] = ':' . implode( ':', $update['sent_worker'] ) . ':';
	}

	if ( isset( $update['sent'] ) && is_array( $update['sent'] ) ) {
		// Let's convert the array to a string
		$update['sent'] = ':' . implode( ':', $update['sent'] ) . ':';
	}


	if ( ! empty( $args['date'] ) && ! empty( $args['time'] ) ) {
		$time = date( 'H:i', strtotime( $args['time'] ) );
		$datetime = strtotime( str_replace( ',', '', $appointments->to_us( $args['date'] ) ) . " " . $time );

		$update['start'] = date( 'Y-m-d H:i:s', $datetime );
		$update_wildcards[] = '%s';

		$update['end'] = date( 'Y-m-d H:i:s', $datetime + ( $service->duration * 60 ) );
		$update_wildcards[] = '%s';
	}

	// Change status?
	$updated_status = false;
	if ( ! empty( $args['status'] ) ) {
		// Yeah, maybe change status
		$updated_status = appointments_update_appointment_status( $app_id, $args['status'] );
	}


	if ( empty( $update ) && empty( $updated_status ) )
		return false;

	$result = false;
	if ( ! empty( $update ) ) {
		$table = appointments_get_table( 'appointments' );

		$result = $wpdb->update(
			$table,
			$update,
			array( 'ID' => $app_id ),
			$update_wildcards,
			array( '%d' )
		);
	}


	if ( ! $result && ! $updated_status ) {
		// Nothing has changed
		return false;
	}


	$app = appointments_get_appointment( $app_id );

	appointments_update_user_appointment_data( $app_id );
	appointments_clear_appointment_cache( $app_id );

	do_action( 'wpmudev_appointments_update_appointment', $app_id, $args, $old_appointment );

	return true;
}

/**
 * Update an appointment status
 *
 * @param int $app_id Appointment ID
 * @param string $new_status New appointment status
 *
 * @return bool True in case of success
 */
function appointments_update_appointment_status( $app_id, $new_status ) {
	global $wpdb;

	$app = appointments_get_appointment( $app_id );
	if ( ! $app ) {
		return false;
	}

	$old_status = $app->status;
	if ( $old_status === $new_status ) {
		return false;
	}

	$allowed_status = appointments_get_statuses();
	if ( ! array_key_exists( $new_status, $allowed_status ) ) {
		return false;
	}

	$table = appointments_get_table( 'appointments' );

	$result = $wpdb->update(
		$table,
		array( 'status' => $new_status ),
		array( 'ID' => $app_id ),
		array( '%s' ),
		array( '%d' )
	);

	if ( $result ) {
		if ( 'removed' == $new_status ) {
			do_action( 'app_removed', $app_id );
		}

		appointments_clear_appointment_cache( $app_id );

		if ( 'removed' === $new_status ) {
			appointments_send_removal_notification( $app_id );
		}

		/**
		 * Fired when an Appointment changes its status
		 *
		 * @used-by AppointmentsGcal::app_change_status()
		 * @used-by App_Users_AdditionalFields::manual_cleanup_data()
		 */
		do_action( 'wpmudev_appointments_update_appointment_status', $app_id, $new_status, $old_status );

		/**
		 * Fired when an Appointment changes its status
		 *
		 * @deprecated since 1.5.7.1
		 */
		do_action( 'app_change_status', $new_status, $app_id );

		return true;
	}

	return false;
}

/**
 * Get the appointments list given its location, service, worker and week of the year
 *
 * @param array $args {
 *      An array of arguments to execute the query
 *
 *      @type bool|int $location Location ID
 *      @type bool|int $service Service ID
 *      @type int $worker Worker ID
 *      @type int $week Number of week in the year. Set to 0 if not searching by week
 * }
 *
 * @return array|null|object
 */
function appointments_get_appointments( $args = array() ) {
	global $wpdb;

	$defaults = array(
		'location' => false,
		'service' => false,
		'worker' => 0,
		'week' => 0
	);

	$args = wp_parse_args( $args, $defaults );

	$location_id = absint( $args['location'] );
	$service_id = absint( $args['service'] );
	$worker_id = absint( $args['worker'] );
	$week = absint( $args['week'] );

	$cache_args = $args;
	unset( $cache_args['service'] ); // We don't query based on service

	$cache_key = md5( 'app-get-appointments-' . maybe_serialize( $cache_args ) );
	$cached_queries = wp_cache_get( 'app_get_appointments' );
	if ( ! is_array( $cached_queries ) ) {
		$cached_queries = array();
	}

	if ( isset( $cached_queries[ $cache_key ] ) ) {
		$apps = $cached_queries[ $cache_key ];
	}
	else {
		$table = appointments_get_table( 'appointments' );
		$where = array();

		$where[] = $wpdb->prepare( "worker = %d", $worker_id );

		if ( $location_id ) {
			$where[] = $wpdb->prepare( "location = %d", $location_id );
		}

		if ( 0 == $week ) {
			$where[] = "(status='pending' OR status='paid' OR status='confirmed' OR status='reserved')";
		}
		else {
			// @FIX: Problem: an appointment might already be ticked as "completed",
			// because of it's start time being in the past. Its end time, however, can still easily be
			// in the future. For long-running appointments (e.g. 2-3h) this could break the schedule slots
			// and show a registered- and paid for- slot as "available", when it's actually not.
			// E.g. http://premium.wpmudev.org/forums/topic/appointments-booking-conflictoverlapping-bookings
			$where[] = "(status='pending' OR status='paid' OR status='confirmed' OR status='reserved' OR status='completed')";

			$where[] = $wpdb->prepare( "WEEKOFYEAR(start)=%d", $week );
			// *ONLY* applied to weekly-scoped data gathering, because otherwise this would possibly
			// return all kinds of irrelevant data (appointments passed LONG time ago).
			// End @FIX
		}

		$where = "WHERE " . implode( " AND ", $where );

		$query = "SELECT * FROM $table $where";

		$apps = $wpdb->get_results( $query );
	}


	if ( empty( $apps ) ) {
		$apps = array();
	}

	foreach ( $apps as $app ) {
		wp_cache_add( $app->ID, $app, 'app_appointments' );
	}

	$cached_queries[ $cache_key ] = $apps;
	wp_cache_set( 'app_get_appointments', $cached_queries );

	// Now filter by service
	$filtered_apps = array();
	foreach ( $apps as $app ) {
		/** @var Appointments_Appointment $app */
		$app_service = absint( $app->service );
		if ( $app_service && $app_service == $service_id ) {
			$filtered_apps[] = $app;
		}
	}



	return $filtered_apps;
}

/**
 * @param $user_id
 *
 * @return array
 */
function appointments_get_user_appointments( $user_id ) {
	global $wpdb;

	$user_id = absint( $user_id );
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return array();
	}

	$table = appointments_get_table( 'appointments' );
	$statuses_in = array( 'paid', 'confirmed' );
	$where = "WHERE status IN ( '" . implode( "','", $statuses_in ) . "' ) AND user = $user_id";
	$results = $wpdb->get_results( "SELECT * FROM $table $where" );

	if ( ! $results ) {
		return array();
	}

	$appointments = array();
	foreach ( $results as $row ) {
		$appointments[] = new Appointments_Appointment( $row );
	}

	return $appointments;
}

/**
 * Clear the appointments cache
 *
 * @param bool $app_id
 */
function appointments_clear_appointment_cache( $app_id = false ) {
	global $wpdb;

	if ( $app_id ) {
		wp_cache_delete( $app_id, 'app_appointments' );
	}
	else {
		$table = appointments_get_table( 'appointments' );
		$ids = $wpdb->get_col( "SELECT ID FROM $table" );
		foreach ( $ids as $id ) {
			wp_cache_delete( $id, 'app_appointments' );
		}
	}

	wp_cache_delete( 'app_count_appointments' );
	wp_cache_delete( 'app_get_appointments' );
	appointments_delete_timetables_cache();
}


/**
 * Return all available statuses
 * @return array
 */
function appointments_get_statuses() {
	return apply_filters( 'app_statuses',
		array(
			'pending'	=> __('Pending', 'appointments'),
			'paid'		=> __('Paid', 'appointments'),
			'confirmed'	=> __('Confirmed', 'appointments'),
			'completed'	=> __('Completed', 'appointments'),
			'reserved'	=> __('Reserved by GCal', 'appointments'),
			'removed'	=> __('Removed', 'appointments')
		)
	);
}

/**
 * Delete an appointment forever
 *
 * @param $app_id
 *
 * @return bool|false|int
 */
function appointments_delete_appointment( $app_id ) {
	global $wpdb;

	$app = appointments_get_appointment( $app_id );
	if ( ! $app )
		return false;

	$table = appointments_get_table( 'appointments' );
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE ID = %d", $app_id ) );

	appointments_clear_appointment_cache( $app_id );
	return (bool)$result;
}

/**
 * Return the expired appointments
 *
 * @param int $pending_seconds If > 0, it will also include those that are pending and were created more than $pending_seconds seconds ago
 *
 * @return array List of expired appointments
 */
function appointments_get_expired_appointments( $pending_seconds = 0 ) {
	global $wpdb;

	$pending_seconds = absint( $pending_seconds );

	$current_time = current_time( 'timestamp' );

	$table = appointments_get_table( 'appointments' );
	$expired = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table
			WHERE start < %s
			AND status NOT IN ('completed', 'removed')",
			date( 'Y-m-d H:i:s', $current_time )
		)
	);

	if ( $pending_seconds ) {
		// Get all those that are pending and $pending_seconds old
		$pending_expired = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table
					WHERE status='pending'
					AND created < %s",
				date( 'Y-m-d H:i:s', $current_time - $pending_seconds )
			)
		);

		foreach ( $pending_expired as $pending_expired_app ) {
			$expired[] = $pending_expired_app;
		}
	}

	$results = array();
	foreach ( $expired as $app ) {
		// With this, we're caching the rows
		$results[] = appointments_get_appointment( $app );
	}

	return $results;
}


function appointments_count_appointments() {
	global $wpdb;

	$counts = wp_cache_get( 'app_count_appointments' );

	if ( false === $counts ) {
		$table = appointments_get_table( 'appointments' );

		$results = $wpdb->get_results( "SELECT status, COUNT(*) num_apps FROM $table GROUP BY status", ARRAY_A );
		$counts = array_fill_keys( array_keys( appointments_get_statuses() ), 0 );

		foreach ( $results as $row ) {
			$counts[ $row['status'] ] = absint( $row['num_apps'] );
		}

		wp_cache_set( 'app_count_appointments', $counts );
	}


	/**
	 * Modify returned appointments counts by status
	 *
	 * @since 1.5.8.1
	 *
	 * @param array $counts  An array containing the counts by status.
	 */
	return apply_filters( 'appointments_count_appontments', $counts );
}