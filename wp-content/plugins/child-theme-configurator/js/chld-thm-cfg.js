/*!
 *  Script: chld-thm-cfg.js
 *  Plugin URI: http://www.childthemeconfigurator.com/
 *  Description: Handles jQuery, AJAX and other UI
 *  Version: 1.7.9.1
 *  Author: Lilaea Media
 *  Author URI: http://www.lilaeamedia.com/
 *  License: GPLv2
 *  Copyright (C) 2014-2015 Lilaea Media
 */

// ** for muliple property values: **
// make sure sequence is passed with rule/val updates
// determine sequence based on sequence of value array
// add sequence to input name

( function( $ ) {
    $.chldthmcfg = {
        //console.log( 'executing main function' );
        esc_quot: function( str ) {
            var self = this;
            return self.is_empty( str ) ? str : str.toString().replace( /"/g, '&quot;' );
        },
                
        getxt: function( key ){
            return ( text = ctcAjax[ key + '_txt' ] ) ? text : '';
        },
                
        from_ascii: function( str ) {
            var ascii = parseInt( str ),
                chr = String.fromCharCode( ascii )
            return chr;
        },
                
        to_ascii: function( str ) {
            var ascii = str.charCodeAt( 0 );
            return ascii;
        },
    
        /**
         * is_empty
         * return true if value evaluates to false, null, null string, 
         * empty array, empty object or undefined
         * but NOT 0 ( zero returns false )
         */
        is_empty: function( obj ) {
            // first bail when definitely empty or undefined ( true ) NOTE: numeric zero returns false !
            if ( 'undefined' == typeof obj || false === obj || null === obj || '' === obj ) { return true; }
            // then, if this is bool, string or number it must not be empty ( false )
            if ( true === obj || "string" === typeof obj || "number" === typeof obj ) { return false; }
            // check for object type to be safe
            if ( "object" === typeof obj ) {    
                // Use a standard for in loop
                for ( var x in obj ) {
                    // A for in will iterate over members on the prototype
                    // chain as well, but Object.getOwnPropertyNames returns
                    // only those directly on the object, so use hasOwnProperty.
                    if ( obj.hasOwnProperty( x ) ) {
                        // any value means not empty ( false )
                        return false;
                    }
                }
                // no properties, so return empty ( true )
                return true;
            } 
            // this must be an unsupported datatype, so return not empty
            return false; 
        },
        
        /**
         * theme_exists
         * returns true if theme is already present for type
         */
        theme_exists: function( testslug, testtype ) {
            var exists = false;
            $.each( ctcAjax.themes, function( type, theme ) {
                $.each( theme, function( slug, data ) {
                    if ( slug.toLowerCase() === testslug.toLowerCase() && ( 'parnt' == type || 'new' == testtype ) ) {
                        exists = true;
                        return false;
                    }
                } );
                if ( exists ) return false;
            } );
            return exists;
        },
        
        validate: function() {
            var self    = this,
                regex   = /[^\w\-]/,
                newslug = $( '#ctc_child_template' ).length ? $( '#ctc_child_template' )
                    .val().toString().replace( regex ) : '',
                slug    = $( '#ctc_theme_child' ).length ? $( '#ctc_theme_child' )
                    .val().toString().replace( regex ) : newslug,
                type    = $( 'input[name=ctc_child_type]:checked' ).val(),
                errors  = [];
            if ( 'new' == type ) slug = newslug;
            if ( self.theme_exists( slug, type ) ) {
                errors.push( self.getxt( 'theme_exists' ).toString().replace( /%s/, slug ) );
            }
            if ( '' === slug ) {
                errors.push( self.getxt( 'inval_theme' ) );
            }
            if ( '' === $( '#ctc_child_name' ).val() ) {
                errors.push( self.getxt( 'inval_name' ) );
            }
            if ( errors.length ) {
                self.set_notice( { 'error': errors } );
                return false;
            }
            return true;
        },
        
        autogen_slugs: function() {
            if ( $( '#ctc_theme_parnt' ).length ) {
                var self    = this,
                    parent  = $( '#ctc_theme_parnt' ).val(),
                    slug    = slugbase = parent + '-child',
                    name    = ctcAjax.themes.parnt[ parent ].Name + ' Child',
                    suffix  = '',
                    padded  = '',
                    pad     = '00';
                while ( self.theme_exists( slug, 'new' ) ) {
                    suffix  = ( '' == suffix ? 2 : suffix + 1 );
                    padded  = pad.substring( 0, pad.length - suffix.toString().length ) + suffix.toString();
                    slug    = slugbase + padded;
                }
                self.testslug = slug;
                self.testname = name + ( padded.length ? ' ' + padded : '' );
            }
        },
        
        focus_panel: function( id ) {
            var panelid = id + '_panel';
            $( '.nav-tab' ).removeClass( 'nav-tab-active' );
            $( '.ctc-option-panel' ).removeClass( 'ctc-option-panel-active' );
            //$( '.ctc-selector-container' ).hide();
            $( id ).addClass( 'nav-tab-active' );
            $( '.ctc-option-panel-container' ).scrollTop( 0 );
            $( panelid ).addClass( 'ctc-option-panel-active' );
        },
        
        selector_input_toggle: function( obj ) {
            //console.log( 'selector_input_toggle: ' + obj );
            var self = this,
                origval;
            if ( $( '#ctc_rewrite_selector' ).length ) {
                origval = $( '#ctc_rewrite_selector_orig' ).val();
                $( '#ctc_sel_ovrd_selector_selected' ).text( origval );
                $( obj ).text( self.getxt( 'rename' ) );
            } else {
                origval = $( '#ctc_sel_ovrd_selector_selected' ).text();
                $( '#ctc_sel_ovrd_selector_selected' ).html( 
                    '<textarea id="ctc_rewrite_selector"'
                    + ' name="ctc_rewrite_selector" autocomplete="off"></textarea>'
                    + '<input id="ctc_rewrite_selector_orig" name="ctc_rewrite_selector_orig"'
                    + ' type="hidden" value="' + self.esc_quot( origval ) + '"/>' );
                $( '#ctc_rewrite_selector' ).val( origval );
                $( obj ).text( self.getxt( 'cancel' ) );
            }
        },
    
        fade_update_notice: function() {
            $( '.updated, .error' ).slideUp( 'slow', function() { $( '.updated' ).remove(); } );
        },
        
        coalesce_inputs: function( obj ) {
            //**console.log( 'coalesce_inputs ' + $( obj ).attr( 'id' ) );
            var self        = this,
                id          = $( obj ).attr( 'id' ),
                regex       = /^(ctc_(ovrd|\d+)_(parent|child)_([0-9a-z\-]+)_(\d+?)(_(\d+))?)(_\w+)?$/,
                container   = $( obj ).parents( '.ctc-selector-row, .ctc-parent-row' ).first(),
                swatch      = container.find( '.ctc-swatch' ).first(),
                cssrules    = { 'parent': {}, 'child': {} },
                gradient    = { 
                    'parent': {
                        'origin':   '',
                        'start':    '',
                        'end':      ''
                    }, 
                    'child': {
                        'origin':   '',
                        'start':    '',
                        'end':      ''
                    } 
                },
                has_gradient    = { 'child': false, 'parent': false },
                postdata        = {};
            // set up objects for all neighboring inputs
            container.find( '.ctc-parent-value, .ctc-child-value' ).each( function() {
                var inputid     = $( this ).attr( 'id' ),
                    inputparts  = inputid.toString().match( regex ),
                    inputseq    = inputparts[ 2 ],
                    inputtheme  = inputparts[ 3 ],
                    inputrule   = ( 'undefined' == typeof inputparts[ 4 ] ? '' : inputparts[ 4 ] ),
                    rulevalid   = inputparts[ 7 ],
                    qsid        = inputparts[ 5 ],
                    rulepart    = ( 'undefined' == typeof inputparts[ 7 ] ? '' : inputparts[ 8 ] ),
                    value       = ( 'parent' == inputtheme ? $( this ).text().replace( /!$/, '' ) : 
                                    ( 'seq' != inputrule && 'ctc_delete_query_selector' == id ? '' : $( this ).val() ) ), // clear values if delete was clicked
                    important   = ( 'seq' == inputrule ? false : 'ctc_' + inputseq + '_child_' + inputrule + '_i_' + qsid + '_' + rulevalid ),
                    parts, subparts;
                //**console.log( inputparts );
                //**console.log( 'value: ' + value );
                if ( 'child' == inputtheme ) {
                    if ( !self.is_empty( $( this ).data( 'color' ) ) ) {
                        value = self.color_text( $( this ).data( 'color' ) );
                        $( this ).data( 'color', null );
                    }
                    postdata[ inputid ]     = value;
                    if ( important )
                        postdata[ important ]   = ( $( '#' + important ).is( ':checked' ) ) ? 1 : 0;
                }
                if ( '' !== value ) {
                    // handle specific inputs
                    if ( !self.is_empty( rulepart ) ) {
                        switch( rulepart ) {
                            case '_border_width':
                                cssrules[ inputtheme ][ inputrule + '-width' ] = ( 'none' == value ? 0 : value );
                                break;
                            case '_border_style':
                                cssrules[ inputtheme ][ inputrule + '-style' ] = value;
                                break;
                            case '_border_color':
                                cssrules[ inputtheme ][ inputrule + '-color' ] = value;
                                break;
                            case '_background_url':
                                cssrules[ inputtheme ][ 'background-image' ] = self.image_url( inputtheme, value );
                                break;
                            case '_background_color':
                                cssrules[ inputtheme ][ 'background-color' ] = value; // was obj.value ???
                                break;
                            case '_background_color1':
                                gradient[ inputtheme ].start   = value;
                                has_gradient[ inputtheme ] = true;
                                break;
                            case '_background_color2':
                                gradient[ inputtheme ].end     = value;
                                has_gradient[ inputtheme ] = true;
                                break;
                            case '_background_origin':
                                gradient[ inputtheme ].origin  = value;
                                has_gradient[ inputtheme ] = true;
                                break;
                        }
                    } else {
                        // handle borders
                        if ( parts = inputrule.toString().match( /^border(\-(top|right|bottom|left))?$/ ) && !value.match( /none/ ) ) {
                            var borderregx = new RegExp( self.border_regx + self.color_regx, 'i' ),
                                subparts = value.toString().match( borderregx );
                            //**console.log( 'border after regex: ');
                            //**console.log( value );
                            //**console.log( borderregx );
                            //**console.log( subparts );
                            if ( !self.is_empty( subparts ) ) {
                                subparts.shift();
                                cssrules[ inputtheme ][ inputrule + '-width' ] = subparts.shift() || '';
                                subparts.shift();
                                cssrules[ inputtheme ][ inputrule + '-style' ] = subparts.shift() || '';
                                cssrules[ inputtheme ][ inputrule + '-color' ] = subparts.shift() || '';
                            }
                        // handle background images
                        } else if ( 'background-image' == inputrule && !value.match( /none/ ) ) {
                            if ( value.toString().match( /url\(/ ) ) {
                                cssrules[ inputtheme ][ 'background-image' ] = self.image_url( inputtheme, value );
                            } else {
                                var gradregex = new RegExp( self.grad_regx + self.color_regx + self.color_regx, 'i' ),
                                    subparts = value.toString().match( gradregex );
                            //**console.log( 'background-image after regex: ');
                                    //**console.log( value );
                                    //**console.log( gradregex );
                                    //**console.log( subparts );
                                if ( !self.is_empty( subparts ) && subparts.length > 2 ) {
                                    subparts.shift();
                                    gradient[ inputtheme ].origin = subparts.shift() || 'top';
                                    gradient[ inputtheme ].start  = subparts.shift() || 'transparent';
                                    gradient[ inputtheme ].end    = subparts.shift() || 'transparent';
                                    has_gradient[ inputtheme ] = true;
                                } else {
                                    cssrules[ inputtheme ][ 'background-image' ] = value;
                                }
                            }
                        } else if ( 'seq' != inputrule ) {
                            cssrules[ inputtheme ][ inputrule ] = value;
                        }
                    }
                }
            } );
            // update swatch
            if ( 'undefined' != typeof swatch && !self.is_empty( swatch.attr( 'id' ) ) ) {
                swatch.removeAttr( 'style' );
                if ( has_gradient.parent ) {
                    swatch.ctcgrad( gradient.parent.origin, [ gradient.parent.start, gradient.parent.end ] );
                }
                //**console.log( 'combined css rules' );
                //**console.log( cssrules );
                swatch.css( cssrules.parent );  
                if ( !( swatch.attr( 'id' ).toString().match( /parent/ ) ) ) {
                    if ( has_gradient.child ) {
                        swatch.ctcgrad( gradient.child.origin, [ gradient.child.start, gradient.child.end ] );
                    }
                    //console.log( cssrules.child );
                    swatch.css( cssrules.child );
                }
                swatch.css( {'z-index':-1} );
            }
            return postdata;
        },
        
        decode_value: function( rule, value ) {
            //**console.log( 'in decode_value ( ' + rule + ' ...' );
            value = ( 'undefined' == typeof value ? '' : value );
            var self = this,
                obj = { 
                    'orig':     value, 
                    'names':    [ '' ],
                    'values':   [ value ]
                };
            if ( rule.toString().match( /^border(\-(top|right|bottom|left))?$/ ) ) {
                var regex = new RegExp( self.border_regx + '(' + self.color_regx + ')?', 'i' ),
                    params = value.toString().match( regex );
                if ( self.is_empty( params ) ) params = [];
                obj.names = [
                    '_border_width',
                    '_border_style',
                    '_border_color',
                ];
                orig = params.shift();
                //**console.log( value );
                //**console.log( regex );
                //**console.log( params );
                obj.values[ 0 ] = params.shift() || '';
                params.shift();
                obj.values[ 1 ] = params.shift() || '';
                params.shift();
                obj.values[ 2 ] = params.shift() || '';
            } else if ( rule.toString().match( /^background\-image/ ) ) {
                obj.names = [
                    '_background_url',
                    '_background_origin', 
                    '_background_color1', 
                    '_background_color2'
                ];
                obj.values = [ '', '', '', '' ];
                if ( !self.is_empty( value ) && !( value.toString().match( /(url|none)/ ) ) ) {
                    var params = value.toString().split( /:/ ),
                        stop1, stop2;
                //**console.log( value );
                //**console.log( params );
                    obj.values[ 1 ] = params.shift() || '';
                    obj.values[ 2 ] = params.shift() || '';
                    stop1 = params.shift() || '';
                    obj.values[ 3 ] = params.shift() || '';
                    stop2 = params.shift() || '';
                    obj.orig = [ 
                        obj.values[ 1 ],
                        obj.values[ 2 ],
                        obj.values[ 3 ] 
                    ].join( ' ' );
                } else {
                    obj.values[ 0 ] = value;
                }
            }
            //**console.log( obj );
            return obj;
        },
        
        image_url: function( theme, value ) {
            var self = this,
                parts = value.toString().match( /url\(['" ]*(.+?)['" ]*\)/ ),
                path = self.is_empty( parts ) ? null : parts[ 1 ],
                url = ctcAjax.theme_uri + '/' + ( 'parent' == theme ? ctcAjax.parnt : ctcAjax.child ) + '/',
                image_url;
            if ( !path ) { 
                return false; 
            } else if ( path.toString().match( /^(data:|https?:|\/)/ ) ) { 
                image_url = value; 
            } else { 
                image_url = 'url(' + url + path + ')'; 
            }
            return image_url;
        },
    
        setup_menus: function() {
            var self = this;
            //console.log( 'setup_menus' );
            self.setup_query_menu();
            self.setup_selector_menu();
            self.setup_rule_menu();
            self.setup_new_rule_menu();
            self.load_queries();
            self.load_rules();
            // selectors will be loaded after query selected
            self.set_query( self.current_query );
        },
        
        load_queries: function() {
            var self = this;
            //console.log( 'load_queries' );
            // retrieve unique media queries
            self.query_css( 'queries', null );
        },
        
        load_selectors: function() {
            var self = this;
            //console.log( 'load_selectors' );
            // retrieve unique selectors from query value
            self.query_css( 'selectors', self.current_query );
        },
        
        load_rules: function() {
            var self = this;
            //console.log( 'load_rules' );
            // retrieve all unique rules
            self.query_css( 'rules', null );
        },
        
        load_selector_values: function() {
            var self = this;
            //console.log( 'load_selector_values: ' + self.current_qsid );
            // retrieve individual values from qsid
            self.query_css( 'qsid', self.current_qsid );
        },
        
        get_queries: function( request, response ) {
            //console.log( 'get_queries' );
            //console.log( this );
            var self = this,
                arr = [], 
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
            if ( $.chldthmcfg.is_empty( this.element.data( 'menu' ) ) ) {
                arr.push( { 'label': ctcAjax.nosels_txt, 'value': null } );
            } else {
                // note: key = ndx, value = query name
                $.each( this.element.data( 'menu' ), function( key, val ) {
                    if ( matcher.test( val ) ) {
                        arr.push( { 'label': val, 'value': val } );
                    }
                } );
            }
            response( arr );
        },
        
        get_selectors: function( request, response ) {
            //console.log( 'get_selectors' );
            var self = this,
                arr = [], 
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
            if ( $.chldthmcfg.is_empty( this.element.data( 'menu' ) ) ) {
                arr.push( { 'label': ctcAjax.nosels_txt, 'value': null } );
            } else {
                // note: key = selector name, value = qsid
                $.each( this.element.data( 'menu' ), function( key, val ) {
                    if ( matcher.test( key ) ) {
                        arr.push( { 'label': key, 'value': val } );
                    }
                } );
            }
            response( arr );
        },
        
        get_rules: function( request, response ) {
            //console.log( 'get_rules' );
            var self = this,
                arr = [], 
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
            if ( $.chldthmcfg.is_empty( this.element.data( 'menu' ) ) ) {
                arr.push( { 'label': ctcAjax.nosels_txt, 'value': null } );
            } else {
                // note: key = ruleid, value = rule name
                $.each( this.element.data( 'menu' ), function( key, val ) {
                    if ( matcher.test( key ) ) {
                        arr.push( { 'label': key, 'value': val } );
                    }
                } );
            }
            response( arr );
        },
                
        get_filtered_rules: function( request, response ) {
            //console.log( 'get_filtered_rules' );
            var arr = [],
                matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" ); //,
            $.each( $( '#ctc_rule_menu' ).data( 'menu' ), function( key, val ) {
                //multiple versions of rule ok
                if ( matcher.test( key ) ) {
                    arr.push( { 'label': key, 'value': val } );
                }
            } );
            response( arr );
        },
        
        /**
         * parent and child values are stored in separate arrays
         * this function puts them into parent/child columns by rulevalid
         */
        merge_ruleval_arrays: function( rule, value, isnew ) {
            //**console.log( 'merge_ruleval_arrays' );
            var self = this,
                valarr = {};
                nextval = isnew ? value.child.pop() : null; // if new rule, pop off the top before counting
            //**console.log( value );
            $.each( [ 'parnt', 'child' ], function( ndx, template ) {
                // iterate through parent and child val arrays and populate new assoc array with parent/child for each rulevalid
                if ( !self.is_empty( value[ template ] ) ) {
                    $.each( value[ template ], function( ndx2, val ) {
                        if ( isnew ) {
                            // if new rule, increment new rulevalid but do not add to parent/child assoc array
                            if ( parseInt( val[ 2 ] ) >= parseInt( nextval[ 2 ] ) ) nextval[ 2 ] = parseInt( val[ 2 ] ) + 1;
                        } else {
                            // add to parent/child assoc array with rulevalid as key
                            if ( self.is_empty( valarr[ val[ 2 ] ] ) ) valarr[ val[ 2 ] ] = {};
                            valarr[ val[ 2 ] ][ template ] = val;
                        }
                    } );
                }
            } );
            // if new rule, create new parent child assoc array element with new rulevalid as key
            if ( isnew ) {
                valarr[ nextval[ 2 ] ] = {
                    parnt: [],
                    child: nextval
                };
            }
            return valarr;
        },

        /**
         * input_row
         * render individual row of inputs for a given selector/rule combination
         * qsid     query/selector id
         * rule     css property 
         * seq      panel id from rule/value tab
         * data     contains all rules/values for selector
         * isnew    is passed true when new rule is selected from menu
         */
        input_row: function( qsid, rule, seq, data, isnew ) {
            //console.log( 'in input_row' );
            var self = this,
                html = '';
            if ( !self.is_empty( data ) && !self.is_empty( data.value ) && !self.is_empty( data.value[ rule ] ) ) {
                var value = data.value[ rule ],
                    valarr = self.merge_ruleval_arrays( rule, value, isnew );
                $.each( valarr, function( ndx, val ) {
                    var pval = self.decode_value( rule, self.is_empty( val.parnt ) ? '' : val.parnt[ 0 ] ),
                        pimp = self.is_empty( val.parnt ) || 0 == val.parnt[ 1 ] ? 0 : 1,
                        cval = self.decode_value( rule, self.is_empty( val.child ) ? '' : val.child[ 0 ] ),
                        cimp = self.is_empty( val.child ) || 0 == val.child[ 1 ] ? 0 : 1;
                    html += '<div class="ctc-' + ( 'ovrd' == seq ? 'input' : 'selector' ) + '-row clearfix"><div class="ctc-input-cell">';
                    if ( 'ovrd' == seq ) {
                        html += rule.replace( /\d+/g, self.from_ascii );
                    } else {
                        html += data.selector + '<br/><a href="#" class="ctc-selector-edit"'
                            + ' id="ctc_selector_edit_' + qsid + '" >' + self.getxt( 'edit' ) + '</a> '
                            + ( self.is_empty( pval.orig ) ? self.getxt( 'child_only' ) : '' );
                    }
                    html += '</div><div class="ctc-parent-value ctc-input-cell"' + ( 'ovrd' != seq ? ' style="display:none"' : '' )
                        + ' id="ctc_' + seq + '_parent_' + rule + '_' + qsid + '_' + ndx + '">' 
                        + ( self.is_empty( pval.orig ) ? '[no value]' : pval.orig + ( pimp ? self.getxt( 'important' ) : '' ) ) 
                        + '</div><div class="ctc-input-cell">';
                    if ( !self.is_empty( pval.names ) ) {
                        $.each( pval.names, function( namendx, newname ) {
                            newname = ( self.is_empty( newname ) ? '' : newname );
                            html += '<div class="ctc-child-input-cell clear">';
                            var id = 'ctc_' + seq + '_child_' + rule + '_' + qsid + '_' + ndx + newname,
                                newval;
                            if ( false === ( newval = cval.values.shift() ) ) {
                                newval = '';
                            }
                                
                            html += ( self.is_empty( newname ) ? '' : self.getxt( newname ) + ':<br/>' ) 
                                + '<input type="text" id="' + id + '" name="' + id + '" class="ctc-child-value' 
                                + ( ( newname + rule ).toString().match( /color/ ) ? ' color-picker' : '' ) 
                                + ( ( newname ).toString().match( /url/ ) ? ' ctc-input-wide' : '' )
                                + '" value="' + self.esc_quot( newval ) + '" /></div>';
                        } );
                        var impid = 'ctc_' + seq + '_child_' + rule + '_i_' + qsid + '_' + ndx;
                        html += '<label for="' + impid + '"><input type="checkbox"'
                            + ' id="' + impid + '" name="' + impid + '" value="1" '
                            + ( cimp ? 'checked' : '' ) + ' />' 
                            + self.getxt( 'important' ) + '</label>';
                    }
                    html += '</div>';
                    if ( 'ovrd' != seq ) {
                        html += '<div class="ctc-swatch ctc-specific"'
                            + ' id="ctc_child_' + rule + '_' + qsid + '_' + ndx + '_swatch">' 
                            + self.getxt( 'swatch' ) + '</div>' 
                            + '<div class="ctc-child-input-cell ctc-button-cell"'
                            + ' id="ctc_save_' + rule + '_' + qsid + '_' + ndx + '_cell">'
                            + '<input type="button" class="button ctc-save-input"'
                            + ' id="ctc_save_' + rule + '_' + qsid + '_' + ndx + '"'
                            + ' name="ctc_save_' + rule + '_' + qsid + '_' + ndx + '"'
                            + ' value="Save" /></div>';
                    }
                    html += '</div><!-- end input row -->' + "\n";
                } );
            }
            return html;
        },
        
        scrolltop: function() {
            $('html, body, .ctc-option-panel-container').animate( { scrollTop: 0 } );        
        },
        
        css_preview: function( theme ) {
            var self = this,
                theme;
            //console.log( 'css_preview: ' + theme );
            if ( !( theme = theme.match( /(child|parnt)/ )[ 1 ] ) ) {
                theme = 'child';
            }
            // retrieve raw stylesheet ( parent or child )
            self.query_css( 'preview', theme );
        },
        
        /**
         * The "setup" functions initialize jQuery UI widgets
         */
        setup_iris: function( obj ) {
            // deprecated: using spectrum for alpha support
            var self = this;
            self.setup_spectrum( obj );
        },
        
        setup_spectrum: function( obj ) {
            var self        = this,
                colortxt    = $( obj ).attr( 'id' ) + '_colortxt',
                palette     = !self.is_empty( ctcAjax.palette );
            try {
                $( obj ).spectrum( {
                    showInput:              true,
                    allowEmpty:             true,
                    showAlpha:              true,
                    showInitial:            true,
                    preferredFormat:        "hex", // 'name', //
                    clickoutFiresChange:    true,
                    move:                   function( color ) {
                        $( obj ).data( 'color', color );
                        self.coalesce_inputs( obj );
                    },
                    showPalette: palette ? true : false, 
                    showSelectionPalette: palette ? true : false,
                    palette: [ ],
                    maxSelectionSize: 36,
                    localStorageKey: "ctc-palette." + ctcAjax.child,
                    hideAfterPaletteSelect: true,
                } ).on( 'change', function( e ){
                    var color = $( this ).spectrum( 'get' );
                    //console.log( 'color change: ' + color );
                    self.coalesce_inputs( this );
                } ).on( 'keyup', function( e ) {
                    // update spectrum ui to match text input after half-second delay
                    var $this = this,
                        $val = $( this ).val();
                    clearTimeout( $( this ).data( 'spectrumTimer' ) );
                    $( this ).data( 'spectrumTimer', setTimeout( 
                        function() { 
                            self.coalesce_inputs( $this );
                            $( $this ).spectrum( 'set', $val );
                            
                        }, 
                        500  
                    ) );
                } );
                
            } catch ( exn ) {
                self.jquery_exception( exn, 'Spectrum Color Picker' );
            }
        },
        
        color_text: function( color ) {
            var self = this;
            if ( self.is_empty( color ) ) {
                return '';
            } else if ( color.getAlpha() < 1 ) {
                return color.toRgbString();
            } else {
                return color.toHexString();
            }
        },
        
        setup_query_menu: function() {
            var self = this;
            //console.log( 'setup_query_menu' );
            try {
                $( '#ctc_sel_ovrd_query' ).autocomplete( {
                    source: self.get_queries,
                    minLength: 0,
                    selectFirst: true,
                    autoFocus: true,
                    select: function( e, ui ) {
                        self.set_query( ui.item.value );
                        return false;
                    },
                    focus: function( e ) { 
                        e.preventDefault(); 
                    }
                } ).data( 'menu' , {} );
            } catch ( exn ) {
                self.jquery_exception( exn, 'Query Menu' );
            }
        },
        
        setup_selector_menu: function() {
            var self = this;
            //console.log( 'setup_selector_menu' );
            try {
                $( '#ctc_sel_ovrd_selector' ).autocomplete( {
                    source: self.get_selectors,
                    selectFirst: true,
                    autoFocus: true,
                    select: function( e, ui ) {
                        self.set_selector( ui.item.value, ui.item.label );
                        return false;
                    },
                    focus: function( e ) { 
                        e.preventDefault(); 
                    }
                } ).data( 'menu' , {} );
            } catch ( exn ) {
                self.jquery_exception( exn, 'Selector Menu' );
            }
        },
        
        setup_rule_menu: function() {
            var self = this;
            //console.log( 'setup_rule_menu' );
            try {
            $( '#ctc_rule_menu' ).autocomplete( {
                source: self.get_rules,
                //minLength: 0,
                selectFirst: true,
                autoFocus: true,
                select: function( e, ui ) {
                    self.set_rule( ui.item.value, ui.item.label );
                    return false;
                },
                focus: function( e ) { 
                    e.preventDefault(); 
                }
            } ).data( 'menu' , {} );
            } catch ( exn ) {
                self.jquery_exception( exn, 'Property Menu' );
            }
        },
        
        setup_new_rule_menu: function() {
            var self = this;
            try {
            $( '#ctc_new_rule_menu' ).autocomplete( {
                source: self.get_filtered_rules,
                //minLength: 0,
                selectFirst: true,
                autoFocus: true,
                select: function( e, ui ) {
                    //console.log( 'new rule selected' );
                    e.preventDefault();
                    var newrule = ui.item.label.replace( /[^\w\-]/g, self.to_ascii ),
                        n = 1,
                        row,
                        first;
                    //console.log( 'current qsdata before:' );
                    //console.log( self.current_qsdata );
                    if ( self.is_empty( self.current_qsdata.value ) ) {
                        self.current_qsdata.value = {};
                    }
                    if ( self.is_empty( self.current_qsdata.value[ ui.item.label ] ) ) {
                        self.current_qsdata.value[ ui.item.label ] = {};
                    }
                    if ( self.is_empty( self.current_qsdata.value[ ui.item.label ].child ) ) {
                        self.current_qsdata.value[ ui.item.label ].child = [];
                    }
                    //console.log( 'current qsdata after:' );
                    //console.log( self.current_qsdata );
                    // seed current qsdata with new blank value with id 1
                    // this will be modified during input_row function to be next id in order
                    self.current_qsdata.value[ ui.item.label ].child.push( [ '', 0, 1, 1 ] );
                    row = $( self.input_row( self.current_qsid, newrule, 'ovrd', self.current_qsdata, true ) );
                    $( '#ctc_sel_ovrd_rule_inputs' ).append( row );
                    $( '#ctc_new_rule_menu' ).val( '' );
                    
                    row.find( 'input[type="text"]' ).each( function( ndx, el ) {
                        if (! first) first = el;
                        if ( $( el ).hasClass( 'color-picker' ) )
                            self.setup_spectrum( el );
                    } );
                    if ( first )
                        $( first ).focus();
                    if ( self.jquery_err.length ) 
                        self.jquery_notice( 'setup_new_rule_menu' );
                    return false;
                },
                focus: function( e ) { 
                    e.preventDefault(); 
                }
            } ).data( 'menu' , {} );
            } catch ( exn ) {
                self.jquery_exception( exn, 'New Property Menu' );
            }
        },
        
        set_existing: function() {
            var self = this;
            if ( $( '#ctc_theme_child' ).length && $( '#ctc_child_type_existing' ).is( ':checked' ) ) {
                var child   = $( '#ctc_theme_child' ).val();
                if ( !self.is_empty( child ) ) {
                    $( '#ctc_child_name' ).val( ctcAjax.themes.child[ child ].Name );
                    $( '#ctc_child_author' ).val( ctcAjax.themes.child[ child ].Author );
                    $( '#ctc_child_version' ).val( ctcAjax.themes.child[ child ].Version );
                    $( '#ctc_child_authoruri' ).val( ctcAjax.themes.child[ child ].AuthorURI );
                    $( '#ctc_child_themeuri' ).val( ctcAjax.themes.child[ child ].ThemeURI );
                    $( '#ctc_child_descr' ).val( ctcAjax.themes.child[ child ].Descr );
                    $( '#ctc_child_tags' ).val( ctcAjax.themes.child[ child ].Tags );
                    $( '#ctc_duplicate_theme' ).prop( 'checked', false );
                    $( '#ctc_duplicate_theme_slug' ).val( '' );
                    $( '#input_row_duplicate_theme' ).show();
                }
            }
        },
        
        set_notice: function( noticearr ) {
            var self = this,
                errorHtml = '';
            if ( !self.is_empty( noticearr ) ) {
                $.each( noticearr, function( type, list ) {
                    errorHtml += '<div class="' + type + '"><ul>' + "\n";
                    $( list ).each( function( ndx, el ) {
                        errorHtml += '<li>' + el.toString() + '</li>' + "\n";
                    } );
                    errorHtml += '</ul></div>';        
                } );
            }
            $( '#ctc_error_notice' ).html( errorHtml );
            $( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );        
        },
        
        set_parent_menu: function( obj ) {
            $( '#ctc_theme_parnt' ).parents( '.ctc-input-row' ).first()
                .append( '<span class="ctc-status-icon spinner"></span>' );
            $( '.spinner' ).show();
            document.location='?page=' + ctcAjax.page + '&ctc_parent=' + obj.value;
        },
        
        set_child_menu: function( obj ) {
            var self = this,
                template,
                parent;
            if ( !self.is_empty( ctcAjax.themes.child[ obj.value ] ) ) {
                template = ctcAjax.themes.child[ obj.value ].Template,
                parent  = $( '#ctc_theme_parnt' ).val();
                //console.log( 'template: ' + template + ' parent: ' + parent );
                if ( template == parent ) {
                    $( '#ctc_child_name' ).val( ctcAjax.themes.child[ obj.value ].Name );
                    $( '#ctc_child_author' ).val( ctcAjax.themes.child[ obj.value ].Author );
                    $( '#ctc_child_version' ).val( ctcAjax.themes.child[ obj.value ].Version );
                } else {
                    $( '#ctc_theme_child' ).parents( '.ctc-input-row' ).first()
                        .append( '<span class="ctc-status-icon spinner"></span>' );
                    $( '.spinner' ).show();
                    document.location='?page=' + ctcAjax.page + '&ctc_parent=' + template + '&ctc_child=' + obj.value;
                }
            }
        },
        
        set_query: function( value ) {
            var self = this;
            if ( self.is_empty( value ) ) return false;
            //console.log( 'set_query: ' + value );
            self.current_query = value;
            $( '#ctc_sel_ovrd_query' ).val( '' );
            $( '#ctc_sel_ovrd_query_selected' ).text( value );
            $( '#ctc_sel_ovrd_selector' ).val( '' );
            $( '#ctc_sel_ovrd_selector_selected' ).html( '&nbsp;' );
            //$( '#ctc_sel_ovrd_rule_inputs' ).html( '' );
            self.load_selectors();
            self.scrolltop();
        },
        
        set_selector: function( value, label ) {
            var self = this;
            if ( self.is_empty( value ) ) return false;
            //console.log( 'set_selector: ' + value + ' label: ' + label );
            $( '#ctc_sel_ovrd_selector' ).val( '' );
            self.current_qsid = value;
            self.reload_menus = false;
            self.load_selector_values();
            self.scrolltop();
        },
        
        set_rule: function( value, label ) {
            //console.log( 'set_rule: ' + value + ' label: ' + label );
            var self = this;
            if ( self.is_empty( value ) ) return false;
            $( '#ctc_rule_menu' ).val( '' );
            $( '#ctc_rule_menu_selected' ).text( label );
            $( '.ctc-rewrite-toggle' ).text( self.getxt( 'rename' ) );
            $( '#ctc_rule_value_inputs, #ctc_input_row_rule_header' ).show();
            // retrieve unique values by rule
            self.query_css( 'rule_val', value );
            self.scrolltop();
        },
        
        set_qsid: function( obj ) {
            var self = this;
            //console.log( 'set_qsid: ' + $( obj ).attr( 'id' ) );
            self.current_qsid = $( obj ).attr( 'id' ).match( /_(\d+)$/ )[ 1 ];
            self.focus_panel( '#query_selector_options' );
            self.reload_menus = true;
            self.load_selector_values();  
        },
        
        /**
         * slurp website home page and parse header for linked stylesheets
         * set these to be parsed as "default" stylesheets
         */
        set_addl_css: function() { 
            //console.log( 'set_addl_css' );
            var self = this,
                template    = $( '#ctc_theme_parnt' ).val(),
                theme_uri   = ctcAjax.theme_uri.replace( /^https?:\/\//, '' ),
                homeurl     = ctcAjax.homeurl.replace( /^https?/, ctcAjax.ssl ? 'https' : 'http' ),
                url         = homeurl + '&template=' + template + '&stylesheet=' + template,
                regex       = new RegExp( "<link rel=[\"']stylesheet[\"'][^>]+?" 
                    + theme_uri + '/' + template + '/(.+?\\.css)[^>]+?>', 'g' ),
                additional;
            if ( self.is_empty( template ) ) return;
            //console.log( template );
            if ( template != ctcAjax.parnt ) {
                $.get( url, function( data ) {
                    //console.log( data );
                    while ( additional = regex.exec( data ) ) {
                        //console.log( additional );
                        if ( 'style.css' == additional[ 1 ] ) break; // bail after main stylesheet
                        if ( additional[ 1 ].match( /bootstrap/ ) ) continue; // don't autoselect Bootstrap stylesheets
                        $( '.ctc_checkbox' ).each( function( ndx, el ) {
                            if ( $( this ).val() == additional[ 1 ] ) $( this ).prop( 'checked', true );
                        } );
                    }
                    data = null; // send page to garbage
                } );
            } else {
                //console.log('existing... using addl_css array');
                $( ctcAjax.addl_css ).each( function( ndx, el ) {
                    $( '#ctc_stylesheet_files .ctc_checkbox' ).each( function( index, elem ) {
                        if ( $( this ).val() == el ) $( this ).prop( 'checked', true );
                    } );
                } );
            }
        },
        
        /**
         * Retrieve data from server and execute callback on completion
         */
        query_css: function( obj, key, params ) {
            //console.log( 'query_css: ' + obj + ' key: ' + key );
            var self = this,
                postdata = { 'ctc_query_obj' : obj, 'ctc_query_key': key },
                status_sel = '#ctc_status_' + obj + ( 'val_qry' == obj ? '_' + key : '' );
            
            if ( 'object' === typeof params ) {
                $.each( params, function( key, val ) {
                    postdata[ 'ctc_query_' + key ] = val;
                } );
            }
            $( '.query-icon,.ctc-status-icon' ).remove();
            //console.log( status_sel + ' ' + $( status_sel ).length );
            $( status_sel + ' .ctc-status-icon' ).remove();
            $( status_sel ).append( '<span class="ctc-status-icon spinner query-icon"></span>' );
            $( '.spinner' ).show();
            // add wp ajax action to array
            //console.log( $( '#ctc_action' ).val() );
            postdata[ 'action' ] = ( !self.is_empty( $( '#ctc_action' ).val() ) 
                && 'plugin' == $( '#ctc_action' ).val() ) ? 
                    'ctc_plgqry' : 'ctc_query';
            postdata[ '_wpnonce' ] = $( '#_wpnonce' ).val();
            // ajax post input data
            //console.log( 'query_css postdata:' );
            //console.log( postdata );
            self.ajax_post( obj, postdata );
        },
        /**
         * Post data to server for saving and execute callback on completion
         */
        save: function( obj ) {
            //console.log( 'save: ' + $( obj ).attr( 'id' ) );
            var self = this,
                url = ctcAjax.ajaxurl,  // get ajax url from localized object
                postdata = {},
                $selector, $query, $imports, $rule,
                id = $( obj ).attr( 'id' ), newsel, origsel;
    
            // disable the button until ajax returns
            $( obj ).prop( 'disabled', true );
            // clear previous success/fail icons
            $( '.ctc-query-icon,.ctc-status-icon' ).remove();
            // show spinner
            $( obj ).parent( '.ctc-textarea-button-cell, .ctc-button-cell' )
                .append( '<span class="ctc-status-icon spinner save-icon"></span>' );
            if ( id.match( /ctc_configtype/ ) ) {
                $( obj ).parents( '.ctc-input-row' ).first()
                    .append( '<span class="ctc-status-icon spinner save-icon"></span>' );
                postdata[ 'ctc_configtype' ] = $( obj ).val();
            } else if ( ( $selector = $( '#ctc_new_selectors' ) ) 
                && 'ctc_save_new_selectors' == $( obj ).attr( 'id' ) ) {
                postdata[ 'ctc_new_selectors' ] = $selector.val();
                if ( $query = $( '#ctc_sel_ovrd_query_selected' ) ) {
                    postdata[ 'ctc_sel_ovrd_query' ] = $query.text();
                }
                self.reload_menus = true;
            } else if ( ( $imports = $( '#ctc_child_imports' ) ) 
                && 'ctc_save_imports' == id ) {
                postdata[ 'ctc_child_imports' ] = $imports.val();
            } else if ( 'ctc_is_debug' == id ) {
                postdata[ 'ctc_is_debug' ] = $( '#ctc_is_debug' ).is( ':checked' ) ? 1 : 0;
            } else {
                // coalesce inputs
                postdata = self.coalesce_inputs( obj );
            }
            $( '.save-icon' ).show();
            // add rename selector value if it exists
            $( '#ctc_sel_ovrd_selector_selected' )
                .find( '#ctc_rewrite_selector' ).each( function() {
                newsel = $( '#ctc_rewrite_selector' ).val();
                origsel = $( '#ctc_rewrite_selector_orig' ).val();
                if ( self.is_empty( newsel ) || !newsel.toString().match( /\w/ ) ) {
                    newsel = origsel;
                } else {
                    postdata[ 'ctc_rewrite_selector' ] = newsel;
                    self.reload_menus = true;
                }
                $( '.ctc-rewrite-toggle' ).text( self.getxt( 'rename' ) );
                $( '#ctc_sel_ovrd_selector_selected' ).html( newsel );
            } );
            // add wp ajax action to array
            //console.log( $( '#ctc_action' ).val() );
            postdata[ 'action' ] = ( !self.is_empty( $( '#ctc_action' ).val() ) 
                && 'plugin' == $( '#ctc_action' ).val() ) ? 
                    'ctc_plugin' : 'ctc_update';
            postdata[ '_wpnonce' ] = $( '#_wpnonce' ).val();
            //console.log( postdata );
            // ajax post input data
            self.ajax_post( 'qsid', postdata );
        },
        
        ajax_post: function( obj, data, datatype ) {
            var self = this,
                url = ctcAjax.ajaxurl;
            //console.log( 'ajax_post: ' + obj );
            //console.log( data );
            // get ajax url from localized object
            $.ajax( { 
                url:        url,  
                data:       data,
                dataType:   ( self.is_empty( datatype ) ? 'json' : datatype ), 
                // 'ctc_update' == data.action && // 'rule_val' == obj ? 'text' : // 'qsid' == obj ? 'text' : 
                // 'ctc_update' == data.action && 'qsid' == obj ? 'text' : 
                type:       'POST'
            } ).done( function( response ) {
                //console.log( response );
                self.handle_success( obj, response );
            } ).fail( function() {
                self.handle_failure( obj );
            } );  
        },
        
        handle_failure: function( obj ) {
            var self = this;
            //console.log( 'handle_failure: ' + obj );
            $( '.query-icon, .save-icon' ).removeClass( 'spinner' ).addClass( 'failure' );
            $( 'input[type=submit], input[type=button], input[type=checkbox],.ctc-delete-input' ).prop( 'disabled', false );
            $( '.ajax-pending' ).removeClass( 'ajax-pending' );
            //FIXME: return fail text in ajax response
            if ( 'preview' == obj )
                $( '#view_parnt_options_panel,#view_child_options_panel' )
                    .text( self.getxt( 'css_fail' ) );
        },
        
        handle_success: function( obj, response ) {
            var self = this;
            // query response
            //console.log( 'handle_success: ' + obj );
            //console.log( response );
            // hide spinner
            $( '.query-icon, .save-icon' ).removeClass( 'spinner' );
            $( '.ajax-pending' ).removeClass( 'ajax-pending' );
            // hide spinner
            if ( self.is_empty( response ) ) {
                self.handle_failure( obj );
            } else {
                $( '#ctc_new_selectors' ).val( '' );
                // update data objects   
                // show check mark
                // FIXME: distinction between save and query, update specific status icon
                $( '.query-icon, .save-icon' ).addClass( 'success' );
                $( 'input[type=submit], input[type=button], input[type=checkbox],.ctc-delete-input' ).prop( 'disabled', false );
                // update ui from each response object  
                $( response ).each( function() {
                    if ( 'function' == typeof self.update[ this.obj ] ) {
                        //console.log( 'executing method update.' + this.obj );
                        self.update[ this.obj ].call( self, this );
                    } else {
                        //console.log( 'Fail: no method update.' + this.obj );
                    }
                } );
            }
        },
        
        jquery_exception: function( exn, type ) {
            var self = this,
                ln = self.is_empty( exn.lineNumber ) ? '' : ' line: ' + exn.lineNumber,
                fn = self.is_empty( exn.fileName ) ? '' : ' ' + exn.fileName.split( /\?/ )[ 0 ];
            self.jquery_err.push( '<code><small>' + type + ': ' + exn.message + fn + ln + '</small></code>' );
        },
        
        jquery_notice: function( fn ) {
            //console.log( fn );
            var self        = this,
                culprits    = [],
                errors      = [];
            // disable form submits
            $( 'input[type=submit], input[type=button]' ).prop( 'disabled', true );
            $( 'script' ).each( function( ndx,el ){
                var url = $( this ).prop( 'src' );
                if ( !self.is_empty( url ) && url.match( /jquery(\.min|\.js|\-?ui)/i ) 
                    && ! url.match( /load\-scripts.php/ ) ) {
                    culprits.push( '<code><small>' + url.split( /\?/ )[ 0 ] + '</small></code>' );
                }
            } );
            errors.push( '<strong>' + self.getxt( 'js' ) + '</strong> ' + self.getxt( 'contact' ) );
            //if ( 1 == ctcAjax.is_debug ) {
                errors.push( self.jquery_err.join( '<br/>' ) );
            //}
            if ( culprits.length ) {
                errors.push( self.getxt( 'jquery' ) + '<br/>' + culprits.join( '<br/>' ) );
            }
            errors.push( self.getxt( 'plugin' ) );
            self.set_notice( { 'error': errors } );
        },
    
        update: {
            // render individual selector inputs on Query/Selector tab
            qsid: function( res ) {
                //console.log( res );
                var self = this,
                    id, html, val, selector, empty;
                self.current_qsid  = res.key;
                self.current_qsdata = res.data;
                //console.log( 'update.qsid: ' + self.current_qsid );
                $( '#ctc_sel_ovrd_qsid' ).val( self.current_qsid );
                if ( self.is_empty( self.current_qsdata.seq ) ) {
                    $( '#ctc_child_load_order_container' ).empty();
                } else {
                    id = 'ctc_ovrd_child_seq_' + self.current_qsid;
                    val = parseInt( self.current_qsdata.seq );
                    html = '<input type="text" id="' + id + '" name="' + id + '"'
                        + ' class="ctc-child-value" value="' + val + '" />';
                    $( '#ctc_child_load_order_container' ).html( html );
                }
                if ( self.is_empty( self.current_qsdata.value ) ) {
                    //console.log( 'qsdata is empty' );
                    empty = true;
                    $( '#ctc_sel_ovrd_rule_inputs' ).empty(); 
                } else {
                    //console.log( 'qsdata NOT empty' );
                    empty = false;
                    html = '';
                    $.each( self.current_qsdata.value, function( rule, value ) {
                        html += self.input_row( self.current_qsid, rule, 'ovrd', self.current_qsdata );
                    } );
                    $( '#ctc_sel_ovrd_rule_inputs' ).html( html ).find( '.color-picker' ).each( function() {
                        self.setup_spectrum( this );
                    } );
                    self.coalesce_inputs( '#ctc_child_all_0_swatch' );
                }
                if ( self.jquery_err.length ) {
                    self.jquery_notice( 'update.qsid' );
                } else {
                    //console.log( 'reload menus: ' + ( self.reload_menus ? 'true' : 'false' ) );
                    if ( self.reload_menus ) {
                        self.load_queries();
                        self.set_query( self.current_qsdata.query );
                        self.load_rules();
                    }
                    $( '#ctc_sel_ovrd_selector_selected' ).text( self.current_qsdata.selector );
                    $( '.ctc-rewrite-toggle' ).text( self.getxt( 'rename' ) );
                    $( '.ctc-rewrite-toggle' ).show();
                    if ( !empty ){
                        $( '#ctc_sel_ovrd_rule_header,'
                        + '#ctc_sel_ovrd_new_rule,'
                        + '#ctc_sel_ovrd_rule_inputs_container,'
                        + '#ctc_sel_ovrd_rule_inputs' ).show();
                    } else {
                        $( '#ctc_sel_ovrd_rule_header,'
                        + '#ctc_sel_ovrd_new_rule,'
                        + '#ctc_sel_ovrd_rule_inputs_container,'
                        + '#ctc_sel_ovrd_rule_inputs' ).hide();
                    }
                    //self.scrolltop();
                }
            }, 
            // render list of unique values for given rule on Property/Value tab
            rule_val: function( res ) {
                //console.log( 'update.rule_val: ' + res.key );
                //console.log( res.data );
                var self = this,
                    rule = $( '#ctc_rule_menu_selected' ).text(), 
                    html = '<div class="ctc-input-row clearfix" id="ctc_rule_row_' + rule + '">' + "\n";
                //console.log( 'rule: ' + rule );
                if ( !self.is_empty( res.data ) ) {
                    $.each( res.data, function( valid, value ) {
                        var parentObj = self.decode_value( rule, value );
                        html += '<div class="ctc-parent-row clearfix"'
                            + ' id="ctc_rule_row_' + rule + '_' + valid + '">' + "\n"
                            + '<div class="ctc-input-cell ctc-parent-value"'
                            + ' id="ctc_' + valid + '_parent_' + rule + '_' + valid + '">' 
                            + parentObj.orig + '</div>' + "\n"
                            + '<div class="ctc-input-cell">' + "\n"
                            + '<div class="ctc-swatch ctc-specific"'
                            + ' id="ctc_' + valid + '_parent_' + rule + '_' + valid + '_swatch">' 
                            + self.getxt( 'swatch' ) + '</div></div>' + "\n"
                            + '<div class="ctc-input-cell">'
                            + '<a href="#" class="ctc-selector-handle"'
                            + ' id="ctc_selector_' + rule + '_' + valid + '">'
                            + self.getxt( 'selector' ) + '</a></div>' + "\n"
                            + '<div id="ctc_selector_' + rule + '_' + valid + '_container"'
                            + ' class="ctc-selector-container">' + "\n"
                            + '<a href="#" id="ctc_selector_' + rule + '_' + valid + '_close"'
                            + ' class="ctc-selector-handle ctc-exit" title="' 
                            + self.getxt( 'close' ) + '"></a>'
                            + '<div id="ctc_selector_' + rule + '_' + valid + '_inner_container"'
                            + ' class="ctc-selector-inner-container clearfix">' + "\n"
                            + '<div id="ctc_status_val_qry_' + valid + '"></div>' + "\n"
                            + '<div id="ctc_selector_' + rule + '_' + valid + '_rows"></div>' + "\n"
                            + '</div></div></div>' + "\n";
                    } );
                    html += '</div>' + "\n";
                }
                $( '#ctc_rule_value_inputs' ).html( html ).find( '.ctc-swatch' ).each( function() {
                    self.coalesce_inputs( this );
                } );
            },
            // render list of selectors grouped by query for given value on Property/Value Tab
            val_qry: function( res ) {
                var self = this,
                    html = '';
                if ( !self.is_empty( res.data ) ) {
                    $.each( res.data, function( rule, queries ) {
                        page_rule = rule;
                        $.each( queries, function( query, selectors ) {
                            html += '<h4 class="ctc-query-heading">' + query + '</h4>' + "\n";
                            if ( !self.is_empty( selectors ) ) {
                                $.each( selectors, function( qsid, qsdata ) {
                                    html += self.input_row( qsid, rule, res.key, qsdata );
                                } );
                            }
                        } );
                    } );
                }
                selector = '#ctc_selector_' + rule + '_' + res.key + '_rows';
                $( selector ).html( html ).find( '.color-picker' ).each( function() {
                    self.setup_spectrum( this );
                } );
                $( selector ).find( '.ctc-swatch' ).each( function() {
                    self.coalesce_inputs( this );
                } );
                if ( self.jquery_err.length ) self.jquery_notice( 'val_qry' );
            },
            // populate list of queries and attach to query input element
            queries: function( res ) {
                $( '#ctc_sel_ovrd_query' ).data( 'menu', res.data );
            },
            // populate list of selectors and attach to selector input element
            selectors: function( res ) {
                $( '#ctc_sel_ovrd_selector' ).data( 'menu', res.data );
            },
            // populate list of rules and attach to rule input element
            rules: function( res ) {
                $( '#ctc_rule_menu' ).data( 'menu', res.data );
            },
            // render debug output
            debug: function( res ) {
                $( '#ctc_debug_container' ).html( res.data );
                //console.log( 'debug:' );
                //console.log( res.data );
            },
            // render stylesheet preview on child or parent css tab
            preview: function( res ) {
                $( '#view_' + res.key + '_options_panel' ).text( res.data );
            }
            
        },
        
        // initialize object vars, bind event listeners to elements, load menus and start plugin
        init: function() {
            //console.log( 'initializing...' )
            var self = this;
            // auto populate parent/child tab values
            self.autogen_slugs();
            self.set_existing();
            // initialize theme menus
            if ( !$( '#ctc_theme_parnt' ).is( 'input' ) ) {
                //console.log( 'initializing theme select menus...' )
                try {
                    $.widget( 'ctc.themeMenu', $.ui.selectmenu, {
                        _renderItem: function( ul, item ) {
                            var li = $( "<li>" ),
                                sel = item.value.replace( /[^\w\-]/, '' );
                            $( '#ctc_theme_option_' + sel )
                                .detach().appendTo( li );
                            return li.appendTo( ul );
                        }    
                    } );
                } catch( exn ) {
                    self.jquery_exception( exn, 'Theme Menu' );
                }
                try {
                    $( '#ctc_theme_parnt' ).themeMenu( {
                        select: function( event, ui ) {
                            self.set_parent_menu( ui.item );
                        }
                    } );
                } catch( exn ) {
                    if ( 'function' == typeof themeMenu )
                        $( '#ctc_theme_parnt' ).themeMenu( 'destroy' );
                    else $( '#ctc_theme_parnt-button' ).remove();
                    self.jquery_exception( exn, 'Parent Theme Menu' );
                }
                if ( self.is_empty( ctcAjax.themes.child ) ) {
                    if ( $( '#ctc_child_name' ).length ) {
                        $( '#ctc_child_name' ).val( self.testname );
                        $( '#ctc_child_template' ).val( self.testslug );
                    }
                } else {
                    try {
                        $( '#ctc_theme_child' ).themeMenu( {
                            select: function( event, ui ) {
                                self.set_child_menu( ui.item );
                            }
                        } );
                    } catch( exn ) {
                        if ( 'function' == typeof themeMenu )
                            $( '#ctc_theme_child' ).themeMenu( 'destroy' );
                        else $( '#ctc_theme_child-button' ).remove();
                        self.jquery_exception( exn, 'Child Theme Menu' );
                    }
                }
            }
            if ( self.is_empty( self.jquery_err ) ){
                //console.log( 'delegating event bindings...' )
                $( '#ctc_main' ).on( 'click', '.ctc-selector-handle', function( e ) {
                    //'.ctc-option-panel-container'
                    e.preventDefault();
                    if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
                    $( this ).addClass( 'ajax-pending' );
                    //set_notice( '' );
                    var id = $( this ).attr( 'id' ).toString().replace( '_close', '' ),
                        parts = id.toString().match( /_([^_]+)_(\d+)$/ );
                    if ( $( '#' + id + '_container' ).is( ':hidden' ) ) {
                        if ( !self.is_empty( parts[ 1 ] ) && !self.is_empty( parts[ 2 ] ) ) {
                            rule = parts[ 1 ];
                            valid = parts[ 2 ];
                            // retrieve selectors / values for individual value
                            self.query_css( 'val_qry', valid, { 'rule': rule } );
                        }
                    }
                    $( '#' + id + '_container' ).fadeToggle( 'fast' );
                    $( '.ctc-selector-container' ).not( '#' + id + '_container' ).fadeOut( 'fast' );
                } );
                $( '#ctc_main' ).on( 'click', '.ctc-save-input[type=button], .ctc-delete-input', function( e ) {
                    e.preventDefault();
                    if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
                    $( this ).addClass( 'ajax-pending' );
                    self.save( this ); // refresh menus after updating data
                    return false;
                } );
                $( '#ctc_main' ).on( 'keydown', '.ctc-selector-container .ctc-child-value[type=text]', function( e ) {
                    if ( 13 === e.which ) { 
                        //console.log( 'return key pressed' );
                        var $obj = $( this ).parents( '.ctc-selector-row' ).find( '.ctc-save-input[type=button]' ).first();
                        if ( $obj.length ) {
                            e.preventDefault();
                            //console.log( $obj.attr( 'id' ) );
                            if ( $obj.hasClass( 'ajax-pending' ) ) return false;
                            $obj.addClass( 'ajax-pending' );
                            self.save( $obj );
                            return false;
                        }
                    }
                } );
                $( '#ctc_main' ).on( 'click', '.ctc-selector-edit', function( e ) {
                    e.preventDefault();
                    if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
                    $( this ).addClass( 'ajax-pending' );
                    self.set_qsid( this );
                } );
                $( '#ctc_main' ).on( 'click', '.ctc-rewrite-toggle', function( e ) {
                    e.preventDefault();
                    self.selector_input_toggle( this );
                } );
                $( '#ctc_main' ).on( 'click', '#ctc_copy_selector', function( e ) {
                    var txt = $( '#ctc_sel_ovrd_selector_selected' ).text().trim();
                    if ( !self.is_empty( txt ) )
                        $( '#ctc_new_selectors' ).val( $( '#ctc_new_selectors' ).val() + "\n" + txt + " {\n\n}" );
                } );
                $( '#ctc_configtype' ).on( 'change', function( e ) {
                    var val = $( this ).val();
                    if ( self.is_empty( val ) || 'theme' == val ) {
                        $( '.ctc-theme-only, .ctc-themeonly-container' ).removeClass( 'ctc-disabled' );
                        $( '.ctc-theme-only, .ctc-themeonly-container input' ).prop( 'disabled', false );
                        try {
                            $( '#ctc_theme_parnt, #ctc_theme_child' ).themeMenu( 'enable' );
                        } catch ( exn ) {
                            self.jquery_exception( exn, 'Theme Menu' );
                        }
                    } else {
                        $( '.ctc-theme-only, .ctc-themeonly-container' ).addClass( 'ctc-disabled' );
                        $( '.ctc-theme-only, .ctc-themeonly-container input' ).prop( 'disabled', true );
                        try {
                            $( '#ctc_theme_parnt, #ctc_theme_child' ).themeMenu( 'disable' );
                        } catch ( exn ) {
                            self.jquery_exception( exn, 'Theme Menu' );
                        }
                    }
                } );    
                // these elements are not replaced so use direct selector events
                $( '.nav-tab' ).on( 'click', function( e ) {
                    e.preventDefault();
                    // clear the notice box
                    //set_notice( '' );
                    $( '.ctc-query-icon,.ctc-status-icon' ).remove();
                    var id = '#' + $( this ).attr( 'id' );
                    self.focus_panel( id );
                } );
                $( '.ctc-section-toggle' ).on( 'click', function( e ) {
                    e.preventDefault();
                    $( this ).parents( '.ctc-input-row, .update-nag' ).first().find( '.ctc-section-toggle' )
                        .each( function() { 
                            $( this ).toggleClass( 'open' );
                        } );
                    var id = $( this ).attr( 'id' ).replace(/\d$/, '') + '_content';
                    $( '#' + id ).stop().slideToggle( 'fast' );
                    return false;
                } );
                $( '#view_child_options, #view_parnt_options' ).on( 'click', function( e ){ 
                    if ( $( this ).hasClass( 'ajax-pending' ) ) return false;
                    $( this ).addClass( 'ajax-pending' );
                    self.css_preview( $( this ).attr( 'id' ) ); 
                } );
                $( '#ctc_load_form' ).on( 'submit', function() {
                    return ( self.validate() ); //&& confirm( self.getxt( 'load' ) ) ) ;
                } );
                $( '#ctc_query_selector_form' ).on( 'submit', function( e ) {
                    e.preventDefault();
                    $this = $( '#ctc_save_query_selector' );
                    if ( $this.hasClass( 'ajax-pending' ) ) return false;
                    $this.addClass( 'ajax-pending' );
                    self.save( $this ); // refresh menus after updating data
                    return false;
                } );
                $( '#ctc_rule_value_form' ).on( 'submit', function( e ) {
                    //console.log( 'rule value empty submit' );
                    e.preventDefault();
                    return false;
                } );
                $( '#ctc_theme_child, #ctc_theme_child-button, #ctc_child_type_existing' )
                    .on( 'focus click', function() {
                    // change the inputs to use existing child theme
                    $( '#ctc_child_type_existing' ).prop( 'checked', true );
                    $( '#ctc_child_type_new' ).prop( 'checked', false );
                    $( '#ctc_child_template' ).val( '' );
                    self.set_existing();
                } );
                $( '#ctc_duplicate_theme' ).on( 'click', function() {
                    if ( $( '#ctc_duplicate_theme' ).is( ':checked' ) ) {
                        $( '#ctc_child_name' ).val( self.testname );
                        $( '#ctc_duplicate_theme_slug' ).val( self.testslug );
                    } else {
                        self.set_existing();
                    }
                } );
                $( '#ctc_child_type_new, #ctc_child_template' ).on( 'focus click', function() {
                    // change the inputs to use new child theme
                    $( '#ctc_child_type_existing' ).prop( 'checked', false );
                    $( '#ctc_duplicate_theme' ).prop( 'checked', false );
                    $( '#ctc_duplicate_theme_slug' ).val( '' );
                    $( '#ctc_child_type_new' ).prop( 'checked', true );
                    $( '#input_row_duplicate_theme' ).hide();
                    $( '#ctc_child_name' ).val( self.testname );
                    $( '#ctc_child_template' ).val( self.testslug );
                } );
                $( '#ctc_is_debug' ).on( 'change', function( e ) {
                    self.save( this );
                } );
                $( '.ctc-live-preview' ).on( 'click', function( e ) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    document.location = $( this ).prop( 'href' );
                    return false;
                } );
                //console.log( 'loading autoselect menus...' )
                // initialize autoselect menus
                self.setup_menus();
                //console.log( 'checking for additional stylesheets...' )
                // mark additional linked stylesheets for parsing
                self.set_addl_css();
                // show last 25 selectors edited
    //                    render_recent();
                // turn on submit buttons (disabled until everything is loaded to prevent errors)
                //console.log( 'releasing submit buttons...' )
                $( 'input[type=submit], input[type=button]' ).prop( 'disabled', false );
                self.scrolltop();
                //console.log( 'Ready.' )
                // disappear any notices after 20 seconds
                setTimeout( self.fade_update_notice, 20000 );
            } else {
                //$( '.ctc-select' ).css( { 'visibility': 'visible' } ).show();
                self.jquery_notice( 'init' );
            }
        },
        // object properties
        testslug:       '',
        testname:       '',
        reload_menus:   false,
        current_query:  'base',
        current_qsid:   null,
        current_qsdata: {},
        jquery_err:     [],
        color_regx:     '\\s+(\\#[a-f0-9]{3,6}|rgba?\\([\\d., ]+?\\)|hsla?\\([\\d%., ]+?\\)|[a-z]+)',
        border_regx:    '(\\w+)(\\s+(\\w+))?',
        grad_regx:      '(\\w+)'

    };
    //console.log( 'creating new chldthmcfg object ...' );
    $.chldthmcfg.init();
} ( jQuery ) );
