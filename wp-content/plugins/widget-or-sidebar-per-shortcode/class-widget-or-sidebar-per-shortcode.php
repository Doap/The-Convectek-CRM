<?php

/*  Copyright 2012-2013  Frank Staude  (email : frank@staude.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class widget_or_sidebar_per_shortcode {
    function __construct() {
        add_shortcode( 'widget', array( 'widget_or_sidebar_per_shortcode', 'WidgetShortcode' ) );
        add_shortcode( 'sidebar', array( 'widget_or_sidebar_per_shortcode', 'SidebarShortcode' ) );
    }

    /**
     * Ausgabe eines Windgets
     *
     * Gibt per Shortcode ein Widget aus. Wenn das Widget parameter auswertet,
     * koennen diese uebergeben werden. Um z.B. das Kalender Widget mit "Hello, World!" als
     * Titel per Shortcode aufzurufen ist folgender Aufruf  noetig.
     * <code>
     * [widget name="Kalender" instance="title=Hello,World!"]
     * [widget classname="Calendar" instance="title=Hello,World!"]
     * </code>
     *
     * @param array $atts
     * @return string
     */
    static public function WidgetShortcode( array $atts ) {
        global  $wp_registered_widgets;
        $back = '';
        $widget_class = '';
                
        extract( shortcode_atts( array( 'name' => '', 'instance' => '', 'classname' => '' ), $atts ) );
        $instance = html_entity_decode( $instance );
        if ( $name != '' ) {
            // Nun den Klassennamen des Widgets ermitteln, die Funktion the_widget erwartet den
            // Klassennamen des Widgets als Parameter.
            foreach ( $wp_registered_widgets as $widget ) {
                if ( $widget[ 'name' ] == $name ) {
                    $widget_class =  get_class( $widget[ 'callback' ][ 0 ] );
                    continue;
                }
            }
        } elseif ( $classname != '' ) {
            foreach ( $wp_registered_widgets as $widget ) {
                if ( get_class( $widget[ 'callback' ][ 0 ] ) == $classname ) {
                    $widget_class =  $classname;
                    continue;
                }
            }
        }
        if ( $widget_class != '' ) {
            $back =  "<div id='" . str_replace( " ", "_", $name ) . "' class='widget_shortcode'>";
            ob_start();
            the_widget( $widget_class, $instance );
            $back .= ob_get_contents();
            ob_end_clean();
            $back .= "</div>";
        }
        return $back;
    }

    /**
     * Ausgabe einer Sidebar
     *
     * Gibt per Shortcode alle Widgets aus, die einer Sidebar zugeordnet sind. Dazu wird
     * der Name der Sidebar im Aufruf uebergeben. Beispielsweise
     * <code>
     * [sidebar name="Allgemeine Sidebar"]
     * </code>
     *
     * @param array $atts
     * @return string
     */
    static public function SidebarShortcode( array $atts ) {
        extract( shortcode_atts( array( 'name' => '1' ), $atts ) );
        $back =  "<div id='" . str_replace( " ", "_", $name ) . "' class='sidebar_shortcode'>";
        ob_start();
        if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( $name ) ) {}
        $back .= ob_get_contents();
        ob_end_clean();
        $back .= "</div>";
        return $back;
    }
}

