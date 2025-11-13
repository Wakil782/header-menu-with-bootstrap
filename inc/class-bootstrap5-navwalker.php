<?php
/**
 * Bootstrap 5 compatible simple navwalker
 * File: inc/class-bootstrap5-navwalker.php
 */

if ( ! class_exists( 'Bootstrap5_WP_Navwalker' ) ) :

class Bootstrap5_WP_Navwalker extends Walker_Nav_Menu {

    /**
     * Stack to hold current parent toggle ids for submenus.
     * This helps to set aria-labelledby on submenu <ul>.
     *
     * @var array
     */
    protected $dropdown_id_stack = array();

    // Start level (sub-menu)
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        // spacing
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        // submenu classes (top-level child = bootstrap dropdown-menu)
        $submenu_class = 'dropdown-menu';
        if ( $depth > 0 ) {
            $submenu_class .= ' dropdown-submenu';
        }

        // get labelledby from top of stack if present
        $labelledby = '';
        if ( ! empty( $this->dropdown_id_stack ) ) {
            $id = end( $this->dropdown_id_stack );
            if ( $id ) {
                $labelledby = ' aria-labelledby="' . esc_attr( $id ) . '"';
            }
        }

        $output .= "{$n}{$indent}<ul class=\"" . esc_attr( $submenu_class ) . "\"{$labelledby}>{$n}";
    }

    // Start element (menu item)
    public function start_el(  &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // prepare spacing
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

        // Ensure $item->classes is an array
        $classes = array();
        if ( isset( $item->classes ) ) {
            if ( is_array( $item->classes ) ) {
                $classes = $item->classes;
            } elseif ( is_string( $item->classes ) && $item->classes !== '' ) {
                // sometimes classes may come as a string — split on spaces
                $classes = preg_split( '/\s+/', trim( $item->classes ) );
            }
        }

        // detect children: WordPress normally adds 'menu-item-has-children'
        $has_children = in_array( 'menu-item-has-children', $classes, true );

        // prepare li classes
        $li_classes = array( 'nav-item' );
        if ( $has_children && $depth === 0 ) {
            $li_classes[] = 'dropdown';
        } elseif ( $has_children && $depth > 0 ) {
            // optional
            $li_classes[] = 'dropdown-submenu';
        }
        if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
            $li_classes[] = 'active';
        }

        // compile li attributes
        $li_class_names = implode( ' ', array_filter( $li_classes ) );
        $li_class_attr  = $li_class_names ? ' class="' . esc_attr( $li_class_names ) . '"' : '';

        $output .= $indent . '<li' . $li_class_attr . '>';

        // link attributes
        $link_atts = array();
        $link_atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $link_atts['target'] = ! empty( $item->target ) ? $item->target : '';
        if ( '_blank' === $item->target && empty( $item->xfn ) ) {
            $link_atts['rel'] = 'noopener noreferrer';
        } else {
            $link_atts['rel'] = ! empty( $item->xfn ) ? $item->xfn : '';
        }

        // classes for link and extra attributes for dropdown toggles
        if ( $depth === 0 ) {
            $link_classes = array( 'nav-link' );
            if ( $has_children ) {
                $link_classes[] = 'dropdown-toggle';
                // create an id for aria-labelledby and push to stack
                $toggle_id = 'menu-item-dropdown-' . $item->ID;
                $link_atts['id'] = $toggle_id;
                $link_atts['href'] = '#';
                $link_atts['role'] = 'button';
                $link_atts['data-bs-toggle'] = 'dropdown';
                $link_atts['aria-expanded'] = 'false';
                // push onto stack for start_lvl usage
                $this->dropdown_id_stack[] = $toggle_id;
            } else {
                $link_atts['href'] = ! empty( $item->url ) ? $item->url : '#';
            }
        } else {
            // depth > 0 (submenu items)
            $link_classes = array( 'dropdown-item' );
            $link_atts['href'] = ! empty( $item->url ) ? $item->url : '#';
        }

        // class attr
        $link_atts['class'] = implode( ' ', array_filter( $link_classes ) );

        // aria-current
        $link_atts['aria-current'] = $item->current ? 'page' : '';

        // apply filters
        $link_atts = apply_filters( 'nav_menu_link_attributes', $link_atts, $item, $args, $depth );

        // build attribute string
        $attributes = '';
        foreach ( $link_atts as $attr => $value ) {
            if ( $value !== '' && $value !== null ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        // title
        $title = apply_filters( 'the_title', $item->title, $item->ID );
        $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

        // final output for item
        $item_output  = isset( $args->before ) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $title;
        $item_output .= '</a>';
        $item_output .= isset( $args->after ) ? $args->after : '';

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    // End element
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        // If this item had children and we pushed an id to stack, pop it now
        // WordPress marks items with 'menu-item-has-children', so check classes
        $classes = array();
        if ( isset( $item->classes ) ) {
            if ( is_array( $item->classes ) ) {
                $classes = $item->classes;
            } elseif ( is_string( $item->classes ) && $item->classes !== '' ) {
                $classes = preg_split( '/\s+/', trim( $item->classes ) );
            }
        }
        if ( in_array( 'menu-item-has-children', $classes, true ) && ! empty( $this->dropdown_id_stack ) ) {
            array_pop( $this->dropdown_id_stack );
        }

        $output .= "</li>\n";
    }

    // End level
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $n = '';
        } else {
            $n = "\n";
        }
        $indent = str_repeat( "\t", $depth );
        $output .= "{$indent}</ul>{$n}";
    }
}

endif;
