<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MGI_Financial {

    /**
     * Record a sale in today's financial summary.
     */
    public static function record_sale( $total, $cash, $transfer ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = current_time( 'Y-m-d' );

        self::ensure_record( $date );

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$prefix}financial
             SET total_sales = total_sales + %f,
                 cash_total = cash_total + %f,
                 transfer_total = transfer_total + %f,
                 cash_left = old_cash + cash_total + %f
             WHERE date = %s",
            $total, $cash, $transfer, $cash, $date
        ) );

        // Recalculate cash_left.
        self::recalculate( $date );
    }

    /**
     * Ensure a financial record exists for a given date.
     * old_cash = yesterday's cash_left.
     */
    public static function ensure_record( $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = $date ?: current_time( 'Y-m-d' );

        $exists = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$prefix}financial WHERE date = %s", $date
        ) );

        if ( $exists ) {
            return $exists;
        }

        // Get yesterday's cash_left as today's old_cash.
        $yesterday = gmdate( 'Y-m-d', strtotime( $date . ' -1 day' ) );
        $old_cash  = $wpdb->get_var( $wpdb->prepare(
            "SELECT cash_left FROM {$prefix}financial WHERE date = %s", $yesterday
        ) );
        $old_cash = floatval( $old_cash ?: 0 );

        $wpdb->insert( $prefix . 'financial', array(
            'date'           => $date,
            'total_sales'    => 0,
            'transfer_total' => 0,
            'cash_total'     => 0,
            'old_cash'       => $old_cash,
            'cash_left'      => $old_cash,
        ) );

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$prefix}financial WHERE date = %s", $date
        ) );
    }

    /**
     * Recalculate cash_left = old_cash + cash_total.
     */
    public static function recalculate( $date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();
        $date   = $date ?: current_time( 'Y-m-d' );

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$prefix}financial SET cash_left = old_cash + cash_total WHERE date = %s",
            $date
        ) );
    }

    public static function get_summary( $date = null ) {
        $date = $date ?: current_time( 'Y-m-d' );
        return self::ensure_record( $date );
    }

    public static function get_history( $start_date = null, $end_date = null ) {
        global $wpdb;
        $prefix = MGI_Database::get_prefix();

        $where  = '1=1';
        $params = array();

        if ( $start_date ) {
            $where   .= ' AND date >= %s';
            $params[] = $start_date;
        }
        if ( $end_date ) {
            $where   .= ' AND date <= %s';
            $params[] = $end_date;
        }

        $sql = "SELECT * FROM {$prefix}financial WHERE $where ORDER BY date DESC";

        if ( ! empty( $params ) ) {
            return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
        }
        return $wpdb->get_results( $sql );
    }
}
